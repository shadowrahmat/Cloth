<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'subtitle',
        'content',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
