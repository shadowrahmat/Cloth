<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['variants', 'category']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($filter = $request->input('filter')) {
            if ($filter === 'low_stock') {
                $query->where('stock', '<=', 5)->where('stock', '>', 0);
            } elseif ($filter === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function adjustStock(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer', // positive to add, negative to subtract
            'type' => 'required|in:manual_adjustment,purchase,return',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);
            $prevStock = $product->stock;
            $newStock = max(0, $prevStock + (int) $request->quantity);

            $product->update(['stock' => $newStock]);

            if ($request->variant_id) {
                $variant = ProductVariant::lockForUpdate()->find($request->variant_id);
                if ($variant) {
                    $variant->update(['stock' => max(0, $variant->stock + (int) $request->quantity)]);
                }
            }

            $movement = InventoryMovement::create([
                'product_id' => $product->id,
                'variant_id' => $request->variant_id,
                'type' => $request->type,
                'quantity' => (int) $request->quantity,
                'previous_stock' => $prevStock,
                'current_stock' => $newStock,
                'reference_type' => 'Manual',
                'notes' => $request->notes,
                'created_by' => $request->user()?->id,
            ]);

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'adjust_stock',
                'model_type' => Product::class,
                'model_id' => $product->id,
                'description' => "Adjusted stock for '{$product->name}' by {$request->quantity} (Now: {$newStock})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'data' => [
                    'product_id' => $product->id,
                    'stock' => $newStock,
                    'movement' => $movement,
                ],
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Stock adjustment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function movements(Request $request): JsonResponse
    {
        $query = InventoryMovement::with(['product', 'variant', 'creator'])->latest();

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        $perPage = (int) $request->input('per_page', 20);
        $movements = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $movements->items(),
            'meta' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
            ],
        ]);
    }
}
