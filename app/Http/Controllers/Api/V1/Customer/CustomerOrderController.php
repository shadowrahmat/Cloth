<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function orders(Request $request): JsonResponse
    {
        // Product photo is the fallback thumbnail when an order line has no stored image.
        $orders = Order::with(['items.product:id,slug,featured_image'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function orderDetail(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::with(['items.product', 'items.variant', 'payments'])
            ->where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function getAddresses(Request $request): JsonResponse
    {
        $addresses = CustomerAddress::where('user_id', $request->user()->id)->get();
        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'nullable|in:shipping,billing',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'company' => 'nullable|string|max:100',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:30',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            CustomerAddress::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        $address = CustomerAddress::create(array_merge($validated, [
            'user_id' => $request->user()->id,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Address saved successfully',
            'data' => $address,
        ], 201);
    }

    public function deleteAddress(Request $request, int $id): JsonResponse
    {
        CustomerAddress::where('user_id', $request->user()->id)->where('id', $id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Address deleted',
        ]);
    }
}
