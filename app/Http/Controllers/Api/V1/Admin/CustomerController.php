<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $query = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum(['orders as lifetime_spent' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'total')
            ->with(['orders' => function ($q) {
                $q->latest()->take(1);
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $customer = User::where('role', 'customer')
            ->with(['orders' => function ($q) {
                $q->latest();
            }, 'addresses'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    }
}
