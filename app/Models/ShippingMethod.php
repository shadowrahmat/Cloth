<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'rate',
        'free_threshold',
        'estimated_delivery',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'free_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
