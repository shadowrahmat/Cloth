<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Services\StoreMailer;
use App\Support\EmailTemplateDefaults;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function __construct(private StoreMailer $mailer) {}

    /** Templates with their purpose, the variables they may use, mail status and recent send counts. */
    public function index(): JsonResponse
    {
        $meta = EmailTemplateDefaults::all();
        $lastSent = EmailLog::where('is_test', false)->selectRaw('template_key, max(created_at) as last_at, count(*) as total')
            ->groupBy('template_key')->get()->keyBy('template_key');

        $templates = $this->mailer->templates()->map(fn (EmailTemplate $t, $key) => [
            'key' => $key,
            'name' => $meta[$key]['name'],
            'audience' => $meta[$key]['audience'],
            'trigger' => $meta[$key]['trigger'],
            'subject' => $t->subject,
            'heading' => $t->heading,
            'body' => $t->body,
            'button_label' => $t->button_label,
            'button_url' => $t->button_url,
            'is_active' => $t->is_active,
            'is_customized' => $this->isCustomized($t, $meta[$key]),
            'sent_count' => (int) ($lastSent[$key]->total ?? 0),
            'last_sent_at' => $lastSent[$key]->last_at ?? null,
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'templates' => $templates,
                'variables' => collect(EmailTemplateDefaults::VARIABLES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
                'status' => $this->mailer->status(),
                'admin_recipients' => $this->mailer->adminRecipients(),
            ],
        ]);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $template = $this->findOrFail($key);
        $data = $request->validate($this->rules(), $this->messages());
        $template->update($data);
        $this->audit($request, "Updated email template '{$key}'");

        return response()->json(['success' => true, 'message' => 'টেমপ্লেট সেভ হয়েছে', 'data' => $template->fresh()]);
    }

    /** Render (possibly unsaved) wording with real-looking data, for the live preview. */
    public function preview(Request $request, string $key): JsonResponse
    {
        $template = $this->findOrFail($key);
        $data = $request->validate($this->rules(false), $this->messages());
        $rendered = $this->mailer->render(array_merge($template->only(['subject', 'heading', 'body', 'button_label', 'button_url']), $data), $this->mailer->sampleVariables());

        return response()->json(['success' => true, 'data' => $rendered]);
    }

    public function test(Request $request, string $key): JsonResponse
    {
        $template = $this->findOrFail($key);
        $request->validate(['email' => 'required|email'] + $this->rules(false), ['email.required' => 'যে ইমেইলে পাঠাবেন সেটি দিন।', 'email.email' => 'সঠিক ইমেইল ঠিকানা দিন।'] + $this->messages());

        $override = array_filter($request->only(['subject', 'heading', 'body', 'button_label', 'button_url']), fn ($v) => $v !== null);
        $log = $this->mailer->send($template->key, $request->input('email'), $this->mailer->sampleVariables(), null, true, $override);

        if (! $log || $log->status === 'failed') {
            return response()->json([
                'success' => false,
                'message' => 'ইমেইল পাঠানো যায়নি' . ($log?->error ? ': ' . $log->error : ''),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $log->status === 'sent'
                ? "টেস্ট ইমেইল {$log->to_email} ঠিকানায় পাঠানো হয়েছে"
                : 'ইমেইল চালু না থাকায় শুধু লগে লেখা হয়েছে — ইনবক্সে যাবে না',
            'data' => $log,
        ]);
    }

    public function reset(Request $request, string $key): JsonResponse
    {
        $template = $this->findOrFail($key);
        $d = EmailTemplateDefaults::all()[$key];
        $template->update(collect($d)->only(['subject', 'heading', 'body', 'button_label', 'button_url'])->all());
        $this->audit($request, "Reset email template '{$key}' to default");

        return response()->json(['success' => true, 'message' => 'আগের লেখায় ফেরানো হয়েছে', 'data' => $template->fresh()]);
    }

    public function settings(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->mailer->settings() + ['status' => $this->mailer->status()]]);
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'from_name' => 'nullable|string|max:80',
            'from_address' => 'nullable|required_if:enabled,true|email|max:150',
            'admin_emails' => 'nullable|string|max:500',
            'smtp_host' => 'nullable|required_if:enabled,true|string|max:150',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'smtp_username' => 'nullable|string|max:150',
            'smtp_password' => 'nullable|string|max:255',
        ], [
            'from_address.required_if' => 'ইমেইল চালু করতে “যে ঠিকানা থেকে যাবে” দিন।',
            'from_address.email' => 'সঠিক ইমেইল ঠিকানা দিন।',
            'smtp_host.required_if' => 'ইমেইল চালু করতে SMTP হোস্ট দিন।',
        ]);

        foreach (preg_split('/[\s,;]+/', (string) ($data['admin_emails'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $address) {
            if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['message' => "“{$address}” সঠিক ইমেইল না।", 'errors' => ['admin_emails' => ["“{$address}” সঠিক ইমেইল না।"]]], 422);
            }
        }

        $this->mailer->saveSettings($data);
        $this->audit($request, 'Updated email (SMTP) settings');

        return response()->json([
            'success' => true,
            'message' => 'ইমেইল সেটিং সেভ হয়েছে',
            'data' => $this->mailer->settings() + ['status' => $this->mailer->status()],
        ]);
    }

    public function logs(Request $request): JsonResponse
    {
        $query = EmailLog::with('order:id,order_number')->latest('id');
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = trim((string) $request->input('search'))) {
            $query->where(fn ($q) => $q->where('to_email', 'like', "%{$search}%")->orWhere('subject', 'like', "%{$search}%"));
        }
        $page = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $page->items(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }

    // ----------------------------------------------------------------------

    private function findOrFail(string $key): EmailTemplate
    {
        $template = $this->mailer->template($key);
        abort_unless($template, 404, 'এই টেমপ্লেট পাওয়া যায়নি');

        return $template;
    }

    private function rules(bool $full = true): array
    {
        $req = $full ? 'required' : 'sometimes|required';

        return [
            'subject' => "{$req}|string|max:200",
            'heading' => 'nullable|string|max:200',
            'body' => "{$req}|string|max:5000",
            'button_label' => 'nullable|string|max:80',
            'button_url' => 'nullable|string|max:255',
            'is_active' => $full ? 'required|boolean' : 'sometimes|boolean',
        ];
    }

    private function messages(): array
    {
        return [
            'subject.required' => 'বিষয় (Subject) খালি রাখা যাবে না।',
            'body.required' => 'ইমেইলের লেখা খালি রাখা যাবে না।',
            'body.max' => 'লেখা ৫০০০ অক্ষরের মধ্যে রাখুন।',
        ];
    }

    private function isCustomized(EmailTemplate $t, array $d): bool
    {
        foreach (['subject', 'heading', 'body', 'button_label', 'button_url'] as $f) {
            if ((string) $t->{$f} !== (string) ($d[$f] ?? '')) {
                return true;
            }
        }

        return false;
    }

    private function audit(Request $request, string $description): void
    {
        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update_settings',
            'model_type' => EmailTemplate::class,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
