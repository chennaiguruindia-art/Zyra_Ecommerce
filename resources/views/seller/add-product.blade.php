@extends('layouts.seller')

@section('title', 'Add New Product | ZYRA Seller Hub')
@section('meta_description', 'Add a new fashion item to your ZYRA catalog.')

@push('scripts')
<script>
    window.ZyraSeller.page = 'add-product';
</script>
@endpush

@section('content')

<div class="seller-page-header">
    <div>
        <h1 class="h3 fw-bold mb-1">Add New Product</h1>
        <p class="text-muted mb-0">List a new women's fashion design in your storefront catalog.</p>
    </div>
    <a href="{{ route('seller.products') }}" class="btn btn-outline-dark">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<form id="sellerAddProductForm" novalidate>
    <div class="row g-4">
        
        <!-- Left: Product Details Inputs -->
        <div class="col-lg-8">
            <div class="seller-card mb-4">
                <div class="seller-card-header">
                    <h5 class="mb-0 fw-bold">General Information</h5>
                </div>
                <div class="seller-card-body">
                    <div class="mb-3">
                        <label for="productNameInput" class="seller-form-label">Product Title *</label>
                        <input type="text" id="productNameInput" class="form-control seller-form-control" placeholder="e.g. Handcrafted Indigo Block Print Kurti" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="productCategoryInput" class="seller-form-label">Category *</label>
                            <select id="productCategoryInput" class="form-select seller-form-control" required>
                                <option value="" selected disabled>Select Category</option>
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="productSubcategoryInput" class="seller-form-label">Subcategory / Silhouette</label>
                            <input type="text" id="productSubcategoryInput" class="form-control seller-form-control" placeholder="e.g. Anarkali, Crop Top, Ankle Length">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="productDescriptionInput" class="seller-form-label">Product Description</label>
                        <textarea id="productDescriptionInput" rows="4" class="form-control seller-form-control" placeholder="Describe the design cut, drape, embroidery, styling recommendations, etc."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="productMaterialInput" class="seller-form-label">Fabric & Material</label>
                        <input type="text" id="productMaterialInput" class="form-control seller-form-control" placeholder="e.g. 100% Breathable Organic Cotton">
                    </div>
                </div>
            </div>

            <!-- Pricing & Inventory -->
            <div class="seller-card mb-4">
                <div class="seller-card-header">
                    <h5 class="mb-0 fw-bold">Pricing & Stock</h5>
                </div>
                <div class="seller-card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="productPriceInput" class="seller-form-label">Selling Price (₹) *</label>
                            <input type="number" id="productPriceInput" class="form-control seller-form-control" placeholder="999" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="productOldPriceInput" class="seller-form-label">Original / MRP (₹)</label>
                            <input type="number" id="productOldPriceInput" class="form-control seller-form-control" placeholder="1499">
                        </div>
                        <div class="col-md-4">
                            <label for="productStockInput" class="seller-form-label">Stock Quantity (Units) *</label>
                            <input type="number" id="productStockInput" class="form-control seller-form-control" placeholder="25" min="0" required value="20">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="productSkuInput" class="seller-form-label">SKU (Stock Keeping Unit)</label>
                        <input type="text" id="productSkuInput" class="form-control seller-form-control" placeholder="e.g. ZYR-KUR-099">
                    </div>
                </div>
            </div>

            <!-- Variants: Sizes & Colors -->
            <div class="seller-card">
                <div class="seller-card-header">
                    <h5 class="mb-0 fw-bold">Sizes & Colors</h5>
                </div>
                <div class="seller-card-body">
                    <!-- Sizes -->
                    <div class="mb-4">
                        <label class="seller-form-label d-block mb-2">Available Sizes</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $sz)
                                <label class="d-flex align-items-center gap-2 border px-3 py-2 rounded bg-light cursor-pointer">
                                    <input type="checkbox" class="size-checkbox" value="{{ $sz }}" {{ in_array($sz, ['S', 'M', 'L']) ? 'checked' : '' }}>
                                    <span class="fw-semibold">{{ $sz }}</span>
                                    <input type="number" class="form-control form-control-sm size-stock-input" style="width: 70px;" min="0" value="2" title="Stock for {{ $sz }}">
                                </label>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mt-2">Set the stock available for each size. Total stock is calculated automatically.</small>
                    </div>

                    <!-- Dynamic Colors with + Icon -->
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="seller-form-label mb-0">Available Colors *</label>
                            <small class="text-muted">Click chip to toggle selection</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center" id="sellerColorsContainer">
                            <!-- Rendered dynamically by ZyraSeller.renderColorsList() -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Image Media (Max 4 Images) & Submission -->
        <div class="col-lg-4">
            <div class="seller-card mb-4 position-sticky" style="top: 90px;">
                <div class="seller-card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold">Product Media</h5>
                    <span class="badge bg-dark text-white" id="imagesCountDisplay">0 / 4 uploaded</span>
                </div>
                <div class="seller-card-body">
                    <!-- Dropzone / File Picker (Max 4) -->
                    <div class="seller-upload-dropzone mb-3" onclick="document.getElementById('productFileInput').click()">
                        <i class="bi bi-cloud-arrow-up display-6 text-muted mb-2 d-block"></i>
                        <div class="fw-semibold text-dark">Upload Images (Max 4)</div>
                        <small class="text-muted d-block mt-1">Click to browse device files (PNG, JPG, WEBP)</small>
                        <span class="badge bg-light text-dark border mt-2"><i class="bi bi-star-fill text-gold me-1"></i> 1st image is Cover</span>
                        <input type="file" id="productFileInput" accept="image/*" multiple class="d-none">
                    </div>

                    <!-- Add via Image URL -->
                    <div class="mb-3">
                        <label for="productImageUrlInput" class="seller-form-label">Or Add Image by URL</label>
                        <div class="input-group">
                            <input type="url" id="productImageUrlInput" class="form-control seller-form-control" placeholder="https://images.unsplash.com/...">
                            <button class="btn btn-outline-dark" type="button" id="addImageUrlBtn">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">Paste online image URL and click Add</small>
                    </div>

                    <!-- 4 Slots Image Grid -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="seller-form-label mb-0">Gallery Slots (Max 4)</label>
                            <small class="text-muted">Delete / replace anytime</small>
                        </div>
                        <div class="seller-images-grid" id="sellerImagesGrid">
                            <!-- 4 slots rendered by ZyraSeller.renderImagesGrid() -->
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-zyra-primary py-2">
                            <i class="bi bi-cloud-upload me-1"></i> Publish Product
                        </button>
                        <a href="{{ route('seller.products') }}" class="btn btn-outline-secondary btn-sm">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

