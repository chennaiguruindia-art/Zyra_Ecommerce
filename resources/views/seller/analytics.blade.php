@extends('layouts.seller')

@section('title', 'Sales Analytics & Earnings | ZYRA Seller Hub')
@section('meta_description', 'View sales performance analytics, category breakdown and recent transactions.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'analytics';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Store Analytics &amp; Earnings</h1>
        <p class="text-muted mb-0">Track revenue growth, average order value, and recent order transactions.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('seller.sales') }}" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-receipt me-1"></i> View All Orders
        </a>
        <button type="button" class="btn btn-dark btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="seller-stat-value" id="analyticsRevenue">₹{{ number_format($grossRevenue ?? 0) }}</div>
                <div class="seller-stat-label">Gross Revenue</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-bag-check"></i></div>
            <div>
                <div class="seller-stat-value" id="analyticsUnitsSold">{{ $unitsDispatched ?? 0 }} units</div>
                <div class="seller-stat-label">Units Dispatched</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="seller-stat-value" id="analyticsAov">₹{{ number_format($aov ?? 0) }}</div>
                <div class="seller-stat-label">Average Order Value (AOV)</div>
            </div>
        </div>
    </div>
</div>

<!-- Category Performance & Recent Transactions -->
<div class="row g-4">
    <!-- Category Revenue Share -->
    <div class="col-lg-4">
        <div class="seller-card h-100">
            <div class="seller-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold fs-6"><i class="bi bi-pie-chart me-2 text-muted"></i>Revenue Overview</h5>
                <span class="badge bg-light text-dark border">All Time</span>
            </div>
            <div class="seller-card-body">
                <div class="mb-3 p-3 rounded bg-light border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small">Total Orders</span>
                        <strong class="text-dark">{{ $totalOrdersCount ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small">Total Revenue</span>
                        <strong class="text-success">₹{{ number_format($grossRevenue ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Average / Order</span>
                        <strong class="text-primary">₹{{ number_format($aov ?? 0, 2) }}</strong>
                    </div>
                </div>

                <div class="text-center text-muted py-3">
                    <i class="bi bi-bar-chart-line fs-2 d-block mb-2 text-secondary opacity-50"></i>
                    <p class="small mb-1">Detailed category breakdown</p>
                    <small class="text-muted">Calculates automatically as customer purchases are placed.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions by Order ID (Last 5) -->
    <div class="col-lg-8">
        <div class="seller-card h-100">
            <div class="seller-card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold fs-6"><i class="bi bi-clock-history me-2 text-muted"></i>Recent Transactions</h5>
                    <small class="text-muted">Last 5 transactions sorted by Order ID</small>
                </div>
                <span class="badge bg-dark text-white px-2 py-1">Last 5 Orders</span>
            </div>
            <div class="seller-card-body p-0">
                <div class="table-responsive">
                    <table class="table seller-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer &amp; Date</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody id="analyticsRecentTransactionsBody">
                            @forelse($recentOrders ?? [] as $order)
                                @php
                                    $statusClass = match($order->order_status) {
                                        'Pending' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'Processing' => 'bg-warning-subtle text-dark border border-warning-subtle',
                                        'Shipped' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                        'Delivered' => 'bg-success-subtle text-success border border-success-subtle',
                                        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                    };
                                    $statusLabel = match($order->order_status) {
                                        'Processing' => 'In Packing',
                                        'Shipped' => 'Dispatched',
                                        default => $order->order_status,
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('seller.sales') }}?search={{ urlencode($order->order_number) }}" class="text-decoration-none">
                                            <span class="badge bg-white text-dark border font-monospace fw-bold px-2 py-1 shadow-sm">
                                                #{{ $order->order_number }}
                                            </span>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $order->customer_name }}</div>
                                        <div class="text-muted smaller">{{ $order->created_at?->format('d M Y, h:i A') }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">₹{{ number_format($order->total, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-muted text-uppercase">{{ $order->payment_method }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClass }} rounded-pill px-2 py-1 small">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('seller.sales') }}?search={{ urlencode($order->order_number) }}" class="btn btn-sm btn-outline-dark py-1 px-2" title="View details in Orders & Sales">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-muted opacity-50"></i>
                                        No transactions recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(isset($recentOrders) && count($recentOrders) > 0)
                <div class="seller-card-footer p-2 text-center border-top bg-light">
                    <a href="{{ route('seller.sales') }}" class="small text-dark fw-semibold text-decoration-none">
                        View all orders in Sales Hub <i class="bi bi-chevron-right small"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
