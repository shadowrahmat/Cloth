<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\ContactMessage;
use App\Models\HomepageSection;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\SiteSetting;
use App\Models\SocialGalleryItem;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function homepage(): JsonResponse
    {
        $sections = HomepageSection::where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->each(function (HomepageSection $section) {
                if ($section->type === 'category_showcase') {
                    $section->setAttribute('showcase', $this->categoryShowcase((array) $section->content));
                }
            });

        // New Arrivals (8 items)
        $newArrivals = Product::with(['category', 'variants'])
            ->where('status', 'published')
            ->latest()
            ->take(8)
            ->get();

        // Best Sellers (8 items)
        $bestSellers = Product::with(['category', 'variants'])
            ->where('status', 'published')
            ->where(function($q) {
                $q->where('is_bestseller', true)
                  ->orWhere('stock', '>', 0);
            })
            ->take(8)
            ->get();

        // Featured Categories (Men, Women, Kids, Winter)
        $featuredCategories = Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->take(4)
            ->get();

        // Testimonials
        $testimonials = Testimonial::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('display_order')
            ->take(6)
            ->get();

        // Instagram Gallery
        $socialGallery = SocialGalleryItem::where('is_active', true)
            ->orderBy('display_order')
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sections' => $sections,
                'featured_categories' => $featuredCategories,
                'new_arrivals' => $newArrivals,
                'best_sellers' => $bestSellers,
                'testimonials' => $testimonials,
                'social_gallery' => $socialGallery,
            ],
        ]);
    }

    /**
     * Data for the scroll-driven "category showcase" homepage section:
     * each chosen category with a few of its best product photos.
     * content.categories = ordered list of slugs (empty = top categories by product count).
     */
    private function categoryShowcase(array $content): array
    {
        $perCategory = max(1, min(4, (int) ($content['products_per_category'] ?? 3)));
        $slugs = array_values(array_filter((array) ($content['categories'] ?? []), 'is_string'));

        $published = fn ($q) => $q->where('status', 'published')->whereNotNull('featured_image')->where('featured_image', '!=', '');

        $query = Category::where('is_active', true)->with('children:id,parent_id');
        $categories = $slugs
            ? $query->whereIn('slug', $slugs)->get()->sortBy(fn ($c) => array_search($c->slug, $slugs, true))->values()
            : $query->withCount(['products' => $published])->orderByDesc('products_count')->take(5)->get();

        return $categories->map(function (Category $cat) use ($perCategory, $published) {
            $ids = $cat->children->pluck('id')->push($cat->id);
            $base = Product::whereIn('category_id', $ids)->where($published);

            $products = (clone $base)
                ->orderByDesc('is_featured')->orderByDesc('is_bestseller')->latest()
                ->take($perCategory)
                ->get(['id', 'name', 'slug', 'price', 'sale_price', 'featured_image']);

            return [
                'slug' => $cat->slug,
                'name' => $cat->name,
                'url' => "/category/{$cat->slug}",
                'count' => (clone $base)->count(),
                'products' => $products->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => (float) $p->price,
                    'sale_price' => $p->sale_price ? (float) $p->sale_price : null,
                    'image' => $p->featured_image,
                ])->values(),
            ];
        })->filter(fn ($c) => count($c['products']) > 0)->values()->all();
    }

    public function categories(): JsonResponse
    {
        // Categories with their subcategories tree
        $categories = Category::with(['children' => fn($q) => $q->where('is_active', true)->orderBy('display_order')])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /** Comma-separated (or array) query value -> clean list of strings. */
    private function listParam(Request $request, string $key): array
    {
        $raw = $request->input($key);
        $items = is_array($raw) ? $raw : explode(',', (string) $raw);

        return array_values(array_filter(array_map(fn ($v) => trim((string) $v), $items), fn ($v) => $v !== ''));
    }

    /** SQL for the price a customer actually pays (sale price when set and lower). */
    // `+ 0` forces numeric comparison (SQLite stores decimals as text).
    private const EFFECTIVE_PRICE = 'CASE WHEN sale_price IS NOT NULL AND sale_price + 0 > 0 AND sale_price + 0 < price + 0 THEN sale_price + 0 ELSE price + 0 END';

    private const ON_SALE = 'sale_price IS NOT NULL AND sale_price + 0 > 0 AND sale_price + 0 < price + 0';

    /**
     * Apply storefront filters. $skip lets the facet endpoint leave one
     * filter out, so e.g. size counts reflect every other active filter.
     */
    private function applyProductFilters($query, Request $request, array $skip = [])
    {
        $query->where('status', 'published');

        // Category: route slug (`category`) plus any extra ticked categories (`categories`), children included
        if (! in_array('category', $skip, true)) {
            $slugs = array_merge($this->listParam($request, 'category'), $this->listParam($request, 'categories'));
            if ($slugs) {
                $cats = Category::whereIn('slug', $slugs)->with('children:id,parent_id')->get(['id']);
                if ($cats->isNotEmpty()) {
                    $ids = $cats->flatMap(fn ($c) => $c->children->pluck('id')->push($c->id))->unique()->all();
                    $query->whereIn('category_id', $ids);
                }
            }
        }

        if ($collectionSlug = $request->input('collection')) {
            $collection = Collection::where('slug', $collectionSlug)->first();
            if ($collection) {
                $query->whereHas('collections', fn ($q) => $q->where('collections.id', $collection->id));
            }
        }

        if ($brandSlug = $request->input('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $brandSlug));
        }

        if (! in_array('price', $skip, true)) {
            if (is_numeric($request->input('min_price'))) {
                $query->whereRaw(self::EFFECTIVE_PRICE.' >= CAST(? AS DECIMAL(12,2))', [(float) $request->input('min_price')]);
            }
            if (is_numeric($request->input('max_price'))) {
                $query->whereRaw(self::EFFECTIVE_PRICE.' <= CAST(? AS DECIMAL(12,2))', [(float) $request->input('max_price')]);
            }
        }

        foreach (['size', 'color'] as $attr) {
            $values = $this->listParam($request, $attr);
            if ($values && ! in_array($attr, $skip, true)) {
                $query->whereHas('variants', fn ($vq) => $vq->whereIn($attr, $values)->where('is_active', true));
            }
        }

        if ($request->boolean('in_stock') && ! in_array('in_stock', $skip, true)) {
            $query->where('stock', '>', 0);
        }

        if ($request->boolean('on_sale') && ! in_array('on_sale', $skip, true)) {
            $query->whereRaw(self::ON_SALE);
        }

        if ($search = trim((string) $request->input('search'))) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
            $query->where(fn ($q) => $q
                ->where('name', 'like', $like)
                ->orWhere('short_description', 'like', $like)
                ->orWhere('sku', 'like', $like));
        }

        return $query;
    }

    /**
     * Filter options with live counts for the shop page:
     * categories, sizes, colours, price range, on-sale and in-stock totals.
     */
    public function productFacets(Request $request): JsonResponse
    {
        $base = fn (array $skip = []) => $this->applyProductFilters(Product::query(), $request, $skip);

        $variantCounts = function (string $attr) use ($base) {
            $ids = $base([$attr])->pluck('id');

            return \App\Models\ProductVariant::query()
                ->whereIn('product_id', $ids)
                ->where('is_active', true)
                ->whereNotNull($attr)->where($attr, '!=', '')
                ->selectRaw("{$attr} as value, COUNT(DISTINCT product_id) as count")
                ->groupBy($attr)
                ->get()
                ->map(fn ($r) => ['value' => $r->value, 'count' => (int) $r->count]);
        };

        $sizeRank = function (string $s): array {
            $order = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '2XL', '3XL', '4XL', '5XL'];
            $i = array_search(strtoupper($s), $order, true);
            if ($i !== false) {
                return [0, $i];
            }

            return is_numeric($s) ? [1, (float) $s] : [2, $s];
        };
        $sizes = $variantCounts('size')->sortBy(fn ($r) => $sizeRank($r['value']))->values();

        $colors = $variantCounts('color');
        $colorCodes = \App\Models\ProductVariant::whereIn('color', $colors->pluck('value'))
            ->whereNotNull('color_code')->pluck('color_code', 'color');
        $colors = $colors->map(fn ($c) => $c + ['code' => $colorCodes[$c['value']] ?? null])->sortByDesc('count')->values();

        // Category counts (only categories that have matching products)
        $byCategory = $base(['category'])->selectRaw('category_id, COUNT(*) as c')->groupBy('category_id')->pluck('c', 'category_id');
        $categories = Category::where('is_active', true)->get(['id', 'name', 'slug', 'parent_id'])
            ->map(function ($cat) use ($byCategory) {
                return ['slug' => $cat->slug, 'name' => $cat->name, 'parent_id' => $cat->parent_id, 'id' => $cat->id, 'count' => (int) ($byCategory[$cat->id] ?? 0)];
            });
        // Parents also count their children's products
        $categories = $categories->map(function ($cat) use ($categories) {
            $childTotal = $categories->where('parent_id', $cat['id'])->sum('count');

            return $cat + ['total' => $cat['count'] + $childTotal];
        })->filter(fn ($c) => $c['total'] > 0)->sortByDesc('total')->values()
            ->map(fn ($c) => ['slug' => $c['slug'], 'name' => $c['name'], 'count' => $c['total'], 'is_child' => (bool) $c['parent_id']]);

        $price = $base(['price'])->selectRaw('MIN('.self::EFFECTIVE_PRICE.') as min, MAX('.self::EFFECTIVE_PRICE.') as max')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $base()->count(),
                'categories' => $categories,
                'sizes' => $sizes,
                'colors' => $colors,
                'price' => ['min' => (float) ($price->min ?? 0), 'max' => (float) ($price->max ?? 0)],
                'on_sale' => $base(['on_sale'])->whereRaw(self::ON_SALE)->count(),
                'in_stock' => $base(['in_stock'])->where('stock', '>', 0)->count(),
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $query = $this->applyProductFilters(Product::with(['category', 'brand', 'variants']), $request);
        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw(self::EFFECTIVE_PRICE.' asc');
                break;
            case 'price_desc':
                $query->orderByRaw(self::EFFECTIVE_PRICE.' desc');
                break;
            case 'discount':
                $query->orderByRaw('CASE WHEN '.self::ON_SALE.' THEN (price - sale_price) / price ELSE 0 END desc');
                break;
            case 'bestseller':
                $query->orderBy('is_bestseller', 'desc')->latest();
                break;
            case 'featured':
                $query->orderBy('is_featured', 'desc')->latest();
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $perPage = (int) $request->input('per_page', 12);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function productDetail(string $slug): JsonResponse
    {
        $product = Product::with([
            'category.parent',
            'brand',
            'collections',
            'variants' => fn($q) => $q->where('is_active', true),
            'reviews' => fn($q) => $q->where('is_approved', true)->latest(),
        ])
        ->where('slug', $slug)
        ->where('status', 'published')
        ->firstOrFail();

        // Related products in the same category
        $relatedProducts = Product::with(['category', 'variants'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->take(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'related_products' => $relatedProducts,
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $products = Product::where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('short_description', 'like', "%{$q}%");
            })
            ->take(8)
            ->get(['id', 'name', 'slug', 'sku', 'price', 'sale_price', 'featured_image', 'category_id']);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function collections(): JsonResponse
    {
        $collections = Collection::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $collections,
        ]);
    }

    public function collectionDetail(string $slug): JsonResponse
    {
        $collection = Collection::with(['products' => function ($q) {
            $q->where('status', 'published')->with('category');
        }])
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $collection,
        ]);
    }

    public function page(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }

    public function submitContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $contact = ContactMessage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your message. Our client concierge will get in touch shortly.',
        ], 201);
    }

    public function subscribeNewsletter(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        NewsletterSubscriber::firstOrCreate(
            ['email' => $request->email],
            ['is_active' => true, 'subscribed_at' => now()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing to ATELIER journals.',
        ]);
    }

    public function addReview(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'author_name' => 'required|string|max:100',
            'author_email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'review' => 'required|string',
        ]);

        $product = Product::findOrFail($productId);

        $review = $product->reviews()->create([
            'user_id' => $request->user('sanctum')?->id,
            'author_name' => $request->author_name,
            'author_email' => $request->author_email,
            'rating' => (int) $request->rating,
            'title' => $request->title,
            'review' => $request->review,
            'is_approved' => true, // default approved for seamless demo
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your review!',
            'data' => $review,
        ], 201);
    }

    /** Storefront appearance: logo, announcement, menus, footer, contact, SEO. */
    public function site(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Support\SiteConfig::get(),
        ]);
    }

    /**
     * Settings that are safe to expose publicly. Everything else in
     * site_settings (gateway secrets, SMTP/SMS credentials, captcha secret)
     * must never leave the admin API.
     */
    private const PUBLIC_SETTING_KEYS = [
        'site_name', 'site_tagline', 'currency_code', 'currency_symbol',
        'free_shipping_threshold', 'inside_city_charge', 'outside_city_charge', 'delivery_charge_later',
        'enable_cod', 'enable_bkash', 'enable_nagad', 'enable_sslcommerz', 'enable_stripe',
        'enable_recaptcha', 'recaptcha_site_key',
    ];

    public function settings(): JsonResponse
    {
        $settings = SiteSetting::whereIn('key', self::PUBLIC_SETTING_KEYS)->get()->keyBy('key')->map(function ($s) {
            if ($s->type === 'boolean') {
                return filter_var($s->value, FILTER_VALIDATE_BOOLEAN);
            }
            if ($s->type === 'json') {
                return json_decode($s->value, true);
            }
            return $s->value;
        });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
