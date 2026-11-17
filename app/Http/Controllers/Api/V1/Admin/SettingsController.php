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
        $query = SiteSetting::query();

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
        $settings = $request->input('settings', []);
        $group = $request->input('group', 'general');

        foreach ($settings as $key => $value) {
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
