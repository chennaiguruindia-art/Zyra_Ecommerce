<!-- Offcanvas Mini Cart Drawer (Modern Bag & Offers Design) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="zyraMiniCart" aria-labelledby="zyraMiniCartLabel" style="width: 440px; max-width: 95vw;">
    <div class="offcanvas-header border-bottom py-3 px-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="offcanvas-title fw-bold mb-0" id="zyraMiniCartLabel" style="font-size: 1.15rem;">
                My Bag
            </h5>
            <span id="miniCartItemCountBadge" class="badge rounded-pill bg-light text-dark border fw-normal" style="font-size: 0.8rem;">
                0 items
            </span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body d-flex flex-column p-0">
        <!-- Scrollable Body Content -->
        <div class="flex-grow-1 overflow-y-auto px-3 py-2" id="miniCartScrollBody">
            <!-- Dynamic Mini-Cart Loyalty Upsell / Treat Bar -->
            <div id="miniCartLoyaltyUpsell" class="p-3 my-2 rounded-3 border-0" style="display: none; background: #FAF5FF; border: 1.5px solid #D8B4FE !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge py-1 px-2 text-uppercase fw-bold" style="background: #6B21A8; color: #fff; font-size: 0.68rem; letter-spacing: 0.5px;">
                        FESTIVE TREAT
                    </span>
                    <span id="miniLoyaltyPill" class="badge bg-white text-dark border fw-semibold" style="font-size: 0.72rem;">
                        UNLOCK REWARD
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="miniLoyaltyIcon" class="fs-4">🎁</span>
                    <div class="flex-grow-1">
                        <div id="miniLoyaltyText" class="small fw-semibold text-dark lh-sm">
                            Shop more to unlock coupons!
                        </div>
                        <div class="progress mt-2" style="height: 6px; background-color: #E9D5FF; border-radius: 6px;">
                            <div id="miniLoyaltyProgressBar" class="progress-bar" role="progressbar" style="width: 0%; background: linear-gradient(90deg, #7C3AED 0%, #D48D3B 100%); border-radius: 6px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Items Container populated by cart.js -->
            <div id="miniCartItemsContainer">
                <!-- Populated via ZyraCart.renderMiniCart() -->
            </div>

            <!-- Offers for You / Coupon Section -->
            <div id="miniCartOffersSection" class="border-top pt-3 pb-2 mt-3" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">
                        <i class="bi bi-tag-fill me-1 text-primary"></i> Offers for you
                    </h6>
                    <span class="text-primary fw-semibold small" style="cursor: pointer; font-size: 0.75rem;" onclick="document.getElementById('miniCartCouponInput')?.focus()">
                        View all
                    </span>
                </div>

                <!-- Coupon Input Form -->
                <div class="input-group mb-2">
                    <input type="text" id="miniCartCouponInput" class="form-control form-control-sm text-uppercase" placeholder="Type coupon code here" style="letter-spacing: 0.5px; border-color: #D1D5DB; font-size: 0.85rem;" onkeydown="if(event.key==='Enter'){ZyraCart.applyCouponFromMiniCart();}">
                    <button class="btn btn-sm px-3 fw-bold text-white" type="button" id="miniCartApplyCouponBtn" onclick="ZyraCart.applyCouponFromMiniCart()" style="background-color: #581C87;">
                        Apply
                    </button>
                </div>

                <!-- Dynamic Coupon Application Feedback / Error Message -->
                <div id="miniCartCouponFeedback" class="mb-2" style="display: none;"></div>

                <!-- Horizontal Scrollable Coupon Voucher Tickets -->
                <div class="mini-cart-coupon-scroll d-flex gap-2 pb-2 pt-1" style="overflow-x: auto; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: thin;">
                    <!-- Ticket 1: FIRSTORDER -->
                    <div class="coupon-ticket-card p-2 rounded-3 flex-shrink-0" style="width: 200px; background: #FAF5FF; border: 1px dashed #A855F7; scroll-snap-align: start; cursor: pointer;" onclick="ZyraCart.applyCoupon('FIRSTORDER')">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">FIRSTORDER</span>
                            <span class="badge border border-dark text-dark bg-white py-1 px-2" style="border-style: dashed !important; font-size: 0.65rem;">APPLY</span>
                        </div>
                        <div class="small text-muted mb-1 lh-sm" style="font-size: 0.72rem;">Save ₹100 on your very 1st order</div>
                        <div class="text-primary fw-semibold" style="font-size: 0.68rem;">Tap to apply &rsaquo;</div>
                    </div>

                    <!-- Ticket 2: ZYRA300 -->
                    <div class="coupon-ticket-card p-2 rounded-3 flex-shrink-0" style="width: 200px; background: #FFFBEB; border: 1px dashed #F59E0B; scroll-snap-align: start; cursor: pointer;" onclick="ZyraCart.applyCoupon('ZYRA300')">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">ZYRA300</span>
                            <span class="badge border border-dark text-dark bg-white py-1 px-2" style="border-style: dashed !important; font-size: 0.65rem;">APPLY</span>
                        </div>
                        <div class="small text-muted mb-1 lh-sm" style="font-size: 0.72rem;">Save ₹250 on orders above ₹3,000</div>
                        <div class="text-primary fw-semibold" style="font-size: 0.68rem;">Tap to apply &rsaquo;</div>
                    </div>

                    <!-- Ticket 3: ZYRA500 -->
                    <div class="coupon-ticket-card p-2 rounded-3 flex-shrink-0" style="width: 200px; background: #F0FDF4; border: 1px dashed #22C55E; scroll-snap-align: start; cursor: pointer;" onclick="ZyraCart.applyCoupon('ZYRA500')">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">ZYRA500</span>
                            <span class="badge border border-dark text-dark bg-white py-1 px-2" style="border-style: dashed !important; font-size: 0.65rem;">APPLY</span>
                        </div>
                        <div class="small text-muted mb-1 lh-sm" style="font-size: 0.72rem;">Save ₹500 (VIP) on orders above ₹5,000</div>
                        <div class="text-primary fw-semibold" style="font-size: 0.68rem;">Tap to apply &rsaquo;</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Mini Cart Footer -->
        <div class="border-top p-3 bg-white mt-auto shadow-sm" id="miniCartFooter">
            <!-- Dynamic Savings Strip (matches design banner) -->
            <div id="miniCartSavingsStrip" class="py-2 px-3 mb-2 rounded text-center small fw-semibold" style="background: #F3E8FF; color: #5B21B6; display: none;">
                You are saving <strong id="miniCartSavingsValue">₹0</strong> on this order.
            </div>

            <!-- Price Breakdown & Checkout Action -->
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div>
                    <div class="small text-muted" style="font-size: 0.72rem;">Total Payable</div>
                    <div class="d-flex align-items-baseline gap-1">
                        <span id="miniCartOriginalPrice" class="text-muted text-decoration-line-through small" style="display: none;">₹0</span>
                        <span id="miniCartSubtotal" class="fw-bold fs-5 text-dark">₹0</span>
                        <span id="miniCartDiscountBadge" class="badge bg-success-subtle text-success small" style="display: none; font-size: 0.7rem;"></span>
                    </div>
                </div>
                <a href="{{ route('checkout') }}" id="miniCartCheckoutBtn" class="btn text-white fw-bold px-4 py-2" style="background-color: #581C87; min-width: 140px; border-radius: 6px; letter-spacing: 0.5px;">
                    CHECKOUT
                </a>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="text-muted" style="font-size: 0.72rem;">Taxes & shipping calculated at checkout</span>
                <a href="{{ route('cart') }}" id="miniCartViewCartBtn" class="text-secondary small text-decoration-underline" style="font-size: 0.75rem;">
                    View Full Bag
                </a>
            </div>
        </div>
    </div>
</div>
