@extends('admin.layouts.admin')

@section('title', 'Admin Dashboard Overview')

@section('content')
<div class="dashboard-wrapper" id="adminDashboardApp">

    {{-- ======================= HERO HEADER ======================= --}}
    <div class="dash-hero mb-4 fade-in-up">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-hero-avatar shadow-sm">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h1 class="dash-hero-title m-0">Dashboard Overview</h1>
                            <span class="badge dash-live-badge"><span class="live-dot"></span> Live Store</span>
                        </div>
                        <p class="dash-hero-subtitle m-0">Welcome back, <strong>{{ Auth::user()->name ?? 'Admin' }}</strong> — here is your real-time store performance summary.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
                    <span class="dash-date-pill">
                        <i class="far fa-calendar-alt me-1 text-primary"></i> {{ date('l, M j, Y') }}
                    </span>
                    <button type="button" class="btn btn-dash-action shadow-sm" id="btnRefreshDashboard" title="Refresh Dashboard Stats">
                        <i class="fas fa-sync-alt me-1" id="refreshIcon"></i> Refresh Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================= LOW STOCK ALERT BANNER ======================== --}}
    @if($data['lowStockCount'] > 0)
    <div class="dash-alert-banner mb-4 fade-in-up">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="dash-alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h6 class="dash-alert-title m-0">Low Stock Warning</h6>
                    <p class="dash-alert-desc m-0">You have <strong>{{ $data['lowStockCount'] }}</strong> item(s) running low on inventory (5 or fewer units remaining).</p>
                </div>
            </div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-alert-action">
                Review Products <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    @endif

    {{-- ======================= KPI STAT CARDS ======================== --}}
    <div class="row mb-4 g-3" id="kpiCardsContainer">
        {{-- Total Sales --}}
        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card kpi-card-sales fade-in-up" style="--delay: 0ms;">
                <div class="kpi-card-top">
                    <div class="kpi-icon-wrap kpi-icon-sales">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="kpi-trend {{ $data['salesGrowth'] >= 0 ? 'kpi-trend-up' : 'kpi-trend-down' }}" title="Growth vs previous period">
                        <i class="fas fa-arrow-{{ $data['salesGrowth'] >= 0 ? 'up' : 'down' }} me-1"></i>{{ abs($data['salesGrowth']) }}%
                    </div>
                </div>
                <div class="kpi-card-body">
                    <span class="kpi-label">Total Revenue</span>
                    <h2 class="kpi-value" data-count="{{ $data['totalSales'] }}" data-prefix="$" data-decimals="2">$0.00</h2>
                    <div class="kpi-meta">
                        <span>Today: <strong>${{ number_format($data['todaySales'], 2) }}</strong></span>
                    </div>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="kpi-footer">
                    <span>Revenue Breakdown</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Orders --}}
        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card kpi-card-orders fade-in-up" style="--delay: 60ms;">
                <div class="kpi-card-top">
                    <div class="kpi-icon-wrap kpi-icon-orders">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <div class="kpi-trend {{ $data['ordersGrowth'] >= 0 ? 'kpi-trend-up' : 'kpi-trend-down' }}" title="Growth vs previous period">
                        <i class="fas fa-arrow-{{ $data['ordersGrowth'] >= 0 ? 'up' : 'down' }} me-1"></i>{{ abs($data['ordersGrowth']) }}%
                    </div>
                </div>
                <div class="kpi-card-body">
                    <span class="kpi-label">Total Orders</span>
                    <h2 class="kpi-value" data-count="{{ $data['totalOrders'] }}" data-prefix="" data-decimals="0">0</h2>
                    <div class="kpi-meta">
                        <span class="text-success fw-medium">Done: <strong>{{ $data['completedOrders'] }}</strong></span>
                        <span class="text-muted">|</span>
                        <span class="text-warning fw-medium">Pending: <strong>{{ $data['pendingOrders'] }}</strong></span>
                    </div>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="kpi-footer">
                    <span>Manage All Orders</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Active Vendors --}}
        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card kpi-card-vendors fade-in-up" style="--delay: 120ms;">
                <div class="kpi-card-top">
                    <div class="kpi-icon-wrap kpi-icon-vendors">
                        <i class="fas fa-store-alt"></i>
                    </div>
                    <span class="kpi-pill kpi-pill-warning">Active Sellers</span>
                </div>
                <div class="kpi-card-body">
                    <span class="kpi-label">Active Vendors</span>
                    <h2 class="kpi-value" data-count="{{ $data['totalVendors'] }}" data-prefix="" data-decimals="0">0</h2>
                    <div class="kpi-meta">
                        <span>Approved Store Partners</span>
                    </div>
                </div>
                <a href="{{ route('admin.vendors.index') }}" class="kpi-footer">
                    <span>View Merchants</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Total Active Customers --}}
        <div class="col-xl-3 col-sm-6">
            <div class="kpi-card kpi-card-customers fade-in-up" style="--delay: 180ms;">
                <div class="kpi-card-top">
                    <div class="kpi-icon-wrap kpi-icon-customers">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <span class="kpi-pill kpi-pill-purple">Registered</span>
                </div>
                <div class="kpi-card-body">
                    <span class="kpi-label">Total Customers</span>
                    <h2 class="kpi-value" data-count="{{ $data['totalCustomers'] }}" data-prefix="" data-decimals="0">0</h2>
                    <div class="kpi-meta">
                        <span>Active Buyer Accounts</span>
                    </div>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="kpi-footer">
                    <span>Customer List</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ======================= CHARTS SECTION ======================== --}}
    <div class="row mb-4 g-3">
        {{-- Interactive Sales Trend Line Chart --}}
        <div class="col-lg-8">
            <div class="dash-card h-100 position-relative shadow-sm">
                <div class="dash-card-header flex-wrap">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon-box bg-primary-subtle text-primary">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h5 class="dash-card-title m-0">Revenue & Sales Trend</h5>
                            <p class="dash-card-subtitle m-0">Timeline analytics of store revenue performance</p>
                        </div>
                    </div>
                    {{-- Timeframe selector tabs --}}
                    <div class="btn-group time-range-selector" role="group" aria-label="Select sales chart timeframe">
                        <button type="button" class="btn time-tab active" data-range="7d">7 Days</button>
                        <button type="button" class="btn time-tab" data-range="30d">30 Days</button>
                        <button type="button" class="btn time-tab" data-range="1y">1 Year</button>
                    </div>
                </div>
                <div class="dash-card-body position-relative">
                    {{-- Skeleton loader --}}
                    <div class="chart-skeleton-overlay d-none" id="chartSkeleton">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading data...</span>
                        </div>
                    </div>
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
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h5 class="dash-card-title m-0">Order Fulfillment</h5>
                            <p class="dash-card-subtitle m-0">Status distribution across orders</p>
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
                            <span class="doughnut-center-label">Total Orders</span>
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
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div>
                            <h5 class="dash-card-title m-0">Recent Orders</h5>
                            <p class="dash-card-subtitle m-0">Latest transactions registered in store</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm rounded-3 fw-semibold">
                        View All Orders <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="dash-card-body p-0">
                    @if($recentOrders->isEmpty())
                        <div class="empty-state text-center py-5">
                            <div class="empty-icon text-muted mb-2">
                                <i class="fas fa-inbox fa-3x"></i>
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
                                            <span class="order-amount">${{ number_format($order->total_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            @if($order->status === 'completed')
                                                <span class="badge-status status-completed">
                                                    <span class="dot"></span> Completed
                                                </span>
                                            @elseif($order->status === 'pending')
                                                <span class="badge-status status-pending">
                                                    <span class="dot"></span> Pending
                                                </span>
                                            @else
                                                <span class="badge-status status-cancelled">
                                                    <span class="dot"></span> {{ ucfirst($order->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-muted font-size-13">
                                            {{ $order->created_at ? $order->created_at->format('M j, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.orders.index') }}" class="btn-action-icon" title="View Order">
                                                <i class="fas fa-eye"></i>
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
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h5 class="dash-card-title m-0">Quick Shortcuts</h5>
                            <p class="dash-card-subtitle m-0">Common store management operations</p>
                        </div>
                    </div>
                </div>
                <div class="dash-card-body">
                    <div class="action-grid">
                        <a href="{{ route('admin.products.create') }}" class="action-card">
                            <div class="action-icon bg-indigo">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Add New Product</span>
                                <span class="action-desc">Create catalog item</span>
                            </div>
                            <i class="fas fa-chevron-right action-arrow"></i>
                        </a>

                        <a href="{{ route('admin.orders.index') }}" class="action-card">
                            <div class="action-icon bg-cyan">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Process Orders</span>
                                <span class="action-desc">Manage store orders</span>
                            </div>
                            <i class="fas fa-chevron-right action-arrow"></i>
                        </a>

                        <a href="{{ route('admin.customers.index') }}" class="action-card">
                            <div class="action-icon bg-emerald">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Customer Database</span>
                                <span class="action-desc">View buyer accounts</span>
                            </div>
                            <i class="fas fa-chevron-right action-arrow"></i>
                        </a>

                        <a href="{{ route('admin.vendors.index') }}" class="action-card">
                            <div class="action-icon bg-amber">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Vendor Management</span>
                                <span class="action-desc">Approve merchant stores</span>
                            </div>
                            <i class="fas fa-chevron-right action-arrow"></i>
                        </a>

                        <a href="{{ route('admin.categories.index') }}" class="action-card">
                            <div class="action-icon bg-purple">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">Category & Brands</span>
                                <span class="action-desc">Organize catalog hierarchy</span>
                            </div>
                            <i class="fas fa-chevron-right action-arrow"></i>
                        </a>

                        <a href="{{ route('admin.site-settings.edit') }}" class="action-card">
                            <div class="action-icon bg-slate">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="action-details">
                                <span class="action-title">System Settings</span>
                                <span class="action-desc">Configure store options</span>
                            </div>
                            <i class="fas fa-chevron-right action-arrow"></i>
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
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    .dashboard-wrapper {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
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
        font-size: 1.25rem;
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
        padding: .45rem .85rem;
        font-size: .82rem;
        font-weight: 600;
        color: #334155;
    }
    .btn-dash-action {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
        font-size: .82rem;
        font-weight: 600;
        padding: .45rem .95rem;
        border-radius: 10px;
        transition: all .2s ease;
    }
    .btn-dash-action:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }

    /* ---------- Low Stock Banner ---------- */
    .dash-alert-banner {
        background: linear-gradient(135deg, #fffbebe6, #fff7ed);
        border: 1px solid #fde68a;
        border-radius: 14px;
        padding: 1rem 1.25rem;
    }
    .dash-alert-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #f59e0b;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    .dash-alert-title { font-weight: 700; color: #92400e; font-size: .95rem; }
    .dash-alert-desc { font-size: .84rem; color: #b45309; }
    .btn-alert-action {
        background: #f59e0b;
        color: #ffffff;
        font-weight: 700;
        font-size: .8rem;
        padding: .4rem .9rem;
        border-radius: 8px;
        border: none;
        transition: background .15s ease;
    }
    .btn-alert-action:hover { background: #d97706; color: #ffffff; }

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
    .kpi-card-sales::before     { background: linear-gradient(90deg, #06b6d4, #3b82f6); }
    .kpi-card-orders::before    { background: linear-gradient(90deg, #10b981, #059669); }
    .kpi-card-vendors::before   { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .kpi-card-customers::before { background: linear-gradient(90deg, #8b5cf6, #ec4899); }

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
        font-size: 1.15rem;
        color: #ffffff;
    }
    .kpi-icon-sales     { background: linear-gradient(135deg, #06b6d4, #2563eb); box-shadow: 0 6px 14px rgba(6, 182, 212, 0.3); }
    .kpi-icon-orders    { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 14px rgba(16, 185, 129, 0.3); }
    .kpi-icon-vendors   { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 6px 14px rgba(245, 158, 11, 0.3); }
    .kpi-icon-customers { background: linear-gradient(135deg, #8b5cf6, #d946ef); box-shadow: 0 6px 14px rgba(139, 92, 246, 0.3); }

    .kpi-trend {
        font-size: .75rem;
        font-weight: 700;
        padding: .2rem .55rem;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
    }
    .kpi-trend-up   { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .kpi-trend-down { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

    .kpi-pill {
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .55rem;
        border-radius: 20px;
    }
    .kpi-pill-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .kpi-pill-purple  { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }

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
        font-size: 1rem;
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

    /* ---------- Timeframe Selector ---------- */
    .time-range-selector {
        background: #f1f5f9;
        padding: 3px;
        border-radius: 10px;
    }
    .time-tab {
        border: none;
        background: transparent;
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
        padding: .3rem .75rem;
        border-radius: 8px !important;
        transition: all .15s ease;
    }
    .time-tab.active {
        background: #ffffff;
        color: #4f46e5;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    /* ---------- Chart Wrap ---------- */
    .chart-wrap { position: relative; height: 280px; width: 100%; }
    .chart-skeleton-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(2px);
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
    }

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

    .bg-emerald { background: #10b981; }
    .bg-amber   { background: #f59e0b; }
    .bg-rose    { background: #ef4444; }

    .progress-thin {
        height: 6px;
        border-radius: 10px;
        background: #f1f5f9;
        overflow: hidden;
    }

    /* ---------- Modern Table ---------- */
    .modern-table { width: 100%; }
    .modern-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: .73rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: .85rem 1.25rem;
        border: none;
    }
    .modern-table tbody td {
        padding: .85rem 1.25rem;
        border-top: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .modern-table tbody tr:hover { background: #f8fafc; }

    .order-code {
        font-family: monospace;
        font-weight: 700;
        color: #0f172a;
        font-size: .85rem;
        background: #f1f5f9;
        padding: .25rem .5rem;
        border-radius: 6px;
    }

    .user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #ffffff;
        font-size: .85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .user-name { font-weight: 600; color: #0f172a; font-size: .85rem; }
    .user-email { font-size: .78rem; color: #64748b; }

    .order-amount { font-weight: 700; color: #0f172a; font-size: .88rem; }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .25rem .65rem;
        border-radius: 20px;
        font-size: .75rem;
        font-weight: 700;
    }
    .badge-status .dot { width: 6px; height: 6px; border-radius: 50%; }

    .status-completed { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .status-completed .dot { background: #10b981; }

    .status-pending { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .status-pending .dot { background: #f59e0b; }

    .status-cancelled { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .status-cancelled .dot { background: #ef4444; }

    .btn-action-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: .82rem;
        transition: all .15s ease;
    }
    .btn-action-icon:hover {
        background: #6366f1;
        color: #ffffff;
    }

    /* ---------- Quick Actions Grid ---------- */
    .action-grid {
        display: flex;
        flex-direction: column;
        gap: .6rem;
    }
    .action-card {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .75rem .9rem;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        color: #0f172a;
        transition: all .2s ease;
    }
    .action-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        color: #0f172a;
    }
    .action-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        flex-shrink: 0;
    }
    .bg-indigo  { background: #6366f1; }
    .bg-cyan    { background: #06b6d4; }
    .bg-amber   { background: #f59e0b; }
    .bg-purple  { background: #a855f7; }
    .bg-slate   { background: #475569; }

    .action-details { flex: 1; }
    .action-title { font-size: .88rem; font-weight: 700; display: block; }
    .action-desc  { font-size: .76rem; color: #64748b; display: block; }
    .action-arrow { font-size: .75rem; color: #94a3b8; transition: transform .15s ease, color .15s ease; }
    .action-card:hover .action-arrow { transform: translateX(3px); color: #6366f1; }

    /* Fade entrance */
    .fade-in-up {
        opacity: 0;
        transform: translateY(14px);
        transition: opacity .4s ease, transform .4s ease;
        transition-delay: var(--delay, 0ms);
    }
    .fade-in-up.visible { opacity: 1; transform: translateY(0); }
    @media (prefers-reduced-motion: reduce) {
        .fade-in-up { opacity: 1; transform: none; transition: none; }
    }

    /* ---------- RESPONSIVE MOBILE OPTIMIZATIONS ---------- */
    @media (max-width: 991.98px) {
        .dash-hero { padding: 1.1rem 1.25rem; }
        .dash-card-header { padding: 1rem 1.15rem; }
        .dash-card-body { padding: 1.15rem; }
    }

    @media (max-width: 767.98px) {
        .dash-hero { padding: 1rem; }
        .dash-hero-title { font-size: 1.25rem; }
        .dash-hero-subtitle { font-size: .82rem; }
        .dash-hero-avatar { width: 42px; height: 42px; font-size: 1.1rem; border-radius: 12px; }

        .kpi-card { padding: 1rem; }
        .kpi-value { font-size: 1.5rem; }
        .kpi-label { font-size: .75rem; }
        .kpi-icon-wrap { width: 40px; height: 40px; font-size: 1.05rem; }

        .dash-card-header { flex-direction: column; align-items: flex-start !important; gap: .75rem !important; }
        .time-range-selector { width: 100%; display: flex; }
        .time-tab { flex: 1; text-align: center; }

        .chart-wrap { height: 230px; }
        .doughnut-wrap { max-width: 160px; }
        .doughnut-center-value { font-size: 1.4rem; }

        .modern-table thead th, .modern-table tbody td { padding: .75rem .85rem; }
        .user-email { max-width: 120px; display: inline-block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }
    }

    @media (max-width: 575.98px) {
        .dash-hero { padding: .85rem; border-radius: 12px; }
        .dash-hero-title { font-size: 1.15rem; }
        .dash-hero-avatar { width: 36px; height: 36px; font-size: 1rem; border-radius: 10px; }
        .dash-date-pill, .btn-dash-action { font-size: .75rem; padding: .35rem .65rem; width: 100%; text-align: center; justify-content: center; }

        .dash-alert-banner { padding: .85rem; border-radius: 12px; }
        .dash-alert-icon { width: 32px; height: 32px; font-size: .95rem; }
        .btn-alert-action { width: 100%; margin-top: .5rem; text-align: center; }

        .kpi-card { padding: .85rem; border-radius: 12px; }
        .kpi-value { font-size: 1.35rem; margin-bottom: .2rem; }
        .kpi-icon-wrap { width: 36px; height: 36px; font-size: .95rem; border-radius: 10px; }
        .kpi-trend { font-size: .7rem; padding: .15rem .45rem; }
        .kpi-meta { font-size: .78rem; margin-bottom: .5rem; }
        .kpi-footer { font-size: .75rem; padding-top: .6rem; }

        .dash-card { border-radius: 12px; }
        .header-icon-box { width: 32px; height: 32px; font-size: .9rem; border-radius: 8px; }
        .dash-card-title { font-size: .95rem; }
        .dash-card-subtitle { font-size: .75rem; }

        .chart-wrap { height: 190px; }
        .doughnut-wrap { max-width: 140px; }
        .doughnut-center-value { font-size: 1.2rem; }
        .doughnut-center-label { font-size: .65rem; }

        .modern-table thead th { font-size: .68rem; padding: .6rem .65rem; }
        .modern-table tbody td { padding: .6rem .65rem; font-size: .82rem; }
        .order-code { font-size: .78rem; padding: .2rem .4rem; }
        .user-avatar { width: 30px; height: 30px; font-size: .78rem; }
        .user-name { font-size: .8rem; }
        .user-email { display: none; } /* Hide email on tiny mobile screens for max space */
        .order-amount { font-size: .82rem; }
        .badge-status { font-size: .7rem; padding: .2rem .5rem; }

        .action-card { padding: .65rem .75rem; border-radius: 10px; }
        .action-icon { width: 34px; height: 34px; font-size: .88rem; border-radius: 8px; }
        .action-title { font-size: .82rem; }
        .action-desc { font-size: .7rem; }
    }
</style>
@endsection

@section('js')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const chartSkeleton = document.getElementById('chartSkeleton');
    const btnRefresh = document.getElementById('btnRefreshDashboard');
    const refreshIcon = document.getElementById('refreshIcon');

    // --- Chart.js Line Gradient ---
    let salesGradient = salesCtx.createLinearGradient(0, 0, 0, 280);
    salesGradient.addColorStop(0, 'rgba(99,102,241,0.25)');
    salesGradient.addColorStop(1, 'rgba(99,102,241,0.00)');

    const chartSalesData = @json($chartSales);
    const maxSaleVal = Math.max(...chartSalesData, 0);

    let salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Revenue',
                data: chartSalesData,
                borderColor: '#4f46e5',
                borderWidth: 3,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 7,
                fill: true,
                backgroundColor: salesGradient,
                tension: 0.38
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    cornerRadius: 10,
                    titleFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif", weight: 'bold' },
                    bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                    displayColors: false,
                    callbacks: {
                        label: ctx => ' Revenue: $' + ctx.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" }, color: '#64748b' }
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: maxSaleVal > 0 ? maxSaleVal * 1.2 : 100,
                    grid: { color: '#f1f5f9' },
                    border: { display: false },
                    ticks: {
                        font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" },
                        color: '#64748b',
                        precision: 0,
                        callback: function(value) {
                            if (Math.floor(value) === value) {
                                return '$' + value.toLocaleString('en-US');
                            }
                            return '';
                        }
                    }
                }
            }
        }
    });

    // --- Order Status Doughnut Chart ---
    const completedCount = {{ $orderStatusCounts['completed'] }};
    const pendingCount   = {{ $orderStatusCounts['pending'] }};
    const cancelledCount = {{ $orderStatusCounts['cancelled'] }};
    const totalForChart  = completedCount + pendingCount + cancelledCount;

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending', 'Cancelled'],
            datasets: [{
                data: totalForChart > 0 ? [completedCount, pendingCount, cancelledCount] : [1, 0, 0],
                backgroundColor: totalForChart > 0
                    ? ['#10b981', '#f59e0b', '#ef4444']
                    : ['#e2e8f0', '#e2e8f0', '#e2e8f0'],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '76%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: totalForChart > 0,
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' orders' }
                }
            }
        }
    });

    // --- Timeframe Range Buttons AJAX ---
    const timeTabs = document.querySelectorAll('.time-range-selector .time-tab');
    timeTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const range = this.dataset.range;
            timeTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            chartSkeleton.classList.remove('d-none');

            fetch(`{{ route('admin.dashboard.chart-data') }}?range=${range}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success) {
                    const newMax = Math.max(...resData.sales, 0);
                    salesChart.options.scales.y.suggestedMax = newMax > 0 ? newMax * 1.2 : 100;
                    salesChart.data.labels = resData.labels;
                    salesChart.data.datasets[0].data = resData.sales;
                    salesChart.update('active');
                }
            })
            .catch(err => console.error('Error loading chart metrics:', err))
            .finally(() => {
                setTimeout(() => chartSkeleton.classList.add('d-none'), 200);
            });
        });
    });

    // --- Refresh Button ---
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            refreshIcon.classList.add('fa-spin');
            setTimeout(() => window.location.reload(), 300);
        });
    }

    // --- Intersection Observer for Entrance Animation ---
    (function () {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in-up').forEach(function (el) { observer.observe(el); });
    })();

    // --- Counter Animation ---
    (function () {
        function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }
        function animateCounter(el) {
            const target = parseFloat(el.dataset.count) || 0;
            const prefix = el.dataset.prefix || '';
            const decimals = parseInt(el.dataset.decimals, 10) || 0;
            const duration = 1200;
            let startTime = null;

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                const elapsed = timestamp - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const current = easeOutCubic(progress) * target;
                el.textContent = prefix + current.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = prefix + target.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
                }
            }
            requestAnimationFrame(step);
        }

        const counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    setTimeout(() => animateCounter(entry.target), 120);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });
        document.querySelectorAll('.kpi-value[data-count]').forEach(function (el) { counterObserver.observe(el); });
    })();

});
</script>
@endsection