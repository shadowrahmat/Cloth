<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'banner_image',
        'mobile_banner',
        'display_order',
        'is_featured',
        'is_active',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($collection) {
            if (empty($collection->slug)) {
                $collection->slug = Str::slug($collection->title);
            }
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_products');
    }
}
