@extends('vendor.layouts.master')

@section('title', 'Order #' . str_pad($order->id, 5, '0', STR_PAD_LEFT))

@section('content')

<x-admin.page-header
    :title="'Order #' . str_pad($order->id, 5, '0', STR_PAD_LEFT)"
    icon="bi bi-receipt"
    :subtitle="'Placed on ' . ($order->created_at ? $order->created_at->format('F d, Y \a\t h:i A') : 'N/A')"
    :breadcrumbs="['Orders' => route('vendor.orders.index'), 'Order #' . str_pad($order->id, 5, '0', STR_PAD_LEFT) => '#']">
    <x-slot:actions>
        <a href="{{ route('vendor.orders.index') }}"
           class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-sm"
           style="border-radius:10px; font-size:.85rem;">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </x-slot:actions>
</x-admin.page-header>

<div class="row g-4">

    {{-- Order Line Items (Vendor Items Only) --}}
    <div class="col-lg-8">
        <x-admin.form-card :title="'Purchased Products'" icon="bi bi-box-seam" class="mb-4">
            <x-slot:headerActions>
                @php
                    $status = strtolower($order->status ?? 'pending');
                    $badgeClass = match ($status) {
                        'completed', 'paid' => 'bg-success-soft',
                        'pending', 'processing' => 'bg-warning-soft',
                        'canceled', 'cancelled', 'failed' => 'bg-danger-soft',
                        default => 'bg-secondary-soft',
                    };
                @endphp
                <span class="badge {{ $badgeClass }} px-3 py-1">
                    {{ ucfirst($order->status ?? 'Pending') }}
                </span>
            </x-slot:headerActions>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Unit Price</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->details as $detail)
                            @php
                                $thumb = $detail->product?->image_url ?: $detail->product?->images?->first()?->image_url;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width:48px; height:48px; border-radius:8px; overflow:hidden; background:#f1f5f9; border:1px solid #e2e8f0; flex-shrink:0;">
                                            @if ($thumb)
                                                <img src="{{ \Illuminate\Support\Str::startsWith($thumb, ['http://', 'https://']) ? $thumb : asset('storage/' . $thumb) }}"
                                                     alt="{{ $detail->product?->name }}"
                                                     style="width:100%; height:100%; object-fit:cover;">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                                    <i class="bi bi-image" style="font-size:1.2rem;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">
                                                {{ $detail->product?->name ?? 'Deleted Product' }}
                                            </div>
                                            @if ($detail->product)
                                                <small class="text-muted">Product ID: #{{ $detail->product->id }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-semibold text-muted">
                                    ${{ number_format((float) $detail->price, 2) }}
                                </td>
                                <td class="text-center fw-bold">
                                    {{ $detail->quantity }}
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    ${{ number_format((float) ($detail->quantity * $detail->price), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No items found for this order.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Vendor Items Subtotal:</td>
                            <td class="text-end fw-bold text-primary fs-5">
                                ${{ number_format((float) $vendorSubtotal, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-admin.form-card>
    </div>

    {{-- Customer & Order Status Sidebar --}}
    <div class="col-lg-4">

        {{-- Fulfillment Status Update --}}
        <x-admin.form-card :title="'Fulfillment Status'" icon="bi bi-clock-history" class="mb-4">
            <form action="{{ route('vendor.orders.updateStatus', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="orderStatus">Current Order Status</label>
                    <select name="status" id="orderStatus" class="form-select">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="canceled" {{ in_array($order->status, ['canceled', 'cancelled']) ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm fw-semibold" style="border-radius:10px;">
                    <i class="bi bi-check2-circle"></i> Update Status
                </button>
            </form>
        </x-admin.form-card>

        {{-- Customer Info --}}
        <x-admin.form-card :title="'Customer Details'" icon="bi bi-person-fill" class="mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;font-size:1.1rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    {{ strtoupper(substr($order->customer?->name ?? ($order->guest_email ?? 'G'), 0, 1)) }}
                </div>
                <div>
                    <div class="fw-bold text-dark">
                        {{ $order->customer?->name ?? 'Guest Customer' }}
                    </div>
                    <div class="text-muted small">
                        {{ $order->customer?->email ?? ($order->guest_email ?? 'No email provided') }}
                    </div>
                </div>
            </div>

            <ul class="list-group list-group-flush small">
                @if ($order->customer?->phone)
                    <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                        <span class="text-muted">Phone:</span>
                        <span class="fw-semibold">{{ $order->customer->phone }}</span>
                    </li>
                @endif
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">Customer Type:</span>
                    <span class="badge {{ $order->customer ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">
                        {{ $order->customer ? 'Registered Customer' : 'Guest Checkout' }}
                    </span>
                </li>
            </ul>
        </x-admin.form-card>

        {{-- Order Summary Meta --}}
        <x-admin.form-card :title="'Order Summary'" icon="bi bi-info-circle-fill">
            <dl class="mb-0 small">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <dt class="text-muted fw-normal">Order ID</dt>
                    <dd class="fw-bold mb-0">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</dd>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <dt class="text-muted fw-normal">Total Items (Yours)</dt>
                    <dd class="fw-bold mb-0">{{ $order->details->sum('quantity') }}</dd>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <dt class="text-muted fw-normal">Order Date</dt>
                    <dd class="fw-semibold mb-0">{{ $order->created_at?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <dt class="text-muted fw-normal">Payment Method</dt>
                    <dd class="fw-semibold mb-0 text-capitalize">{{ $order->payment_method ?? 'Standard' }}</dd>
                </div>
            </dl>
        </x-admin.form-card>

    </div>

</div>

@endsection
