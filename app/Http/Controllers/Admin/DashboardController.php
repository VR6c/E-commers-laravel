<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startThisWeek = Carbon::today()->subDays(6)->startOfDay();
        $startLastWeek = Carbon::today()->subDays(13)->startOfDay();
        $endLastWeek   = Carbon::today()->subDays(7)->endOfDay();

        // --- Core Metrics ---
        $totalSales     = (float) Order::where('status', 'completed')->sum('total_amount');
        $thisWeekSales  = (float) Order::where('status', 'completed')->where('created_at', '>=', $startThisWeek)->sum('total_amount');
        $lastWeekSales  = (float) Order::where('status', 'completed')->whereBetween('created_at', [$startLastWeek, $endLastWeek])->sum('total_amount');
        $salesGrowth    = $lastWeekSales > 0 ? round((($thisWeekSales - $lastWeekSales) / $lastWeekSales) * 100, 1) : ($thisWeekSales > 0 ? 100 : 0);

        $totalOrders    = Order::count();
        $thisWeekOrders = Order::where('created_at', '>=', $startThisWeek)->count();
        $lastWeekOrders = Order::whereBetween('created_at', [$startLastWeek, $endLastWeek])->count();
        $ordersGrowth   = $lastWeekOrders > 0 ? round((($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 1) : ($thisWeekOrders > 0 ? 100 : 0);

        $completedOrders  = Order::where('status', 'completed')->count();
        $pendingOrders    = Order::where('status', 'pending')->count();
        $cancelledOrders  = Order::where('status', 'cancelled')->count();

        $totalVendors     = Vendor::where('status', 'active')->count();
        $totalCustomers   = Customer::where('status', 'active')->count();

        // --- Low Stock Inventory Alert ---
        $lowStockCount = Schema::hasColumn('products', 'stock')
            ? Product::where('stock', '<=', 5)->count()
            : ProductVariant::where('stock', '<=', 5)->count();

        $data = [
            'totalSales'       => $totalSales,
            'todaySales'       => (float) Order::whereDate('created_at', today())->where('status', 'completed')->sum('total_amount'),
            'salesGrowth'      => $salesGrowth,
            'totalOrders'      => $totalOrders,
            'ordersGrowth'     => $ordersGrowth,
            'completedOrders'  => $completedOrders,
            'pendingOrders'    => $pendingOrders,
            'cancelledOrders'  => $cancelledOrders,
            'totalVendors'     => $totalVendors,
            'totalCustomers'   => $totalCustomers,
            'lowStockCount'    => $lowStockCount,
        ];

        // --- Sales Chart: default 7 days ---
        $chartData = $this->getChartMetrics('7d');

        // --- Order Status Doughnut ---
        $orderStatusCounts = [
            'completed'  => $completedOrders,
            'pending'    => $pendingOrders,
            'cancelled'  => $cancelledOrders,
        ];

        // --- Recent Orders (last 6 with eager-loaded customer) ---
        $recentOrders = Order::with('customer')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard.index', [
            'data'              => $data,
            'chartLabels'       => $chartData['labels'],
            'chartSales'        => $chartData['sales'],
            'chartOrders'       => $chartData['orders'],
            'orderStatusCounts' => $orderStatusCounts,
            'recentOrders'      => $recentOrders,
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
            'range'   => $range,
            'labels'  => $metrics['labels'],
            'sales'   => $metrics['sales'],
            'orders'  => $metrics['orders'],
        ]);
    }

    /**
     * Helper to retrieve sales and orders trend metrics by range.
     */
    private function getChartMetrics(string $range): array
    {
        if ($range === '30d') {
            $days = collect(range(29, 0))->map(fn($i) => Carbon::today()->subDays($i));
            $salesByDate = Order::where('status', 'completed')
                ->where('created_at', '>=', Carbon::today()->subDays(29)->startOfDay())
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(id) as count')
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $labels = $days->map(fn($d) => $d->format('M j'))->values()->toArray();
            $sales  = $days->map(fn($d) => (float) ($salesByDate[$d->toDateString()]->total ?? 0))->values()->toArray();
            $orders = $days->map(fn($d) => (int) ($salesByDate[$d->toDateString()]->count ?? 0))->values()->toArray();

        } elseif ($range === '1y') {
            $months = collect(range(11, 0))->map(fn($i) => Carbon::today()->startOfMonth()->subMonths($i));
            $salesByMonth = Order::where('status', 'completed')
                ->where('created_at', '>=', Carbon::today()->startOfMonth()->subMonths(11))
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(total_amount) as total, COUNT(id) as count")
                ->groupBy('ym')
                ->get()
                ->keyBy('ym');

            $labels = $months->map(fn($m) => $m->format('M Y'))->values()->toArray();
            $sales  = $months->map(fn($m) => (float) ($salesByMonth[$m->format('Y-m')]->total ?? 0))->values()->toArray();
            $orders = $months->map(fn($m) => (int) ($salesByMonth[$m->format('Y-m')]->count ?? 0))->values()->toArray();

        } else { // 7d default
            $days = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i));
            $salesByDate = Order::where('status', 'completed')
                ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(id) as count')
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $labels = $days->map(fn($d) => $d->format('D, M j'))->values()->toArray();
            $sales  = $days->map(fn($d) => (float) ($salesByDate[$d->toDateString()]->total ?? 0))->values()->toArray();
            $orders = $days->map(fn($d) => (int) ($salesByDate[$d->toDateString()]->count ?? 0))->values()->toArray();
        }

        return [
            'labels' => $labels,
            'sales'  => $sales,
            'orders' => $orders,
        ];
    }
}

