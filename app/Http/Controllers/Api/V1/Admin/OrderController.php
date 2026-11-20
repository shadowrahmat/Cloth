<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items', 'user'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('order_status')) {
            $query->where('order_status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        $perPage = (int) $request->input('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(string|int $id): JsonResponse
    {
        $order = Order::with(['items.product', 'items.variant', 'user', 'payments'])->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function updateStatus(Request $request, string|int $id): JsonResponse
    {
        $order = Order::with('items')->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $validated = $request->validate([
            'order_status' => 'nullable|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'nullable|in:unpaid,pending,paid,failed,refunded',
            'tracking_number' => 'nullable|string|max:100',
            'order_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldOrderStatus = $order->order_status;
            $newOrderStatus = $validated['order_status'] ?? $oldOrderStatus;

            // If changing to cancelled or refunded from an active order, restore inventory
            if (in_array($newOrderStatus, ['cancelled', 'refunded']) && !in_array($oldOrderStatus, ['cancelled', 'refunded'])) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $prev = $product->stock;
                            $product->increment('stock', $item->quantity);
                            InventoryMovement::create([
                                'product_id' => $product->id,
                                'variant_id' => $item->variant_id,
                                'type' => 'order_cancel',
                                'quantity' => $item->quantity,
                                'previous_stock' => $prev,
                                'current_stock' => $prev + $item->quantity,
                                'reference_type' => 'Order',
                                'reference_id' => (string) $order->id,
                                'notes' => "Restored stock due to order cancel (#{$order->order_number})",
                                'created_by' => $request->user()?->id,
                            ]);
                        }
                    }
                    if ($item->variant_id) {
                        $variant = ProductVariant::find($item->variant_id);
                        if ($variant) {
                            $variant->increment('stock', $item->quantity);
                        }
                    }
                }
            }

            $order->update($validated);

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'update_order_status',
                'model_type' => Order::class,
                'model_id' => $order->id,
                'description' => "Updated order #{$order->order_number} status to '{$order->order_status}', payment '{$order->payment_status}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Tell the customer (confirmed / shipped / delivered / cancelled) unless the admin unticked it.
            $template = \App\Support\EmailTemplateDefaults::STATUS_TEMPLATES[$newOrderStatus] ?? null;
            if ($template && $newOrderStatus !== $oldOrderStatus && $request->boolean('notify_customer', true)) {
                \App\Services\StoreMailer::afterResponse($template, $order->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order->fresh(['items', 'payments']),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadInvoice(Request $request, string|int $id)
    {
        $order = Order::with(['items', 'user', 'payments'])->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $pdf = Pdf::loadView('pdf.invoice', compact('order'));
        $pdf->setPaper('a4', 'portrait');

        $fileName = "invoice-{$order->order_number}.pdf";

        if ($request->boolean('stream')) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }
}
