<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Storefront appearance managed from the admin "Site Customize" page:
 * branding (logos), announcement bar, header menu (incl. mega menu),
 * footer, contact details, social links and SEO.
 *
 * Stored as one JSON setting (`appearance`). Anything not saved yet falls
 * back to defaults(), which mirror the storefront's original content.
 */
class SiteConfig
{
    public const KEY = 'appearance';

    private const CACHE_KEY = 'site_config.appearance';

    public static function defaults(): array
    {
        $cat = fn (string $label, string $slug) => ['label' => $label, 'url' => "/category/{$slug}"];

        return [
            'branding' => [
                'site_name' => 'DRAWER CLOTHING',
                'tagline' => 'সমসাময়িক লাইফস্টাইল ও প্রিমিয়াম পোশাক',
                'logo' => '/images/logo-transparent.png',
                'logo_light' => '/images/logo-white.png',
                'favicon' => '/images/logo-transparent.png',
            ],
            // Cart drawer: "add ৳X more for free delivery" progress bar (0 = hide)
            'shop' => [
                'free_shipping_threshold' => 2500,
            ],
            'announcement' => [
                'enabled' => true,
                'text' => 'সারাদেশে ২৫০০ টাকার বেশি অর্ডারে ফ্রি ডেলিভারি • সহজ ৭ দিনের সাইজ এক্সচেঞ্জ সুবিধা',
                'link_text' => 'কালেকশন দেখুন',
                'link_url' => '/collections',
                'bg_color' => '#111111',
                'text_color' => '#FFFFFF',
            ],
            'header' => [
                'sticky' => true,
                'show_search' => true,
                'show_account' => true,
                'show_wishlist' => true,
                'menu' => [
                    ['label' => 'হোম', 'url' => '/'],
                    ['label' => 'আমাদের সম্পর্কে', 'url' => '/about'],
                    [
                        'label' => 'শপ',
                        'url' => '/collections',
                        'mega' => [
                            ['title' => 'টি-শার্ট ও স্ট্রিটওয়্যার', 'links' => [
                                $cat('টি-শার্ট', 't-shirt'), $cat('পোলো টি-শার্ট', 'polo-t-shirt'),
                                $cat('ড্রপ শোল্ডার', 'drop-shoulder'), $cat('বক্সি ফিট', 'boxy'),
                                $cat('স্লিভলেস টি-শার্ট', 'sleeveless-t-shirt'),
                            ]],
                            ['title' => 'শার্ট ও পাঞ্জাবি', 'links' => [
                                $cat('পাঞ্জাবি', 'panjabi'), $cat('ক্যাজুয়াল শার্ট', 'casual-shirt'),
                                $cat('হাফ হাতা শার্ট', 'short-sleeve-shirt'), $cat('ফুল হাতা শার্ট', 'full-sleeves-shirt'),
                            ]],
                            ['title' => 'প্যান্ট ও ট্রাউজার', 'links' => [
                                $cat('ট্রাউজার', 'trouser'), $cat('জিন্স প্যান্ট', 'jeans-pant'),
                                $cat('ব্যাগী প্যান্ট', 'baggy-pants'), $cat('২ কোয়ার্টার প্যান্ট', '2-quarter-pants'),
                            ]],
                            ['title' => 'লেডিস কালেকশন', 'links' => [
                                $cat('লেডিস টপস', 'ladies-tops'), $cat('লেডিস প্যান্ট', 'ladies-pants'),
                                $cat('বেল-বটম প্যান্ট', 'ladies-bell-bottom-pants'),
                            ]],
                        ],
                    ],
                    ['label' => 'ব্লগ', 'url' => '/blog'],
                    ['label' => 'যোগাযোগ', 'url' => '/contact'],
                ],
                // Product photos shown inside the mega menu: new | bestseller | featured | none
                'mega_products' => [
                    'title' => 'নতুন এসেছে',
                    'source' => 'new',
                    'count' => 3,
                ],
                // Optional text card, only used when mega_products is "none"
                'mega_promo' => [
                    'title' => 'সব পোশাক এক জায়গায়',
                    'text' => 'উৎসবের পাঞ্জাবি থেকে শুরু করে ড্রপ শোল্ডার টি-শার্ট, আরামদায়ক ট্রাউজার ও লেডিস কালেকশন।',
                    'button_text' => 'সব পোশাক দেখুন',
                    'button_url' => '/collections',
                    'image' => '',
                ],
            ],
            'footer' => [
                'description' => 'সমসাময়িক লাইফস্টাইল, আধুনিক মার্জিত টেইলারিং এবং দীর্ঘস্থায়ী আরামের উদ্দেশ্যে তৈরি প্রিমিয়াম পোশাক।',
                'columns' => [
                    ['title' => 'গ্রাহক সহায়তা', 'links' => [
                        ['label' => 'যোগাযোগ করুন', 'url' => '/contact'],
                        ['label' => 'অর্ডার ট্র্যাক করুন', 'url' => '/track-order'],
                        ['label' => 'শিপিং ও রিটার্ন পলিসি', 'url' => '/pages/shipping-returns'],
                        ['label' => 'সাইজ গাইড', 'url' => '/pages/size-guide'],
                        ['label' => 'গ্রাহক অ্যাকাউন্ট', 'url' => '/account'],
                    ]],
                    ['title' => 'ক্যাটাগরি', 'links' => [
                        $cat('পাঞ্জাবি কালেকশন', 'panjabi'), $cat('ড্রপ শোল্ডার টি-শার্ট', 'drop-shoulder'),
                        $cat('বক্সি ফিট টি-শার্ট', 'boxy'), $cat('ব্যাগী প্যান্ট ও ট্রাউজার', 'baggy-pants'),
                        ['label' => 'নতুন সিজন রিলিজ', 'url' => '/collection/new-arrivals'],
                    ]],
                    ['title' => 'আমাদের পরিচয়', 'links' => [
                        ['label' => 'আমাদের গল্প ও দর্শন', 'url' => '/about'],
                        ['label' => 'ব্লগ ও স্টাইল গাইড', 'url' => '/blog'],
                        ['label' => 'সব প্রোডাক্ট', 'url' => '/collections'],
                    ]],
                ],
                'payment_methods' => ['বিকাশ (bKash)', 'নগদ (Nagad)', 'ভিসা / মাস্টারকার্ড', 'ক্যাশ অন ডেলিভারি'],
                'copyright' => '© {year} DRAWER CLOTHING. সর্বস্বত্ব সংরক্ষিত।',
            ],
            'contact' => [
                'address' => "হাউজ ৪২, রোড ১১\nগুলশান অ্যাভিনিউ, ঢাকা ১২১২\nবাংলাদেশ",
                'phone' => '+880 1700 000000',
                'whatsapp' => '',
                'email' => 'support@drawerclothing.com',
                'hours' => "শনিবার – বৃহস্পতিবার: সকাল ১০টা – রাত ৮টা\nশুক্রবার: দুপুর ২টা – রাত ৮টা",
                'map_url' => '',
            ],
            'social' => [
                ['platform' => 'facebook', 'url' => ''],
                ['platform' => 'instagram', 'url' => ''],
                ['platform' => 'tiktok', 'url' => ''],
                ['platform' => 'youtube', 'url' => ''],
            ],
            // Content of the About / Blog / Contact pages (Admin → সাইট কাস্টমাইজ → পেজ কনটেন্ট)
            'pages' => [
                'about' => [
                    'kicker' => 'আমাদের গল্প',
                    'title' => 'রুচিশীল পোশাক, প্রতিদিনের জন্য',
                    'intro' => 'DRAWER CLOTHING-এর যাত্রা শুরু হয়েছে ক্ষণস্থায়ী ট্রেন্ডের ঊর্ধ্বে গিয়ে দীর্ঘস্থায়ী, আরামদায়ক ও রুচিশীল পোশাক সবার কাছে পৌঁছে দেওয়ার অঙ্গীকার নিয়ে।',
                    'hero_image' => '/storage/uploads/forest-green-old-money-winter-outfit-elegant-quiet-luxury-style-1794824526-wXXW.jpg',
                    'story_title' => 'পরিমিত ডিজাইন, আধুনিক কাট',
                    'story_text' => "আমাদের প্রতিটি কালেকশন আধুনিক জীবনযাত্রা, স্বাচ্ছন্দ্য এবং আত্মবিশ্বাসী ব্যক্তিত্বের কথা মাথায় রেখে তৈরি।\n\nসুতা বাছাই থেকে শুরু করে প্রতিটি সেলাই, বোতাম ও ফিনিশিংয়ে আমরা আন্তর্জাতিক মান বজায় রাখি — টি-শার্ট, পাঞ্জাবি, শার্ট কিংবা ট্রাউজার, প্রতিটি পোশাকেই প্রিমিয়াম স্পর্শ।",
                    'story_image' => '',
                    'stats' => [
                        ['value' => '১০০%', 'label' => 'প্রিমিয়াম কম্বড কটন'],
                        ['value' => '৬৪', 'label' => 'জেলায় হোম ডেলিভারি'],
                        ['value' => '৭ দিন', 'label' => 'সহজ সাইজ এক্সচেঞ্জ'],
                        ['value' => '২৪/৭', 'label' => 'অনলাইন অর্ডার'],
                    ],
                    'values_title' => 'যে কারণে আমরা আলাদা',
                    'values' => [
                        ['title' => 'সেরা মানের ফেব্রিক', 'text' => 'খাঁটি কম্বড সুতা ও ব্রিদেবল ফেব্রিক — সহজে রং হারায় না, অনেকদিন টেকে।'],
                        ['title' => 'নিখুঁত ফিটিং', 'text' => 'অভিজ্ঞ কারিগরদের কাটিং ও সেলাইয়ে তৈরি আরামদায়ক আধুনিক সিলুয়েট।'],
                        ['title' => 'সাশ্রয়ী দাম', 'text' => 'প্রিমিয়াম মান, তবুও সবার নাগালে — কোনো লুকানো খরচ নেই।'],
                        ['title' => 'ঝামেলাহীন এক্সচেঞ্জ', 'text' => 'সাইজ না মিললে ৭ দিনের মধ্যে সহজেই বদলে নিন।'],
                    ],
                    'quote' => 'ভালো পোশাক শুধু পরার জিনিস নয় — এটা আত্মবিশ্বাসের প্রকাশ।',
                    'quote_author' => 'DRAWER CLOTHING',
                    'cta_title' => 'আপনার পছন্দের পোশাক খুঁজে নিন',
                    'cta_text' => 'নতুন কালেকশন, জনপ্রিয় পোশাক ও বিশেষ অফার — সব এক জায়গায়।',
                    'cta_button' => 'শপে যান',
                    'cta_url' => '/collections',
                ],
                'blog' => [
                    'kicker' => 'ব্লগ',
                    'title' => 'স্টাইল টিপস, গাইড ও নতুন খবর',
                    'intro' => 'কোন পোশাকের সাথে কী মানায়, কাপড়ের যত্ন, নতুন কালেকশনের গল্প — সব এখানে।',
                ],
                'contact' => [
                    'faq_title' => 'সচরাচর জিজ্ঞাসা',
                    'faq' => [
                        ['q' => 'ডেলিভারি পেতে কত দিন লাগে?', 'a' => 'ঢাকার ভেতরে সাধারণত ১–২ দিন, ঢাকার বাইরে ২–৪ দিনের মধ্যে পৌঁছে যায়।'],
                        ['q' => 'ডেলিভারি চার্জ কত?', 'a' => 'ঢাকার ভেতরে ও বাইরে চার্জ চেকআউটে দেখানো হয়। ২৫০০ টাকার বেশি অর্ডারে ডেলিভারি ফ্রি।'],
                        ['q' => 'সাইজ না মিললে কী করব?', 'a' => 'পণ্য হাতে পাওয়ার ৭ দিনের মধ্যে অ্যাকাউন্ট পেজ থেকে বা আমাদের ফোন করে সহজেই এক্সচেঞ্জ করতে পারবেন।'],
                        ['q' => 'কীভাবে পেমেন্ট করব?', 'a' => 'ক্যাশ অন ডেলিভারি, বিকাশ, নগদ ও কার্ড — যেটা সুবিধা।'],
                        ['q' => 'অর্ডার কোথায় আছে কীভাবে জানব?', 'a' => '“অর্ডার ট্র্যাক করুন” পেজে অর্ডার নম্বর ও ইমেইল দিয়ে যেকোনো সময় দেখতে পারবেন।'],
                    ],
                ],
            ],
            'seo' => [
                'title' => 'DRAWER CLOTHING | প্রিমিয়াম পোশাক অনলাইন শপ',
                'description' => 'পাঞ্জাবি, টি-শার্ট, শার্ট, প্যান্ট ও লেডিস কালেকশন — সারাদেশে হোম ডেলিভারি।',
            ],
        ];
    }

