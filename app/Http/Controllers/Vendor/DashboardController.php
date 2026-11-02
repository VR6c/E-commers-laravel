<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $vendorId = Auth::guard('vendor')->id();

        // Orders that contain at least one product from this vendor
        $vendorProductIds = Product::select('id')->where('vendor_id', $vendorId);
        $ordersBase = fn () => Order::whereIn(
            'id',
            OrderDetail::select('order_id')->whereIn('product_id', $vendorProductIds)
        );

        // Order counts and status breakdown in a single query
        $orderStats = $ordersBase()->toBase()->selectRaw(
            "COUNT(*) as total,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending,
            COALESCE(SUM(CASE WHEN status IN ('canceled', 'cancelled') THEN 1 ELSE 0 END), 0) as cancelled"
        )->first();

        // Vendor-specific revenue: sum(qty * price) on this vendor's items of completed orders
        $revenueBase = fn () => OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('products.vendor_id', $vendorId)
            ->where('orders.status', 'completed');

        $todayStart = today()->startOfDay();
        $todayEnd = today()->endOfDay();

        $revenue = $revenueBase()->toBase()->selectRaw(
            'COALESCE(SUM(order_details.quantity * order_details.price), 0) as total,
            COALESCE(SUM(CASE WHEN orders.created_at BETWEEN ? AND ? THEN order_details.quantity * order_details.price ELSE 0 END), 0) as today',
            [$todayStart, $todayEnd]
        )->first();

        $data = [
            'totalSales' => $revenue->total,
            'todaySales' => $revenue->today,
            'totalOrders' => (int) $orderStats->total,
            'completedOrders' => (int) $orderStats->completed,
            'totalProducts' => Product::where('vendor_id', $vendorId)->count(),
        ];

        // Recent 5 orders — eager-load only this vendor's items
        $recentOrders = $ordersBase()
            ->with([
                'details' => fn ($q) => $q->whereIn('product_id', $vendorProductIds),
                'details.product',
                'customer',
            ])
            ->latest()
            ->take(5)
            ->get();

        // Attach vendor subtotal to each recent order
        foreach ($recentOrders as $order) {
            $order->vendor_total = $order->details->sum(fn ($d) => $d->quantity * $d->price);
        }

        $orderStatusCounts = [
            'completed' => (int) $orderStats->completed,
            'pending' => (int) $orderStats->pending,
            'cancelled' => (int) $orderStats->cancelled,
        ];

        // Last 7 days chart — one grouped query instead of one query per day
        $revenueByDate = $revenueBase()
            ->where('orders.created_at', '>=', now()->subDays(6)->startOfDay())
            ->toBase()
            ->selectRaw('DATE(orders.created_at) as day, SUM(order_details.quantity * order_details.price) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $chartLabels = [];
        $chartSales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('M j');
            $chartSales[] = $revenueByDate[$date->toDateString()] ?? 0;
        }

        return view('vendor.dashboard.index', compact(
            'data', 'recentOrders', 'orderStatusCounts', 'chartLabels', 'chartSales'
        ));
    }
}
