<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type'); // general or wholesale
        $status = $request->query('status');
        $search = $request->query('search');

        $query = ContactMessage::with('assignedTo');

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $messages = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,resolved,archived',
            'internal_notes' => 'nullable|string',
            'is_read' => 'nullable|boolean',
        ]);

        $message->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inquiry updated successfully.',
            'data' => $message,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry deleted successfully.',
        ]);
    }
}