    /** Saved values merged over the defaults (saved lists replace default lists). */
    /** Cache key includes a fingerprint of defaults(), so changing them in code refreshes the cache. */
    private static function cacheKey(): string
    {
        return self::CACHE_KEY.'.'.substr(md5(json_encode(self::defaults())), 0, 12);
    }

    public static function get(): array
    {
        return Cache::rememberForever(self::cacheKey(), function () {
            $saved = json_decode((string) SiteSetting::where('key', self::KEY)->value('value'), true);

            return self::merge(self::defaults(), is_array($saved) ? $saved : []);
        });
    }

    public static function save(array $config): array
    {
        // Only keep the known top-level sections.
        $config = Arr::only($config, array_keys(self::defaults()));

        SiteSetting::updateOrCreate(
            ['key' => self::KEY],
            ['value' => json_encode($config, JSON_UNESCAPED_UNICODE), 'group' => 'appearance', 'type' => 'json'],
        );
        Cache::forget(self::cacheKey());

        return self::get();
    }

    public static function reset(): array
    {
        SiteSetting::where('key', self::KEY)->delete();
        Cache::forget(self::cacheKey());

        return self::get();
    }

    /**
     * Recursive merge where associative arrays merge key-by-key but lists
     * (menus, columns, links) from $saved replace the default list entirely,
     * so deleting a menu item actually deletes it.
     */
    private static function merge(array $defaults, array $saved): array
    {
        foreach ($saved as $key => $value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key])
                && ! array_is_list($value) && ! array_is_list($defaults[$key])) {
                $defaults[$key] = self::merge($defaults[$key], $value);
            } else {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }
}
