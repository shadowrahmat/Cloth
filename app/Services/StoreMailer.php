<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Support\EmailTemplateDefaults;
use App\Support\SiteConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Renders the admin-editable email templates and sends them.
 *
 * Sending never throws: a broken SMTP setup must not stop a customer from
 * placing an order. Every attempt is written to email_logs instead.
 */
class StoreMailer
{
    /** Settings rows owned by the email setup screen. */
    public const SETTING_KEYS = [
        'mail_enabled', 'mail_from_name', 'mail_from_address', 'mail_admin_emails',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password',
    ];

    // ------------------------------------------------------------ templates

    /** All templates keyed by key; rows missing from the database are created from the defaults. */
    public function templates(): Collection
    {
        $defaults = EmailTemplateDefaults::all();
        $rows = EmailTemplate::all()->keyBy('key');

        foreach ($defaults as $key => $d) {
            if (! $rows->has($key)) {
                $rows[$key] = EmailTemplate::create([
                    'key' => $key,
                    'subject' => $d['subject'],
                    'heading' => $d['heading'],
                    'body' => $d['body'],
                    'button_label' => $d['button_label'],
                    'button_url' => $d['button_url'],
                    'is_active' => true,
                ]);
            }
        }

        return collect($defaults)->keys()->mapWithKeys(fn ($k) => [$k => $rows[$k]]);
    }

    public function template(string $key): ?EmailTemplate
    {
        return $this->templates()->get($key);
    }

    // ------------------------------------------------------------- settings

