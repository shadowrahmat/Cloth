<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ReturnRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Orders that count as a sale: everything except cancelled ones. */
    private function sales(): Builder
    {
        return Order::query()->where('order_status', '!=', 'cancelled');
    }

    /** Percentage change between two periods, null when there is no baseline. */
    private function change(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function index(Request $request): JsonResponse
    {
        $now = Carbon::now();
        $thisWeek = [$now->copy()->subDays(6)->startOfDay(), $now];
        $lastWeek = [$now->copy()->subDays(13)->startOfDay(), $now->copy()->subDays(7)->endOfDay()];

        $salesThisWeek = (float) $this->sales()->whereBetween('created_at', $thisWeek)->sum('total');
        $salesLastWeek = (float) $this->sales()->whereBetween('created_at', $lastWeek)->sum('total');
        $ordersThisWeek = $this->sales()->whereBetween('created_at', $thisWeek)->count();
        $ordersLastWeek = $this->sales()->whereBetween('created_at', $lastWeek)->count();
        $customersThisWeek = User::where('role', 'customer')->whereBetween('created_at', $thisWeek)->count();
        $customersLastWeek = User::where('role', 'customer')->whereBetween('created_at', $lastWeek)->count();

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days[] = [
                'date' => $date->format('M d'),
                'sales' => (float) $this->sales()->whereDate('created_at', $date)->sum('total'),
                'orders' => $this->sales()->whereDate('created_at', $date)->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_sales' => (float) $this->sales()->sum('total'),
                    'collected' => (float) Order::where('payment_status', 'paid')->sum('total'),
                    'sales_today' => (float) $this->sales()->whereDate('created_at', Carbon::today())->sum('total'),
                    'orders_today' => $this->sales()->whereDate('created_at', Carbon::today())->count(),
                    'sales_week' => $salesThisWeek,
                    'sales_week_change' => $this->change($salesThisWeek, $salesLastWeek),
                    'orders_week' => $ordersThisWeek,
                    'orders_week_change' => $this->change($ordersThisWeek, $ordersLastWeek),
                    'customers_week' => $customersThisWeek,
                    'customers_week_change' => $this->change($customersThisWeek, $customersLastWeek),
                    'total_orders' => Order::count(),
                    'pending_orders' => Order::where('order_status', 'pending')->count(),
                    'total_customers' => User::where('role', 'customer')->count(),
                    'total_products' => Product::count(),
                    'total_categories' => Category::count(),
                    'low_stock_count' => Product::where('stock', '<=', 5)->count(),
                ],
                'sales_chart' => $days,
                'recent_orders' => Order::latest()->take(6)->get(),
                'low_stock_products' => Product::with('category:id,name')
                    ->where('stock', '<=', 5)
                    ->orderBy('stock')
                    ->take(6)
                    ->get(['id', 'name', 'slug', 'sku', 'stock', 'price', 'sale_price', 'featured_image', 'category_id']),
                'recent_activities' => ActivityLog::with('user:id,name')->latest()->take(6)->get(),
            ],
        ]);
    }

    public function badges(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pending_orders' => Order::where('order_status', 'pending')->count(),
                'low_stock' => Product::where('stock', '<=', 5)->count(),
                'unapproved_reviews' => ProductReview::where('is_approved', false)->count(),
                'new_inquiries' => ContactMessage::where('is_read', false)->count(),
                'pending_returns' => ReturnRequest::where('status', 'pending')->count(),
                'total_categories' => Category::count(),
                'total_products' => Product::count(),
            ],
        ]);
    }

    /**
     * Quick search for the admin command palette: orders, products and customers.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => ['orders' => [], 'products' => [], 'customers' => []]]);
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => Order::where(fn (Builder $w) => $w
                        ->where('order_number', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('customer_phone', 'like', $like)
                        ->orWhere('customer_email', 'like', $like))
                    ->latest()->take(5)
                    ->get(['id', 'order_number', 'customer_name', 'total', 'order_status', 'created_at']),
                'products' => Product::where(fn (Builder $w) => $w
                        ->where('name', 'like', $like)
                        ->orWhere('sku', 'like', $like))
                    ->take(5)
                    ->get(['id', 'name', 'sku', 'stock', 'price', 'featured_image', 'status']),
                'customers' => User::where('role', 'customer')
                    ->where(fn (Builder $w) => $w
                        ->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like))
                    ->take(5)
                    ->get(['id', 'name', 'email', 'phone']),
            ],
        ]);
    }
}
