@extends('layouts.app')

@section('title', 'Global Settings | ZYRA')

@push('styles')
<style>
/* ── Layout ─────────────────────────────────────────────────────────── */
.gs-page { background: #f7f8fc; min-height: 100vh; padding: 2rem 0 3rem; }

/* ── Page header ─────────────────────────────────────────────────────── */
.gs-page-title   { font-size: 1.55rem; font-weight: 800; letter-spacing: -.02em; }
.gs-page-sub     { color: #6b7280; font-size: .88rem; }

/* ── Section headings ────────────────────────────────────────────────── */
.gs-section-head {
    display: flex; align-items: center; gap: .6rem;
    font-size: .72rem; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: #9ca3af;
    margin-bottom: 1rem;
}
.gs-section-head i { font-size: 1rem; }

/* ── Cards ───────────────────────────────────────────────────────────── */
.gs-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

/* ── KPI cards ───────────────────────────────────────────────────────── */
.kpi-grid { display: grid; grid-template-columns: repeat(6,1fr); gap: .75rem; }
@media (max-width:1100px){ .kpi-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width:600px) { .kpi-grid { grid-template-columns: repeat(2,1fr); } }

.kpi-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1rem 1.1rem;
    position: relative;
    overflow: hidden;
    transition: box-shadow .15s;
}
.kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.09); }
.kpi-card::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 4px;
    border-radius: 4px 0 0 4px;
    background: var(--kpi-color, #6366f1);
}
.kpi-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    background: color-mix(in srgb, var(--kpi-color, #6366f1) 12%, white);
    color: var(--kpi-color, #6366f1);
    margin-bottom: .6rem;
}
.kpi-value { font-size: 1.6rem; font-weight: 800; line-height: 1; color: #111827; }
.kpi-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-top: .25rem; }
.kpi-sub   { font-size: .68rem; color: #d1d5db; margin-top: .15rem; }

/* ── Funnel table ────────────────────────────────────────────────────── */
.funnel-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.funnel-table thead th {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #9ca3af;
    padding: .5rem 1rem .5rem 0; border-bottom: 2px solid #f3f4f6;
}
.funnel-table tbody tr:hover td { background: #f9fafb; }
.funnel-table td {
    padding: .65rem 1rem .65rem 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: .85rem; color: #374151;
    vertical-align: middle;
}
.funnel-table td:first-child { padding-left: 0; }
.funnel-stage-dot {
    width: 10px; height: 10px; border-radius: 50%;
    display: inline-block; margin-right: .5rem;
    flex-shrink: 0;
}
.funnel-num { font-weight: 700; font-size: .92rem; color: #111827; }
.funnel-bar-wrap { width: 100px; background: #f3f4f6; border-radius: 4px; height: 6px; overflow: hidden; display: inline-block; vertical-align: middle; margin-left: .5rem; }
.funnel-bar-fill  { height: 100%; border-radius: 4px; transition: width .4s ease; }

/* ── Skeleton loader ─────────────────────────────────────────────────── */
.skel { background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%); background-size: 200%; animation: skel-shimmer 1.4s infinite; border-radius: 6px; }
@keyframes skel-shimmer { 0%{background-position:200%} 100%{background-position:-200%} }

/* ── Period tabs ─────────────────────────────────────────────────────── */
.gs-period-tabs { display: flex; gap: .4rem; }
.gs-period-tab {
    font-size: .72rem; font-weight: 600; padding: .3rem .75rem;
    border: 1px solid #e5e7eb; border-radius: 20px;
    background: #fff; color: #6b7280; cursor: pointer; transition: all .15s;
}
.gs-period-tab:hover  { border-color: #111; color: #111; }
.gs-period-tab.active { background: #111; color: #fff; border-color: #111; }

/* ── Coupon & Product tables (existing, cleaned) ─────────────────────── */
.gs-table { vertical-align: middle; font-size: .875rem; }
.gs-table thead th {
    text-transform: uppercase; font-size: .7rem; letter-spacing: .05em;
    color: #9ca3af; border-bottom: 2px solid #f3f4f6; font-weight: 700; padding: .65rem 1rem;
}
.gs-table td { border-color: #f3f4f6; padding: .75rem 1rem; }
.gs-table img.gs-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; background: #f6f6f6; }
.gs-switch { display: inline-flex; align-items: center; gap: .5rem; }

.wishlist-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8;
    border-radius: 20px; padding: .25rem .75rem; font-size: .75rem; font-weight: 700;
}
</style>
@endpush

@section('content')
<div class="gs-page">
<div class="container-xl">

    {{-- ── Page Header ── --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between mb-4">
        <div>
            <h1 class="gs-page-title mb-1">Global Settings</h1>
            <p class="gs-page-sub mb-0">
                Manage product flags, coupons, and monitor your store's live funnel performance.
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <span class="wishlist-badge" id="wlSavedBadge">
                <i class="bi bi-heart-fill"></i>
                <span id="wlSavedCount">—</span> Wishlisted
            </span>
            <a href="{{ route('home') }}" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-shop me-1"></i> View Store
            </a>
        </div>
    </div>

    {{-- ── KPI Cards ── --}}
    <div class="gs-section-head">
        <i class="bi bi-lightning-charge-fill text-warning"></i> Live Funnel — Today
    </div>
    <div class="kpi-grid mb-4" id="kpiGrid">
        @foreach([
            ['label'=>'Site Visits',   'icon'=>'bi-globe2',     'color'=>'#6366f1'],
            ['label'=>'Product Views', 'icon'=>'bi-eye',        'color'=>'#3b82f6'],
            ['label'=>'Wishlist Adds', 'icon'=>'bi-heart',      'color'=>'#ec4899'],
            ['label'=>'Cart Adds',     'icon'=>'bi-cart-plus',  'color'=>'#f59e0b'],
            ['label'=>'Checkouts',     'icon'=>'bi-credit-card','color'=>'#10b981'],
            ['label'=>'Purchases',     'icon'=>'bi-bag-check',  'color'=>'#22c55e'],
        ] as $k)
        <div class="kpi-card" style="--kpi-color:{{ $k['color'] }}">
            <div class="kpi-icon"><i class="bi {{ $k['icon'] }}"></i></div>
            <div class="kpi-value skel" style="height:32px;width:60px;">&nbsp;</div>
            <div class="kpi-label">{{ $k['label'] }}</div>
            <div class="kpi-sub">Today</div>
        </div>
        @endforeach
    </div>

    {{-- ── Funnel Snapshot Table ── --}}
    <div class="gs-card mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div class="gs-section-head mb-0">
                <i class="bi bi-funnel-fill" style="color:#6366f1"></i> Funnel Snapshot
            </div>
            <div class="gs-period-tabs" id="periodTabs">
                <button class="gs-period-tab active" data-period="today">Today</button>
                <button class="gs-period-tab" data-period="week">This Week</button>
                <button class="gs-period-tab" data-period="month">This Month</button>
                <button class="gs-period-tab" data-period="all">All Time</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="funnel-table" id="funnelTable">
                <thead>
                    <tr>
                        <th style="width:40%">Stage</th>
                        <th class="text-end">Count</th>
                        <th style="width:40%">vs Site Visits</th>
                    </tr>
                </thead>
                <tbody id="funnelBody">
                    @for($i = 0; $i < 6; $i++)
                    <tr>
                        <td><span class="skel d-inline-block" style="width:140px;height:14px"></span></td>
                        <td class="text-end"><span class="skel d-inline-block" style="width:50px;height:14px"></span></td>
                        <td><span class="skel d-inline-block" style="width:100px;height:6px;margin-left:.5rem"></span></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <p class="text-muted small mt-2 mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Data from <strong>customer_activities</strong> + <strong>site_visits</strong> tables.
            <a href="{{ route('seller.activity') }}" class="text-decoration-none ms-1">Full analytics →</a>
        </p>
    </div>

    {{-- ── Coupon Codes ── --}}
    <div class="gs-card">
        <div class="gs-section-head">
            <i class="bi bi-ticket-perforated-fill" style="color:#f59e0b"></i> Coupon Codes
        </div>
        <p class="text-muted small mb-3">Create or update promotional codes. They apply automatically at checkout.</p>

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
                <label class="form-label small fw-semibold">Max Discount ₹ <span class="text-muted">(opt.)</span></label>
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
                            <button type="button" class="btn btn-sm btn-outline-dark coupon-edit"
                                data-id="{{ $c->id }}" data-code="{{ $c->code }}" data-type="{{ $c->discount_type }}"
                                data-value="{{ $c->discount_value }}" data-min="{{ $c->min_order_amount }}"
                                data-max="{{ $c->max_discount ?? '' }}"
                                data-start="{{ optional($c->start_date)?->format('Y-m-d') ?? '' }}"
                                data-end="{{ optional($c->expiry_date)?->format('Y-m-d') ?? '' }}"
                                data-status="{{ $c->status ? 1 : 0 }}">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger coupon-delete"
                                data-id="{{ $c->id }}" data-code="{{ $c->code }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No coupons yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Product Flags ── --}}
    <div class="gs-card">
        <div class="gs-section-head">
            <i class="bi bi-toggles" style="color:#6366f1"></i> Product Flags
        </div>
        <p class="text-muted small mb-3">
            Toggle <strong>Best Seller</strong> / <strong>Trending</strong> to show the product in that home-page section.
            Toggle <strong>Dupatta</strong> to feature it on <code>/duppata</code> and enable the With/Without Dupatta option (₹1,350 off without).
        </p>
        <div class="table-responsive">
            <table class="table gs-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-0" style="width:60px">Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-center" style="width:130px">Best Seller</th>
                        <th class="text-center" style="width:150px">Trending</th>
                        <th class="text-center" style="width:120px">Dupatta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-0">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="gs-img">
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            <small class="text-muted">ID: {{ $product->id }}{{ $product->sku ? ' · SKU: '.$product->sku : '' }}</small>
                        </td>
                        <td>{{ $product->category?->name ?? '-' }}</td>
                        <td class="text-center">
                            <div class="form-check form-switch gs-switch m-0 justify-content-center">
                                <input class="form-check-input gs-toggle" type="checkbox" role="switch"
                                    data-product-id="{{ $product->id }}" data-field="is_best_seller"
                                    {{ $product->is_best_seller ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch gs-switch m-0 justify-content-center">
                                <input class="form-check-input gs-toggle" type="checkbox" role="switch"
                                    data-product-id="{{ $product->id }}" data-field="is_trending"
                                    {{ $product->is_trending ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch gs-switch m-0 justify-content-center">
                                <input class="form-check-input gs-toggle" type="checkbox" role="switch"
                                    data-product-id="{{ $product->id }}" data-field="dupatta_enabled"
                                    {{ $product->dupatta_enabled ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /container -->
</div><!-- /gs-page -->
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ── Analytics Snapshot ─────────────────────────────────────────────────
    let funnelData  = [];
    let activePeriod = 'today';

    const periodKey = { today: 'today', week: 'week', month: 'month', all: 'all' };

    function renderKpi(funnel) {
        const kpiCards = document.querySelectorAll('#kpiGrid .kpi-card');
        funnel.forEach((row, i) => {
            const card = kpiCards[i];
            if (!card) return;
            const valEl = card.querySelector('.kpi-value');
            if (valEl) {
                valEl.classList.remove('skel');
                valEl.style = '';
                valEl.textContent = row.today.toLocaleString('en-IN');
            }
        });
    }

    function renderFunnel(funnel, period) {
        const body     = document.getElementById('funnelBody');
        const siteVisits = funnel[0]?.[period] || 1;  // avoid divide-by-zero

        body.innerHTML = funnel.map(row => {
            const count  = row[period];
            const pct    = siteVisits > 0 ? Math.min(100, Math.round(count / siteVisits * 100)) : 0;
            return `
            <tr>
                <td>
                    <span class="funnel-stage-dot" style="background:${row.color}"></span>
                    <i class="bi ${row.icon} me-1" style="color:${row.color}"></i>
                    <strong>${row.label}</strong>
                </td>
                <td class="text-end"><span class="funnel-num">${count.toLocaleString('en-IN')}</span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="funnel-bar-wrap" style="flex:1;max-width:180px">
                            <div class="funnel-bar-fill" style="width:${pct}%;background:${row.color}"></div>
                        </div>
                        <span class="text-muted small">${pct}%</span>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    function loadAnalytics() {
        fetch('/global_setting/analytics', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                funnelData = data.funnel;

                // Wishlist badge
                const wlEl = document.getElementById('wlSavedCount');
                if (wlEl) wlEl.textContent = (data.wishlist_saved ?? 0).toLocaleString('en-IN');

                renderKpi(funnelData);
                renderFunnel(funnelData, activePeriod);
            })
            .catch(() => {
                document.getElementById('funnelBody').innerHTML =
                    '<tr><td colspan="3" class="text-center text-muted py-3">Could not load analytics.</td></tr>';
            });
    }

    // Period tab switching
    document.querySelectorAll('.gs-period-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.gs-period-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activePeriod = btn.dataset.period;
            if (funnelData.length) renderFunnel(funnelData, activePeriod);
        });
    });

    loadAnalytics();

    // ── Coupon management ──────────────────────────────────────────────────
    const couponForm    = document.getElementById('couponForm');
    const couponSaveBtn = document.getElementById('couponSaveBtn');
    const couponResetBtn= document.getElementById('couponResetBtn');

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
            couponSaveBtn.disabled = true;
            fetch('/global_setting/coupons', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: new FormData(couponForm)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (window.ZyraApp) window.ZyraApp.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    if (window.ZyraApp) window.ZyraApp.showToast(data.message || 'Could not save.', 'danger');
                }
            })
            .catch(() => { if (window.ZyraApp) window.ZyraApp.showToast('Could not reach server.', 'danger'); })
            .finally(() => { couponSaveBtn.disabled = false; });
        });

        couponResetBtn.addEventListener('click', resetCouponForm);

        document.querySelectorAll('.coupon-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('couponId').value    = btn.dataset.id;
                document.getElementById('couponCode').value  = btn.dataset.code;
                document.getElementById('couponType').value  = btn.dataset.type;
                document.getElementById('couponValue').value = btn.dataset.value;
                document.getElementById('couponMin').value   = btn.dataset.min;
                document.getElementById('couponMax').value   = btn.dataset.max || '';
                document.getElementById('couponStart').value = btn.dataset.start || '';
                document.getElementById('couponEnd').value   = btn.dataset.end || '';
                document.getElementById('couponStatus').checked = btn.dataset.status === '1';
                couponSaveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Update Coupon';
                couponResetBtn.style.display = 'inline-block';
                document.getElementById('couponCode').focus();
                window.scrollTo({ top: couponForm.offsetTop - 90, behavior: 'smooth' });
            });
        });

        document.querySelectorAll('.coupon-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!window.confirm('Delete coupon "' + btn.dataset.code + '"? This cannot be undone.')) return;
                btn.disabled = true;
                fetch('/global_setting/coupons/' + btn.dataset.id + '/delete', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (window.ZyraApp) window.ZyraApp.showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else if (window.ZyraApp) window.ZyraApp.showToast(data.message || 'Could not delete.', 'danger');
                })
                .catch(() => { if (window.ZyraApp) window.ZyraApp.showToast('Could not reach server.', 'danger'); })
                .finally(() => { btn.disabled = false; });
            });
        });
    }

    // ── Product flag toggles ───────────────────────────────────────────────
    document.querySelectorAll('.gs-toggle').forEach(toggle => {
        toggle.addEventListener('change', () => {
            const productId = toggle.getAttribute('data-product-id');
            const field     = toggle.getAttribute('data-field');
            const value     = toggle.checked;
            toggle.disabled = true;
            fetch('/global_setting/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ product_id: productId, field: field, value: value })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (window.ZyraApp) window.ZyraApp.showToast(data.product.name + ' updated.', 'success');
                } else {
                    toggle.checked = !value;
                    if (window.ZyraApp) window.ZyraApp.showToast(data.message || 'Update failed.', 'danger');
                }
            })
            .catch(() => {
                toggle.checked = !value;
                if (window.ZyraApp) window.ZyraApp.showToast('Could not reach server.', 'danger');
            })
            .finally(() => { toggle.disabled = false; });
        });
    });
});
</script>
@endpush
