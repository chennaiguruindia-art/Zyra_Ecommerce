@extends('layouts.seller')

@section('title', 'Manage Products | ZYRA Seller Hub')
@section('meta_description', 'View, search, edit, toggle stock and manage all clothing products in your seller store.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'products';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">My Products Catalog</h1>
        <p class="text-muted mb-0">Manage pricing, availability, and details for all your women's fashion items.</p>
    </div>
    <a href="{{ route('seller.products.add') }}" class="btn btn-zyra-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Product
    </a>
</div>

<!-- Filters & Search Toolbar -->
<div class="seller-card mb-4">
    <div class="seller-card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" id="sellerProductSearch" class="form-control" placeholder="Search by product title or SKU..." oninput="ZyraSeller.renderProductsPage()">
                </div>
            </div>
            <div class="col-md-4">
                <select id="sellerProductCategoryFilter" class="form-select form-select-sm" onchange="ZyraSeller.renderProductsPage()">
                    <option value="all" selected>All Categories</option>
                    <option value="Tops">Tops</option>
                    <option value="Leggings">Leggings</option>
                    <option value="Kurtis">Kurtis</option>
                    <option value="Maxi">Maxi Dresses</option>
                    <option value="Nightwear">Nightwear</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end">
                <span id="sellerProductsTotalCount" class="badge bg-dark px-3 py-2 fs-6">0 Products</span>
            </div>
        </div>
    </div>
</div>

<!-- Products Table Card -->
<div class="seller-card">
    <div class="seller-card-body p-0">
        <div class="table-responsive">
            <table class="table seller-table">
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Price</th>
                        <th>Stock Units</th>
                        <th>Status</th>
                        <th>Visibility</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="sellerProductsTableBody">
                    <!-- Populated dynamically by seller.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
