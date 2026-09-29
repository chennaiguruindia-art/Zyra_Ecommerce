@extends('layouts.seller')

@section('title', 'Shopping Analytics | ZYRA Seller Hub')
@section('meta_description', 'Anonymous customer shopping behavior: views, wishlist, cart, checkout and purchases.')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    window.ZyraSeller = window.ZyraSeller || {};
    window.ZyraSeller.page = 'activity';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Customer Activity / Shopping Analytics</h1>
        <p class="text-muted mb-0">100% anonymous — counts shoppers and products only. No names, emails, phones or addresses are collected or shown.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="#" id="activityExport" class="btn btn-dark btn-sm"><i class="bi bi-download me-1"></i> Export CSV</a>
    </div>
</div>

<style>
.act-filters { background: #fff; border: 1px solid #ece5e0; border-radius: 12px; padding: 14px 16px; }
.act-filters .form-select, .act-filters .form-control { font-size: .85rem; }
.act-range-btn { border: 1px solid #e0d6cf; background: #fff; color: #5a4a4a; font-size: .78rem; font-weight: 600; border-radius: 999px; padding: 6px 13px; cursor: pointer; white-space: nowrap; }
.act-range-btn:hover { border-color: #8d5a5a; color: #8d5a5a; }
.act-range-btn.active { background: #18181b; border-color: #18181b; color: #fff; }
.act-funnel-bar { height: 44px; border-radius: 10px; display: flex; align-items: center; padding: 0 14px; color: #fff; font-weight: 700; font-size: .85rem; min-width: 120px; transition: width .5s ease; white-space: nowrap; overflow: hidden; }
.act-table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .4px; color: #6c757d; cursor: pointer; user-select: none; white-space: nowrap; }
.act-table td { font-size: .86rem; vertical-align: middle; }
.act-search { max-width: 260px; font-size: .85rem; }
</style>

<!-- Filters -->
<div class="act-filters mb-4">
    <div class="d-flex flex-wrap gap-2 mb-3" id="activityRanges">
        <button type="button" class="act-range-btn" data-range="today">Today</button>
        <button type="button" class="act-range-btn" data-range="yesterday">Yesterday</button>
        <button type="button" class="act-range-btn active" data-range="last7">Last 7 Days</button>
        <button type="button" class="act-range-btn" data-range="last30">Last 30 Days</button>
        <button type="button" class="act-range-btn" data-range="this_month">This Month</button>
        <button type="button" class="act-range-btn" data-range="custom">Custom</button>
    </div>
    <div class="row g-2">
        <div class="col-6 col-md-2" id="activityFromWrap" style="display:none;">
            <input type="date" id="activityFrom" class="form-control">
        </div>
        <div class="col-6 col-md-2" id="activityToWrap" style="display:none;">
            <input type="date" id="activityTo" class="form-control">
        </div>
        <div class="col-6 col-md-3">
            <select id="activityProduct" class="form-select">
                <option value="">All products</option>
                @foreach($filterProducts as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select id="activityCategory" class="form-select">
                <option value="">All categories</option>
                @foreach($filterCategories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select id="activitySize" class="form-select">
                <option value="">All sizes</option>
                @foreach($filterSizes as $s)
                    <option value="{{ $s->name }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select id="activityColor" class="form-select">
                <option value="">All colors</option>
                @foreach($filterColors as $c)
                    <option value="{{ $c->name }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- Summary cards -->
<div class="row g-3 mb-4" id="activityCards">
    <div class="col-6 col-md-4 col-xl-3"><div class="seller-stat-card"><div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-eye"></i></div><div><div class="seller-stat-value" data-k="views">–</div><div class="seller-stat-label">Product Views</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-3"><div class="seller-stat-card"><div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-heart"></i></div><div><div class="seller-stat-value" data-k="wishlist">–</div><div class="seller-stat-label">Wishlist Adds</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-3"><div class="seller-stat-card"><div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-bag"></i></div><div><div class="seller-stat-value" data-k="carts">–</div><div class="seller-stat-label">Add to Cart</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-3"><div class="seller-stat-card"><div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-credit-card"></i></div><div><div class="seller-stat-value" data-k="checkouts">–</div><div class="seller-stat-label">Checkout Starts</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-3"><div class="seller-stat-card"><div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-bag-check"></i></div><div><div class="seller-stat-value" data-k="purchases">–</div><div class="seller-stat-label">Purchases</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-3"><div class="seller-stat-card"><div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-funnel"></i></div><div><div class="seller-stat-value" data-k="cart_conversion" data-suffix="%">–</div><div class="seller-stat-label">Cart Conversion (view → cart users)</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-3"><div class="seller-stat-card"><div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-funnel"></i></div><div><div class="seller-stat-value" data-k="checkout_conversion" data-suffix="%">–</div><div class="seller-stat-label">Checkout Conversion (cart → checkout users)</div></div></div></div>
    <div class="col-6 col-md-4 col-xl-3"><div class="seller-stat-card"><div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-funnel"></i></div><div><div class="seller-stat-value" data-k="purchase_conversion" data-suffix="%">–</div><div class="seller-stat-label">Purchase Conversion (checkout → buyers)</div></div></div></div>
</div>

<div class="row g-4 mb-4">
    <!-- Funnel -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-1">Shopping Journey</h6>
                <p class="text-muted small mb-3">Anonymous shoppers reaching each stage (total events · unique users).</p>
                <div id="activityFunnel" class="d-flex flex-column gap-2"></div>
            </div>
        </div>
    </div>
    <!-- Trend -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Activity Trend</h6>
                <div style="min-height: 260px;"><canvas id="activityTrend"></canvas></div>
            </div>
        </div>
    </div>
</div>

<!-- Product table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
            <h6 class="fw-bold mb-0 me-auto">Product-wise Analytics</h6>
            <input type="text" id="activityProductSearch" class="form-control form-control-sm act-search" placeholder="Search product…">
        </div>
        <div class="table-responsive">
            <table class="table table-hover act-table mb-0" id="activityProductTable">
                <thead><tr>
                    <th data-s="name">Product</th><th data-s="category">Category</th><th data-s="views">Views</th><th data-s="uniq_viewers">Unique</th><th data-s="wishlist">Wishlist</th><th data-s="carts">Cart</th><th data-s="checkouts">Checkout</th><th data-s="purchases">Purchased</th><th data-s="uniq_buyers">Buyers</th><th data-s="conversion">View→Buy %</th>
                </tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Variant table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
            <h6 class="fw-bold mb-0 me-auto">Size × Color Analytics</h6>
            <input type="text" id="activityVariantSearch" class="form-control form-control-sm act-search" placeholder="Search product / size / color…">
        </div>
        <div class="table-responsive">
            <table class="table table-hover act-table mb-0" id="activityVariantTable">
                <thead><tr>
                    <th data-s="name">Product</th><th data-s="size">Size</th><th data-s="color">Color</th><th data-s="views">Views</th><th data-s="wishlist">Wishlist</th><th data-s="carts">Cart</th><th data-s="checkouts">Checkout</th><th data-s="purchases">Purchased</th>
                </tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const state = { range: 'last7', sortP: { k: 'views', d: -1 }, sortV: { k: 'views', d: -1 }, products: [], variants: [] };
    let trendChart = null;

    const funnelColors = ['#18181b', '#e64980', '#F5B301', '#339af0', '#2f9e44'];

    function params() {
        const p = new URLSearchParams({ range: state.range });
        const from = $('activityFrom').value, to = $('activityTo').value;
        if (state.range === 'custom') { if (from) p.set('from', from); if (to) p.set('to', to); }
        const pid = $('activityProduct').value, cid = $('activityCategory').value;
        const size = $('activitySize').value, color = $('activityColor').value;
        if (pid) p.set('product_id', pid);
        if (cid) p.set('category_id', cid);
        if (size) p.set('size', size);
        if (color) p.set('color', color);
        return p;
    }

    function load() {
        const q = params();
        $('activityExport').href = '/seller/activity/export?' + q.toString();
        fetch('/seller/activity/data?' + q.toString(), { headers: { Accept: 'application/json' } })
            .then((r) => r.json())
            .then(render)
            .catch(() => {});
    }

    function render(d) {
        const s = d.summary || {};
        document.querySelectorAll('#activityCards [data-k]').forEach((el) => {
            const k = el.dataset.k;
            el.textContent = (s[k] ?? '–') + (el.dataset.suffix || '');
        });

        // Funnel
        const funnel = d.funnel || [];
        const max = Math.max(1, ...funnel.map((f) => f.users));
        $('activityFunnel').innerHTML = funnel.map((f, i) => {
            const w = Math.max(8, Math.round((f.users / max) * 100));
            return `<div><div class="d-flex justify-content-between small mb-1"><strong>${f.stage}</strong><span class="text-muted">${f.total} events · ${f.users} users</span></div>` +
                `<div class="act-funnel-bar" style="width:${w}%;background:${funnelColors[i % funnelColors.length]};">${f.users} users</div></div>`;
        }).join('');

        state.products = d.products || [];
        state.variants = d.variants || [];
        drawProductTable();
        drawVariantTable();
        drawTrend(d.trend || { labels: [], series: {} });
    }

    function sorted(rows, st) {
        return rows.slice().sort((a, b) => {
            const av = a[st.k], bv = b[st.k];
            if (typeof av === 'number' && typeof bv === 'number') return (av - bv) * st.d;
            return String(av ?? '').localeCompare(String(bv ?? '')) * st.d;
        });
    }

    function drawProductTable() {
        const q = ($('activityProductSearch').value || '').toLowerCase();
        const rows = sorted(state.products.filter((r) => !q || (r.name || '').toLowerCase().includes(q)), state.sortP);
        const tb = document.querySelector('#activityProductTable tbody');
        tb.innerHTML = rows.length ? rows.map((r) => `<tr>
            <td><a href="/product/${r.id}" target="_blank" class="text-decoration-none fw-semibold text-dark">${escapeHtml(r.name)}</a></td>
            <td class="text-muted">${escapeHtml(r.category)}</td>
            <td><strong>${r.views}</strong></td><td>${r.uniq_viewers}</td><td>${r.wishlist}</td><td>${r.carts}</td><td>${r.checkouts}</td>
            <td><strong>${r.purchases}</strong></td><td>${r.uniq_buyers}</td><td>${r.conversion}%</td></tr>`).join('')
            : '<tr><td colspan="10" class="text-center text-muted py-4">No activity in this period.</td></tr>';
    }

    function drawVariantTable() {
        const q = ($('activityVariantSearch').value || '').toLowerCase();
        const rows = sorted(state.variants.filter((r) => !q || ((r.name + ' ' + r.size + ' ' + r.color).toLowerCase().includes(q))), state.sortV);
        const tb = document.querySelector('#activityVariantTable tbody');
        tb.innerHTML = rows.length ? rows.map((r) => `<tr>
            <td><a href="/product/${r.id}" target="_blank" class="text-decoration-none fw-semibold text-dark">${escapeHtml(r.name)}</a></td>
            <td>${escapeHtml(r.size)}</td><td>${escapeHtml(r.color)}</td>
            <td><strong>${r.views}</strong></td><td>${r.wishlist}</td><td>${r.carts}</td><td>${r.checkouts}</td><td><strong>${r.purchases}</strong></td></tr>`).join('')
            : '<tr><td colspan="8" class="text-center text-muted py-4">No variant activity in this period.</td></tr>';
    }

    function drawTrend(trend) {
        if (typeof Chart === 'undefined') return;
        const labels = trend.labels || [];
        const series = trend.series || {};
        const defs = [
            ['product_viewed', 'Views', '#868e96'],
            ['wishlist_added', 'Wishlist', '#e64980'],
            ['cart_added', 'Cart', '#F5B301'],
            ['checkout_started', 'Checkout', '#339af0'],
            ['purchase_completed', 'Purchases', '#2f9e44'],
        ];
        if (trendChart) trendChart.destroy();
        trendChart = new Chart($('activityTrend'), {
            type: 'line',
            data: {
                labels,
                datasets: defs.map(([k, label, color]) => ({
                    label, data: series[k] || [], borderColor: color, backgroundColor: color,
                    tension: 0.3, pointRadius: 2, borderWidth: 2,
                })),
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    }

    // Events
    document.querySelectorAll('#activityRanges .act-range-btn').forEach((b) => {
        b.addEventListener('click', () => {
            document.querySelectorAll('#activityRanges .act-range-btn').forEach((x) => x.classList.remove('active'));
            b.classList.add('active');
            state.range = b.dataset.range;
            const custom = state.range === 'custom';
            $('activityFromWrap').style.display = custom ? '' : 'none';
            $('activityToWrap').style.display = custom ? '' : 'none';
            if (!custom) load();
        });
    });
    ['activityProduct', 'activityCategory', 'activitySize', 'activityColor', 'activityFrom', 'activityTo'].forEach((id) => {
        $(id).addEventListener('change', load);
    });
    $('activityProductSearch').addEventListener('input', drawProductTable);
    $('activityVariantSearch').addEventListener('input', drawVariantTable);
    document.querySelectorAll('#activityProductTable th[data-s]').forEach((th) => {
        th.addEventListener('click', () => {
            const k = th.dataset.s;
            state.sortP = { k, d: state.sortP.k === k ? -state.sortP.d : -1 };
            drawProductTable();
        });
    });
    document.querySelectorAll('#activityVariantTable th[data-s]').forEach((th) => {
        th.addEventListener('click', () => {
            const k = th.dataset.s;
            state.sortV = { k, d: state.sortV.k === k ? -state.sortV.d : -1 };
            drawVariantTable();
        });
    });

    load();
})();
</script>

@endsection