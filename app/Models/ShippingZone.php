<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'locations' => 'array',
        'is_active' => 'boolean',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'zone_id');
    }
}
