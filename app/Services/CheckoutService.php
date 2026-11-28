<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(protected CartService $cartService) {}

    public function processCheckout(Cart $cart, array $data, ?int $userId = null): Order
    {
        $cart->load(['items.product', 'items.variant']);

        if ($cart->items->isEmpty()) {
            throw new BusinessException('Your shopping cart is empty.');
        }

        return DB::transaction(function () use ($cart, $data, $userId) {
            $subtotal = 0.0;
            $unitPrices = []; // cart item id => price today, not the price when it was added to the bag

            // 1. Validate items & stock availability atomically
            foreach ($cart->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if (! $product || $product->status !== 'published') {
                    throw new BusinessException("Product '{$item->product?->name}' is no longer available.");
                }

                $availableStock = $product->stock;
                $variant = null;

                if ($item->variant_id) {
                    $variant = ProductVariant::lockForUpdate()->find($item->variant_id);
                    if (! $variant || ! $variant->is_active) {
                        throw new BusinessException("Selected option for '{$product->name}' is no longer available.");
                    }
                    $availableStock = $variant->stock;
                }

                if ($product->track_inventory && $availableStock < $item->quantity) {
                    throw new BusinessException("Insufficient stock for '{$product->name}' (Requested: {$item->quantity}, Available: {$availableStock}).");
                }

                $unitPrices[$item->id] = $this->cartService->currentPrice($product, $variant);
                $subtotal += ($unitPrices[$item->id] * $item->quantity);
            }

            // 2. Shipping calculation
            $shippingMethodCode = $data['shipping_method'] ?? 'standard';
            $shippingMethod = ShippingMethod::where('code', $shippingMethodCode)->where('is_active', true)->first();
            $shippingAmount = $shippingMethod ? (float) $shippingMethod->rate : 0.0;

            if ($shippingMethod && $shippingMethod->free_threshold && $subtotal >= $shippingMethod->free_threshold) {
                $shippingAmount = 0.0;
            }

            // 3. Coupon discount
            $discountAmount = 0.0;
            $couponCode = null;
            if (! empty($data['coupon_code'])) {
                $couponCodeInput = strtoupper($data['coupon_code']);
                $coupon = Coupon::where('code', $couponCodeInput)->first();

                if (! $coupon || ! $coupon->isValidFor($subtotal, $userId, $data['email'] ?? null)) {
                    throw new BusinessException('The applied coupon is invalid, expired, or has reached its usage limit.');
                }

                // Atomic usage increment to prevent race condition
                $updated = Coupon::where('id', $coupon->id)
                    ->where(function ($query) {
                        $query->whereNull('usage_limit')
                            ->orWhereColumn('used_count', '<', 'usage_limit');
                    })
                    ->increment('used_count');

                if (! $updated) {
                    throw new BusinessException('The coupon has just reached its maximum usage limit.');
                }

                $discountAmount = $coupon->calculateDiscount($subtotal, $userId, $data['email'] ?? null);
                $couponCode = $coupon->code;
            }

            // 4. Tax calculation (e.g. from site settings or default 5%)
            $taxRate = (float) SiteSetting::get('tax_rate', 0.0);
            $taxableSubtotal = max(0, $subtotal - $discountAmount);
            $taxAmount = round(($taxableSubtotal * $taxRate) / 100, 2);

            $total = round($taxableSubtotal + $shippingAmount + $taxAmount, 2);

            // 5. Create Order
            $orderNumber = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(5));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'customer_name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'] ?? null,
                'shipping_address' => [
                    'first_name' => $data['first_name'] ?? '',
                    'last_name' => $data['last_name'] ?? '',
                    'company' => $data['company'] ?? '',
                    'address_line_1' => $data['address_line_1'] ?? '',
                    'address_line_2' => $data['address_line_2'] ?? '',
                    'city' => $data['city'] ?? '',
                    'state' => $data['state'] ?? '',
                    'postal_code' => $data['postal_code'] ?? '',
                    'country' => $data['country'] ?? 'US',
                    'phone' => $data['phone'] ?? '',
                ],
                'billing_address' => ! empty($data['billing_same_as_shipping']) ? null : ($data['billing_address'] ?? null),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'coupon_code' => $couponCode,
                'shipping_amount' => $shippingAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'payment_method' => $data['payment_method'] ?? 'cod',
                'payment_status' => 'pending',   // only a verified gateway callback may mark an order paid
                'payment_id' => $data['payment_id'] ?? null,
                'order_status' => 'pending',
                'shipping_method' => $shippingMethod ? $shippingMethod->name : 'Standard Delivery',
                'order_notes' => $data['order_notes'] ?? null,
            ]);

            // 6. Create Order Items & Deduct Inventory
            foreach ($cart->items as $item) {
                $product = Product::find($item->product_id);
                $variant = $item->variant_id ? ProductVariant::find($item->variant_id) : null;

                $order->items()->create([
                    'product_id' => $product->id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $product->name,
                    'sku' => $variant?->sku ?? $product->sku,
                    'price' => $unitPrices[$item->id],
                    'quantity' => $item->quantity,
                    'subtotal' => round($unitPrices[$item->id] * $item->quantity, 2),
                    'color' => $item->color,
                    'size' => $item->size,
                    'image' => $variant?->image ?: $product->featured_image,
                ]);

                // Decrement inventory
                if ($product->track_inventory) {
                    $prevProductStock = $product->stock;
                    $product->decrement('stock', $item->quantity);

                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'variant_id' => $item->variant_id,
                        'type' => 'order_sale',
                        'quantity' => -$item->quantity,
                        'previous_stock' => $prevProductStock,
                        'current_stock' => $prevProductStock - $item->quantity,
                        'reference_type' => 'Order',
                        'reference_id' => (string) $order->id,
                        'notes' => "Sale from Order #{$order->order_number}",
                        'created_by' => $userId,
                    ]);

                    if ($variant) {
                        $variant->decrement('stock', $item->quantity);
                    }
                }
            }

            // 7. Create Payment Record
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'transaction_id' => $order->payment_id ?: 'TXN-'.strtoupper(Str::random(10)),
                'amount' => $order->total,
                'currency' => 'USD',
                'status' => $order->payment_status === 'paid' ? 'successful' : 'pending',
                'gateway_response' => [
                    'source' => 'storefront_checkout',
                    'ip' => request()->ip(),
                ],
            ]);

            // 8. Clear Cart
            $cart->items()->delete();

            return $order->load(['items', 'payments']);
        });
    }
}
