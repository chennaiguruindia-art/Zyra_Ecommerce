@extends('layouts.seller')

@section('title', 'Inventory Management | ZYRA Seller Hub')
@section('meta_description', 'Track and update real-time stock levels for all products in your seller store.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'inventory';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Inventory Management</h1>
        <p class="text-muted mb-0">Monitor stock levels, set replenishment triggers, and prevent out-of-stock items.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-dark" onclick="ZyraApp.showToast('All stock levels synchronized with buyer storefront!', 'success')">
            <i class="bi bi-arrow-repeat me-1"></i> Sync Inventory
        </button>
    </div>
</div>

<!-- Low Stock Alert Banner -->
<div id="inventoryLowStockAlert" class="alert alert-warning border-warning-subtle shadow-sm mb-4 d-flex align-items-center gap-3" style="display: none;">
    <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
    <div>
        <strong>Attention Needed:</strong> You have <strong class="low-count">0</strong> product(s) with less than 10 units remaining. Restock soon to prevent order cancellations.
    </div>
</div>

<!-- Inventory Table Card -->
<div class="seller-card">
    <div class="seller-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-boxes me-2 text-muted"></i>Stock Level Tracker</h5>
        <small class="text-muted">Use + / - buttons to adjust live inventory</small>
    </div>
    <div class="seller-card-body p-0">
        <div class="table-responsive">
            <table class="table seller-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Current Units</th>
                        <th>Stock Status</th>
                        <th>Quick Restock</th>
                    </tr>
                </thead>
                <tbody id="sellerInventoryTableBody">
                    <!-- Populated dynamically by seller.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
