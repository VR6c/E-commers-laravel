<?php

namespace App\Http\Controllers\Store\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for the authenticated customer.
     */
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $orders = Order::with(['details.product.thumbnail', 'shippingAddress', 'payments'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $currency = activeCurrency();

        return view('themes.xylo.customer.orders.index', compact('customer', 'orders', 'currency'));
    }

    /**
     * Display the specified order details for the authenticated customer.
     */
    public function show(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();

        $order = Order::with(['details.product.thumbnail', 'shippingAddress', 'payments.gateway'])
            ->where('customer_id', $customer->id)
            ->where('id', $id)
            ->firstOrFail();

        $currency = activeCurrency();

        return view('themes.xylo.customer.orders.show', compact('customer', 'order', 'currency'));
    }
}
