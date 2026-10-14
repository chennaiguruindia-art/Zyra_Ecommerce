@extends('layouts.app')

@section('title', 'Product Catalog | ZYRA Official Collection')
@section('meta_description', 'Browse the official ZYRA product catalog. Handcrafted kurtis, tops, maxi dresses, co-ords, and authentic fabrics.')

@push('styles')
<style>
/* ── Clean Catalog Styles ─────────────────────────────────────────── */
:root {
    --cat-burgundy: #7D1D32;
    --cat-burgundy-dark: #581020;
    --cat-bg: #FAF6F2;
    --cat-border: #EAE1D8;
    --cat-text: #1E1A18;
    --cat-muted: #6E6762;
}

.catalog-wrapper {
    background-color: var(--cat-bg);
    min-height: 100vh;
    padding-top: 1.25rem;
    padding-bottom: 3.5rem;
}

/* ── Compact Top Header ──────────────────────────────────────────── */
.catalog-top-header {
    background: #ffffff;
    border: 1px solid var(--cat-border);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.catalog-page-title {
    font-family: var(--zyra-font-heading, 'Playfair Display', Georgia, serif);
    font-size: 1.55rem;
    font-weight: 700;
    color: var(--cat-text);
    margin: 0;
}

.catalog-page-sub {
    font-size: 0.82rem;
    color: var(--cat-muted);
}

/* ── Filter Toolbar ──────────────────────────────────────────────── */
.catalog-toolbar {
    background-color: #ffffff;
    border: 1px solid var(--cat-border);
    border-radius: 14px;
    padding: 12px 18px;
    margin-bottom: 22px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.catalog-cat-pill {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--cat-text);
    background-color: #F6F1EC;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    white-space: nowrap;
}

.catalog-cat-pill:hover,
.catalog-cat-pill.active {
    background-color: var(--cat-burgundy);
    color: #ffffff;
    border-color: var(--cat-burgundy);
}

.catalog-search-box {
    position: relative;
    min-width: 220px;
}

.catalog-search-box input {
    width: 100%;
    border: 1px solid var(--cat-border);
    border-radius: 999px;
    padding: 7px 14px 7px 34px;
    font-size: 0.82rem;
    outline: none;
    background: #FAF7F4;
    transition: all 0.2s ease;
}

.catalog-search-box input:focus {
    background: #ffffff;
    border-color: var(--cat-burgundy);
}

.catalog-search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #8C847E;
    font-size: 0.85rem;
}

/* ── 4-In-A-Row Lookbook Cards (No price, No barcode) ────────────── */
.catalog-item-card {
    background: #ffffff;
    border: 1px solid var(--cat-border);
    border-radius: 14px;
    overflow: hidden;
    transition: all 0.25s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.catalog-item-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(125, 29, 50, 0.08);
    border-color: #D9CBC0;
}

/* Photo Thumbnail: 100% clean, no badges on model face */
.catalog-item-thumb {
    position: relative;
    padding-top: 126%;
    background-color: #F5EFE9;
    overflow: hidden;
}

.catalog-item-thumb img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    transition: transform 0.4s ease;
}

.catalog-item-card:hover .catalog-item-thumb img {
    transform: scale(1.04);
}

