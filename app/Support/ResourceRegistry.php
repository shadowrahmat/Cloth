<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\ShippingMethod;
use App\Models\SocialGalleryItem;
use App\Models\Testimonial;
use Exception;

class ResourceRegistry
{
    protected static array $resources = [];

    public static function boot(): void
    {
        if (!empty(static::$resources)) {
            return;
        }

        static::register('categories', [
            'model' => Category::class,
            'label' => 'Categories',
            'singular' => 'Category',
            'icon' => 'FolderTree',
            'searchable' => ['name', 'slug', 'description'],
            'filters' => [
                'parent_id' => ['type' => 'select', 'options_from' => 'categories', 'label' => 'Parent Category'],
                'is_featured' => ['type' => 'boolean', 'label' => 'Featured'],
                'is_active' => ['type' => 'boolean', 'label' => 'Active Status'],
            ],
            'with' => ['parent'],
            'with_count' => ['products'],
            'order_by' => ['display_order' => 'asc', 'name' => 'asc'],
            'slug_field' => 'slug',
            'slug_source' => 'name',
            'rules' => [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'image' => 'nullable|string',
                'banner_image' => 'nullable|string',
                'display_order' => 'nullable|integer',
                'is_featured' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string',
            ],
            'permission' => 'manage_categories',
        ]);

        static::register('brands', [
            'model' => Brand::class,
            'label' => 'Brands',
            'singular' => 'Brand',
            'icon' => 'Tag',
            'searchable' => ['name', 'slug'],
            'filters' => [
                'is_featured' => ['type' => 'boolean', 'label' => 'Featured'],
                'is_active' => ['type' => 'boolean', 'label' => 'Active'],
            ],
            'with' => [],
            'order_by' => ['name' => 'asc'],
            'slug_field' => 'slug',
            'slug_source' => 'name',
            'rules' => [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'logo' => 'nullable|string',
                'description' => 'nullable|string',
                'website' => 'nullable|url',
                'is_featured' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ],
            'permission' => 'manage_brands',
        ]);

        static::register('collections', [
            'model' => Collection::class,
            'label' => 'Collections',
            'singular' => 'Collection',
            'icon' => 'Sparkles',
            'searchable' => ['title', 'slug', 'description'],
            'filters' => [
                'is_featured' => ['type' => 'boolean', 'label' => 'Featured'],
                'is_active' => ['type' => 'boolean', 'label' => 'Active'],
            ],
            'with' => ['products'],
            'order_by' => ['display_order' => 'asc', 'title' => 'asc'],
            'slug_field' => 'slug',
            'slug_source' => 'title',
            'rules' => [
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'banner_image' => 'nullable|string',
                'mobile_banner' => 'nullable|string',
                'display_order' => 'nullable|integer',
                'is_featured' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'exists:products,id',
            ],
            'permission' => 'manage_collections',
        ]);

        static::register('coupons', [
            'model' => Coupon::class,
            'label' => 'Coupons & Discounts',
            'singular' => 'Coupon',
            'icon' => 'TicketPercent',
            'searchable' => ['code'],
            'filters' => [
                'type' => ['type' => 'select', 'options' => ['percentage', 'fixed']],
                'is_active' => ['type' => 'boolean'],
            ],
            'with' => [],
            'order_by' => ['created_at' => 'desc'],
            'rules' => [
                'code' => 'required|string|max:50',
                'type' => 'required|in:percentage,fixed',
                'value' => 'required|numeric|min:0',
                'min_order_amount' => 'nullable|numeric|min:0',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'expires_at' => 'nullable|date',
                'usage_limit' => 'nullable|integer|min:1',
                'is_active' => 'nullable|boolean',
            ],
            'permission' => 'manage_coupons',
        ]);

        static::register('shipping-methods', [
            'model' => ShippingMethod::class,
            'label' => 'Shipping Methods',
            'singular' => 'Shipping Method',
            'icon' => 'Truck',
            'searchable' => ['name', 'code'],
            'filters' => [
                'is_active' => ['type' => 'boolean'],
            ],
            'with' => [],
            'order_by' => ['rate' => 'asc'],
            'rules' => [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50',
                'description' => 'nullable|string',
                'rate' => 'required|numeric|min:0',
                'free_threshold' => 'nullable|numeric|min:0',
                'estimated_delivery' => 'nullable|string|max:100',
                'is_active' => 'nullable|boolean',
            ],
            'permission' => 'manage_shipping',
        ]);

        static::register('testimonials', [
            'model' => Testimonial::class,
            'label' => 'Testimonials',
            'singular' => 'Testimonial',
            'icon' => 'Quote',
            'searchable' => ['customer_name', 'review', 'product_purchased'],
            'filters' => [
                'rating' => ['type' => 'select', 'options' => [1, 2, 3, 4, 5]],
                'is_featured' => ['type' => 'boolean'],
                'is_active' => ['type' => 'boolean'],
            ],
            'with' => [],
            'order_by' => ['display_order' => 'asc', 'created_at' => 'desc'],
            'rules' => [
                'customer_name' => 'required|string|max:255',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'required|string',
                'product_purchased' => 'nullable|string|max:255',
                'customer_image' => 'nullable|string',
                'is_featured' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'display_order' => 'nullable|integer',
            ],
            'permission' => 'manage_testimonials',
        ]);

        static::register('social-gallery-items', [
            'model' => SocialGalleryItem::class,
            'label' => 'Instagram & Social Gallery',
            'singular' => 'Gallery Item',
            'icon' => 'Instagram',
            'searchable' => ['title', 'instagram_handle'],
            'filters' => [
                'is_active' => ['type' => 'boolean'],
            ],
            'with' => [],
            'order_by' => ['display_order' => 'asc'],
            'rules' => [
                'title' => 'nullable|string|max:255',
                'image' => 'required|string',
                'link_url' => 'nullable|url',
                'instagram_handle' => 'nullable|string|max:100',
                'display_order' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ],
            'permission' => 'manage_cms',
        ]);

        static::register('pages', [
            'model' => Page::class,
            'label' => 'CMS Pages',
            'singular' => 'Page',
            'icon' => 'FileText',
            'searchable' => ['title', 'slug', 'content'],
            'filters' => [
                'template' => ['type' => 'select', 'options' => ['default', 'about', 'contact', 'terms', 'privacy']],
                'is_active' => ['type' => 'boolean'],
            ],
            'with' => [],
            'order_by' => ['title' => 'asc'],
            'slug_field' => 'slug',
            'slug_source' => 'title',
            'rules' => [
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'template' => 'nullable|string|max:50',
                'is_active' => 'nullable|boolean',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string',
            ],
            'permission' => 'manage_pages',
        ]);

        static::register('contact-messages', [
            'model' => ContactMessage::class,
            'label' => 'Customer Inquiries',
            'singular' => 'Inquiry',
            'icon' => 'Mail',
            'searchable' => ['name', 'email', 'subject', 'message'],
            'filters' => [
                'is_read' => ['type' => 'boolean'],
            ],
            'with' => [],
            'order_by' => ['created_at' => 'desc'],
            'rules' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:50',
                'subject' => 'nullable|string|max:255',
                'message' => 'required|string',
                'is_read' => 'nullable|boolean',
            ],
            'permission' => 'manage_inquiries',
        ]);

