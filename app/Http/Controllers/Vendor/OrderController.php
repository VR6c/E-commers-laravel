<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index()
    {
        return view('vendor.orders.index');
    }

    public function getData(Request $request)
    {
        $vendorId = Auth::guard('vendor')->id();

        $query = Order::whereHas('details.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })
            ->with([
                'details' => function ($q) use ($vendorId) {
                    $q->whereHas('product', fn ($p) => $p->where('vendor_id', $vendorId));
                },
                'details.product',
                'customer',
            ])
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('customer', function (Order $order) {
                if ($order->customer) {
                    return e($order->customer->name) . ' <small class="text-muted">(' . e($order->customer->email) . ')</small>';
                } elseif ($order->guest_email) {
                    return e($order->guest_email) . ' <span class="badge bg-secondary-subtle text-secondary ms-1">Guest</span>';
                }

                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('order_date', fn (Order $order) => $order->created_at?->format('Y-m-d H:i') ?? '—')
            ->addColumn('total_price', function (Order $order) {
                $vendorTotal = $order->details->sum(fn ($d) => $d->quantity * $d->price);

                return '$' . number_format((float) $vendorTotal, 2);
            })
            ->editColumn('status', function (Order $order) {
                $status = strtolower($order->status ?? 'pending');
                $class = match ($status) {
                    'completed', 'paid' => 'bg-success-soft',
                    'pending', 'processing' => 'bg-warning-soft',
                    'canceled', 'cancelled', 'failed' => 'bg-danger-soft',
                    default => 'bg-secondary-soft',
                };

                return '<span class="badge ' . $class . '">' . ucfirst($order->status ?? 'Pending') . '</span>';
            })
            ->addColumn('action', function (Order $order) {
                return '
                    <div class="dt-actions">
                        <a href="' . route('vendor.orders.show', $order->id) . '"
                           class="btn-action btn-action-view" title="View Details">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                        <button type="button"
                                class="btn-action btn-action-delete"
                                onclick="deleteOrder(' . $order->id . ')" title="Remove Item(s)">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['customer', 'status', 'action'])
            ->setRowId('id')
            ->make(true);
    }

    public function show($id)
    {
        $vendorId = Auth::guard('vendor')->id();

        $order = Order::whereHas('details.product', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->with([
            'details' => function ($q) use ($vendorId) {
                $q->whereHas('product', fn ($p) => $p->where('vendor_id', $vendorId))
                  ->with('product.images');
            },
            'customer',
        ])->findOrFail($id);

        $vendorSubtotal = $order->details->sum(fn ($d) => $d->quantity * $d->price);

        return view('vendor.orders.show', compact('order', 'vendorSubtotal'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,canceled',
        ]);

        $vendorId = Auth::guard('vendor')->id();
        $order = Order::whereHas('details.product', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->findOrFail($id);

        $order->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    public function destroy($id)
    {
        $vendorId = Auth::guard('vendor')->id();

        $order = Order::whereHas('details.product', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->with('details.product')->findOrFail($id);

        $vendorDetails = $order->details->filter(fn ($d) => $d->product?->vendor_id === $vendorId);
        $otherDetails  = $order->details->filter(fn ($d) => $d->product?->vendor_id !== $vendorId);

        foreach ($vendorDetails as $detail) {
            $detail->delete();
        }

        if ($otherDetails->isEmpty()) {
            $order->delete();
        } else {
            $order->total_amount = $otherDetails->sum(fn ($d) => $d->quantity * $d->price);
            $order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Order item(s) removed successfully.',
        ]);
    }
}
