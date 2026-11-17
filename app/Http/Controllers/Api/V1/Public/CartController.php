<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CartService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function get(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart($request);
        $details = $this->cartService->getCartDetails($cart);

        return response()->json([
            'success' => true,
            'data' => $details,
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1',
            'color' => 'nullable|string',
            'size' => 'nullable|string',
        ]);

        $cart = $this->cartService->getOrCreateCart($request);

        try {
            $this->cartService->addItem(
                $cart,
                (int) $request->product_id,
                $request->variant_id ? (int) $request->variant_id : null,
                (int) $request->input('quantity', 1),
                $request->color,
                $request->size
            );

            $details = $this->cartService->getCartDetails($cart);

            return response()->json([
                'success' => true,
                'message' => 'Item added to bag',
                'data' => $details,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateQuantity(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = $this->cartService->getOrCreateCart($request);
        $this->cartService->updateQuantity($cart, $itemId, (int) $request->quantity);

        $details = $this->cartService->getCartDetails($cart);

        return response()->json([
            'success' => true,
            'message' => 'Bag updated',
            'data' => $details,
        ]);
    }

    public function remove(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart($request);
        $this->cartService->removeItem($cart, $itemId);

        $details = $this->cartService->getCartDetails($cart);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from bag',
            'data' => $details,
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $cart = $this->cartService->getOrCreateCart($request);
        $details = $this->cartService->getCartDetails($cart);
        $subtotal = $details['subtotal'];

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon code not found.',
            ], 404);
        }

        if (!$coupon->isValidFor($subtotal)) {
            $msg = 'Coupon is expired or inactive.';
            if ($coupon->min_order_amount > 0 && $subtotal < $coupon->min_order_amount) {
                $msg = "Minimum order value of \${$coupon->min_order_amount} required for this coupon.";
            }
            return response()->json([
                'success' => false,
                'message' => $msg,
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'discount_amount' => $discount,
            ],
        ]);
    }
}
