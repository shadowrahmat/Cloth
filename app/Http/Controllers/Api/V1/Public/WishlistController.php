<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Support\GuestSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    protected function getOrCreateWishlist(Request $request): Wishlist
    {
        $userId = $request->user('sanctum')?->id;
        $sessionId = GuestSession::id($request);

        if ($userId) {
            $wl = Wishlist::where('user_id', $userId)->first();
            if (!$wl) {
                $wl = Wishlist::create(['user_id' => $userId, 'session_id' => $sessionId]);
            }
            return $wl;
        }

        $wl = Wishlist::where('session_id', $sessionId)->first();
        if (!$wl) {
            $wl = Wishlist::create(['session_id' => $sessionId]);
        }
        return $wl;
    }

    public function index(Request $request): JsonResponse
    {
        $wishlist = $this->getOrCreateWishlist($request);
        $wishlist->load(['items.product.category']);

        $products = $wishlist->items->map(function ($item) {
            return $item->product;
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $wishlist = $this->getOrCreateWishlist($request);
        $productId = (int) $request->product_id;

        $existing = $wishlist->items()->where('product_id', $productId)->first();
        if ($existing) {
            $existing->delete();
            $inWishlist = false;
            $msg = 'Removed from wishlist';
        } else {
            $wishlist->items()->create(['product_id' => $productId]);
            $inWishlist = true;
            $msg = 'Added to wishlist';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'in_wishlist' => $inWishlist,
            'product_id' => $productId,
        ]);
    }
}
