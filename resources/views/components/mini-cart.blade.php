<!-- Offcanvas Mini Cart Drawer -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="zyraMiniCart" aria-labelledby="zyraMiniCartLabel" style="width: 380px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="zyraMiniCartLabel">
            <i class="bi bi-bag me-2"></i> Shopping Bag
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body d-flex flex-column">
        <!-- Dynamic Items Container populated by cart.js -->
        <div id="miniCartItemsContainer" class="flex-grow-1 overflow-auto pe-1">
            <!-- Will be populated via ZyraCart.renderMiniCart() -->
        </div>

        <!-- Mini Cart Footer -->
        <div class="border-top pt-3 mt-auto">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Subtotal:</span>
                <span id="miniCartSubtotal" class="fw-bold fs-5 text-dark">₹0</span>
            </div>
            <p class="small text-muted mb-3 text-center">Taxes & shipping calculated at checkout.</p>
            <div class="d-grid gap-2">
                <a href="{{ route('cart') }}" id="miniCartViewCartBtn" class="btn btn-outline-dark">
                    View Shopping Bag
                </a>
                <a href="{{ route('checkout') }}" id="miniCartCheckoutBtn" class="btn btn-zyra-primary">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    </div>
</div>
