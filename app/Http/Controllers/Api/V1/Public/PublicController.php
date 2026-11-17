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
            ->get();

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

    public function products(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'brand', 'variants'])
            ->where('status', 'published');

        // Category filter (includes children subcategories)
        if ($categorySlug = $request->input('category')) {
            $cat = Category::where('slug', $categorySlug)->first();
            if ($cat) {
                $catIds = $cat->children()->pluck('id')->push($cat->id)->all();
                $query->whereIn('category_id', $catIds);
            }
        }

        // Collection filter
        if ($collectionSlug = $request->input('collection')) {
            $collection = Collection::where('slug', $collectionSlug)->first();
            if ($collection) {
                $query->whereHas('collections', function ($q) use ($collection) {
                    $q->where('collections.id', $collection->id);
                });
            }
        }

        // Brand filter
        if ($brandSlug = $request->input('brand')) {
            $query->whereHas('brand', function ($q) use ($brandSlug) {
                $q->where('slug', $brandSlug);
            });
        }

        // Price range
        if ($minPrice = $request->input('min_price')) {
            $query->where('price', '>=', (float) $minPrice);
        }
        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        // Size filter
        if ($size = $request->input('size')) {
            $query->where(function ($q) use ($size) {
                $q->whereHas('variants', fn($vq) => $vq->where('size', $size))
                  ->orWhere('attributes', 'like', "%{$size}%");
            });
        }

        // Color filter
        if ($color = $request->input('color')) {
            $query->where(function ($q) use ($color) {
                $q->whereHas('variants', fn($vq) => $vq->where('color', $color))
                  ->orWhere('attributes', 'like', "%{$color}%");
            });
        }

        // In stock only
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // On sale only
        if ($request->boolean('on_sale')) {
            $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
        }

        // Search keyword
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
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

    public function settings(): JsonResponse
    {
        $settings = SiteSetting::all()->keyBy('key')->map(function ($s) {
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
