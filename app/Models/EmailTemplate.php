<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['key', 'subject', 'heading', 'body', 'button_label', 'button_url', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