.catalog-item-body {
    padding: 12px 14px 14px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.catalog-item-category {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    color: var(--cat-burgundy);
    text-transform: uppercase;
    margin-bottom: 2px;
}

.catalog-item-title {
    font-family: var(--zyra-font-sans);
    font-size: 0.94rem;
    font-weight: 700;
    color: var(--cat-text);
    margin-bottom: 6px;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.catalog-item-spec-list {
    background: #FAF6F2;
    border-radius: 6px;
    padding: 7px 9px;
    margin: 4px 0 8px;
    font-size: 0.74rem;
    color: #554D47;
}

.catalog-item-spec-list div {
    margin-bottom: 3px;
    line-height: 1.35;
    word-break: break-word;
}

.catalog-item-spec-list div:last-child {
    margin-bottom: 0;
}

.catalog-spec-label {
    font-weight: 600;
    color: #1E1A18;
    display: inline-block;
    min-width: 48px;
}

.catalog-size-pill {
    display: inline-block;
    background: #ffffff;
    border: 1px solid #DDD4CB;
    color: #333;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 1px 5px;
    border-radius: 3px;
    margin-right: 2px;
}

.catalog-actions-row {
    display: flex;
    gap: 6px;
    margin-top: auto;
    padding-top: 8px;
}

.btn-catalog-order {
    flex: 1;
    background-color: var(--cat-burgundy);
    color: #ffffff;
    border: none;
    font-size: 0.76rem;
    font-weight: 600;
    padding: 7px 10px;
    border-radius: 6px;
    text-align: center;
    text-decoration: none;
    transition: background-color 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.btn-catalog-order:hover {
    background-color: var(--cat-burgundy-dark);
    color: #ffffff;
}

.btn-catalog-item-wa {
    background-color: #E8F8EE;
    color: #1E824C;
    border: 1px solid #B8E7C8;
    font-size: 0.88rem;
    padding: 7px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-catalog-item-wa:hover {
    background-color: #25D366;
    color: #ffffff;
    border-color: #25D366;
}

/* ── Wholesale Spec Sheet Table View ──────────────────────────────── */
.catalog-table-wrap {
    background: #ffffff;
    border: 1px solid var(--cat-border);
    border-radius: 14px;
    overflow-x: auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.catalog-spec-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
}

.catalog-spec-table th {
    background: #FAF4EE;
    color: var(--cat-text);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 12px 14px;
    border-bottom: 1px solid var(--cat-border);
    white-space: nowrap;
}

.catalog-spec-table td {
    padding: 12px 14px;
    vertical-align: middle;
    border-bottom: 1px solid #F2ECE5;
    font-size: 0.82rem;
}

.catalog-table-thumb {
    width: 52px;
    height: 66px;
    border-radius: 6px;
    object-fit: cover;
}

/* ── Clean Print & PDF Stylesheet (No Collapse, Exactly 4 in a row) ── */
@page {
    margin: 0;
    size: auto;
}

@media print {
    /* Hide all website navigation & chrome elements */
    .zyra-top-promo,
    .zyra-header,
    .zyra-footer,
    .zyra-bottom-nav,
    #zyraAiChat,
    .catalog-top-header,
    .catalog-toolbar,
    .catalog-actions-row,
    .btn,
    button {
        display: none !important;
    }

    html, body {
        background: #ffffff !important;
        color: #111111 !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .catalog-wrapper {
        background: #ffffff !important;
        padding: 8mm 10mm !important;
        min-height: auto !important;
    }

    /* Print Letterhead */
    .catalog-print-header {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #7D1D32;
        padding-bottom: 6px;
        margin-bottom: 12px;
    }

    /* 2 cards per row on paper / PDF so fabric and sizes are clearly visible */
    #catalogGridView {
        display: flex !important;
        flex-wrap: wrap !important;
        margin-left: -8px !important;
        margin-right: -8px !important;
    }

    .catalog-item-wrapper {
        flex: 0 0 50% !important;
        max-width: 50% !important;
        width: 50% !important;
        padding-left: 8px !important;
        padding-right: 8px !important;
        margin-bottom: 16px !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        box-sizing: border-box !important;
    }

    .catalog-item-card {
        border: 1px solid #d4cdc7 !important;
        box-shadow: none !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        background: #ffffff !important;
    }

    .catalog-item-thumb {
        position: relative !important;
        padding-top: 110% !important;
        height: auto !important;
        background: #f4f4f4 !important;
    }

    .catalog-item-thumb img {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
    }

    .catalog-item-body {
        padding: 12px 14px !important;
    }

    .catalog-item-category {
        font-size: 0.72rem !important;
        color: #7D1D32 !important;
        font-weight: 700 !important;
        letter-spacing: 0.6px !important;
        margin-bottom: 3px !important;
        display: block !important;
    }

    .catalog-item-title {
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        color: #1E1A18 !important;
        margin-bottom: 6px !important;
        white-space: normal !important;
        overflow: visible !important;
        line-height: 1.3 !important;
    }

    /* Specifications: fully visible, never hidden or truncated */
    .catalog-item-spec-list {
        font-size: 0.78rem !important;
        padding: 8px 10px !important;
        margin: 4px 0 0 !important;
        background: #FAF6F2 !important;
        border: 1px solid #EAE1D8 !important;
        border-radius: 6px !important;
    }

    .catalog-item-spec-list div {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        margin-bottom: 4px !important;
        line-height: 1.35 !important;
    }

    .catalog-spec-label {
        font-weight: 700 !important;
        color: #1E1A18 !important;
        display: inline-block !important;
        min-width: 50px !important;
    }

    .catalog-size-pill {
        display: inline-block !important;
        background: #ffffff !important;
        border: 1px solid #DDD4CB !important;
        color: #333333 !important;
        font-size: 0.72rem !important;
        font-weight: 600 !important;
        padding: 2px 6px !important;
        border-radius: 3px !important;
        margin-right: 3px !important;
    }
}
</style>
@endpush

@section('content')
<div class="catalog-wrapper">
    <div class="container px-3 px-md-4">

        <!-- Print Letterhead Header (Appears ONLY during print / PDF saving) -->
        <div class="catalog-print-header d-none">
            <div>
                <h3 class="fw-bold mb-0" style="color: #7D1D32; font-family: 'Playfair Display', Georgia, serif; letter-spacing: 0.5px;">
                    ZYRA LIFESTYLE
                </h3>
                <div class="small text-muted" style="font-size: 0.78rem;">Official Product Lookbook &bull; Season 2026</div>
            </div>
            <div class="text-end small">
                <div class="fw-bold text-dark">Website: zyralifestyle.in</div>
            </div>
        </div>

        <!-- 1. Clean Compact Top Header (Website View - No unwanted cover banner) -->
        <div class="catalog-top-header d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="catalog-page-title">Product Catalog</h1>
                <span class="catalog-page-sub">Browse our official collection and styles</span>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print / Save as PDF
                </button>
                <a href="https://api.whatsapp.com/send?text={{ urlencode('Browse our ZYRA Product Catalog: ' . url('/catelog')) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-whatsapp"></i> Share on WhatsApp
                </a>
            </div>
        </div>

        <!-- 2. Catalog Filter Toolbar -->
        <div class="catalog-toolbar">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <!-- Category Filter Pills -->
                <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 pb-md-0" style="scrollbar-width: none;">
                    <a href="{{ url('/catelog') }}" class="catalog-cat-pill {{ empty($selectedCategory) ? 'active' : '' }}">
                        All Designs
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ url('/catelog') }}?category={{ $cat['slug'] }}" class="catalog-cat-pill {{ ($selectedCategory === $cat['slug']) ? 'active' : '' }}">
                            {{ $cat['name'] }}
                        </a>
                    @endforeach
                </div>

                <!-- Live Filter Input & View Switcher -->
                <div class="d-flex align-items-center gap-2">
                    <div class="catalog-search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="catalogSearchInput" placeholder="Filter by name or fabric..." onkeyup="filterCatalogItems()">
                    </div>

                    <div class="btn-group" role="group" aria-label="Catalog View Mode">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" id="btnViewGrid" onclick="switchCatalogView('grid')" title="Lookbook Cards">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnViewTable" onclick="switchCatalogView('table')" title="Table View">
                            <i class="bi bi-table"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Grid View: Exactly 4 Cards In A Row (Clean Photo, NO Price, NO Barcode) -->
        <div id="catalogGridView" class="row g-3 g-md-4">
            @forelse($products as $product)
                @php
                    $mat = $product['material'] ?? 'Pure Breathable Cotton';
                    $sizes = is_array($product['sizes'] ?? null) ? $product['sizes'] : ['S', 'M', 'L', 'XL'];
                    $waMsg = 'Hello ZYRA, I am interested in ordering: ' . ($product['name'] ?? 'Product') . ' from your online catalog.';
                    $waLink = 'https://api.whatsapp.com/send?text=' . urlencode($waMsg);
                    $productDetailUrl = !empty($product['id']) ? route('product.details', $product['id']) : route('shop');
                @endphp
                <!-- 4 cards in a row on desktop (col-lg-3), 3 on tablets (col-md-4), 2 on mobile (col-6) -->
                <div class="col-6 col-md-4 col-lg-3 catalog-item-wrapper"
                     data-name="{{ strtolower($product['name'] ?? '') }}"
                     data-material="{{ strtolower($mat) }}"
                     data-category="{{ strtolower($product['category'] ?? '') }}">
                    <div class="catalog-item-card">
                        <!-- Clean Photo: 100% visible, no barcode, no badge on model face -->
                        <div class="catalog-item-thumb">
                            <img src="{{ $product['image'] ?? asset('images/categories/tops.jpg') }}" alt="{{ $product['name'] }}" loading="lazy">
                        </div>

                        <div class="catalog-item-body">
                            <!-- Category label -->
                            <span class="catalog-item-category">{{ $product['category'] ?? 'Apparel' }}</span>

                            <h3 class="catalog-item-title" title="{{ $product['name'] }}">{{ $product['name'] }}</h3>

                            <!-- Specifications -->
                            <div class="catalog-item-spec-list">
                                <div><span class="catalog-spec-label">Fabric:</span> {{ $mat }}</div>
                                @if(!empty($product['fit']))
                                    <div><span class="catalog-spec-label">Fit:</span> {{ $product['fit'] }}</div>
                                @endif
                                <div>
                                    <span class="catalog-spec-label">Sizes:</span>
                                    @foreach($sizes as $sz)
                                        <span class="catalog-size-pill">{{ $sz }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Actions (NO price, NO barcode) -->
                            <div class="catalog-actions-row">
                                <a href="{{ $productDetailUrl }}" class="btn-catalog-order">
                                    <i class="bi bi-eye"></i> View &amp; Order
                                </a>
                                <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="btn-catalog-item-wa" title="Inquire on WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-journal-x fs-1 text-muted"></i>
                    <h4 class="mt-3 fw-bold">No designs found in this category</h4>
                    <p class="text-muted">Browse all designs to see our complete catalogue.</p>
                    <a href="{{ url('/catelog') }}" class="btn btn-dark mt-2">View All Designs</a>
                </div>
            @endforelse
        </div>

        <!-- 4. Table View (Clean Table, NO Price, NO Barcode) -->
        <div id="catalogTableView" class="catalog-table-wrap d-none">
            <table class="catalog-spec-table">
                <thead>
                    <tr>
                        <th>Design</th>
                        <th>Product Title</th>
                        <th>Category</th>
                        <th>Material &amp; Weave</th>
                        <th>Available Sizes</th>
                        <th>Order</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        @php
                            $mat = $product['material'] ?? 'Pure Breathable Cotton';
                            $sizes = is_array($product['sizes'] ?? null) ? $product['sizes'] : ['S', 'M', 'L', 'XL'];
                            $productDetailUrl = !empty($product['id']) ? route('product.details', $product['id']) : route('shop');
                            $waMsg = 'Hello ZYRA, I am interested in enquiry for ' . ($product['name'] ?? 'Product') . '.';
                            $waLink = 'https://api.whatsapp.com/send?text=' . urlencode($waMsg);
                        @endphp
                        <tr class="catalog-table-row"
                            data-name="{{ strtolower($product['name'] ?? '') }}"
                            data-material="{{ strtolower($mat) }}"
                            data-category="{{ strtolower($product['category'] ?? '') }}">
                            <td>
                                <img src="{{ $product['image'] ?? asset('images/categories/tops.jpg') }}" alt="{{ $product['name'] }}" class="catalog-table-thumb" loading="lazy">
                            </td>
                            <td class="fw-bold">{{ $product['name'] }}</td>
                            <td><span class="text-muted">{{ $product['category'] ?? 'Apparel' }}</span></td>
                            <td><span class="small">{{ $mat }}</span></td>
                            <td>
                                @foreach($sizes as $sz)
                                    <span class="catalog-size-pill">{{ $sz }}</span>
                                @endforeach
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ $productDetailUrl }}" class="btn btn-sm btn-dark px-2 py-1" title="Order Online">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success px-2 py-1" title="WhatsApp Enquiry">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

