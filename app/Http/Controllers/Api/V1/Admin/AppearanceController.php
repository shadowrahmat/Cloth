<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\SiteConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin "Site Customize": logo, announcement, header menu, footer, contact, SEO.
 */
class AppearanceController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SiteConfig::get(),
            'defaults' => SiteConfig::defaults(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $link = fn (string $prefix) => [
            "{$prefix}" => 'nullable|array|max:40',
            "{$prefix}.*.label" => 'required|string|max:120',
            "{$prefix}.*.url" => 'required|string|max:500',
        ];

        $validated = $request->validate(array_merge([
            'branding' => 'required|array',
            'branding.site_name' => 'required|string|max:120',
            'branding.tagline' => 'nullable|string|max:255',
            'branding.logo' => 'nullable|string|max:500',
            'branding.logo_light' => 'nullable|string|max:500',
            'branding.favicon' => 'nullable|string|max:500',

            'shop' => 'nullable|array',
            'shop.free_shipping_threshold' => 'nullable|numeric|min:0|max:1000000',

            'announcement' => 'required|array',
            'announcement.enabled' => 'boolean',
            'announcement.text' => 'nullable|string|max:300',
            'announcement.link_text' => 'nullable|string|max:80',
            'announcement.link_url' => 'nullable|string|max:500',
            'announcement.bg_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'announcement.text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            'header' => 'required|array',
            'header.sticky' => 'boolean',
            'header.show_search' => 'boolean',
            'header.show_account' => 'boolean',
            'header.show_wishlist' => 'boolean',
            'header.menu' => 'required|array|max:12',
            'header.menu.*.label' => 'required|string|max:80',
            'header.menu.*.url' => 'required|string|max:500',
            'header.menu.*.mega' => 'nullable|array|max:6',
            'header.menu.*.mega.*.title' => 'required|string|max:80',
            'header.menu.*.mega.*.links' => 'nullable|array|max:20',
            'header.menu.*.mega.*.links.*.label' => 'required|string|max:120',
            'header.menu.*.mega.*.links.*.url' => 'required|string|max:500',
            'header.mega_products' => 'nullable|array',
            'header.mega_products.title' => 'nullable|string|max:80',
            'header.mega_products.source' => 'nullable|in:new,bestseller,featured,none',
            'header.mega_products.count' => 'nullable|integer|min:2|max:4',
            'header.mega_promo' => 'nullable|array',
            'header.mega_promo.title' => 'nullable|string|max:120',
            'header.mega_promo.text' => 'nullable|string|max:400',
            'header.mega_promo.button_text' => 'nullable|string|max:80',
            'header.mega_promo.button_url' => 'nullable|string|max:500',
            'header.mega_promo.image' => 'nullable|string|max:500',

            'footer' => 'required|array',
            'footer.description' => 'nullable|string|max:600',
            'footer.columns' => 'nullable|array|max:5',
            'footer.columns.*.title' => 'required|string|max:80',
            'footer.payment_methods' => 'nullable|array|max:10',
            'footer.payment_methods.*' => 'string|max:60',
            'footer.copyright' => 'nullable|string|max:200',

            'contact' => 'required|array',
            'contact.address' => 'nullable|string|max:500',
            'contact.phone' => 'nullable|string|max:40',
            'contact.whatsapp' => 'nullable|string|max:40',
            'contact.email' => 'nullable|email|max:120',
            'contact.hours' => 'nullable|string|max:300',
            'contact.map_url' => ['nullable', 'string', 'max:1000', 'regex:/^https:\/\/(www\.)?google\.[a-z.]+\/maps/'],

            'social' => 'nullable|array|max:8',
            'social.*.platform' => 'required|in:facebook,instagram,tiktok,youtube,whatsapp,x,linkedin,pinterest',
            'social.*.url' => 'nullable|url|max:500',

            'pages' => 'nullable|array',
            'pages.about' => 'nullable|array',
            'pages.about.kicker' => 'nullable|string|max:80',
            'pages.about.title' => 'nullable|string|max:160',
            'pages.about.intro' => 'nullable|string|max:600',
            'pages.about.hero_image' => 'nullable|string|max:500',
            'pages.about.story_title' => 'nullable|string|max:160',
            'pages.about.story_text' => 'nullable|string|max:3000',
            'pages.about.story_image' => 'nullable|string|max:500',
            'pages.about.stats' => 'nullable|array|max:6',
            'pages.about.stats.*.value' => 'required|string|max:20',
            'pages.about.stats.*.label' => 'required|string|max:80',
            'pages.about.values_title' => 'nullable|string|max:120',
            'pages.about.values' => 'nullable|array|max:8',
            'pages.about.values.*.title' => 'required|string|max:80',
            'pages.about.values.*.text' => 'nullable|string|max:300',
            'pages.about.quote' => 'nullable|string|max:300',
            'pages.about.quote_author' => 'nullable|string|max:80',
            'pages.about.cta_title' => 'nullable|string|max:120',
            'pages.about.cta_text' => 'nullable|string|max:300',
            'pages.about.cta_button' => 'nullable|string|max:60',
            'pages.about.cta_url' => 'nullable|string|max:500',
            'pages.blog' => 'nullable|array',
            'pages.blog.kicker' => 'nullable|string|max:80',
            'pages.blog.title' => 'nullable|string|max:160',
            'pages.blog.intro' => 'nullable|string|max:400',
            'pages.contact' => 'nullable|array',
            'pages.contact.faq_title' => 'nullable|string|max:120',
            'pages.contact.faq' => 'nullable|array|max:20',
            'pages.contact.faq.*.q' => 'required|string|max:200',
            'pages.contact.faq.*.a' => 'required|string|max:1000',

            'size_charts' => 'nullable|array|max:12',
            'size_charts.*.name' => 'required|string|max:80',
            'size_charts.*.categories' => 'nullable|array|max:50',
            'size_charts.*.categories.*' => 'string|max:120',
            'size_charts.*.note' => 'nullable|string|max:400',
            'size_charts.*.columns' => 'required|array|min:2|max:8',
            'size_charts.*.columns.*' => 'required|string|max:30',
            'size_charts.*.rows' => 'nullable|array|max:30',
            'size_charts.*.rows.*' => 'array|max:8',
            'size_charts.*.rows.*.*' => 'nullable|string|max:30',

            'seo' => 'required|array',
            'seo.title' => 'nullable|string|max:120',
            'seo.description' => 'nullable|string|max:300',
        ], $link('footer.columns.*.links')), [
            'contact.map_url.regex' => 'ম্যাপের লিংক Google Maps এর embed লিংক হতে হবে (https://www.google.com/maps/embed?...)।',
            'size_charts.*.name.required' => 'প্রতিটি সাইজ চার্টের একটা নাম দিন।',
            'size_charts.*.columns.min' => 'সাইজ চার্টে অন্তত ২টা কলাম লাগবে।',
            'size_charts.*.columns.*.required' => 'সাইজ চার্টের কোনো কলামের নাম খালি রাখা যাবে না।',
            'size_charts.*.rows.max' => 'একটা সাইজ চার্টে সর্বোচ্চ ৩০টা সারি রাখা যায়।',
        ]);

        $config = SiteConfig::save($this->relativizeOwnUrls($validated, $request));

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update_appearance',
            'description' => 'Updated site appearance (logo, header, footer, contact)',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true, 'message' => 'সাইট আপডেট হয়েছে', 'data' => $config]);
    }

    /**
     * The media picker returns absolute URLs (http://this-host/storage/...).
     * Store links to this site as relative paths so they survive a move from
     * localhost / IP to a real domain.
     */
    private function relativizeOwnUrls(array $data, Request $request): array
    {
        $hosts = array_unique(array_filter([
            rtrim($request->getSchemeAndHttpHost(), '/'),
            rtrim((string) config('app.url'), '/'),
        ]));

        array_walk_recursive($data, function (&$value) use ($hosts) {
            if (! is_string($value)) {
                return;
            }
            foreach ($hosts as $host) {
                if ($value === $host) {
                    $value = '/';
                } elseif (str_starts_with($value, $host.'/')) {
                    $value = substr($value, strlen($host));
                }
            }
        });

        return $data;
    }

    public function reset(Request $request): JsonResponse
    {
        $config = SiteConfig::reset();

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'reset_appearance',
            'description' => 'Reset site appearance to defaults',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true, 'data' => $config]);
    }
}
