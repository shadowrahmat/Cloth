<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $type = $request->query('type');
        $search = $request->query('search');

        $query = ReturnRequest::with(['order', 'user', 'items.orderItem']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%")
                         ->orWhere('customer_name', 'like', "%{$search}%");
                  });
            });
        }

        $returns = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $returns,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $return = ReturnRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed',
            'admin_notes' => 'nullable|string',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        $return->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Return request updated successfully.',
            'data' => $return,
        ]);
    }
}
