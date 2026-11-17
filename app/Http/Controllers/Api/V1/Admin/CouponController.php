<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $query = Coupon::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $coupons = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'nullable|in:fixed,percentage',
            'discount_type' => 'nullable|in:fixed,percentage',
            'value' => 'nullable|numeric|min:0',
            'discount_value' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $coupon = Coupon::create([
            'code' => strtoupper($validated['code']),
            'type' => $validated['discount_type'] ?? $validated['type'] ?? 'percentage',
            'value' => $validated['discount_value'] ?? $validated['value'] ?? 10,
            'min_order_amount' => $validated['min_spend'] ?? $validated['min_order_amount'] ?? 0,
            'max_discount_amount' => $validated['max_discount'] ?? $validated['max_discount_amount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully.',
            'data' => $coupon,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'type' => 'nullable|in:fixed,percentage',
            'discount_type' => 'nullable|in:fixed,percentage',
            'value' => 'nullable|numeric|min:0',
            'discount_value' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $coupon->update([
            'code' => strtoupper($validated['code']),
            'type' => $validated['discount_type'] ?? $validated['type'] ?? $coupon->type,
            'value' => $validated['discount_value'] ?? $validated['value'] ?? $coupon->value,
            'min_order_amount' => $validated['min_spend'] ?? $validated['min_order_amount'] ?? $coupon->min_order_amount,
            'max_discount_amount' => $validated['max_discount'] ?? $validated['max_discount_amount'] ?? $coupon->max_discount_amount,
            'usage_limit' => $validated['usage_limit'] ?? $coupon->usage_limit,
            'expires_at' => $validated['expires_at'] ?? $coupon->expires_at,
            'is_active' => $validated['is_active'] ?? $coupon->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon updated successfully.',
            'data' => $coupon,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted successfully.',
        ]);
    }
}
