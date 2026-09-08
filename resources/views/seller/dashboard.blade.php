@extends('layouts.seller')

@section('title', 'Seller Dashboard | ZYRA Seller Hub')
@section('meta_description', 'ZYRA Seller Dashboard - overview of products, stock levels, sales and revenue.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'dashboard';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Welcome back, {{ auth()->user()?->name ?? 'Ananya Boutique' }}!</h1>
        <p class="text-muted mb-0">Here is what is happening across your ZYRA seller inventory and orders today.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('seller.inventory') }}" class="btn btn-outline-dark">
            <i class="bi bi-boxes me-1"></i> Check Inventory
        </a>
        <a href="{{ route('seller.products.add') }}" class="btn btn-zyra-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Product
        </a>
    </div>
</div>

<!-- Key Performance Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="seller-stat-value" id="sellerStatProducts">0</div>
                <div class="seller-stat-label">Total Catalog</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-boxes"></i></div>
            <div>
                <div class="seller-stat-value" id="sellerStatStockUnits">0</div>
                <div class="seller-stat-label">In-Stock Units</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-danger-soft text-danger"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="seller-stat-value" id="sellerStatLowStock">0</div>
                <div class="seller-stat-label">Low Stock Alerts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-currency-rupee"></i></div>
            <div>
                <div class="seller-stat-value" id="sellerStatRevenue">₹0</div>
                <div class="seller-stat-label">Total Earnings</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Row: Recent Orders & Quick Actions -->
<div class="row g-4 mb-4">
    <!-- Left: Recent Orders -->
    <div class="col-lg-8">
        <div class="seller-card">
            <div class="seller-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-receipt me-2 text-muted"></i>Recent Orders</h5>
                <a href="{{ route('seller.sales') }}" class="btn btn-sm btn-outline-dark">View All Orders</a>
            </div>
            <div class="seller-card-body p-0">
                <div class="table-responsive">
                    <table class="table seller-table mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Fulfillment</th>
                            </tr>
                        </thead>
                        <tbody id="sellerDashboardOrdersTable">
                            <!-- Populated dynamically by seller.js -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Quick Actions & Performance Snippet -->
    <div class="col-lg-4">
        <div class="seller-card mb-4">
            <div class="seller-card-header">
                <h5 class="mb-0 fw-bold"><i class="bi bi-lightning-charge me-2 text-muted"></i>Seller Actions</h5>
            </div>
            <div class="seller-card-body d-grid gap-2">
                <a href="{{ route('seller.products.add') }}" class="btn btn-zyra-primary text-start py-2">
                    <i class="bi bi-plus-circle me-2"></i> Add New Product
                </a>
                <a href="{{ route('seller.inventory') }}" class="btn btn-zyra-outline text-start py-2">
                    <i class="bi bi-boxes me-2"></i> Update Stock Levels
                </a>
                <a href="{{ route('seller.sales') }}" class="btn btn-zyra-outline text-start py-2">
                    <i class="bi bi-receipt me-2"></i> Process Pending Orders
                </a>
                <a href="{{ route('seller.analytics') }}" class="btn btn-outline-dark text-start py-2">
                    <i class="bi bi-graph-up-arrow me-2"></i> Revenue & Payouts
                </a>
            </div>
        </div>

        <div class="seller-card">
            <div class="seller-card-header">
                <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-success"></i>Partner Health</h6>
            </div>
            <div class="seller-card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Dispatch SLA:</span>
                    <strong class="text-success">98.4% on time</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Return Rate:</span>
                    <strong class="text-dark">1.8% (Very Low)</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Seller Rating:</span>
                    <strong class="text-warning">★ 4.9 / 5.0</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products Section -->
<div class="seller-card">
    <div class="seller-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2 text-muted"></i>Recent Products in Catalog</h5>
        <a href="{{ route('seller.products') }}" class="btn btn-sm btn-outline-dark">Manage Catalog</a>
    </div>
    <div class="seller-card-body p-0">
        <div class="table-responsive">
            <table class="table seller-table mb-0">
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Price</th>
                        <th>Stock Units</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="sellerDashboardRecentTable">
                    <!-- Populated dynamically by seller.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection