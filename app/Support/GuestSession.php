<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The guest "identity" is the X-Session-ID header. It is only trusted when it is a UUID,
 * because the browser makes it with crypto.randomUUID() and a UUID cannot be guessed.
 */
class GuestSession
{
    /** The header as a UUID, null when it is absent. Anything else is refused with 422. */
    public static function header(Request $request): ?string
    {
        $value = $request->header('X-Session-ID');

        if ($value === null || $value === '') {
            return null;
        }
        abort_unless(Str::isUuid($value), 422, 'Invalid session id.');

        return strtolower($value);
    }

    public static function id(Request $request): string
    {
        return self::header($request) ?? session()->getId();
    }

    /**
     * Move a guest's cart and wishlist into a user's own, on login or register.
     * Only carts nobody owns are taken, so another customer's ID cannot pull theirs in.
     */
    public static function mergeInto(User $user, Request $request): void
    {
        $sessionId = self::header($request);
        if (! $sessionId) {
            return;
        }

        DB::transaction(function () use ($user, $sessionId) {
            $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
            if ($guestCart) {
                $own = Cart::where('user_id', $user->id)->first();
                if (! $own) {
                    $guestCart->update(['user_id' => $user->id]);
                } else {
                    foreach ($guestCart->items as $item) {
                        $match = $own->items()
                            ->where('product_id', $item->product_id)
                            ->where('variant_id', $item->variant_id)
                            ->first();
                        $match
                            ? $match->increment('quantity', $item->quantity)
                            : $item->update(['cart_id' => $own->id]);
                    }
                    $guestCart->delete();
                }
            }

            $guestList = Wishlist::where('session_id', $sessionId)->whereNull('user_id')->first();
            if ($guestList) {
                $own = Wishlist::where('user_id', $user->id)->first();
                if (! $own) {
                    $guestList->update(['user_id' => $user->id]);
                } else {
                    $have = $own->items()->pluck('product_id');
                    $guestList->items()->whereNotIn('product_id', $have)->update(['wishlist_id' => $own->id]);
                    $guestList->delete();
                }
            }
        });
    }
}
