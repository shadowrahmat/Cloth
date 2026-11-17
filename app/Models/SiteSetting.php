<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        if ($setting->type === 'boolean') {
            return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($setting->type === 'json') {
            return json_decode($setting->value, true) ?: $default;
        }

        return $setting->value;
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): self
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            $type = 'json';
        }

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'group' => $group, 'type' => $type]
        );
    }
}
