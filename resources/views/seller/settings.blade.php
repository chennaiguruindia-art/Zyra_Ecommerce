@extends('layouts.seller')

@section('title', 'Store Settings | ZYRA Seller Hub')
@section('meta_description', 'Configure your store profile, pickup warehouse address, bank payout information and preferences.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'settings';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Store Profile & Settings</h1>
        <p class="text-muted mb-0">Manage your business details, warehouse pickup address, and bank payout settings.</p>
    </div>
</div>

<form id="sellerSettingsForm" novalidate>
    <div class="row g-4">
        
        <div class="col-lg-8">
            <!-- 1. Store Profile -->
            <div class="seller-card mb-4">
                <div class="seller-card-header">
                    <h5 class="mb-0 fw-bold">Store Identity</h5>
                </div>
                <div class="seller-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="seller-form-label">Store Brand Name *</label>
                            <input type="text" class="form-control seller-form-control" value="ZYRA Lifestyle" required>
                        </div>
                        <div class="col-md-6">
                            <label class="seller-form-label">Display Slug</label>
                            <input type="text" class="form-control seller-form-control" value="zyra-lifestyle" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="seller-form-label">Business Email *</label>
                            <input type="email" class="form-control seller-form-control" value="order@shopwithzyra.in" required>
                        </div>
                        <div class="col-md-6">
                            <label class="seller-form-label">Contact Phone *</label>
                            <input type="tel" class="form-control seller-form-control" value="9884125555" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Warehouse Pickup Address -->
            <div class="seller-card mb-4">
                <div class="seller-card-header">
                    <h5 class="mb-0 fw-bold">Pickup Warehouse Address</h5>
                </div>
                <div class="seller-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="seller-form-label">Warehouse Address Line *</label>
                            <input type="text" class="form-control seller-form-control" value="1st Floor, F 200, 1st St, Block F" required>
                        </div>
                        <div class="col-md-4">
                            <label class="seller-form-label">City *</label>
                            <input type="text" class="form-control seller-form-control" value="Chennai" required>
                        </div>
                        <div class="col-md-4">
                            <label class="seller-form-label">State *</label>
                            <input type="text" class="form-control seller-form-control" value="Tamil Nadu" required>
                        </div>
                        <div class="col-md-4">
                            <label class="seller-form-label">PIN Code *</label>
                            <input type="text" class="form-control seller-form-control" value="600102" maxlength="6" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Bank Payout Information -->
            <div class="seller-card mb-4">
                <div class="seller-card-header">
                    <h5 class="mb-0 fw-bold">Settlement Bank Account</h5>
                </div>
                <div class="seller-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="seller-form-label">Bank Name *</label>
                            <input type="text" class="form-control seller-form-control" value="IDBI Bank" required>
                        </div>
                        <div class="col-md-6">
                            <label class="seller-form-label">Beneficiary Account Name *</label>
                            <input type="text" class="form-control seller-form-control" value="ZYRA Lifestyle" required>
                        </div>
                        <div class="col-md-6">
                            <label class="seller-form-label">Account Number *</label>
                            <input type="password" class="form-control seller-form-control" value="8382679882" required>
                        </div>
                        <div class="col-md-6">
                            <label class="seller-form-label">IFSC Code *</label>
                            <input type="text" class="form-control seller-form-control" value="IDIB000A599" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <button type="submit" class="btn btn-zyra-primary px-4 py-2">
                    <i class="bi bi-check2 me-1"></i> Save Store Settings
                </button>
            </div>
        </div>

        <!-- Right: Status & Mode -->
        <div class="col-lg-4">
            <div class="seller-card mb-4">
                <div class="seller-card-header">
                    <h5 class="mb-0 fw-bold">Store Availability</h5>
                </div>
                <div class="seller-card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="storeLiveSwitch" checked>
                        <label class="form-check-label fw-bold" for="storeLiveSwitch">Storefront Live</label>
                        <div class="small text-muted">When enabled, your products are visible to all buyers.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="holidayModeSwitch">
                        <label class="form-check-label fw-bold" for="holidayModeSwitch">Vacation / Holiday Mode</label>
                        <div class="small text-muted">Temporarily pause orders when away.</div>
                    </div>
                </div>
            </div>

            <div class="seller-card">
                <div class="seller-card-header">
                    <h5 class="mb-0 fw-bold">GST & Tax Info</h5>
                </div>
                <div class="seller-card-body small">
                    <div class="mb-2">
                        <span class="text-muted">GSTIN:</span> <strong>33BMTPA7854C2Z0</strong>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">PAN:</span> <strong>ABCDE1234F</strong>
                    </div>
                    <div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">GST Verified</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection
