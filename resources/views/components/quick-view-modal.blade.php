<!-- Quick View Modal -->
<div class="modal fade quick-view-modal" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <span class="badge bg-light text-muted border text-uppercase" id="qvProductCategory">Category</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="row g-4 align-items-center">
                    <!-- Left: Product Image -->
                    <div class="col-md-6 text-center">
                        <div class="bg-light rounded overflow-hidden" style="height: 420px;">
                            <img id="qvProductImage" src="" alt="Product" class="w-100 h-100 object-fit-cover">
                        </div>
                    </div>

                    <!-- Right: Product Details -->
                    <div class="col-md-6">
                        <h4 class="fw-bold mb-1" id="qvProductTitle">Product Title</h4>
                        
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="text-warning small" id="qvProductRating">★★★★☆</span>
                            <span class="text-muted small" id="qvProductReviews">(0 reviews)</span>
                        </div>

                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span class="fs-4 fw-bold text-dark" id="qvProductPrice">₹0</span>
                            <span class="text-muted text-decoration-line-through small" id="qvProductOldPrice"></span>
                            <span class="badge bg-danger" id="qvProductDiscount"></span>
                        </div>

                        <p class="text-muted small mb-3" id="qvProductDescription">
                            Product description goes here.
                        </p>

                        <!-- Color Selector -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Color</label>
                            <div id="qvProductColors" class="d-flex flex-wrap gap-2"></div>
                        </div>

                        <!-- Size Selector -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Select Size</label>
                            <div id="qvProductSizes" class="d-flex flex-wrap gap-1"></div>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase">Quantity</label>
                            <div class="zyra-qty-stepper">
                                <button type="button" class="zyra-qty-btn" onclick="const i = document.getElementById('qvQuantityInput'); let v = parseInt(i.value) || 1; if(v > 1) i.value = v - 1;">-</button>
                                <input type="text" id="qvQuantityInput" class="zyra-qty-input" value="1" readonly>
                                <button type="button" class="zyra-qty-btn" onclick="const i = document.getElementById('qvQuantityInput'); let v = parseInt(i.value) || 1; i.value = v + 1;">+</button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-zyra-primary" id="qvAddToCartBtn">
                                <i class="bi bi-bag-plus me-1"></i> Add to Cart
                            </button>
                            <a href="#" id="qvViewDetailsLink" class="btn btn-outline-dark btn-sm text-center">
                                View Full Product Details <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
