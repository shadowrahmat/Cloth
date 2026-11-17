<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $ordersQuery = Order::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $grossSales = (float) (clone $ordersQuery)->sum('total');
        $netSales = (float) (clone $ordersQuery)->where('payment_status', 'paid')->sum('total');
        $totalDiscounts = (float) (clone $ordersQuery)->sum('discount_amount');
        $shippingRevenue = (float) (clone $ordersQuery)->sum('shipping_amount');
        $taxCollected = (float) (clone $ordersQuery)->sum('tax_amount');
        $totalOrders = (clone $ordersQuery)->count();
        $paidOrders = (clone $ordersQuery)->where('payment_status', 'paid')->count();
        $refundedAmount = (float) (clone $ordersQuery)->where('payment_status', 'refunded')->sum('total');

        $averageOrderValue = $paidOrders > 0 ? round($netSales / $paidOrders, 2) : 0;
        $totalUnitsSold = (int) OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        })->sum('quantity');

        // Payment method breakdown
        $paymentMethods = (clone $ordersQuery)
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(total) as total'))
            ->groupBy('payment_method')
            ->get();

        // Order status breakdown
        $orderStatuses = (clone $ordersQuery)
            ->select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => [
                    'gross_sales' => $grossSales,
                    'net_sales' => $netSales,
                    'total_discounts' => $totalDiscounts,
                    'shipping_revenue' => $shippingRevenue,
                    'tax_collected' => $taxCollected,
                    'refunded_amount' => $refundedAmount,
                    'total_orders' => $totalOrders,
                    'total_units_sold' => $totalUnitsSold,
                    'average_order_value' => $averageOrderValue,
                ],
                'payment_methods' => $paymentMethods,
                'order_statuses' => $orderStatuses,
            ],
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 30);
        $timeline = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $label = $date->format('M d');

            $daySales = (float) Order::whereDate('created_at', $dateStr)
                ->where('payment_status', 'paid')
                ->sum('total');

            $dayOrders = Order::whereDate('created_at', $dateStr)->count();

            $timeline[] = [
                'date' => $label,
                'sales' => $daySales,
                'orders' => $dayOrders,
            ];
        }

        // Top 5 selling categories
        $topCategories = DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->select('categories.name', DB::raw('sum(order_items.quantity) as units_sold'), DB::raw('sum(order_items.subtotal) as revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'timeline' => $timeline,
                'top_categories' => $topCategories,
            ],
        ]);
    }
}
