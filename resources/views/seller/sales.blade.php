@extends('layouts.seller')

@section('title', 'Orders & Sales Hub | ZYRA Seller Hub')
@section('meta_description', 'Manage incoming customer orders, verify delivery addresses, process dispatches and track store revenue.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'sales';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Orders &amp; Sales Hub</h1>
        <p class="text-muted mb-0">Track customer orders, manage delivery addresses, update fulfillment statuses, and log AWB tracking.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- View Mode Switcher -->
        <div class="btn-group" role="group" aria-label="View Mode">
            <button type="button" class="view-mode-btn active" id="btnViewCards" onclick="ZyraSeller.switchSalesView('cards')" title="Spacious Cards View">
                <i class="bi bi-grid-fill me-1"></i> Cards
            </button>
            <button type="button" class="view-mode-btn" id="btnViewTable" onclick="ZyraSeller.switchSalesView('table')" title="Compact Table View">
                <i class="bi bi-table me-1"></i> Table
            </button>
        </div>
        <button type="button" class="btn btn-outline-dark" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Export
        </button>
    </div>
</div>

<!-- Sales Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-currency-rupee"></i></div>
            <div class="flex-grow-1">
                <div class="seller-stat-value text-dark" id="salesTotalRevenue">₹0</div>
                <div class="seller-stat-label">Total Store Revenue</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-receipt"></i></div>
            <div class="flex-grow-1">
                <div class="seller-stat-value text-primary" id="salesTotalOrders">0</div>
                <div class="seller-stat-label">Total Orders Placed</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="seller-stat-card">
            <div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-check-circle"></i></div>
            <div class="flex-grow-1">
                <div class="seller-stat-value text-success" id="salesDeliveredOrders">0</div>
                <div class="seller-stat-label">Delivered Orders</div>
            </div>
        </div>
    </div>
</div>

<!-- Modern Filter Pills & Search Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #fff;">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-lg-5 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="sellerOrderSearch" class="form-control border-start-0 bg-light" placeholder="Search by Order ID, customer, city, phone, AWB..." oninput="ZyraSeller.renderSalesPage()">
                </div>
            </div>
            <div class="col-lg-7 col-md-6">
                <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                    <ul class="nav nav-pills seller-stage-pills" id="salesStatusPills">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" data-status="all" onclick="ZyraSeller.filterSalesStatus('all')">
                                All <span class="badge bg-secondary ms-1" id="filterCountAll">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-status="Pending" onclick="ZyraSeller.filterSalesStatus('Pending')">
                                Pending <span class="badge bg-danger ms-1" id="filterCountPending">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-status="Processing" onclick="ZyraSeller.filterSalesStatus('Processing')">
                                In Packing <span class="badge bg-warning text-dark ms-1" id="filterCountProcessing">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-status="Shipped" onclick="ZyraSeller.filterSalesStatus('Shipped')">
                                Dispatched <span class="badge bg-success ms-1" id="filterCountShipped">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-status="Delivered" onclick="ZyraSeller.filterSalesStatus('Delivered')">
                                Delivered <span class="badge bg-primary ms-1" id="filterCountDelivered">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top">
            <small class="text-muted" id="sellerOrdersResultCount">Loading orders...</small>
            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Automatic fulfillment emails trigger when you change status or save AWB.</small>
        </div>
    </div>
</div>

<!-- Primary: Modern Luxury Order Cards Container -->
<div id="sellerOrdersCardsContainer">
    <!-- Populated dynamically with modern order cards by seller.js -->
</div>

<!-- Secondary: Compact Table Container (Hidden by default, toggleable via switch) -->
<div id="sellerOrdersTableContainer" class="d-none">
    <div class="seller-card">
        <div class="seller-card-body p-0">
            <div class="table-responsive">
                <table class="table seller-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 120px;">Order ID</th>
                            <th style="min-width: 250px;">Delivery Address &amp; Customer</th>
                            <th style="min-width: 200px;">Products Ordered</th>
                            <th style="min-width: 90px;">Total</th>
                            <th style="min-width: 80px;">Payment</th>
                            <th style="min-width: 90px;">Date</th>
                            <th style="min-width: 160px;">Fulfillment Status</th>
                            <th style="min-width: 220px;">Tracking (AWB)</th>
                        </tr>
                    </thead>
                    <tbody id="sellerOrdersTableBody">
                        <!-- Populated dynamically by seller.js -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
