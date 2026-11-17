<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\CheckoutService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CheckoutService $checkoutService
    ) {}

    public function getShippingMethods(): JsonResponse
    {
        $methods = ShippingMethod::where('is_active', true)->orderBy('rate')->get();
        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:30',
            'country' => 'nullable|string|max:100',
            'shipping_method' => 'nullable|string',
            'payment_method' => 'required|in:cod,stripe,card',
            'coupon_code' => 'nullable|string',
            'order_notes' => 'nullable|string',
        ]);

        $cart = $this->cartService->getOrCreateCart($request);
        $userId = $request->user('sanctum')?->id;

        try {
            $order = $this->checkoutService->processCheckout($cart, $request->all(), $userId);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => (float) $order->total,
                    'customer_email' => $order->customer_email,
                    'payment_method' => $order->payment_method,
                    'order_status' => $order->order_status,
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function trackOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_number' => 'required|string',
            'email' => 'required|email',
        ]);

        $order = Order::with('items')
            ->where('order_number', trim($request->order_number))
            ->where('customer_email', trim($request->email))
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'No order found matching these details.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}