<!-- Modal: Add New Color (+) -->
<div class="modal fade" id="addColorModal" tabindex="-1" aria-labelledby="addColorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="addColorModalLabel">
                    <i class="bi bi-palette-fill text-gold me-2"></i>Add New Color
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="newColorName" class="seller-form-label">Color Name *</label>
                    <input type="text" id="newColorName" class="form-control seller-form-control" placeholder="e.g. Lavender, Maroon, Olive" required>
                </div>
                <div class="mb-3">
                    <label for="newColorHex" class="seller-form-label">Color Picker</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" id="newColorHex" class="form-control form-control-color border" value="#a57cbb" title="Choose color shade">
                        <small class="text-muted">Select exact shade</small>
                    </div>
                </div>

                <!-- Quick Presets -->
                <label class="seller-form-label d-block mb-1">Quick Suggestions</label>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ZyraSeller.selectPresetColor('Lavender', '#b57edc')">Lavender</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ZyraSeller.selectPresetColor('Maroon', '#800000')">Maroon</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ZyraSeller.selectPresetColor('Olive', '#808000')">Olive</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ZyraSeller.selectPresetColor('Coral', '#ff7f50')">Coral</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ZyraSeller.selectPresetColor('Teal', '#008080')">Teal</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="ZyraSeller.selectPresetColor('Mustard', '#e1ad01')">Mustard</button>
                </div>
            </div>
            <div class="modal-footer pt-0 border-top-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-zyra-primary btn-sm px-3" onclick="ZyraSeller.addNewColor(document.getElementById('newColorName').value, document.getElementById('newColorHex').value)">
                    <i class="bi bi-plus-lg me-1"></i> Add Color
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
