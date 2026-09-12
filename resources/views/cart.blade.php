@extends('layouts.app')

@section('title', 'Shopping Bag | ZYRA Lifestyle')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Review items in your shopping bag, apply promo coupons, and proceed to secure checkout.')

@section('content')

<x-breadcrumb :items="[['label' => 'Shopping Bag', 'url' => '']]" />

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h1 class="h3 fw-bold mb-0">Your Shopping Bag</h1>
        <a href="{{ route('shop') }}" class="btn btn-sm btn-link text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i> Continue Shopping
        </a>
    </div>

    <!-- Active Cart Content Section -->
    <div id="cartContentSection">
        <div class="row g-4">
            
            <!-- Left: Cart Items Table -->
            <div class="col-lg-8">
                <div class="table-responsive">
                    <table class="zyra-cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody">
                            <!-- Populated dynamically by cart.js -->
                        </tbody>
                    </table>
                </div>

                <!-- Shipping Notice -->
                <div class="alert alert-light border mt-3 d-flex align-items-center gap-2 small">
                    <i class="bi bi-truck text-dark fs-5"></i>
                    <div>
                        <strong>Free Express Delivery</strong> on all orders.
                    </div>
                </div>

                <!-- Save Cart for Reminder -->
                <div class="alert alert-zyra-reminder border mt-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-bell fs-5"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block">Saving your bag?</strong>
                            <span class="text-muted small">Enter your email and we'll remind you if you leave without checking out.</span>
                            <div class="input-group input-group-sm mt-2">
                                <input type="email" id="saveCartEmailInput" class="form-control" placeholder="you@example.com" maxlength="190">
                                <button class="btn btn-dark" type="button" id="saveCartBtn" onclick="ZyraCart.saveCartForReminder(document.getElementById('saveCartEmailInput').value)">
                                    Save My Bag
                                </button>
                            </div>
                            <div id="saveCartFeedback" class="mt-1 small"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary & Coupon -->
            <div class="col-lg-4">
                <div class="zyra-summary-card">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Order Summary</h5>

                    <div class="zyra-summary-row">
                        <span class="text-muted">Subtotal</span>
                        <span id="cartSummarySubtotal" class="fw-semibold">₹0</span>
                    </div>

                    <div class="zyra-summary-row text-success" id="cartDiscountRow" style="display: none;">
                        <span>Coupon Discount</span>
                        <span id="cartSummaryDiscount" class="fw-semibold">-₹0</span>
                    </div>

                    <div class="zyra-summary-row">
                        <span class="text-muted">Estimated Delivery</span>
                        <span id="cartSummaryShipping" class="fw-semibold">₹0</span>
                    </div>

                    <div class="zyra-summary-row">
                        <span class="text-muted">GST Taxes</span>
                        <span class="text-success small fw-semibold">Inclusive</span>
                    </div>

                    <div class="zyra-summary-row total-row">
                        <span>Total Payable</span>
                        <span id="cartSummaryTotal" class="fw-bold">₹0</span>
                    </div>

                    <!-- Coupon Code Input -->
                    <div class="mt-4 pt-3 border-top">
                        <label for="cartCouponInput" class="form-label small fw-bold text-uppercase">Have a Promo Code?</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" id="cartCouponInput" class="form-control" placeholder="e.g. SAVE20" style="text-transform: uppercase;">
                            <button class="btn btn-dark" type="button" onclick="ZyraCart.applyCoupon(document.getElementById('cartCouponInput').value)">
                                Apply
                            </button>
                        </div>
                        <div id="appliedCouponWrap"></div>
                        <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                            <i class="bi bi-tag-fill me-1 text-secondary"></i> Available: <strong>WELCOME10</strong> (10%), <strong>SAVE20</strong> (20%), <strong>FASHION15</strong> (15%)
                        </small>
                    </div>

                    <!-- Checkout CTA -->
                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('checkout') }}" class="btn btn-zyra-primary py-2">
                            Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a href="{{ route('shop') }}" class="btn btn-zyra-outline btn-sm">
                            Add More Items
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Empty Cart State -->
    <div id="cartEmptyState" class="text-center py-5 my-4" style="display: none;">
        <div class="mb-3 text-muted" style="font-size: 3.5rem;">
            <i class="bi bi-bag"></i>
        </div>
        <h4 class="fw-bold mb-2">Your shopping bag is empty</h4>
        <p class="text-muted small mb-4">
            Looks like you haven't added anything to your cart yet. Browse our curated fashion styles!
        </p>
        <a href="{{ route('shop') }}" class="btn btn-zyra-primary px-4 py-2">
            Explore Collection <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

</div>

@endsection
