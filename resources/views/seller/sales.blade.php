@extends('layouts.seller')

@section('title', 'Orders & Sales Management | ZYRA Seller Hub')
@section('meta_description', 'Manage incoming customer orders, update fulfillment statuses and track store revenue.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'sales';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Orders & Sales</h1>
        <p class="text-muted mb-0">Track customer orders, process dispatches, and manage fulfillment across India.</p>
    </div>
    <div>
        <button type="button" class="btn btn-outline-dark" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Export Report
        </button>
    </div>
</div>

<!-- Sales Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-currency-rupee"></i></div>
            <div>
                <div class="seller-stat-value" id="salesTotalRevenue">₹0</div>
                <div class="seller-stat-label">Total Store Revenue</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="seller-stat-value" id="salesTotalOrders">0</div>
                <div class="seller-stat-label">Total Orders Placed</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="seller-stat-value" id="salesDeliveredOrders">0</div>
                <div class="seller-stat-label">Delivered Orders</div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table Card -->
<div class="seller-card">
    <div class="seller-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-truck me-2 text-muted"></i>Customer Order Stream</h5>
        <small class="text-muted">Change status dropdown to trigger automatic fulfillment updates</small>
    </div>
    <div class="seller-card-body p-0">
        <div class="table-responsive">
            <table class="table seller-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Details</th>
                        <th>Products Ordered</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Fulfillment Status</th>
                        <th>Tracking (AWB)</th>
                    </tr>
                </thead>
                <tbody id="sellerOrdersTableBody">
                    <!-- Populated dynamically by seller.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
