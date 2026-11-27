@extends('layouts.seller')

@section('title', 'Sales & Revenue Tally — ZYRA Seller Hub')

@section('content')
<div class="container-fluid py-4 px-md-4">

    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-receipt-cutoff me-2 text-primary"></i> Sales &amp; Revenue Tally
            </h1>
            <p class="text-muted small mb-0">
                Track your actual sales, customer payments, coupon discounts, and Friday special offer performance.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('seller.accounts.export', request()->all()) }}" class="btn btn-outline-success btn-sm px-3 shadow-sm rounded-2">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
            </a>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm px-3 shadow-sm rounded-2">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm mb-4 rounded-3 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('seller.accounts') }}" class="row g-2 align-items-center">
                
                <!-- Period Quick Filters -->
                <div class="col-12 col-xl-5">
                    <div class="btn-group btn-group-sm w-100 shadow-sm rounded-2 overflow-hidden" role="group">
                        <a href="{{ route('seller.accounts', array_merge(request()->except('page'), ['range' => 'all'])) }}" class="btn {{ $currentRange === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">All Time</a>
                        <a href="{{ route('seller.accounts', array_merge(request()->except('page'), ['range' => 'today'])) }}" class="btn {{ $currentRange === 'today' ? 'btn-dark' : 'btn-outline-secondary' }}">Today</a>
                        <a href="{{ route('seller.accounts', array_merge(request()->except('page'), ['range' => 'week'])) }}" class="btn {{ $currentRange === 'week' ? 'btn-dark' : 'btn-outline-secondary' }}">This Week</a>
                        <a href="{{ route('seller.accounts', array_merge(request()->except('page'), ['range' => 'month'])) }}" class="btn {{ $currentRange === 'month' ? 'btn-dark' : 'btn-outline-secondary' }}">This Month</a>
                        <a href="{{ route('seller.accounts', array_merge(request()->except('page'), ['range' => 'friday_only'])) }}" class="btn {{ $currentRange === 'friday_only' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' }}">✨ Friday Sales</a>
                    </div>
                </div>

                <!-- Coupon Filter -->
                <div class="col-6 col-md-3 col-xl-2">
                    <select name="coupon" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all">All Coupons</option>
                        <option value="with_coupon" {{ request('coupon') === 'with_coupon' ? 'selected' : '' }}>🎟️ Coupon Used</option>
                        <option value="no_coupon" {{ request('coupon') === 'no_coupon' ? 'selected' : '' }}>⚪ Without Coupon</option>
                        @foreach($couponsList as $cpn)
                            <option value="{{ $cpn->code }}" {{ request('coupon') === $cpn->code ? 'selected' : '' }}>{{ $cpn->code }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Order Status Filter -->
                <div class="col-6 col-md-3 col-xl-2">
                    <select name="order_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all">All Orders</option>
                        <option value="Delivered" {{ request('order_status') === 'Delivered' ? 'selected' : '' }}>Delivered Only</option>
                        <option value="Pending" {{ request('order_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Cancelled" {{ request('order_status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search order #, customer..." value="{{ request('search') }}">
                        <button class="btn btn-dark" type="submit"><i class="bi bi-search"></i></button>
                        @if(request()->hasAny(['search', 'coupon', 'range', 'order_status', 'from', 'to']))
                            <a href="{{ route('seller.accounts') }}" class="btn btn-outline-danger" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Clean, Luxury Summary Cards -->
    <div class="row g-3 mb-4">
        
        <!-- Total Orders -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Orders</span>
                    <span class="badge bg-light text-dark border p-2"><i class="bi bi-bag-check fs-6"></i></span>
                </div>
                <div class="h3 fw-bold text-dark mb-0">{{ $analytics['total_orders'] }}</div>
                <div class="text-muted small mt-1">
                    <span class="text-success fw-semibold">{{ $analytics['delivered_orders'] }} Delivered</span> &bull; 
                    <span class="text-danger fw-semibold">{{ $analytics['cancelled_orders'] }} Cancelled</span>
                </div>
            </div>
        </div>

        <!-- Total Sales Value -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Original Value</span>
                    <span class="badge bg-light text-primary border p-2"><i class="bi bi-tag fs-6"></i></span>
                </div>
                <div class="h3 fw-bold text-dark mb-0">₹{{ number_format($analytics['total_gross_sales'], 2) }}</div>
                <div class="text-muted small mt-1">Total catalog price of active orders</div>
            </div>
        </div>

        <!-- Discounts Given -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Discounts Given</span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle p-2"><i class="bi bi-percent fs-6"></i></span>
                </div>
                <div class="h3 fw-bold text-danger mb-0">-₹{{ number_format($analytics['total_discounts_given'], 2) }}</div>
                <div class="text-muted small mt-1">Coupon &amp; Offer savings passed to buyers</div>
            </div>
        </div>

        <!-- Total Cash Collected (Net Revenue) -->
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100" style="border-top: 3px solid #198754 !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-success small fw-semibold text-uppercase">Net Revenue Collected</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle p-2"><i class="bi bi-cash-coin fs-6"></i></span>
                </div>
                <div class="h3 fw-bold text-success mb-0">₹{{ number_format($analytics['total_net_revenue'], 2) }}</div>
                <div class="text-muted small mt-1">Actual revenue realized in bank/payments</div>
            </div>
        </div>

    </div>

    <!-- Section 2: Friday Special Sales & Coupon Performance -->
    <div class="row g-4 mb-4">
        
        <!-- Friday Special Box -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <span class="fw-bold text-dark"><i class="bi bi-calendar-event me-2 text-warning"></i> Friday Special Offer Tally</span>
                    <span class="badge bg-warning text-dark px-2 py-1">Short Kurti @ ₹500</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center mb-3">
                        <div class="col-4 border-end">
                            <div class="text-muted small">Friday Orders</div>
                            <div class="h4 fw-bold text-dark mb-0">{{ $analytics['friday']['orders_count'] }}</div>
                        </div>
                        <div class="col-4 border-end">
                            <div class="text-muted small">Discounts Absorbed</div>
                            <div class="h4 fw-bold text-danger mb-0">₹{{ number_format($analytics['friday']['discounts'], 2) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Friday Revenue</div>
                            <div class="h4 fw-bold text-success mb-0">₹{{ number_format($analytics['friday']['net_revenue'], 2) }}</div>
                        </div>
                    </div>
                    <p class="small text-muted mb-0 bg-light p-2 rounded-2 text-center">
                        <i class="bi bi-info-circle me-1 text-primary"></i> 
                        Tracks orders where customers unlocked the ₹500 Short Kurti deal on Fridays.
                    </p>
                </div>
            </div>
        </div>

        <!-- Coupon Wise Sales Box -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <span class="fw-bold text-dark"><i class="bi bi-ticket-perforated me-2 text-primary"></i> Coupon Performance Summary</span>
                    <span class="small text-muted">{{ count($analytics['coupons']) }} coupons tracked</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Coupon Code</th>
                                <th class="text-center">Times Used</th>
                                <th class="text-end">Sales Generated</th>
                                <th class="text-end">Total Discount</th>
                                <th class="text-end">Net Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics['coupons'] as $cp)
                                <tr>
                                    <td>
                                        <span class="badge bg-dark font-monospace px-2 py-1">{{ $cp['code'] }}</span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $cp['used_count'] }}</td>
                                    <td class="text-end">₹{{ number_format($cp['gross_sales'], 2) }}</td>
                                    <td class="text-end text-danger">{{ $cp['discount_cost'] > 0 ? '-₹'.number_format($cp['discount_cost'], 2) : '—' }}</td>
                                    <td class="text-end fw-bold text-success">₹{{ number_format($cp['net_revenue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No coupon orders found in this range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Section 3: Clean Order-by-Order Sales Ledger -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-bold text-dark"><i class="bi bi-list-check me-2"></i> Order-by-Order Sales Ledger</span>
            <span class="small text-muted">Showing {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Order # &amp; Date</th>
                        <th>Customer</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Original Total</th>
                        <th class="text-center">Coupon Code</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Customer Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        @php
                            $created = \Carbon\Carbon::parse($o->created_at);
                            $isFriday = $created->isFriday();
                            $gross = (float) ($o->subtotal > 0 ? $o->subtotal : $o->total + $o->discount);
                            $discount = (float) $o->discount;
                            $net = (float) $o->total;
                        @endphp
                        <tr>
                            <td>
                                <strong class="text-dark font-monospace">{{ $o->order_number }}</strong>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $created->format('d M Y, h:i A') }} 
                                    @if($isFriday)
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Friday</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $o->customer_name }}</div>
                                <small class="text-muted">{{ $o->customer_phone ?? $o->customer_email }}</small>
                            </td>
                            <td class="text-center">
                                @if($o->order_status === 'Delivered')
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Delivered</span>
                                @elseif($o->order_status === 'Cancelled')
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">Cancelled</span>
                                @elseif($o->order_status === 'Shipped')
                                    <span class="badge bg-info-subtle text-info border px-2 py-1">Shipped</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-dark border px-2 py-1">{{ $o->order_status }}</span>
                                @endif
                            </td>
                            <td class="text-end text-muted">
                                ₹{{ number_format($gross, 2) }}
                            </td>
                            <td class="text-center">
                                @if(!empty($o->coupon_code))
                                    <span class="badge bg-dark font-monospace">{{ $o->coupon_code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end text-danger fw-semibold">
                                {{ $discount > 0 ? '-₹'.number_format($discount, 2) : '—' }}
                            </td>
                            <td class="text-end">
                                <span class="fw-bold fs-6 {{ $o->order_status === 'Cancelled' ? 'text-muted text-decoration-line-through' : 'text-dark' }}">
                                    ₹{{ number_format($net, 2) }}
                                </span>
                                @if($o->order_status === 'Cancelled')
                                    <div class="text-danger small" style="font-size: 0.7rem;">(Cancelled)</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                No orders found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white py-2 d-flex justify-content-end">
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
