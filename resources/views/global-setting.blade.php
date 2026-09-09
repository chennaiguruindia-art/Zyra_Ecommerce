@extends('layouts.app')

@section('title', 'Global Settings | ZYRA')

@push('styles')
<style>
    .gs-table { vertical-align: middle; }
    .gs-table thead th {
        text-transform: uppercase;
        font-size: .75rem;
        letter-spacing: .04em;
        color: #8a8a8a;
        border-bottom: 2px solid #000;
    }
    .gs-table td { border-color: #f0f0f0; }
    .gs-table img.gs-img {
        width: 56px; height: 56px; object-fit: cover; border-radius: 8px;
        background: #f6f6f6;
    }
    .gs-switch { display: inline-flex; align-items: center; gap: .5rem; }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1">Global Settings</h1>
            <p class="text-muted mb-0">
                Enable a product's toggle to show it in the corresponding home page section (Best Sellers / Trending Collection). Toggles are saved to the database.
            </p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-dark btn-sm">View Home Page</a>
    </div>

    <!-- Coupon Date Management -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1">Coupon Offer Window</h5>
            <p class="text-muted small mb-3">
                Set the start &amp; end dates for the <strong>WELCOME10</strong> launch offer.
                The coupon is only valid (enabled) for orders placed within this date range.
                <strong>FIRSTORDER</strong> (10% off first order) is always available for new customers.
            </p>

            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="welcomeStart" class="form-label small fw-semibold">WELCOME10 Start Date</label>
                    <input type="date" id="welcomeStart" class="form-control"
                           value="{{ optional($welcomeCoupon)->start_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="welcomeEnd" class="form-label small fw-semibold">WELCOME10 End Date</label>
                    <input type="date" id="welcomeEnd" class="form-control"
                           value="{{ optional($welcomeCoupon)->expiry_date?->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <button type="button" id="saveCouponDates" class="btn btn-dark w-100">
                        <i class="bi bi-calendar-check me-1"></i> Save Offer Dates
                    </button>
                </div>
            </div>

            @if(optional($welcomeCoupon)->status && optional($welcomeCoupon)->start_date)
                <div class="mt-3 small text-muted" id="couponStatusText">
                    <i class="bi bi-info-circle me-1"></i>
                    WELCOME10 is enabled
                    {{ \Carbon\Carbon::now()->between($welcomeCoupon->start_date, $welcomeCoupon->expiry_date?->endOfDay()) ? 'and active now.' : 'but is outside its active window.' }}
                </div>
            @endif
        </div>
    </div>

    <!-- Coupon Code Management -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Coupon Codes</h5>
                    <p class="text-muted small mb-0">Create or update promotional codes here. Codes apply automatically at checkout.</p>
                </div>
            </div>

            <form id="couponForm" class="row g-3">
                @csrf
                <input type="hidden" id="couponId" name="coupon_id" value="">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Code</label>
                    <input type="text" id="couponCode" name="code" class="form-control text-uppercase" placeholder="e.g. SUMMER15" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select id="couponType" name="discount_type" class="form-select">
                        <option value="percent">% Off</option>
                        <option value="fixed">₹ Off</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Value</label>
                    <input type="number" step="0.01" min="0.01" id="couponValue" name="discount_value" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Min Order ₹</label>
                    <input type="number" step="0.01" min="0" id="couponMin" name="min_order_amount" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Max Discount ₹ <span class="text-muted">(optional)</span></label>
                    <input type="number" step="0.01" min="0" id="couponMax" name="max_discount" class="form-control" placeholder="Unlimited">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Start Date</label>
                    <input type="date" id="couponStart" name="start_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">End Date</label>
                    <input type="date" id="couponEnd" name="expiry_date" class="form-control">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="couponStatus" name="status" value="1" checked>
                        <label class="form-check-label" for="couponStatus">Active</label>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-dark" id="couponSaveBtn">
                        <i class="bi bi-plus-lg me-1"></i> Save Coupon
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="couponResetBtn" style="display:none;">Reset</button>
                </div>
            </form>

            <hr class="my-4">

            <div class="table-responsive">
                <table class="table gs-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Min Order</th>
                            <th>Validity</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $c)
                        <tr data-code="{{ $c->code }}">
                            <td class="fw-semibold">{{ $c->code }}</td>
                            <td>
                                {{ $c->discount_type === 'percent' ? $c->discount_value . '%' : '₹' . $c->discount_value }}
                                @if($c->max_discount)
                                    <span class="text-muted small">(max ₹{{ $c->max_discount }})</span>
                                @endif
                            </td>
                            <td>₹{{ $c->min_order_amount }}</td>
                            <td class="small">
                                @if($c->start_date || $c->expiry_date)
                                    {{ optional($c->start_date)?->format('d M Y') ?? 'Always' }} → {{ optional($c->expiry_date)?->format('d M Y') ?? 'Forever' }}
                                @else
                                    <span class="text-muted">Always valid</span>
                                @endif
                            </td>
                            <td>
                                @if($c->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-dark coupon-edit" data-id="{{ $c->id }}" data-code="{{ $c->code }}" data-type="{{ $c->discount_type }}" data-value="{{ $c->discount_value }}" data-min="{{ $c->min_order_amount }}" data-max="{{ $c->max_discount ?? '' }}" data-start="{{ optional($c->start_date)?->format('Y-m-d') ?? '' }}" data-end="{{ optional($c->expiry_date)?->format('Y-m-d') ?? '' }}" data-status="{{ $c->status ? 1 : 0 }}"><i class="bi bi-pencil"></i> Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger coupon-delete" data-id="{{ $c->id }}" data-code="{{ $c->code }}"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No coupons created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table gs-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width:70px;">Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th class="text-center" style="width:150px;">Best Seller</th>
                            <th class="text-center pe-4" style="width:180px;">Trending Collection</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-4">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="gs-img">
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    <small class="text-muted">ID: {{ $product->id }}{{ $product->sku ? ' · SKU: '.$product->sku : '' }}</small>
                                </td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch gs-switch m-0">
                                        <input class="form-check-input gs-toggle" type="checkbox" role="switch"
                                               data-product-id="{{ $product->id }}" data-field="is_best_seller"
                                               {{ $product->is_best_seller ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="form-check form-switch gs-switch m-0">
                                        <input class="form-check-input gs-toggle" type="checkbox" role="switch"
                                               data-product-id="{{ $product->id }}" data-field="is_trending"
                                               {{ $product->is_trending ? 'checked' : '' }}>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const saveBtn = document.getElementById('saveCouponDates');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                const start = document.getElementById('welcomeStart').value;
                const end = document.getElementById('welcomeEnd').value;

                if (!start || !end) {
                    if (window.ZyraApp) {
                        window.ZyraApp.showToast('Please choose both start and end dates.', 'danger');
                    }
                    return;
                }
                if (new Date(end) < new Date(start)) {
                    if (window.ZyraApp) {
                        window.ZyraApp.showToast('End date cannot be before the start date.', 'danger');
                    }
                    return;
                }

                saveBtn.disabled = true;
                fetch('/global_setting/coupon-dates', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ start_date: start, expiry_date: end })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.ZyraApp) {
                            window.ZyraApp.showToast(data.message || 'Offer dates saved.', 'success');
                        }
                    } else {
                        if (window.ZyraApp) {
                            window.ZyraApp.showToast(data.message || 'Could not save.', 'danger');
                        }
                    }
                })
                .catch(() => {
                    if (window.ZyraApp) {
                        window.ZyraApp.showToast('Could not reach server.', 'danger');
                    }
                })
                .finally(() => {
                    saveBtn.disabled = false;
                });
            });
        }

        // --- Coupon Code management ---
        const couponForm = document.getElementById('couponForm');
        const couponSaveBtn = document.getElementById('couponSaveBtn');
        const couponResetBtn = document.getElementById('couponResetBtn');

        const resetCouponForm = () => {
            document.getElementById('couponId').value = '';
            couponForm.reset();
            document.getElementById('couponStatus').checked = true;
            couponSaveBtn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Save Coupon';
            couponResetBtn.style.display = 'none';
        };

        if (couponForm) {
            couponForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const body = new FormData(couponForm);
                couponSaveBtn.disabled = true;
                fetch('/global_setting/coupons', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: body
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.ZyraApp) window.ZyraApp.showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        if (window.ZyraApp) window.ZyraApp.showToast(data.message || 'Could not save coupon.', 'danger');
                    }
                })
                .catch(() => {
                    if (window.ZyraApp) window.ZyraApp.showToast('Could not reach server.', 'danger');
                })
                .finally(() => { couponSaveBtn.disabled = false; });
            });

            couponResetBtn.addEventListener('click', resetCouponForm);

            document.querySelectorAll('.coupon-edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('couponId').value = btn.getAttribute('data-id');
                    document.getElementById('couponCode').value = btn.getAttribute('data-code');
                    document.getElementById('couponType').value = btn.getAttribute('data-type');
                    document.getElementById('couponValue').value = btn.getAttribute('data-value');
                    document.getElementById('couponMin').value = btn.getAttribute('data-min');
                    document.getElementById('couponMax').value = btn.getAttribute('data-max') || '';
                    document.getElementById('couponStart').value = btn.getAttribute('data-start') || '';
                    document.getElementById('couponEnd').value = btn.getAttribute('data-end') || '';
                    document.getElementById('couponStatus').checked = btn.getAttribute('data-status') === '1';
                    couponSaveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Update Coupon';
                    couponResetBtn.style.display = 'inline-block';
                    document.getElementById('couponCode').focus();
                    window.scrollTo({ top: couponForm.offsetTop - 90, behavior: 'smooth' });
                });
            });

            document.querySelectorAll('.coupon-delete').forEach(btn => {
                btn.addEventListener('click', () => {
                    const code = btn.getAttribute('data-code');
                    if (!window.confirm('Delete coupon "' + code + '"? This cannot be undone.')) return;
                    btn.disabled = true;
                    fetch('/global_setting/coupons/' + btn.getAttribute('data-id'), {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (window.ZyraApp) window.ZyraApp.showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 600);
                        } else if (window.ZyraApp) {
                            window.ZyraApp.showToast(data.message || 'Could not delete.', 'danger');
                        }
                    })
                    .catch(() => {
                        if (window.ZyraApp) window.ZyraApp.showToast('Could not reach server.', 'danger');
                    })
                    .finally(() => { btn.disabled = false; });
                });
            });
        }

        document.querySelectorAll('.gs-toggle').forEach(toggle => {
            toggle.addEventListener('change', () => {
                const productId = toggle.getAttribute('data-product-id');
                const field = toggle.getAttribute('data-field');
                const value = toggle.checked;

                toggle.disabled = true;

                fetch('/global_setting/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ product_id: productId, field: field, value: value })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.ZyraApp) {
                            window.ZyraApp.showToast(data.product.name + ' updated.', 'success');
                        }
                    } else {
                        toggle.checked = !value;
                        if (window.ZyraApp) {
                            window.ZyraApp.showToast(data.message || 'Update failed.', 'danger');
                        }
                    }
                })
                .catch(() => {
                    toggle.checked = !value;
                    if (window.ZyraApp) {
                        window.ZyraApp.showToast('Could not reach server.', 'danger');
                    }
                })
                .finally(() => {
                    toggle.disabled = false;
                });
            });
        });
    });
</script>
@endpush