@push('scripts')
<script>
function filterCatalogItems() {
    const query = document.getElementById('catalogSearchInput').value.toLowerCase().trim();
    const gridItems = document.querySelectorAll('.catalog-item-wrapper');
    const tableRows = document.querySelectorAll('.catalog-table-row');

    gridItems.forEach(item => {
        const name = item.getAttribute('data-name') || '';
        const mat = item.getAttribute('data-material') || '';
        const cat = item.getAttribute('data-category') || '';

        const match = !query || name.includes(query) || mat.includes(query) || cat.includes(query);
        item.style.display = match ? '' : 'none';
    });

    tableRows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const mat = row.getAttribute('data-material') || '';
        const cat = row.getAttribute('data-category') || '';

        const match = !query || name.includes(query) || mat.includes(query) || cat.includes(query);
        row.style.display = match ? '' : 'none';
    });
}

function switchCatalogView(mode) {
    const grid = document.getElementById('catalogGridView');
    const table = document.getElementById('catalogTableView');
    const btnGrid = document.getElementById('btnViewGrid');
    const btnTable = document.getElementById('btnViewTable');

    if (mode === 'table') {
        grid.classList.add('d-none');
        table.classList.remove('d-none');
        btnTable.classList.add('active');
        btnGrid.classList.remove('active');
    } else {
        table.classList.add('d-none');
        grid.classList.remove('d-none');
        btnGrid.classList.add('active');
        btnTable.classList.remove('active');
    }
}
</script>
@endpush
@endsection
