<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\Request;

class CartService
{
    public function getOrCreateCart(Request $request): Cart
    {
        $userId = $request->user('sanctum')?->id;
        $sessionId = $request->header('X-Session-ID') ?: session()->getId();

        if ($userId) {
            $cart = Cart::where('user_id', $userId)->first();
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                ]);
            }
            return $cart;
        }

        $cart = Cart::where('session_id', $sessionId)->first();
        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sessionId,
            ]);
        }

        return $cart;
    }

    public function getCartDetails(Cart $cart): array
    {
        $cart->load(['items.product.category', 'items.variant']);

        $items = $cart->items->map(function ($item) {
            $product = $item->product;
            $variant = $item->variant;
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'name' => $product?->name ?? 'Unknown item',
                'slug' => $product?->slug ?? '',
                'sku' => $variant?->sku ?? $product?->sku ?? '',
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'color' => $item->color,
                'size' => $item->size,
                'image' => $variant?->image ?: $product?->featured_image,
                'subtotal' => round($item->price * $item->quantity, 2),
                'stock' => $variant ? $variant->stock : ($product ? $product->stock : 0),
            ];
        });

        $subtotal = round($items->sum('subtotal'), 2);
        $totalItems = $items->sum('quantity');

        return [
            'id' => $cart->id,
            'items' => $items,
            'subtotal' => $subtotal,
            'total_items' => $totalItems,
        ];
    }

    public function addItem(Cart $cart, int $productId, ?int $variantId = null, int $quantity = 1, ?string $color = null, ?string $size = null): CartItem
    {
        $product = Product::findOrFail($productId);

        $price = $product->effective_price;
        $variant = null;

        if ($variantId) {
            $variant = ProductVariant::where('product_id', $productId)->findOrFail($variantId);
            if ($variant->price && $variant->price > 0) {
                $price = (float) $variant->price;
            }
            if (!$color && $variant->color) {
                $color = $variant->color;
            }
            if (!$size && $variant->size) {
                $size = $variant->size;
            }
        }

        // Check if item already in cart with same variant or color/size
        $existing = $cart->items()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->where('color', $color)
            ->where('size', $size)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);
            return $existing->fresh();
        }

        return $cart->items()->create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity,
            'price' => $price,
            'color' => $color,
            'size' => $size,
        ]);
    }

    public function updateQuantity(Cart $cart, int $itemId, int $quantity): ?CartItem
    {
        $item = $cart->items()->find($itemId);
        if (!$item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();
            return null;
        }

        $item->update(['quantity' => $quantity]);
        return $item->fresh();
    }

    public function removeItem(Cart $cart, int $itemId): bool
    {
        return (bool) $cart->items()->where('id', $itemId)->delete();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }
}
