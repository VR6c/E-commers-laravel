@extends('vendor.layouts.master')

@section('title', 'Vendor Dashboard')

@section('content')
@php $vendor = Auth::guard('vendor')->user(); @endphp

<div class="dashboard-wrapper" id="vendorDashboardApp">

    {{-- ======================= HERO HEADER ======================= --}}
    <div class="dash-hero mb-4 fade-in-up">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-hero-avatar shadow-sm">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h1 class="dash-hero-title m-0">Vendor Dashboard</h1>
                            <span class="badge dash-live-badge"><span class="live-dot"></span> Active Seller</span>
                        </div>
                        <p class="dash-hero-subtitle m-0">Welcome back, <strong>{{ $vendor->name }}</strong> — here is your real-time store performance summary.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="d-flex justify-content-start justify-content-lg-end align-items-center gap-2 flex-wrap pt-1 pt-lg-0">
                    <span class="dash-date-pill shadow-sm">
                        <i class="bi bi-calendar3 me-1 text-primary"></i> {{ date('l, M j, Y') }}
                    </span>
                    <a href="{{ route('vendor.products.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm rounded-3 py-2 px-3 fw-semibold">
                        <i class="bi bi-plus-lg"></i> Add Product
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================= KPI STAT CARDS ======================== --}}
    <div class="row mb-4 g-3" id="kpiCardsContainer">
        {{-- Total Sales --}}
        <div class="col-xl-4 col-sm-6">
            <div class="kpi-card kpi-card-sales fade-in-up">
                <div class="kpi-card-top">
                    <div class="kpi-icon-wrap kpi-icon-sales">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <span class="kpi-pill kpi-pill-emerald">Gross Revenue</span>
                </div>
                <div class="kpi-card-body">
                    <span class="kpi-label">My Total Sales</span>
                    <h2 class="kpi-value" data-count="{{ $data['totalSales'] }}" data-prefix="$" data-decimals="2">$0.00</h2>
                    <div class="kpi-meta">
                        <span>Today's Sales: <strong>${{ number_format($data['todaySales'], 2) }}</strong></span>
                    </div>
                </div>
                <a href="{{ route('vendor.orders.index') }}" class="kpi-footer">
                    <span>View Orders</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Orders --}}
        <div class="col-xl-4 col-sm-6">
            <div class="kpi-card kpi-card-orders fade-in-up">
                <div class="kpi-card-top">
                    <div class="kpi-icon-wrap kpi-icon-orders">
                        <i class="bi bi-bag-check-fill"></i>
                    </div>
                    <span class="kpi-pill kpi-pill-purple">Fulfillment</span>
                </div>
                <div class="kpi-card-body">
                    <span class="kpi-label">My Orders</span>
                    <h2 class="kpi-value" data-count="{{ $data['totalOrders'] }}" data-prefix="" data-decimals="0">0</h2>
                    <div class="kpi-meta">
                        <span class="text-success fw-medium">Completed: <strong>{{ $data['completedOrders'] }}</strong></span>
                        <span class="text-muted mx-1">|</span>
                        <span class="text-warning fw-medium">Active: <strong>{{ $orderStatusCounts['pending'] }}</strong></span>
                    </div>
                </div>
                <a href="{{ route('vendor.orders.index') }}" class="kpi-footer">
                    <span>Manage Orders</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Active Products --}}
        <div class="col-xl-4 col-sm-12">
            <div class="kpi-card kpi-card-vendors fade-in-up">
                <div class="kpi-card-top">
                    <div class="kpi-icon-wrap kpi-icon-vendors">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <span class="kpi-pill kpi-pill-warning">Store Catalog</span>
                </div>
                <div class="kpi-card-body">
                    <span class="kpi-label">Active Products</span>
                    <h2 class="kpi-value" data-count="{{ $data['totalProducts'] }}" data-prefix="" data-decimals="0">0</h2>
                    <div class="kpi-meta">
                        <span>Listed in your shop catalog</span>
                    </div>
                </div>
                <a href="{{ route('vendor.products.index') }}" class="kpi-footer">
                    <span>Manage Products</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ======================= CHARTS SECTION ======================== --}}
    <div class="row mb-4 g-3">
        {{-- Interactive Sales Trend Line Chart --}}
        <div class="col-lg-8">
            <div class="dash-card h-100 position-relative shadow-sm">
                <div class="dash-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div>
                            <h5 class="dash-card-title m-0">Sales Revenue Trend</h5>
                            <p class="dash-card-subtitle m-0">Last 7 days revenue performance</p>
                        </div>
                    </div>
                </div>
                <div class="dash-card-body position-relative">
                    <div class="chart-wrap">
                        <canvas id="salesChart" height="280"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Status Doughnut Chart --}}
        <div class="col-lg-4">
            <div class="dash-card h-100 shadow-sm">
                <div class="dash-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon-box bg-success-subtle text-success">
                            <i class="bi bi-pie-chart-fill"></i>
                        </div>
                        <div>
                            <h5 class="dash-card-title m-0">Order Status</h5>
                            <p class="dash-card-subtitle m-0">Fulfillment ratio breakdown</p>
                        </div>
                    </div>
                </div>
                <div class="dash-card-body d-flex flex-column align-items-center justify-content-center">
                    <div class="doughnut-wrap">
                        <canvas id="statusChart"></canvas>
                        <div class="doughnut-center">
                            <span class="doughnut-center-value" id="doughnutTotalCount">
                                {{ $orderStatusCounts['completed'] + $orderStatusCounts['pending'] + $orderStatusCounts['cancelled'] }}
                            </span>
                            <span class="doughnut-center-label">Total</span>
                        </div>
                    </div>

                    {{-- Legend & Progress bars --}}
                    <div class="status-legend w-100 mt-4">
                        @php
                            $grandTotal = array_sum($orderStatusCounts);
                            $completedPct = $grandTotal > 0 ? round(($orderStatusCounts['completed'] / $grandTotal) * 100, 1) : 0;
                            $pendingPct = $grandTotal > 0 ? round(($orderStatusCounts['pending'] / $grandTotal) * 100, 1) : 0;
                            $cancelledPct = $grandTotal > 0 ? round(($orderStatusCounts['cancelled'] / $grandTotal) * 100, 1) : 0;
                        @endphp
                        <div class="status-item mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="d-flex align-items-center gap-2 font-size-13 text-dark fw-semibold">
                                    <span class="status-dot dot-emerald"></span> Completed
                                </span>
                                <span class="font-size-13 fw-bold text-dark">{{ $orderStatusCounts['completed'] }} <small class="text-muted">({{ $completedPct }}%)</small></span>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-emerald" style="width: {{ $completedPct }}%"></div>
                            </div>
                        </div>

                        <div class="status-item mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="d-flex align-items-center gap-2 font-size-13 text-dark fw-semibold">
                                    <span class="status-dot dot-amber"></span> Pending
                                </span>
                                <span class="font-size-13 fw-bold text-dark">{{ $orderStatusCounts['pending'] }} <small class="text-muted">({{ $pendingPct }}%)</small></span>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-amber" style="width: {{ $pendingPct }}%"></div>
                            </div>
                        </div>

                        <div class="status-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="d-flex align-items-center gap-2 font-size-13 text-dark fw-semibold">
                                    <span class="status-dot dot-rose"></span> Cancelled
                                </span>
                                <span class="font-size-13 fw-bold text-dark">{{ $orderStatusCounts['cancelled'] }} <small class="text-muted">({{ $cancelledPct }}%)</small></span>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-rose" style="width: {{ $cancelledPct }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- =================== RECENT TRANSACTIONS & QUICK ACTIONS ========================= --}}
    <div class="row g-3">
        {{-- Recent Orders Table --}}
        <div class="col-lg-8">
            <div class="dash-card h-100 shadow-sm">
                <div class="dash-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon-box bg-indigo-subtle text-indigo">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div>
                            <h5 class="dash-card-title m-0">Recent Store Orders</h5>
                            <p class="dash-card-subtitle m-0">Latest purchases for your products</p>
                        </div>
                    </div>
                    <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-primary btn-sm rounded-3 fw-semibold">
                        View All Orders <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="dash-card-body p-0">
                    @if($recentOrders->isEmpty())
                        <div class="empty-state text-center py-5">
                            <div class="empty-icon text-muted mb-2">
                                <i class="bi bi-inbox fs-1"></i>
                            </div>
                            <h6 class="empty-title fw-bold">No Orders Yet</h6>
                            <p class="empty-desc text-muted mb-0">Customer orders will appear here automatically.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table modern-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <span class="order-code">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="user-avatar">
                                                    {{ strtoupper(substr($order->customer?->name ?? $order->guest_email ?? 'G', 0, 1)) }}
                                                </div>
                                                <div class="user-info">
                                                    <span class="user-name d-block">{{ $order->customer?->name ?? 'Guest User' }}</span>
                                                    <small class="user-email text-muted">{{ $order->customer?->email ?? $order->guest_email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="order-amount">${{ number_format($order->vendor_total ?? 0, 2) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $s = strtolower($order->status);
                                                $cls = match($s) {
                                                    'completed', 'paid' => 'status-completed',
                                                    'pending', 'processing' => 'status-pending',
                                                    default => 'status-cancelled',
                                                };
                                            @endphp
                                            <span class="badge-status {{ $cls }}">
                                                <span class="dot"></span> {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-muted font-size-13">
                                            {{ $order->created_at ? $order->created_at->format('M j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('vendor.orders.show', $order->id) }}" class="btn-action-icon" title="View Order">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Actions Grid --}}
        <div class="col-lg-4">
            <div class="dash-card h-100 shadow-sm">
                <div class="dash-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon-box bg-warning-subtle text-warning">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div>
                            <h5 class="dash-card-title m-0">Quick Shortcuts</h5>
                            <p class="dash-card-subtitle m-0">Common seller management actions</p>
                        </div>
                    </div>
                </div>
                <div class="dash-card-body">
                    <div class="action-grid">
                        <a href="{{ route('vendor.products.create') }}" class="action-card">
                            <div class="action-icon bg-indigo">
                                <i class="bi bi-plus-lg"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Add New Product</span>
                                <span class="action-desc">Create catalog item</span>
                            </div>
                            <i class="bi bi-chevron-right action-arrow"></i>
                        </a>

                        <a href="{{ route('vendor.products.index') }}" class="action-card">
                            <div class="action-icon bg-cyan">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Manage Products</span>
                                <span class="action-desc">Update stock and variants</span>
                            </div>
                            <i class="bi bi-chevron-right action-arrow"></i>
                        </a>

                        <a href="{{ route('vendor.orders.index') }}" class="action-card">
                            <div class="action-icon bg-emerald">
                                <i class="bi bi-bag-check"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Order Processing</span>
                                <span class="action-desc">Fulfill customer orders</span>
                            </div>
                            <i class="bi bi-chevron-right action-arrow"></i>
                        </a>

                        <a href="{{ route('vendor.reviews.index') }}" class="action-card">
                            <div class="action-icon bg-amber">
                                <i class="bi bi-star"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Product Reviews</span>
                                <span class="action-desc">Customer feedback & ratings</span>
                            </div>
                            <i class="bi bi-chevron-right action-arrow"></i>
                        </a>

                        <a href="{{ route('vendor.profile.edit') }}" class="action-card">
                            <div class="action-icon bg-purple">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Store Profile</span>
                                <span class="action-desc">Account and credentials</span>
                            </div>
                            <i class="bi bi-chevron-right action-arrow"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('css')
<style>
    .dashboard-wrapper {
        font-family: inherit;
        color: #1e293b;
    }

    /* ---------- Hero Header ---------- */
    .dash-hero {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
    }
    .dash-hero-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
        box-shadow: 0 6px 16px -2px rgba(99, 102, 241, 0.35);
    }
    .dash-hero-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .dash-hero-subtitle {
        color: #64748b;
        font-size: .88rem;
    }
    .dash-live-badge {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        border-radius: 20px;
        padding: .25rem .65rem;
        font-size: .72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }
    .live-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        animation: pulseLive 2s infinite;
    }
    @keyframes pulseLive {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .dash-date-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: .5rem .95rem;
        font-size: .82rem;
        font-weight: 600;
        color: #334155;
    }

    /* ---------- KPI Stat Cards ---------- */
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
        transition: all .25s ease;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
    }
    .kpi-card-sales::before     { background: linear-gradient(90deg, #6366f1, #4f46e5); }
    .kpi-card-orders::before    { background: linear-gradient(90deg, #10b981, #059669); }
    .kpi-card-vendors::before   { background: linear-gradient(90deg, #f59e0b, #d97706); }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .kpi-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .85rem;
    }
    .kpi-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #ffffff;
    }
    .kpi-icon-sales     { background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 6px 14px rgba(99, 102, 241, 0.3); }
    .kpi-icon-orders    { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 14px rgba(16, 185, 129, 0.3); }
    .kpi-icon-vendors   { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 6px 14px rgba(245, 158, 11, 0.3); }

    .kpi-pill {
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .55rem;
        border-radius: 20px;
    }
    .kpi-pill-emerald { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .kpi-pill-purple  { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
    .kpi-pill-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }

    .kpi-card-body { flex: 1; }
    .kpi-label {
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        display: block;
        margin-bottom: .2rem;
    }
    .kpi-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 .35rem;
        letter-spacing: -0.02em;
    }
    .kpi-meta {
        font-size: .82rem;
        color: #64748b;
        margin-bottom: .75rem;
    }

    .kpi-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: .75rem;
        border-top: 1px solid #f1f5f9;
        font-size: .8rem;
        font-weight: 700;
        color: #6366f1;
        text-decoration: none;
        transition: color .15s ease;
    }
    .kpi-footer i { transition: transform .15s ease; }
    .kpi-card:hover .kpi-footer i { transform: translateX(4px); }

    /* ---------- Dashboard Generic Cards ---------- */
    .dash-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
    }
    .dash-card-header {
        padding: 1.15rem 1.35rem;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .header-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .bg-primary-subtle { background: #e0e7ff; color: #4338ca; }
    .bg-success-subtle { background: #d1fae5; color: #047857; }
    .bg-warning-subtle { background: #fef3c7; color: #b45309; }
    .bg-indigo-subtle  { background: #e0e7ff; color: #4f46e5; }
    .text-indigo       { color: #4f46e5; }

    .dash-card-title {
        font-size: 1.02rem;
        font-weight: 700;
        color: #0f172a;
    }
    .dash-card-subtitle {
        font-size: .8rem;
        color: #64748b;
    }
    .dash-card-body { padding: 1.35rem; flex: 1; }

    /* ---------- Chart Wrap ---------- */
    .chart-wrap { position: relative; height: 280px; width: 100%; }

    /* ---------- Doughnut & Progress Legends ---------- */
    .doughnut-wrap {
        position: relative;
        max-width: 190px;
        margin: 0 auto;
    }
    .doughnut-center {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }
    .doughnut-center-value {
        display: block;
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }
    .doughnut-center-label {
        display: block;
        font-size: .7rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 3px;
    }

    .font-size-13 { font-size: .82rem; }
    .status-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        display: inline-block;
    }
    .dot-emerald { background: #10b981; }
    .dot-amber   { background: #f59e0b; }
    .dot-rose    { background: #ef4444; }
    .bg-emerald  { background-color: #10b981 !important; }
    .bg-amber    { background-color: #f59e0b !important; }
    .bg-rose     { background-color: #ef4444 !important; }

    .progress-thin {
        height: 5px;
        border-radius: 10px;
        background: #f1f5f9;
        overflow: hidden;
    }

    /* ---------- Modern Table ---------- */
    .modern-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-top: none;
        border-bottom: 1px solid #e2e8f0;
        padding: .85rem 1.25rem;
    }
    .modern-table tbody td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: .85rem;
    }
    .modern-table tbody tr:last-child td { border-bottom: none; }
    .modern-table tbody tr:hover td { background: #f8fafc; }

    .order-code {
        font-family: monospace;
        font-size: .85rem;
        font-weight: 700;
        color: #334155;
    }
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #818cf8);
        color: #ffffff;
        font-size: .75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .user-name { font-weight: 600; color: #0f172a; line-height: 1.2; }
    .user-email { font-size: .75rem; }
    .order-amount { font-weight: 700; color: #0f172a; }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .3rem .65rem;
        border-radius: 20px;
        font-size: .75rem;
        font-weight: 700;
    }
    .badge-status .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
    .status-completed { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .status-completed .dot { background: #10b981; }
    .status-pending   { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .status-pending .dot { background: #f59e0b; }
    .status-cancelled { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .status-cancelled .dot { background: #ef4444; }

    .btn-action-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        text-decoration: none;
        transition: all .15s ease;
    }
    .btn-action-icon:hover {
        background: #e0e7ff;
        color: #4f46e5;
    }

    /* ---------- Action Grid ---------- */
    .action-grid {
        display: flex;
        flex-direction: column;
        gap: .65rem;
    }
    .action-card {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .75rem .95rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        text-decoration: none;
        color: #1e293b;
        transition: all .2s ease;
    }
    .action-card:hover {
        background: #ffffff;
        border-color: #6366f1;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.12);
        transform: translateY(-2px);
        color: #0f172a;
    }
    .action-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: .95rem;
        flex-shrink: 0;
    }
    .bg-indigo  { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .bg-cyan    { background: linear-gradient(135deg, #06b6d4, #0284c7); }
    .bg-emerald { background: linear-gradient(135deg, #10b981, #059669); }
    .bg-amber   { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .bg-purple  { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

    .action-details { flex: 1; }
    .action-title { font-size: .85rem; font-weight: 700; display: block; line-height: 1.2; }
    .action-desc { font-size: .74rem; color: #64748b; }
    .action-arrow { font-size: .75rem; color: #94a3b8; transition: transform .15s ease; }
    .action-card:hover .action-arrow { transform: translateX(3px); color: #6366f1; }

    /* ---------- Responsive Breakpoints ---------- */
    @media (max-width: 991.98px) {
        .dash-hero {
            padding: 1.15rem 1.25rem;
        }
        .dash-hero-title {
            font-size: 1.3rem;
        }
        .dash-card-header {
            flex-wrap: wrap;
            gap: .75rem;
        }
    }

    @media (max-width: 767.98px) {
        .kpi-card {
            padding: 1rem 1.15rem;
        }
        .kpi-value {
            font-size: 1.55rem;
        }
        .chart-wrap {
            height: 240px;
        }
    }

    @media (max-width: 575.98px) {
        .dash-hero {
            padding: 1rem;
            border-radius: 14px;
        }
        .dash-hero-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            font-size: 1.1rem;
        }
        .dash-hero-title {
            font-size: 1.15rem;
        }
        .dash-hero-subtitle {
            font-size: .8rem;
        }
        .dash-date-pill {
            font-size: .75rem;
            padding: .4rem .7rem;
        }
        .dash-hero .btn-primary {
            font-size: .82rem;
            padding: .45rem .85rem;
        }
        .dash-card {
            border-radius: 14px;
        }
        .dash-card-header {
            padding: 1rem;
        }
        .dash-card-body {
            padding: 1rem;
        }
        .chart-wrap {
            height: 210px;
        }
        .doughnut-wrap {
            max-width: 160px;
        }
        .doughnut-center-value {
            font-size: 1.35rem;
        }
        .action-card {
            padding: .65rem .8rem;
        }
        .action-icon {
            width: 32px;
            height: 32px;
            font-size: .85rem;
        }
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* Sales line chart — Tech Indigo Palette */
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const grad = salesCtx.createLinearGradient(0, 0, 0, 260);
    grad.addColorStop(0, 'rgba(99,102,241,0.25)');
    grad.addColorStop(1, 'rgba(99,102,241,0.00)');

    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Revenue',
                data: @json($chartSales),
                borderColor: '#6366f1',
                borderWidth: 2.5,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                backgroundColor: grad,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#ffffff',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: { label: c => '  $' + c.parsed.y.toFixed(2) }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { font: { size: 11 }, color: '#64748b' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    border: { display: false },
                    ticks: {
                        font: { size: 11 },
                        color: '#64748b',
                        callback: v => '$' + v.toFixed(0)
                    }
                }
            }
        }
    });

    /* Doughnut chart */
    const completed = {{ $orderStatusCounts['completed'] }};
    const pending   = {{ $orderStatusCounts['pending'] }};
    const cancelled = {{ $orderStatusCounts['cancelled'] }};
    const total     = completed + pending + cancelled;

    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending', 'Cancelled'],
            datasets: [{
                data: total > 0 ? [completed, pending, cancelled] : [1, 0, 0],
                backgroundColor: total > 0 ? ['#10b981', '#f59e0b', '#ef4444'] : ['#e2e8f0'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 5
            }]
        },
        options: {
            responsive: true,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#ffffff',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: { label: c => '  ' + c.label + ': ' + c.parsed }
                }
            }
        }
    });

    /* Animated counters */
    function easeOut(t) { return 1 - Math.pow(1 - t, 3); }
    function runCounter(el) {
        const target   = parseFloat(el.dataset.count) || 0;
        const prefix   = el.dataset.prefix || '';
        const decimals = parseInt(el.dataset.decimals, 10) || 0;
        const duration = 1400;
        let start = null;
        (function tick(ts) {
            if (!start) start = ts;
            const p = Math.min((ts - start) / duration, 1);
            const v = easeOut(p) * target;
            el.textContent = prefix + v.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
            if (p < 1) {
                requestAnimationFrame(tick);
            } else {
                el.textContent = prefix + target.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
            }
        })(performance.now());
    }

    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                setTimeout(() => runCounter(e.target), 150);
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.3 });
    document.querySelectorAll('.kpi-value[data-count]').forEach(el => obs.observe(el));
});
</script>
@endsection