        static::register('newsletter-subscribers', [
            'model' => NewsletterSubscriber::class,
            'label' => 'Newsletter Subscribers',
            'singular' => 'Subscriber',
            'icon' => 'Send',
            'searchable' => ['email'],
            'filters' => [
                'is_active' => ['type' => 'boolean'],
            ],
            'with' => [],
            'order_by' => ['subscribed_at' => 'desc'],
            'rules' => [
                'email' => 'required|email|max:255',
                'is_active' => 'nullable|boolean',
            ],
            'permission' => 'manage_newsletter',
        ]);

        static::register('returns', [
            'model' => \App\Models\ReturnRequest::class,
            'label' => 'Returns & Exchanges',
            'singular' => 'Return Request',
            'icon' => 'RotateCcw',
            'searchable' => ['return_number', 'reason', 'status'],
            'filters' => [
                'status' => ['type' => 'select', 'options' => ['pending', 'approved', 'rejected', 'completed']],
                'type' => ['type' => 'select', 'options' => ['return', 'exchange']],
            ],
            'with' => ['order', 'user'],
            'order_by' => ['created_at' => 'desc'],
            'rules' => [
                'status' => 'required|in:pending,approved,rejected,completed',
                'admin_notes' => 'nullable|string',
                'refund_amount' => 'nullable|numeric|min:0',
            ],
            'permission' => 'manage_orders',
        ]);

