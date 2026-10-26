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
        <small class="text-muted">Use Restock to add incoming units per size</small>
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
                        <th>Restock</th>
                    </tr>
                </thead>
                <tbody id="sellerInventoryTableBody">
                    <!-- Populated dynamically by seller.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Per-Size Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-box-seam"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="restockModalTitle">Restock Product</h5>
                        <small class="text-muted" id="restockModalSubtitle">Add incoming units for each size</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div id="restockModalBody">
                    <!-- Size rows built by ZyraSeller.openRestockModal() -->
                </div>

                <!-- Fallback for products with no size variants -->
                <div id="restockModalNoSizes" class="d-none">
                    <div class="row g-3 align-items-end">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="restockTotalUnits">Total Units</label>
                            <input type="number" min="0" step="1" class="form-control" id="restockTotalUnits" value="0">
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">This product has no size variants. The value above replaces the total stock.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <div class="me-auto small">
                    <span class="text-muted">Total to add:</span>
                    <strong id="restockGrandTotal" class="text-dark ms-1">0</strong>
                    <span class="text-muted">unit(s)</span>
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark px-4" id="restockSaveBtn" onclick="ZyraSeller.saveRestock()">
                    <i class="bi bi-check2-circle me-1"></i> Save Restock
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
