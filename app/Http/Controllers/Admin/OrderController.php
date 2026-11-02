<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.orders.index', ['statusFilter' => null, 'pageTitle' => 'All Orders']);
    }

    public function pending()
    {
        return view('admin.orders.index', ['statusFilter' => 'pending', 'pageTitle' => 'Pending Orders']);
    }

    public function completed()
    {
        return view('admin.orders.index', ['statusFilter' => 'completed', 'pageTitle' => 'Completed Orders']);
    }

    public function getData(Request $request)
    {
        $query = Order::query()->latest()->with('customer');

        if ($request->filled('status_filter')) {
            $query->where('status', $request->input('status_filter'));
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('customer', function (Order $order) {
                if ($order->customer) {
                    return $order->customer->name.' ('.$order->customer->email.')';
                }

                return $order->guest_email ?? 'Guest';
            })
            ->addColumn('order_date', function (Order $order) {
                return $order->created_at?->format('Y-m-d H:i');
            })
            ->addColumn('total_price', function (Order $order) {
                return number_format((float) $order->total_amount, 2);
            })
            ->editColumn('status', function (Order $order) {
                $status = strtolower($order->status);
                $class = match ($status) {
                    'completed', 'paid' => 'bg-success-soft',
                    'pending', 'processing' => 'bg-warning-soft',
                    'cancelled', 'failed' => 'bg-danger-soft',
                    default => 'bg-secondary-soft',
                };

                return '<span class="badge '.$class.'">'.ucfirst($order->status).'</span>';
            })
            ->addColumn('action', function (Order $order) {
                return '<div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn-action-delete" onclick="deleteOrder('.$order->id.')" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>';
            })
            ->rawColumns(['status', 'action'])
            ->setRowId('id')
            ->make(true);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['success' => true, 'message' => 'Order deleted successfully.']);
    }
}
