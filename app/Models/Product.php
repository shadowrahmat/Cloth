<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'sale_price',
        'compare_at_price',
        'cost_price',
        'category_id',
        'brand_id',
        'featured_image',
        'gallery',
        'stock',
        'low_stock_threshold',
        'track_inventory',
        'is_featured',
        'is_bestseller',
        'is_new',
        'status',
        'attributes',
        'fabric_care',
        'shipping_info',
        'returns_info',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'track_inventory' => 'boolean',
        'is_featured' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_new' => 'boolean',
        'gallery' => 'array',
        'attributes' => 'array',
    ];

    protected $appends = [
        'effective_price',
        'is_on_sale',
        'rating_stats',
    ];

    protected static function booted(): void
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'SKU-' . strtoupper(Str::random(8));
            }
        });
    }

    public function getFeaturedImageAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return url($value);
        }

        if (str_starts_with($value, 'storage/')) {
            return url('/' . $value);
        }

        return url('/storage/' . ltrim($value, '/'));
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->sale_price && $this->sale_price > 0 ? $this->sale_price : $this->price);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return (bool) ($this->sale_price && $this->sale_price < $this->price);
    }

    public function getRatingStatsAttribute(): array
    {
        $avg = $this->reviews()->where('is_approved', true)->avg('rating') ?: 5.0;
        $count = $this->reviews()->where('is_approved', true)->count();
        return [
            'average' => round((float) $avg, 1),
            'count' => $count,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_products');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