        static::register('shipments', [
            'model' => \App\Models\Shipment::class,
            'label' => 'Logistics & Consignments',
            'singular' => 'Shipment',
            'icon' => 'Truck',
            'searchable' => ['consignment_id', 'tracking_code', 'recipient_name', 'recipient_phone'],
            'filters' => [
                'status' => ['type' => 'select', 'options' => ['pending', 'consignment_created', 'in_transit', 'delivered', 'returned', 'failed']],
                'courier_name' => ['type' => 'select', 'options' => ['steadfast', 'redx', 'pathao', 'custom']],
            ],
            'with' => ['order'],
            'order_by' => ['created_at' => 'desc'],
            'rules' => [
                'status' => 'required|string',
                'tracking_code' => 'nullable|string',
            ],
            'permission' => 'manage_orders',
        ]);

        static::register('blog-posts', [
            'model' => \App\Models\BlogPost::class,
            'label' => 'Blog Posts',
            'singular' => 'Post',
            'icon' => 'BookOpen',
            'searchable' => ['title', 'slug', 'excerpt'],
            'filters' => [
                'status' => ['type' => 'select', 'options' => ['draft', 'published', 'scheduled']],
            ],
            'with' => ['category', 'author'],
            'order_by' => ['created_at' => 'desc'],
            'slug_field' => 'slug',
            'slug_source' => 'title',
            'rules' => [
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'excerpt' => 'nullable|string',
                'featured_image' => 'nullable|string',
                'status' => 'required|in:draft,published,scheduled',
            ],
            'permission' => 'manage_cms',
        ]);

        static::register('blog-categories', [
            'model' => \App\Models\BlogCategory::class,
            'label' => 'Blog Categories',
            'singular' => 'Category',
            'icon' => 'Tag',
            'searchable' => ['name', 'slug'],
            'filters' => [
                'is_active' => ['type' => 'boolean'],
            ],
            'with' => [],
            'order_by' => ['name' => 'asc'],
            'slug_field' => 'slug',
            'slug_source' => 'name',
            'rules' => [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ],
            'permission' => 'manage_cms',
        ]);

        static::register('shipping-zones', [
            'model' => \App\Models\ShippingZone::class,
            'label' => 'Shipping Zones',
            'singular' => 'Zone',
            'icon' => 'MapPin',
            'searchable' => ['name', 'code'],
            'filters' => [
                'is_active' => ['type' => 'boolean'],
            ],
            'with' => ['rates'],
            'order_by' => ['name' => 'asc'],
            'rules' => [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50',
                'is_active' => 'nullable|boolean',
            ],
            'permission' => 'manage_settings',
        ]);
    }

    public static function register(string $key, array $config): void
    {
        static::$resources[$key] = $config;
    }

    public static function get(string $key): ?array
    {
        static::boot();
        return static::$resources[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        static::boot();
        return isset(static::$resources[$key]);
    }

    public static function all(): array
    {
        static::boot();
        return static::$resources;
    }
}
