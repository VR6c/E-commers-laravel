<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $vendorId = Auth::guard('vendor')->id();

        // Orders that contain at least one product from this vendor
        $ordersBase = fn () => Order::whereHas('details.product', fn ($q) => $q->where('vendor_id', $vendorId));

        // Vendor-specific revenue: sum(qty * price) on order_details for this vendor's products
        $salesBase = fn () => OrderDetail::whereHas('product', fn ($q) => $q->where('vendor_id', $vendorId))
            ->whereHas('order', fn ($q) => $q->where('status', 'completed'))
            ->selectRaw('SUM(quantity * price) as revenue');

        $data = [
            'totalSales' => (clone $salesBase())->value('revenue') ?? 0,

            'todaySales' => OrderDetail::whereHas('product', fn ($q) => $q->where('vendor_id', $vendorId))
                ->whereHas('order', fn ($q) => $q->where('status', 'completed')->whereDate('created_at', today()))
                ->sum(DB::raw('quantity * price')),

            'totalOrders' => $ordersBase()->count(),
            'completedOrders' => $ordersBase()->where('status', 'completed')->count(),
            'totalProducts' => Product::where('vendor_id', $vendorId)->count(),
        ];

        // Recent 5 orders — eager-load only this vendor's items
        $recentOrders = $ordersBase()
            ->with([
                'details' => fn ($q) => $q->whereHas('product', fn ($p) => $p->where('vendor_id', $vendorId)),
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

        // Order status breakdown
        $orderStatusCounts = [
            'completed' => $ordersBase()->where('status', 'completed')->count(),
            'pending' => $ordersBase()->where('status', 'pending')->count(),
            'cancelled' => $ordersBase()->where('status', 'cancelled')->count(),
        ];

        // Last 7 days chart — vendor item revenue per day
        $chartLabels = [];
        $chartSales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('M j');
            $chartSales[] = OrderDetail::whereHas('product', fn ($q) => $q->where('vendor_id', $vendorId))
                ->whereHas('order', fn ($q) => $q->where('status', 'completed')->whereDate('created_at', $date->toDateString()))
                ->sum(DB::raw('quantity * price'));
        }

        return view('vendor.dashboard.index', compact(
            'data', 'recentOrders', 'orderStatusCounts', 'chartLabels', 'chartSales'
        ));
    }
}
