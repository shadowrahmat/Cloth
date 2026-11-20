<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $group = $request->input('group');
        // Appearance and the email/SMTP setup have their own screens; the SMTP password is never sent back.
        $query = SiteSetting::query()->where('key', '!=', \App\Support\SiteConfig::KEY)
            ->whereNotIn('key', \App\Services\StoreMailer::SETTING_KEYS);

        if ($group) {
            $query->where('group', $group);
        }

        $settings = $query->get()->keyBy('key')->map(function ($s) {
            if ($s->type === 'boolean') {
                return filter_var($s->value, FILTER_VALIDATE_BOOLEAN);
            }
            if ($s->type === 'json') {
                return json_decode($s->value, true);
            }
            return $s->value;
        });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        // Accept both {settings: {...}, group} and a flat {key: value} body
        // (the admin Settings page sends the flat form).
        $settings = $request->has('settings') ? (array) $request->input('settings', []) : $request->except(['group']);
        $group = $request->input('group', 'general');

        foreach ($settings as $key => $value) {
            // Appearance is managed by AppearanceController; keys must be simple identifiers.
            if ($key === \App\Support\SiteConfig::KEY || in_array($key, \App\Services\StoreMailer::SETTING_KEYS, true) || ! preg_match('/^[a-z0-9_]{1,64}$/', (string) $key)) {
                continue;
            }

            $type = 'text';
            if (is_bool($value)) {
                $type = 'boolean';
                $value = $value ? '1' : '0';
            } elseif (is_array($value)) {
                $type = 'json';
                $value = json_encode($value);
            }

            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value, 'group' => $group, 'type' => $type]
            );
        }

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update_settings',
            'model_type' => SiteSetting::class,
            'description' => "Updated '{$group}' site settings",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }
}
