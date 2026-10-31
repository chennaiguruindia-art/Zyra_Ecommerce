@extends('layouts.seller')

@section('title', 'Stock Management & Inward Reports | ZYRA Seller Hub')
@section('meta_description', 'Track received stock, inward amounts by day, week and month, and export stock valuation.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'stock-management';
</script>
@endpush

@section('content')

@php
    $currentRange = request('range', 'all');
@endphp

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Stock Management</h1>
        <p class="text-muted mb-0">Monitor received stock batches, calculate incoming inventory value, and analyze inward totals.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-zyra-primary" onclick="openInwardModal()">
            <i class="bi bi-box-arrow-in-down me-1"></i> Inward New Stock
        </button>
        <a href="{{ route('seller.stock.export.excel', ['range' => $currentRange, 'search' => request('search')]) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Download Excel
        </a>
        <a href="{{ route('seller.stock.export.pdf', ['range' => $currentRange, 'search' => request('search')]) }}" target="_blank" class="btn btn-outline-dark">
            <i class="bi bi-printer me-1"></i> Download / Print PDF
        </a>
    </div>
</div>

<!-- Stock Inward KPI Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Today -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="seller-stat-card border-start border-primary border-3">
            <div class="seller-stat-icon bg-primary-soft text-primary"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="seller-stat-value fs-4 mb-0">₹{{ number_format($metrics['today']['amount'], 2) }}</div>
                <div class="text-muted small fw-semibold">{{ $metrics['today']['units'] }} unit(s) received</div>
                <div class="seller-stat-label text-uppercase mt-1" style="font-size: 0.72rem;">Received Today</div>
            </div>
        </div>
    </div>

    <!-- This Week -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="seller-stat-card border-start border-info border-3">
            <div class="seller-stat-icon bg-info-soft text-info"><i class="bi bi-calendar3"></i></div>
            <div>
                <div class="seller-stat-value fs-4 mb-0">₹{{ number_format($metrics['week']['amount'], 2) }}</div>
                <div class="text-muted small fw-semibold">{{ $metrics['week']['units'] }} unit(s) received</div>
                <div class="seller-stat-label text-uppercase mt-1" style="font-size: 0.72rem;">Received This Week</div>
            </div>
        </div>
    </div>

    <!-- This Month -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="seller-stat-card border-start border-success border-3">
            <div class="seller-stat-icon bg-success-soft text-success"><i class="bi bi-calendar-month"></i></div>
            <div>
                <div class="seller-stat-value fs-4 mb-0">₹{{ number_format($metrics['month']['amount'], 2) }}</div>
                <div class="text-muted small fw-semibold">{{ $metrics['month']['units'] }} unit(s) received</div>
                <div class="seller-stat-label text-uppercase mt-1" style="font-size: 0.72rem;">Received This Month</div>
            </div>
        </div>
    </div>

    <!-- Total Catalog Valuation -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="seller-stat-card border-start border-warning border-3">
            <div class="seller-stat-icon bg-gold-soft text-gold"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="seller-stat-value fs-4 mb-0">₹{{ number_format($metrics['inventory']['total_valuation'], 2) }}</div>
                <div class="text-muted small fw-semibold">{{ number_format($metrics['inventory']['total_units']) }} total units in stock</div>
                <div class="seller-stat-label text-uppercase mt-1" style="font-size: 0.72rem;">Total Stock Valuation</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs & Filter Navigation -->
<div class="seller-card mb-4">
    <div class="seller-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <ul class="nav nav-pills" id="stockTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="inward-tab" data-bs-toggle="pill" data-bs-target="#inward-pane" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i> Stock Inward Log ({{ $receipts->total() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="catalog-tab" data-bs-toggle="pill" data-bs-target="#catalog-pane" type="button" role="tab">
                    <i class="bi bi-boxes me-1"></i> Current Stock Levels &amp; Valuation
                </button>
            </li>
        </ul>

        <!-- Range Quick Filters & Search -->
        <form method="GET" action="{{ route('seller.stock') }}" class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('seller.stock', ['range' => 'all', 'search' => request('search')]) }}" class="btn {{ $currentRange === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
                <a href="{{ route('seller.stock', ['range' => 'today', 'search' => request('search')]) }}" class="btn {{ $currentRange === 'today' ? 'btn-dark' : 'btn-outline-secondary' }}">Today</a>
                <a href="{{ route('seller.stock', ['range' => 'week', 'search' => request('search')]) }}" class="btn {{ $currentRange === 'week' ? 'btn-dark' : 'btn-outline-secondary' }}">This Week</a>
                <a href="{{ route('seller.stock', ['range' => 'month', 'search' => request('search')]) }}" class="btn {{ $currentRange === 'month' ? 'btn-dark' : 'btn-outline-secondary' }}">This Month</a>
            </div>
            <input type="hidden" name="range" value="{{ $currentRange }}">
            <div class="input-group input-group-sm" style="max-width: 220px;">
                <input type="text" name="search" class="form-control" placeholder="Search product / SKU" value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
            </div>
            @if(request('search'))
                <a href="{{ route('seller.stock', ['range' => $currentRange]) }}" class="btn btn-sm btn-outline-danger" title="Clear search"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>
    </div>

    <div class="tab-content" id="stockTabsContent">
        <!-- Tab 1: Stock Inward Logs -->
        <div class="tab-pane fade show active" id="inward-pane" role="tabpanel">
            <div class="table-responsive">
                <table class="table seller-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Receipt ID &amp; Date</th>
                            <th>Product</th>
                            <th>Sizes Breakdown</th>
                            <th class="text-center">Units Added</th>
                            <th>Unit Cost</th>
                            <th>Total Inward Value</th>
                            <th>Source / Ref</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td>
                                    <div class="fw-bold small text-dark">#SR-{{ str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    <small class="text-muted">{{ $receipt->created_at->format('d M Y, h:i A') }}</small>
                                </td>
                                <td>
                                    @if($receipt->product)
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $receipt->product->image_url }}" alt="{{ $receipt->product->name }}" class="seller-product-img" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px;">
                                            <div>
                                                <div class="fw-semibold small text-dark">{{ $receipt->product->name }}</div>
                                                <small class="text-muted">SKU: {{ $receipt->product->sku ?: '-' }} &bull; {{ $receipt->product->category?->name ?? 'Category' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">Product deleted</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($receipt->sizes_breakdown) && is_array($receipt->sizes_breakdown))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($receipt->sizes_breakdown as $sz => $q)
                                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.75rem;">
                                                    <strong>{{ $sz }}</strong>: +{{ $q }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Bulk Total (+{{ $receipt->quantity }})</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success fs-6 px-2 py-1">+{{ $receipt->quantity }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold small">₹{{ number_format($receipt->unit_cost, 2) }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">₹{{ number_format($receipt->total_cost, 2) }}</div>
                                    <small class="text-muted">{{ $receipt->quantity }} × ₹{{ number_format($receipt->unit_cost, 2) }}</small>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $receipt->reference ?: '-' }}</div>
                                    <span class="badge bg-light text-muted border text-capitalize" style="font-size: 0.7rem;">
                                        {{ str_replace('_', ' ', $receipt->source) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    <h6>No stock inward receipts found for this range</h6>
                                    <p class="small mb-3">Click "+ Inward New Stock" to receive your first inventory batch.</p>
                                    <button type="button" class="btn btn-sm btn-dark" onclick="openInwardModal()">
                                        <i class="bi bi-plus-lg me-1"></i> Inward New Stock
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($receipts->hasPages())
                <div class="p-3 border-top">
                    {{ $receipts->links() }}
                </div>
            @endif
        </div>

        <!-- Tab 2: Catalog Stock & Valuation Overview -->
        <div class="tab-pane fade" id="catalog-pane" role="tabpanel">
            <div class="table-responsive">
                <table class="table seller-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Sizes &amp; Per-Size Stock</th>
                            <th class="text-center">Total In Stock</th>
                            <th>Selling Price</th>
                            <th>Total Holding Value</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $prod)
                            @php
                                $isLow = $prod->stock_units < 10 && $prod->stock_units > 0;
                                $isOut = $prod->stock_units <= 0;
                                $holdingValue = $prod->stock_units * $prod->price;
                            @endphp
                            <tr class="{{ $isOut ? 'table-danger-subtle' : ($isLow ? 'table-warning-subtle' : '') }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" class="seller-product-img" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px;">
                                        <div>
                                            <div class="fw-semibold small text-dark">{{ $prod->name }}</div>
                                            <small class="text-muted">SKU: {{ $prod->sku ?: '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $prod->category?->name ?? '-' }}</span></td>
                                <td>
                                    @if($prod->sizes->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($prod->sizes as $sz)
                                                @php
                                                    $szStock = $sz->pivot->stock !== null ? $sz->pivot->stock : $prod->stock_units;
                                                @endphp
                                                <span class="badge {{ $szStock <= 0 ? 'bg-danger-subtle text-danger' : ($szStock < 5 ? 'bg-warning-subtle text-dark' : 'bg-secondary-subtle text-dark') }} border px-2 py-1" style="font-size: 0.75rem;">
                                                    {{ $sz->name }}: <strong>{{ $szStock }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">Standard (No size variants)</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <strong class="fs-6 {{ $isOut ? 'text-danger' : ($isLow ? 'text-warning' : 'text-dark') }}">{{ $prod->stock_units }}</strong>
                                </td>
                                <td>
                                    <span class="small fw-semibold">₹{{ number_format($prod->price, 2) }}</span>
                                </td>
                                <td>
                                    <strong class="text-dark">₹{{ number_format($holdingValue, 2) }}</strong>
                                </td>
                                <td>
                                    @if($isOut)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($isLow)
                                        <span class="badge bg-warning text-dark">Low Stock ({{ $prod->stock_units }})</span>
                                    @else
                                        <span class="badge bg-success">Healthy Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-dark" onclick="openInwardModal({{ $prod->id }})">
                                        <i class="bi bi-box-arrow-in-down me-1"></i> Inward
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- INWARD NEW STOCK MODAL (Per-Size Calculation & Amount Tracking) -->
<!-- ========================================================================= -->
<div class="modal fade" id="inwardStockModal" tabindex="-1" aria-labelledby="inwardStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-box-arrow-in-down fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="inwardStockModalLabel">Inward / Receive Stock</h5>
                        <small class="text-muted">Enter incoming units per size. New totals and value calculate automatically.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-3">
                <form id="inwardStockForm" onsubmit="event.preventDefault(); submitInwardStock();">
                    <!-- Product Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark" for="inwardProductSelect">Select Product <span class="text-danger">*</span></label>
                        <select id="inwardProductSelect" class="form-select" onchange="onInwardProductChange(this.value)" required>
                            <option value="">-- Choose product to inward stock --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-price="{{ (float)$p->price }}" data-sku="{{ $p->sku }}" data-stock="{{ $p->stock_units }}" data-sizes="{{ json_encode($p->sizes->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'stock' => $s->pivot->stock !== null ? (int)$s->pivot->stock : (int)$p->stock_units])) }}">
                                    {{ $p->name }} (SKU: {{ $p->sku ?: '-' }}) — Current Stock: {{ $p->stock_units }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selected Product Card Preview -->
                    <div id="inwardProductPreview" class="p-3 bg-light rounded-3 mb-3 d-none border">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-dark" id="prevProdName"></span>
                                <div class="text-muted small">
                                    SKU: <span id="prevProdSku">-</span> &bull; Current Total Units: <strong id="prevProdUnits" class="text-dark">0</strong>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small">Listed Price:</span>
                                <div class="fw-bold text-dark" id="prevProdPrice">₹0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Per-Size Stock Entry Table -->
                    <div id="inwardSizesContainer" class="mb-3 d-none">
                        <label class="form-label small fw-bold text-dark mb-2">Sizes &amp; Incoming Quantities</label>
                        <div class="table-responsive border rounded-3 bg-white p-2">
                            <div class="row fw-semibold text-muted text-uppercase border-bottom pb-2 mb-2 px-2" style="font-size: .72rem; letter-spacing: .04em;">
                                <div class="col-3">Size</div>
                                <div class="col-3 text-center">Current Stock</div>
                                <div class="col-3 text-center">Add Qty</div>
                                <div class="col-3 text-center">Calculated New Stock</div>
                            </div>
                            <div id="inwardSizesRows" class="px-2">
                                <!-- Populated dynamically by JS -->
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top px-2">
                                <span class="small text-muted align-self-center me-1">Quick Add:</span>
                                <button type="button" class="btn btn-xs btn-outline-dark" onclick="applyInwardAll(5)">+5 All</button>
                                <button type="button" class="btn btn-xs btn-outline-dark" onclick="applyInwardAll(10)">+10 All</button>
                                <button type="button" class="btn btn-xs btn-outline-dark" onclick="applyInwardAll(20)">+20 All</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="applyInwardAll(0)">Clear</button>
                            </div>
                        </div>
                    </div>

                    <!-- Standard (No sizes) Fallback -->
                    <div id="inwardNoSizesContainer" class="mb-3 d-none">
                        <div class="row g-3 align-items-center">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark" for="inwardDirectUnits">Units to Inward <span class="text-danger">*</span></label>
                                <input type="number" min="1" step="1" id="inwardDirectUnits" class="form-control" value="10" oninput="calculateInwardTotals()">
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block mt-3">This product has no size variations. The entered quantity will be added directly to the total stock.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Unit Cost & References -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark" for="inwardUnitCost">
                                Unit Cost / Price (₹) <span class="text-muted fw-normal">(Defaults to Listed Price)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">₹</span>
                                <input type="number" step="0.01" min="0" id="inwardUnitCost" class="form-control" placeholder="0.00" oninput="calculateInwardTotals()">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark" for="inwardReference">
                                Reference / Batch No. <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <input type="text" id="inwardReference" class="form-control" placeholder="e.g. Batch #2026-05, PO-102">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark" for="inwardNote">
                                Inward Notes <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <input type="text" id="inwardNote" class="form-control" placeholder="e.g. Received from Jaipur manufacturing unit in pristine condition">
                        </div>
                    </div>

                    <!-- Live Calculation Summary Card -->
                    <div class="p-3 bg-dark text-white rounded-3 mb-3">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-white-50 small text-uppercase" style="letter-spacing: .05em;">Total Units To Inward</span>
                                <div class="fs-4 fw-bold" id="calcInwardTotalUnits">0 units</div>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-white-50 small text-uppercase" style="letter-spacing: .05em;">Total Inward Amount</span>
                                <div class="fs-4 fw-bold text-warning" id="calcInwardTotalAmount">₹0.00</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark px-4" id="inwardSubmitBtn" onclick="submitInwardStock()">
                    <i class="bi bi-check2-circle me-1"></i> Confirm &amp; Save Inward Stock
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentInwardProduct = null;

    function openInwardModal(productId = null) {
        const select = document.getElementById('inwardProductSelect');
        if (productId) {
            select.value = productId;
            onInwardProductChange(productId);
        } else {
            if (!select.value && select.options.length > 1) {
                select.selectedIndex = 1;
                onInwardProductChange(select.value);
            }
        }
        const modalEl = document.getElementById('inwardStockModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function onInwardProductChange(productId) {
        const select = document.getElementById('inwardProductSelect');
        const opt = select.selectedOptions[0];
        if (!opt || !opt.value) {
            document.getElementById('inwardProductPreview').classList.add('d-none');
            document.getElementById('inwardSizesContainer').classList.add('d-none');
            document.getElementById('inwardNoSizesContainer').classList.add('d-none');
            currentInwardProduct = null;
            calculateInwardTotals();
            return;
        }

        const price = parseFloat(opt.getAttribute('data-price')) || 0;
        const sku = opt.getAttribute('data-sku') || '-';
        const stock = parseInt(opt.getAttribute('data-stock')) || 0;
        const rawSizes = opt.getAttribute('data-sizes');
        let sizes = [];
        try {
            sizes = JSON.parse(rawSizes) || [];
        } catch(e) {
            sizes = [];
        }

        currentInwardProduct = {
            id: opt.value,
            name: opt.text.split(' (SKU:')[0],
            sku: sku,
            price: price,
            stock: stock,
            sizes: sizes
        };

        // Preview
        document.getElementById('prevProdName').textContent = currentInwardProduct.name;
        document.getElementById('prevProdSku').textContent = currentInwardProduct.sku;
        document.getElementById('prevProdUnits').textContent = currentInwardProduct.stock;
        document.getElementById('prevProdPrice').textContent = '₹' + currentInwardProduct.price.toFixed(2);
        document.getElementById('inwardProductPreview').classList.remove('d-none');

        // Unit Cost default
        document.getElementById('inwardUnitCost').value = currentInwardProduct.price.toFixed(2);

        // Size rows
        const sizesBox = document.getElementById('inwardSizesContainer');
        const noSizesBox = document.getElementById('inwardNoSizesContainer');
        const rowsEl = document.getElementById('inwardSizesRows');

        if (sizes && sizes.length > 0) {
            sizesBox.classList.remove('d-none');
            noSizesBox.classList.add('d-none');

            let html = '';
            sizes.forEach(sz => {
                const cur = sz.stock || 0;
                html += `
                    <div class="row g-2 align-items-center border-bottom py-2 inward-size-row" data-size="${sz.name}" data-current="${cur}">
                        <div class="col-3">
                            <span class="badge bg-dark-subtle text-dark border px-2 py-1" style="font-size: .85rem;">${sz.name}</span>
                        </div>
                        <div class="col-3 text-center small fw-semibold text-muted">${cur}</div>
                        <div class="col-3">
                            <div class="input-group input-group-sm mx-auto" style="max-width: 120px;">
                                <button type="button" class="btn btn-outline-secondary" onclick="stepInwardQty(this, -1)">-</button>
                                <input type="number" min="0" step="1" class="form-control text-center inward-qty-input" value="0" oninput="calculateInwardTotals()">
                                <button type="button" class="btn btn-outline-secondary" onclick="stepInwardQty(this, 1)">+</button>
                            </div>
                        </div>
                        <div class="col-3 text-center">
                            <span class="fw-bold inward-new-stock text-success">${cur}</span>
                        </div>
                    </div>
                `;
            });
            rowsEl.innerHTML = html;
        } else {
            sizesBox.classList.add('d-none');
            noSizesBox.classList.remove('d-none');
        }

        calculateInwardTotals();
    }

    function stepInwardQty(btn, delta) {
        const input = btn.parentElement.querySelector('input');
        if (!input) return;
        const cur = Math.max(0, (parseInt(input.value) || 0) + delta);
        input.value = cur;
        calculateInwardTotals();
    }

    function applyInwardAll(qty) {
        document.querySelectorAll('#inwardSizesRows .inward-qty-input').forEach(inp => {
            inp.value = Math.max(0, qty);
        });
        calculateInwardTotals();
    }

    function calculateInwardTotals() {
        let totalUnits = 0;
        const hasSizes = currentInwardProduct && currentInwardProduct.sizes && currentInwardProduct.sizes.length > 0;

        if (hasSizes) {
            document.querySelectorAll('#inwardSizesRows .inward-size-row').forEach(row => {
                const cur = parseInt(row.getAttribute('data-current')) || 0;
                const add = Math.max(0, parseInt(row.querySelector('.inward-qty-input')?.value) || 0);
                totalUnits += add;

                const newEl = row.querySelector('.inward-new-stock');
                if (newEl) {
                    newEl.textContent = cur + add;
                    newEl.classList.toggle('text-success', add > 0);
                    newEl.classList.toggle('text-muted', add === 0);
                }
            });
        } else if (currentInwardProduct) {
            totalUnits = Math.max(0, parseInt(document.getElementById('inwardDirectUnits')?.value) || 0);
        }

        const unitCost = Math.max(0, parseFloat(document.getElementById('inwardUnitCost')?.value) || 0);
        const totalAmount = totalUnits * unitCost;

        document.getElementById('calcInwardTotalUnits').textContent = totalUnits + ' unit(s)';
        document.getElementById('calcInwardTotalAmount').textContent = '₹' + totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function submitInwardStock() {
        if (!currentInwardProduct) {
            ZyraApp.showToast('Please select a product first.', 'warning');
            return;
        }

        const hasSizes = currentInwardProduct.sizes && currentInwardProduct.sizes.length > 0;
        let quantities = {};
        let totalUnits = 0;

        if (hasSizes) {
            document.querySelectorAll('#inwardSizesRows .inward-size-row').forEach(row => {
                const sz = row.getAttribute('data-size');
                const add = Math.max(0, parseInt(row.querySelector('.inward-qty-input')?.value) || 0);
                if (add > 0) {
                    quantities[sz] = add;
                    totalUnits += add;
                }
            });

            if (totalUnits <= 0) {
                ZyraApp.showToast('Please enter an inward quantity for at least one size.', 'warning');
                return;
            }
        } else {
            totalUnits = Math.max(0, parseInt(document.getElementById('inwardDirectUnits')?.value) || 0);
            if (totalUnits <= 0) {
                ZyraApp.showToast('Please enter units to inward.', 'warning');
                return;
            }
        }

        const unitCost = Math.max(0, parseFloat(document.getElementById('inwardUnitCost')?.value) || currentInwardProduct.price);
        const reference = document.getElementById('inwardReference')?.value || '';
        const note = document.getElementById('inwardNote')?.value || '';

        const payload = {
            product_id: currentInwardProduct.id,
            quantities: hasSizes ? quantities : null,
            stock_units: !hasSizes ? totalUnits : null,
            unit_cost: unitCost,
            reference: reference,
            note: note,
        };

        const btn = document.getElementById('inwardSubmitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving Inward…';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch('{{ route("seller.stock.inward") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json().then(data => ({ status: res.status, data })))
        .then(({ status, data }) => {
            if (status >= 400) {
                ZyraApp.showToast(data.message || 'Failed to save inward stock.', 'danger');
                return;
            }

            ZyraApp.showToast(data.message || 'Stock successfully received!', 'success');

            // Close modal
            const modalEl = document.getElementById('inwardStockModal');
            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }

            // Reload page to refresh all metrics and transaction logs
            setTimeout(() => {
                window.location.reload();
            }, 800);
        })
        .catch(() => {
            ZyraApp.showToast('Server communication error. Please try again.', 'danger');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Confirm &amp; Save Inward Stock';
            }
        });
    }
</script>
@endpush

@endsection
