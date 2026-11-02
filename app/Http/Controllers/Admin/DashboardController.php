<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startThisWeek = Carbon::today()->subDays(6)->startOfDay();
        $startLastWeek = Carbon::today()->subDays(13)->startOfDay();
        $endLastWeek = Carbon::today()->subDays(7)->endOfDay();

        // --- Core Metrics (single conditional-aggregate query instead of ~12 separate ones) ---
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $stats = Order::toBase()->selectRaw(
            "COUNT(*) as total_orders,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_orders,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_orders,
            COALESCE(SUM(CASE WHEN status IN ('canceled', 'cancelled') THEN 1 ELSE 0 END), 0) as cancelled_orders,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount END), 0) as total_sales,
            COALESCE(SUM(CASE WHEN status = 'completed' AND created_at >= ? THEN total_amount END), 0) as this_week_sales,
            COALESCE(SUM(CASE WHEN status = 'completed' AND created_at BETWEEN ? AND ? THEN total_amount END), 0) as last_week_sales,
            COALESCE(SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END), 0) as this_week_orders,
            COALESCE(SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) as last_week_orders,
            COALESCE(SUM(CASE WHEN status = 'completed' AND created_at BETWEEN ? AND ? THEN total_amount END), 0) as today_sales",
            [
                $startThisWeek,
                $startLastWeek, $endLastWeek,
                $startThisWeek,
                $startLastWeek, $endLastWeek,
                $todayStart, $todayEnd,
            ]
        )->first();

        $totalSales = (float) $stats->total_sales;
        $thisWeekSales = (float) $stats->this_week_sales;
        $lastWeekSales = (float) $stats->last_week_sales;
        $salesGrowth = $lastWeekSales > 0 ? round((($thisWeekSales - $lastWeekSales) / $lastWeekSales) * 100, 1) : ($thisWeekSales > 0 ? 100 : 0);

        $totalOrders = (int) $stats->total_orders;
        $thisWeekOrders = (int) $stats->this_week_orders;
        $lastWeekOrders = (int) $stats->last_week_orders;
        $ordersGrowth = $lastWeekOrders > 0 ? round((($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 1) : ($thisWeekOrders > 0 ? 100 : 0);

        $completedOrders = (int) $stats->completed_orders;
        $pendingOrders = (int) $stats->pending_orders;
        $cancelledOrders = (int) $stats->cancelled_orders;

        $totalVendors = Vendor::where('status', 'active')->count();
        $totalCustomers = Customer::where('status', 'active')->count();

        // --- Low Stock Inventory Alert (schema lookup cached: it hits information_schema) ---
        $productsHaveStock = Cache::rememberForever(
            'schema:products:has_stock',
            fn () => Schema::hasColumn('products', 'stock')
        );
        $lowStockCount = $productsHaveStock
            ? Product::where('stock', '<=', 5)->count()
            : ProductVariant::where('stock', '<=', 5)->count();

        $data = [
            'totalSales' => $totalSales,
            'todaySales' => (float) $stats->today_sales,
            'salesGrowth' => $salesGrowth,
            'totalOrders' => $totalOrders,
            'ordersGrowth' => $ordersGrowth,
            'completedOrders' => $completedOrders,
            'pendingOrders' => $pendingOrders,
            'cancelledOrders' => $cancelledOrders,
            'totalVendors' => $totalVendors,
            'totalCustomers' => $totalCustomers,
            'lowStockCount' => $lowStockCount,
        ];

        // --- Sales Chart: default 7 days ---
        $chartData = $this->getChartMetrics('7d');

        // --- Order Status Doughnut ---
        $orderStatusCounts = [
            'completed' => $completedOrders,
            'pending' => $pendingOrders,
            'cancelled' => $cancelledOrders,
        ];

        // --- Recent Orders (last 6 with eager-loaded customer) ---
        $recentOrders = Order::with('customer')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard.index', [
            'data' => $data,
            'chartLabels' => $chartData['labels'],
            'chartSales' => $chartData['sales'],
            'chartOrders' => $chartData['orders'],
            'orderStatusCounts' => $orderStatusCounts,
            'recentOrders' => $recentOrders,
        ]);
    }

    /**
     * AJAX endpoint for switching Chart timeframes dynamically (7d, 30d, 1y).
     */
    public function chartData(Request $request)
    {
        $range = in_array($request->query('range'), ['7d', '30d', '1y']) ? $request->query('range') : '7d';
        $metrics = $this->getChartMetrics($range);

        return response()->json([
            'success' => true,
            'range' => $range,
            'labels' => $metrics['labels'],
            'sales' => $metrics['sales'],
            'orders' => $metrics['orders'],
        ]);
    }

    /**
     * Helper to retrieve sales and orders trend metrics by range.
     */
    private function getChartMetrics(string $range): array
    {
        if ($range === '30d') {
            $days = collect(range(29, 0))->map(fn ($i) => Carbon::today()->subDays($i));
            $salesByDate = Order::where('status', 'completed')
                ->where('created_at', '>=', Carbon::today()->subDays(29)->startOfDay())
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(id) as count')
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $labels = $days->map(fn ($d) => $d->format('M j'))->values()->toArray();
            $sales = $days->map(fn ($d) => (float) ($salesByDate[$d->toDateString()]->total ?? 0))->values()->toArray();
            $orders = $days->map(fn ($d) => (int) ($salesByDate[$d->toDateString()]->count ?? 0))->values()->toArray();

        } elseif ($range === '1y') {
            $months = collect(range(11, 0))->map(fn ($i) => Carbon::today()->startOfMonth()->subMonths($i));
            // Group by day in SQL (portable across MySQL/Postgres/SQLite) and bucket into months in PHP
            $monthly = Order::where('status', 'completed')
                ->where('created_at', '>=', Carbon::today()->startOfMonth()->subMonths(11))
                ->toBase()
                ->selectRaw('DATE(created_at) as day, SUM(total_amount) as total, COUNT(id) as count')
                ->groupBy('day')
                ->get()
                ->groupBy(fn ($row) => substr((string) $row->day, 0, 7))
                ->map(fn ($rows) => (object) ['total' => $rows->sum('total'), 'count' => $rows->sum('count')]);
            $salesByMonth = $monthly;

            $labels = $months->map(fn ($m) => $m->format('M Y'))->values()->toArray();
            $sales = $months->map(fn ($m) => (float) ($salesByMonth[$m->format('Y-m')]->total ?? 0))->values()->toArray();
            $orders = $months->map(fn ($m) => (int) ($salesByMonth[$m->format('Y-m')]->count ?? 0))->values()->toArray();

        } else { // 7d default
            $days = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
            $salesByDate = Order::where('status', 'completed')
                ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(id) as count')
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $labels = $days->map(fn ($d) => $d->format('D, M j'))->values()->toArray();
            $sales = $days->map(fn ($d) => (float) ($salesByDate[$d->toDateString()]->total ?? 0))->values()->toArray();
            $orders = $days->map(fn ($d) => (int) ($salesByDate[$d->toDateString()]->count ?? 0))->values()->toArray();
        }

        return [
            'labels' => $labels,
            'sales' => $sales,
            'orders' => $orders,
        ];
    }
}
