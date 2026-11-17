<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\HomepageSection;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomepageBuilderController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = HomepageSection::orderBy('display_order')->get();

        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'content' => 'nullable|array',
            'display_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if (!isset($validated['display_order'])) {
            $maxOrder = HomepageSection::max('display_order') ?? 0;
            $validated['display_order'] = $maxOrder + 1;
        }

        $section = HomepageSection::create($validated);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'create_homepage_section',
            'model_type' => HomepageSection::class,
            'model_id' => $section->id,
            'description' => "Added section '{$section->title}' ({$section->type})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Homepage section added',
            'data' => $section,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $section = HomepageSection::find($id);
        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'content' => 'nullable|array',
            'display_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $section->update($validated);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update_homepage_section',
            'model_type' => HomepageSection::class,
            'model_id' => $section->id,
            'description' => "Updated section '{$section->title}'",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Homepage section updated',
            'data' => $section,
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array', // array of { id, display_order }
        ]);

        foreach ($request->order as $item) {
            HomepageSection::where('id', $item['id'])->update(['display_order' => $item['display_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Homepage layout reordered successfully',
            'data' => HomepageSection::orderBy('display_order')->get(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $section = HomepageSection::find($id);
        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }

        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted',
        ]);
    }
}
