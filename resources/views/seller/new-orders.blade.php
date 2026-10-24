@extends('layouts.seller')

@section('title', 'Order Fulfillment & Packing Queue | ZYRA Seller Hub')
@section('meta_description', 'Pack and dispatch freshly received customer orders, track packing status and delivery addresses.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'neworders';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1" id="orderQueueHeaderTitle">Order Fulfillment Hub</h1>
        <p class="text-muted mb-0">Manage complete order fulfillment: verify delivery addresses, start packing items, and dispatch with courier tracking.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- View Mode Switcher -->
        <div class="btn-group" role="group" aria-label="View Mode">
            <button type="button" class="view-mode-btn active" id="btnNewViewCards" onclick="ZyraSeller.switchNewOrdersView('cards')" title="Spacious Cards View">
                <i class="bi bi-grid-fill me-1"></i> Cards
            </button>
            <button type="button" class="view-mode-btn" id="btnNewViewTable" onclick="ZyraSeller.switchNewOrdersView('table')" title="Compact Table View">
                <i class="bi bi-table me-1"></i> Table
            </button>
        </div>
        <a href="{{ route('seller.sales') }}" class="btn btn-outline-dark">
            <i class="bi bi-receipt me-1"></i> View All Sales
        </a>
    </div>
</div>

<!-- Fulfillment Stage Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <div class="seller-stat-card cursor-pointer" onclick="ZyraSeller.setOrderStage('pending')" role="button" title="View New Received Orders">
            <div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-inbox"></i></div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="seller-stat-value text-danger" id="newOrdersCount">{{ $pendingCount ?? 0 }}</div>
                    <span class="badge bg-danger-subtle text-danger small">Action Needed</span>
                </div>
                <div class="seller-stat-label">New Orders (Received)</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="seller-stat-card cursor-pointer" onclick="ZyraSeller.setOrderStage('processing')" role="button" title="View Orders Currently in Packing">
            <div class="seller-stat-icon bg-gold-soft text-warning"><i class="bi bi-box-seam"></i></div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="seller-stat-value text-warning" id="packingOrdersCount">{{ $processingCount ?? 0 }}</div>
                    <span class="badge bg-warning-subtle text-dark small">In Progress</span>
                </div>
                <div class="seller-stat-label">In Packing (Processing)</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-12">
        <div class="seller-stat-card cursor-pointer" onclick="ZyraSeller.setOrderStage('shipped')" role="button" title="View Dispatched Orders">
            <div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-truck"></i></div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="seller-stat-value text-success" id="dispatchedOrdersCount">{{ $shippedCount ?? 0 }}</div>
                    <span class="badge bg-success-subtle text-success small">Shipped</span>
                </div>
                <div class="seller-stat-label">Dispatched (On the Way)</div>
            </div>
        </div>
    </div>
</div>

<!-- Workflow Stages Navigation Pills -->
<div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; background: #fff;">
    <div class="card-body p-2 p-md-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <ul class="nav nav-pills seller-stage-pills" id="sellerOrderStagePills">
                <li class="nav-item">
                    <button type="button" class="nav-link active" data-stage="pending" onclick="ZyraSeller.setOrderStage('pending')">
                        <i class="bi bi-inbox me-1"></i> New Orders (Received)
                        <span class="badge bg-danger ms-1" id="stageBadgePending">{{ $pendingCount ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" data-stage="processing" onclick="ZyraSeller.setOrderStage('processing')">
                        <i class="bi bi-box-seam me-1"></i> In Packing
                        <span class="badge bg-warning text-dark ms-1" id="stageBadgeProcessing">{{ $processingCount ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" data-stage="shipped" onclick="ZyraSeller.setOrderStage('shipped')">
                        <i class="bi bi-truck me-1"></i> Dispatched
                        <span class="badge bg-success ms-1" id="stageBadgeShipped">{{ $shippedCount ?? 0 }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" data-stage="all" onclick="ZyraSeller.setOrderStage('all')">
                        <i class="bi bi-layers me-1"></i> All Orders
                        <span class="badge bg-secondary ms-1" id="stageBadgeAll">0</span>
                    </button>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-muted border px-2 py-2" id="newOrdersResultCount">Loading orders...</span>
            </div>
        </div>
    </div>
</div>

<!-- Search & Controls Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #fff;">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="newOrderSearch" class="form-control border-start-0 bg-light" placeholder="Search by Order ID, customer, address, city, phone..." oninput="ZyraSeller.renderNewOrdersPage()">
                </div>
            </div>
            <div class="col-md-6 col-lg-7 text-md-end">
                <div class="d-flex align-items-center justify-content-md-end gap-2">
                    <span class="badge bg-light text-muted border px-2 py-2" id="orderQueueCardHeading">
                        <i class="bi bi-box-seam me-1 text-danger"></i>Packing Queue
                    </span>
                    <small class="text-muted" id="orderQueueCardSub">Review delivery addresses &amp; dispatch</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Primary: Modern Luxury Order Cards Container -->
<div id="sellerNewOrdersCardsContainer">
    <!-- Populated dynamically with modern order cards by seller.js -->
</div>

<!-- Secondary: Compact Table Container (Hidden by default, toggleable via switch) -->
<div id="sellerNewOrdersTableContainer" class="d-none">
    <div class="seller-card">
        <div class="seller-card-body p-0">
            <div class="table-responsive">
                <table class="table seller-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 130px;">Order ID</th>
                            <th style="min-width: 260px;">Delivery Address &amp; Customer</th>
                            <th style="min-width: 220px;">Products to Pack</th>
                            <th style="min-width: 100px;">Total</th>
                            <th style="min-width: 90px;">Payment</th>
                            <th style="min-width: 110px;">Status</th>
                            <th style="min-width: 100px;">Date</th>
                            <th style="min-width: 240px;">Fulfillment Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sellerNewOrdersTableBody">
                        <!-- Populated dynamically by seller.js -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection