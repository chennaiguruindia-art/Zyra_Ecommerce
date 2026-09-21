@extends('layouts.seller')

@section('title', 'New Orders | ZYRA Seller Hub')
@section('meta_description', 'Pack and dispatch freshly received customer orders.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'neworders';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">New Orders</h1>
        <p class="text-muted mb-0">Freshly received orders arrive here. Start packing, then dispatch — the order moves to Orders &amp; Sales.</p>
    </div>
    <div>
        <a href="{{ route('seller.sales') }}" class="btn btn-outline-dark">
            <i class="bi bi-receipt me-1"></i> View All Sales
        </a>
    </div>
</div>

<!-- New Order Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-inbox"></i></div>
            <div>
                <div class="seller-stat-value" id="newOrdersCount">0</div>
                <div class="seller-stat-label">New Orders</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-currency-rupee"></i></div>
            <div>
                <div class="seller-stat-value" id="newOrdersValue">₹0</div>
                <div class="seller-stat-label">Pending Order Value</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-box-arrow-right"></i></div>
            <div>
                <div class="seller-stat-value" id="newOrdersDispatched">0</div>
                <div class="seller-stat-label">Dispatched Today</div>
            </div>
        </div>
    </div>
</div>

<!-- New Orders Table Card -->
<div class="seller-card">
    <div class="seller-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2 text-muted"></i>Packing Queue</h5>
        <small class="text-muted">Click "Start Packing" to mark an order as Processing, or "Packed &amp; Dispatch" to ship it out.</small>
    </div>
    <div class="seller-card-body p-3 border-bottom">
        <div class="row g-3 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" id="newOrderSearch" class="form-control" placeholder="Search by Order ID or customer..." oninput="ZyraSeller.renderNewOrdersPage()">
                </div>
            </div>
            <div class="col-lg-7 text-md-end">
                <small class="text-muted" id="newOrdersResultCount"></small>
            </div>
        </div>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="sellerNewOrdersTableBody">
                    <!-- Populated dynamically by seller.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection