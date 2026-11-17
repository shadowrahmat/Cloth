<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_name',
        'disk',
        'path',
        'mime_type',
        'size',
        'alt_text',
        'uploaded_by',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        return url('/storage/' . ltrim($this->path, '/'));
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
