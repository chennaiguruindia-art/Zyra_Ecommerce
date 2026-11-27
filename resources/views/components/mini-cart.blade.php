<!-- Offcanvas Mini Cart Drawer -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="zyraMiniCart" aria-labelledby="zyraMiniCartLabel" style="width: 380px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="zyraMiniCartLabel">
            <i class="bi bi-bag me-2"></i> Shopping Bag
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body d-flex flex-column">
        <!-- Dynamic Mini-Cart Loyalty Upsell Bar -->
        <div id="miniCartLoyaltyUpsell" class="p-3 mb-2 rounded-3 border-0" style="display: none; background: linear-gradient(135deg, #FFF9F3 0%, #FFF3E6 100%); border-left: 3px solid #D48D3B !important;">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span id="miniLoyaltyIcon" class="fs-5">🎁</span>
                <span id="miniLoyaltyText" class="small fw-semibold text-dark lh-sm">
                    Shop more to unlock coupons!
                </span>
            </div>
            <div class="progress mt-2" style="height: 6px; background-color: rgba(212, 141, 59, 0.2); border-radius: 6px;">
                <div id="miniLoyaltyProgressBar" class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(90deg, #D48D3B 0%, #7D1D32 100%); border-radius: 6px;"></div>
            </div>
        </div>

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
