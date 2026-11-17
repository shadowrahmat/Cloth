<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'rate' => 'decimal:2',
        'free_threshold' => 'decimal:2',
        'is_delivery_charge_later' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }
}
