<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $totalSales = (float) Order::where('payment_status', 'paid')->sum('total');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = Product::count();
        $totalCategories = \App\Models\Category::count();
        $lowStockProducts = Product::where('stock', '<=', 5)->count();

        // Recent 5 orders
        $recentOrders = Order::latest()->take(5)->get();

        // Top 5 products by order items count
        $topProducts = Product::with('category')
            ->orderBy('is_bestseller', 'desc')
            ->take(5)
            ->get();

        // Last 7 days sales data for chart
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayFormatted = $date->format('M d');
            $salesAmount = (float) Order::whereDate('created_at', $date->toDateString())
                ->where('payment_status', 'paid')
                ->sum('total');
            $orderCount = Order::whereDate('created_at', $date->toDateString())->count();

            $days[] = [
                'date' => $dayFormatted,
                'sales' => $salesAmount,
                'orders' => $orderCount,
            ];
        }

        // Recent activities
        $recentActivities = ActivityLog::with('user')->latest()->take(6)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_sales' => $totalSales,
                    'total_orders' => $totalOrders,
                    'pending_orders' => $pendingOrders,
                    'total_customers' => $totalCustomers,
                    'total_products' => $totalProducts,
                    'total_categories' => $totalCategories,
                    'low_stock_count' => $lowStockProducts,
                ],
                'sales_chart' => $days,
                'recent_orders' => $recentOrders,
                'top_products' => $topProducts,
                'recent_activities' => $recentActivities,
            ],
        ]);
    }

    public function badges(): JsonResponse
    {
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $lowStockProducts = Product::where('stock', '<=', 5)->count();
        $unapprovedReviews = \App\Models\ProductReview::where('is_approved', false)->count();
        $newInquiries = \App\Models\ContactMessage::where('is_read', false)->count();
        $totalCategories = \App\Models\Category::count();
        $totalProducts = Product::count();
        $pendingReturns = 0;

        return response()->json([
            'success' => true,
            'data' => [
                'pending_orders' => $pendingOrders,
                'low_stock' => $lowStockProducts,
                'unapproved_reviews' => $unapprovedReviews,
                'new_inquiries' => $newInquiries,
                'pending_returns' => $pendingReturns,
                'total_categories' => $totalCategories,
                'total_products' => $totalProducts,
            ],
        ]);
    }
}