    public function settings(): array
    {
        $raw = SiteSetting::whereIn('key', self::SETTING_KEYS)->pluck('value', 'key');

        return [
            'enabled' => filter_var($raw['mail_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'from_name' => $raw['mail_from_name'] ?? SiteConfig::get()['branding']['site_name'] ?? config('app.name'),
            'from_address' => $raw['mail_from_address'] ?? '',
            'admin_emails' => $raw['mail_admin_emails'] ?? '',
            'smtp_host' => $raw['smtp_host'] ?? '',
            'smtp_port' => (int) ($raw['smtp_port'] ?? 587) ?: 587,
            'smtp_encryption' => ($raw['smtp_encryption'] ?? '') === 'ssl' ? 'ssl' : 'tls',
            'smtp_username' => $raw['smtp_username'] ?? '',
            'has_password' => ! empty($raw['smtp_password']),
        ];
    }

    public function saveSettings(array $data): void
    {
        $map = [
            'enabled' => 'mail_enabled', 'from_name' => 'mail_from_name', 'from_address' => 'mail_from_address',
            'admin_emails' => 'mail_admin_emails', 'smtp_host' => 'smtp_host', 'smtp_port' => 'smtp_port',
            'smtp_encryption' => 'smtp_encryption', 'smtp_username' => 'smtp_username',
        ];
        foreach ($map as $field => $key) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                SiteSetting::set($key, is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? ''), 'mail', is_bool($value) ? 'boolean' : 'text');
            }
        }
        // The password is write-only: an empty field keeps the saved one.
        if (! empty($data['smtp_password'])) {
            SiteSetting::set('smtp_password', Crypt::encryptString($data['smtp_password']), 'mail');
        }
    }

    /** Is email actually leaving the server, and if not, why. */
    public function status(): array
    {
        $s = $this->settings();
        if ($s['enabled'] && $s['smtp_host'] && $s['from_address']) {
            return ['ready' => true, 'mode' => 'smtp', 'message' => "ইমেইল চালু আছে ({$s['smtp_host']} দিয়ে পাঠানো হচ্ছে)।"];
        }
        $default = config('mail.default');
        if (! in_array($default, ['log', 'array'], true)) {
            return ['ready' => true, 'mode' => $default, 'message' => 'ইমেইল সার্ভারের .env সেটিং দিয়ে পাঠানো হচ্ছে।'];
        }
        if ($s['enabled']) {
            return ['ready' => false, 'mode' => 'log', 'message' => 'SMTP হোস্ট ও “যে ঠিকানা থেকে যাবে” দুটোই দিন, তারপর ইমেইল যাবে।'];
        }

        return ['ready' => false, 'mode' => 'log', 'message' => 'ইমেইল এখনো চালু হয়নি। নিচে SMTP তথ্য দিয়ে চালু করুন। ততক্ষণ ইমেইল শুধু লগে লেখা থাকবে, গ্রাহকের কাছে যাবে না।'];
    }

    /** Point Laravel's mailer at the SMTP account saved in the admin. */
    private function applyTransport(): void
    {
        $s = $this->settings();
        if (! ($s['enabled'] && $s['smtp_host'] && $s['from_address'])) {
            return;
        }

        $password = (string) SiteSetting::where('key', 'smtp_password')->value('value');
        try {
            $password = $password !== '' ? Crypt::decryptString($password) : '';
        } catch (Throwable) {
            // saved before passwords were encrypted: use as-is
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.scheme' => $s['smtp_encryption'] === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => $s['smtp_host'],
            'mail.mailers.smtp.port' => $s['smtp_port'],
            'mail.mailers.smtp.username' => $s['smtp_username'] ?: null,
            'mail.mailers.smtp.password' => $password ?: null,
            'mail.mailers.smtp.timeout' => 15,
            'mail.from.address' => $s['from_address'],
            'mail.from.name' => $s['from_name'],
        ]);
        app('mail.manager')->purge('smtp');
    }

    public function adminRecipients(): array
    {
        $list = preg_split('/[\s,;]+/', (string) $this->settings()['admin_emails'], -1, PREG_SPLIT_NO_EMPTY);
        $list = array_values(array_filter($list, fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
        if (! $list) {
            $fallback = SiteConfig::get()['contact']['email'] ?? null;
            if ($fallback && filter_var($fallback, FILTER_VALIDATE_EMAIL)) {
                $list = [$fallback];
            }
        }

        return $list;
    }

    // ------------------------------------------------------------ variables

    public function storeVariables(): array
    {
        $site = SiteConfig::get();
        $url = rtrim((string) config('app.url'), '/');

        return [
            'store_name' => $site['branding']['site_name'] ?? config('app.name'),
            'store_phone' => $site['contact']['phone'] ?? '',
            'store_email' => $site['contact']['email'] ?? '',
            'store_url' => $url,
            '__logo' => $this->absolute($site['branding']['logo'] ?? '', $url),
            '__address' => $site['contact']['address'] ?? '',
        ];
    }

    public function orderVariables(Order $order): array
    {
        $order->loadMissing('items');
        $store = $this->storeVariables();
        $a = (array) $order->shipping_address;
        $address = implode("\n", array_filter([
            trim(($a['address_line_1'] ?? '') . (empty($a['address_line_2']) ? '' : ', ' . $a['address_line_2'])),
            implode(', ', array_filter([$a['city'] ?? '', $a['state'] ?? '', $a['postal_code'] ?? ''])),
            $a['phone'] ?? $order->customer_phone,
        ]));

        return $store + [
            'customer_name' => $order->customer_name ?: 'গ্রাহক',
            'customer_phone' => (string) $order->customer_phone,
            'customer_email' => (string) $order->customer_email,
            'order_number' => $order->order_number,
            'order_date' => self::bnDate($order->created_at ?? now()),
            'order_total' => self::money($order->total),
            'subtotal' => self::money($order->subtotal),
            'delivery_charge' => (float) $order->shipping_amount > 0 ? self::money($order->shipping_amount) : 'ফ্রি',
            'discount' => self::money($order->discount_amount),
            'payment_method' => ['cod' => 'ক্যাশ অন ডেলিভারি', 'bkash' => 'বিকাশ', 'nagad' => 'নগদ'][$order->payment_method] ?? (string) $order->payment_method,
            'shipping_method' => (string) $order->shipping_method,
            'shipping_address' => $address,
            'tracking_number' => $order->tracking_number ?: '—',
            'track_url' => $store['store_url'] . '/track-order?' . http_build_query(['order' => $order->order_number, 'email' => $order->customer_email]),
            'admin_order_url' => $store['store_url'] . '/admin/orders?' . http_build_query(['search' => $order->order_number]),
            '__items' => $order->items->map(fn ($i) => [
                'name' => $i->product_name,
                'meta' => implode(' · ', array_filter([$i->size ? 'সাইজ ' . $i->size : null, $i->color])),
                'qty' => (int) $i->quantity,
                'price' => self::money($i->price),
                'total' => self::money($i->subtotal),
            ])->all(),
            '__totals' => array_filter([
                ['পণ্যের দাম', self::money($order->subtotal)],
                (float) $order->discount_amount > 0 ? ['ডিসকাউন্ট', '− ' . self::money($order->discount_amount)] : null,
                ['ডেলিভারি চার্জ', (float) $order->shipping_amount > 0 ? self::money($order->shipping_amount) : 'ফ্রি'],
                ['সর্বমোট', self::money($order->total), true],
            ]),
        ];
    }

    /** Example data for the preview and test emails (uses the latest real order when there is one). */
    public function sampleVariables(): array
    {
        $order = Order::with('items')->latest('id')->first();
        if ($order) {
            return $this->orderVariables($order);
        }

        $store = $this->storeVariables();

        return $store + [
            'customer_name' => 'রহিম উদ্দিন', 'customer_phone' => '01700000000', 'customer_email' => 'customer@example.com',
            'order_number' => 'ORD-20261120-AB12C', 'order_date' => self::bnDate(now()),
            'order_total' => self::money(2060), 'subtotal' => self::money(2000), 'delivery_charge' => self::money(60),
            'discount' => self::money(0), 'payment_method' => 'ক্যাশ অন ডেলিভারি', 'shipping_method' => 'ঢাকার ভেতরে',
            'shipping_address' => "হাউজ ১, রোড ২, ধানমন্ডি\nঢাকা\n01700000000", 'tracking_number' => 'SF-12345678',
            'track_url' => $store['store_url'] . '/track-order', 'admin_order_url' => $store['store_url'] . '/admin/orders',
            '__items' => [['name' => 'ক্লাসিক পাঞ্জাবি', 'meta' => 'সাইজ M · সাদা', 'qty' => 2, 'price' => self::money(1000), 'total' => self::money(2000)]],
            '__totals' => [['পণ্যের দাম', self::money(2000)], ['ডেলিভারি চার্জ', self::money(60)], ['সর্বমোট', self::money(2060), true]],
        ];
    }

    // ------------------------------------------------------------ rendering

    /** @param array{subject:string,heading:?string,body:string,button_label:?string,button_url:?string} $tpl */
    public function render(array $tpl, array $vars): array
    {
        $subject = $this->fill((string) $tpl['subject'], $vars, false);
        $buttonUrl = $this->fill((string) ($tpl['button_url'] ?? ''), $vars, false);

        $html = view('emails.store', [
            'subject' => $subject,
            'heading' => $this->fill((string) ($tpl['heading'] ?? ''), $vars, false),
            'bodyHtml' => $this->bodyHtml((string) $tpl['body'], $vars),
            'buttonLabel' => trim((string) ($tpl['button_label'] ?? '')),
            'buttonUrl' => preg_match('#^https?://#i', $buttonUrl) ? $buttonUrl : '',
            'store' => $vars,
        ])->render();

        return ['subject' => $subject, 'html' => $html];
    }

    private function bodyHtml(string $body, array $vars): string
    {
        $body = preg_replace('/[ \t]*\{\{\s*order_items\s*\}\}[ \t]*/u', "\n\n{{order_items}}\n\n", str_replace("\r\n", "\n", $body));
        $html = '';
        foreach (preg_split("/\n\s*\n/u", trim($body)) as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            if ($block === '{{order_items}}') {
                $html .= view('emails.partials.items', ['items' => $vars['__items'] ?? [], 'totals' => $vars['__totals'] ?? []])->render();
                continue;
            }
            $p = $this->fill(e($block), $vars, true);
            $p = preg_replace('/\*\*(.+?)\*\*/us', '<strong style="color:#111111">$1</strong>', $p);
            $html .= '<p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#333333">' . nl2br($p, false) . '</p>';
        }

        return $html;
    }

    private function fill(string $text, array $vars, bool $escape): string
    {
        return preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/', function ($m) use ($vars, $escape) {
            if (! array_key_exists($m[1], EmailTemplateDefaults::VARIABLES) || ! array_key_exists($m[1], $vars)) {
                return $m[0];
            }
            $value = (string) $vars[$m[1]];

            return $escape ? e($value) : $value;
        }, $text);
    }

    // -------------------------------------------------------------- sending

    /**
     * Send one template. Inactive templates are skipped unless $force (test sends).
     * $override lets the admin test unsaved wording.
     */
    public function send(string $key, string $to, array $vars, ?int $orderId = null, bool $isTest = false, ?array $override = null): ?EmailLog
    {
        $tpl = $this->template($key);
        if (! $tpl || (! $tpl->is_active && ! $isTest) || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $data = array_merge($tpl->only(['subject', 'heading', 'body', 'button_label', 'button_url']), $override ?? []);
        $subject = $key;

        try {
            $rendered = $this->render($data, $vars);
            $subject = ($isTest ? '[টেস্ট] ' : '') . $rendered['subject'];
            $this->applyTransport();
            Mail::html($rendered['html'], fn ($m) => $m->to($to)->subject($subject));
            $status = $this->status()['ready'] ? 'sent' : 'logged';
            $error = null;
        } catch (Throwable $e) {
            report($e);
            $status = 'failed';
            $error = mb_substr($e->getMessage(), 0, 1000);
        }

        return EmailLog::create([
            'template_key' => $key, 'to_email' => $to, 'subject' => mb_substr($subject, 0, 250),
            'status' => $status, 'error' => $error, 'order_id' => $orderId, 'is_test' => $isTest,
        ]);
    }

    /** Send an order email to whoever it is meant for (the buyer, or the shop's notification address). */
    public function sendForOrder(string $key, int $orderId): void
    {
        $order = Order::with('items')->find($orderId);
        $meta = EmailTemplateDefaults::all()[$key] ?? null;
        if (! $order || ! $meta) {
            return;
        }

        $vars = $this->orderVariables($order);
        $to = $meta['audience'] === 'admin' ? $this->adminRecipients() : [$order->customer_email];
        foreach ($to as $address) {
            $this->send($key, (string) $address, $vars, $order->id);
        }
    }

    /** Queue an order email to go out after the HTTP response, so checkout is never slowed down by SMTP. */
    public static function afterResponse(string $key, int $orderId): void
    {
        dispatch(fn () => app(self::class)->sendForOrder($key, $orderId))->afterResponse();
    }

    // -------------------------------------------------------------- helpers

    public static function money($amount): string
    {
        return '৳' . self::bnDigits(number_format((float) $amount, (float) $amount == floor((float) $amount) ? 0 : 2));
    }

    public static function bnDigits(string $s): string
    {
        return strtr($s, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }

    public static function bnDate($date): string
    {
        $months = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        $d = \Illuminate\Support\Carbon::parse($date);

        return self::bnDigits((string) $d->day) . ' ' . $months[$d->month - 1] . ' ' . self::bnDigits((string) $d->year);
    }

    private function absolute(string $path, string $base): string
    {
        if ($path === '' || preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return $base . '/' . ltrim($path, '/');
    }
}
