@extends('layouts.app')

@section('title', 'Secure Checkout | ZYRA Fashion')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Complete your order securely with UPI, Cards, or Net Banking via Razorpay.')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Cart', 'url' => route('cart')],
    ['label' => 'Checkout', 'url' => '']
]" />

<div class="container py-5">
    <div class="row g-5">
        
        <!-- Left: Checkout Forms -->
        <div class="col-lg-7">
            <form id="checkoutForm" novalidate>
                
                <!-- 1. Customer Information -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-dark rounded-circle me-2 p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">1</span>
                        Customer Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="firstName" class="form-label small fw-semibold">First Name *</label>
                            <input type="text" id="firstName" class="form-control" placeholder="e.g. Aditi" required value="{{ old('first_name', $customer['first_name'] ?? '') }}">
                        </div>
                        <div class="col-sm-6">
                            <label for="lastName" class="form-label small fw-semibold">Last Name</label>
                            <input type="text" id="lastName" class="form-control" placeholder="e.g. Sharma" value="{{ old('last_name', $customer['last_name'] ?? '') }}">
                        </div>
                        <div class="col-sm-6">
                            <label for="email" class="form-label small fw-semibold">Email Address *</label>
                            <input type="email" id="email" class="form-control" placeholder="aditi@example.com" required value="{{ old('email', $customer['email'] ?? '') }}">
                        </div>
                        <div class="col-sm-6">
                            <label for="phone" class="form-label small fw-semibold">Phone Number *</label>
                            <input type="tel" id="phone" class="form-control" placeholder="10-digit mobile number" pattern="[0-9]{10}" required value="{{ old('phone', $customer['phone'] ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- 2. Shipping Address -->
                <div class="mb-4 pt-3 border-top">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-dark rounded-circle me-2 p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">2</span>
                        Shipping Address
                    </h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="address" class="form-label small fw-semibold">Street Address *</label>
                            <input type="text" id="address" class="form-control" placeholder="House/Flat No., Street, Landmark" required value="{{ old('address', $customer['address'] ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label for="apartment" class="form-label small fw-semibold">Apartment, Suite, Unit (Optional)</label>
                            <input type="text" id="apartment" class="form-control" placeholder="Apartment name, Tower, etc." value="{{ old('apartment', $customer['apartment'] ?? '') }}">
                        </div>
                        <div class="col-sm-4">
                            <label for="city" class="form-label small fw-semibold">City *</label>
                            <input type="text" id="city" class="form-control" placeholder="e.g. Bengaluru" required value="{{ old('city', $customer['city'] ?? '') }}">
                        </div>
                        <div class="col-sm-4">
                            <label for="state" class="form-label small fw-semibold">State *</label>
                            @php $currentState = old('state', $customer['state'] ?? ''); @endphp
                            <select id="state" class="form-select" required>
                                <option value="" {{ empty($currentState) ? 'selected' : '' }} disabled>Select State</option>
                                @foreach(['Karnataka', 'Maharashtra', 'Delhi NCR', 'Tamil Nadu', 'Telangana', 'Gujarat', 'West Bengal', 'Rajasthan', 'Kerala', 'Uttar Pradesh', 'Punjab', 'Andhra Pradesh'] as $st)
                                    <option value="{{ $st }}" {{ $currentState === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label for="pincode" class="form-label small fw-semibold">PIN Code *</label>
                            <input type="text" id="pincode" class="form-control" placeholder="6 digits" maxlength="6" pattern="[0-9]{6}" required value="{{ old('pincode', $customer['pincode'] ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Country</label>
                            <input type="text" class="form-control" value="India" readonly disabled>
                        </div>
                    </div>
                </div>

                <!-- 3. Delivery Method -->
                <div class="mb-4 pt-3 border-top">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-dark rounded-circle me-2 p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">3</span>
                        Delivery Method
                    </h5>
                    <div class="border rounded p-3 bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Standard Express Delivery</strong>
                            <div class="small text-muted">Estimated arrival within 3–7 business days</div>
                        </div>
                        <span class="badge bg-success">Tracked Express</span>
                    </div>
                </div>

                <!-- 4. Payment Method -->
                <div class="mb-4 pt-3 border-top">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge bg-dark rounded-circle me-2 p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">4</span>
                        Payment Options
                    </h5>
                    <p class="small text-muted">Select your preferred payment method. Online payments are securely processed via Razorpay (UPI, Cards & Net Banking).</p>

                    <div class="d-flex flex-column gap-2 mb-3">
                        <!-- UPI -->
                        <div class="payment-method-box border rounded p-3 border-primary bg-light">
                            <label class="d-flex align-items-center justify-content-between w-100 cursor-pointer m-0">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="radio" name="paymentMethod" value="upi" checked class="form-check-input mt-0">
                                    <strong>UPI (Google Pay, PhonePe, Paytm)</strong>
                                </div>
                                <i class="bi bi-qr-code-scan fs-5 text-primary"></i>
                            </label>
                            <div class="small text-muted ps-4 pt-1"><i class="bi bi-lock-fill me-1"></i>Securely pay at the Razorpay checkout.</div>
                        </div>

                        <!-- Credit/Debit Card -->
                        <div class="payment-method-box border rounded p-3">
                            <label class="d-flex align-items-center justify-content-between w-100 cursor-pointer m-0">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="radio" name="paymentMethod" value="card" class="form-check-input mt-0">
                                    <strong>Credit / Debit Card (Visa, Mastercard, RuPay)</strong>
                                </div>
                                <i class="bi bi-credit-card fs-5 text-dark"></i>
                            </label>
                            <div class="small text-muted ps-4 pt-1"><i class="bi bi-lock-fill me-1"></i>Securely pay at the Razorpay checkout.</div>
                        </div>

                        <!-- Net Banking -->
                        <div class="payment-method-box border rounded p-3">
                            <label class="d-flex align-items-center justify-content-between w-100 cursor-pointer m-0">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="radio" name="paymentMethod" value="netbanking" class="form-check-input mt-0">
                                    <strong>Net Banking (All Major Indian Banks)</strong>
                                </div>
                                <i class="bi bi-bank fs-5 text-secondary"></i>
                            </label>
                            <div class="small text-muted ps-4 pt-1"><i class="bi bi-lock-fill me-1"></i>Securely pay at the Razorpay checkout.</div>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <!-- Right: Order Summary Sidebar -->
        <div class="col-lg-5">
            <div class="zyra-summary-card position-sticky" style="top: 100px;">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Order Review</h5>

                <!-- Items Container -->
                <div id="checkoutItemsList" class="mb-3 max-h-64 overflow-auto pe-1">
                    @forelse(($cartItems ?? []) as $item)
                        <div class="checkout-item-row d-flex align-items-center gap-3 py-2 border-bottom" data-id="{{ $item['id'] ?? 0 }}" data-price="{{ (float) ($item['price'] ?? 0) }}" data-size="{{ $item['size'] ?? '' }}" data-color="{{ $item['color'] ?? '' }}">
                            <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['name'] ?? 'Item' }}" style="width: 50px; height: 65px; object-fit: cover; border-radius: 4px;">
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="text-truncate fw-semibold small">{{ $item['name'] ?? 'Item' }}</div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Size: {{ $item['size'] ?? 'M' }} | Color: {{ $item['color'] ?? 'Standard' }}</small>
                                <div class="zyra-qty-stepper mt-1" style="height: 28px;">
                                    <button type="button" class="zyra-qty-btn checkout-qty-btn" data-dir="-1" data-id="{{ $item['id'] ?? 0 }}" data-size="{{ $item['size'] ?? '' }}" data-color="{{ $item['color'] ?? '' }}">−</button>
                                    <input type="text" class="zyra-qty-input checkout-qty" value="{{ (int) ($item['quantity'] ?? 1) }}" readonly style="width: 36px; height: 28px; font-size: 0.8rem;">
                                    <button type="button" class="zyra-qty-btn checkout-qty-btn" data-dir="1" data-id="{{ $item['id'] ?? 0 }}" data-size="{{ $item['size'] ?? '' }}" data-color="{{ $item['color'] ?? '' }}">+</button>
                                </div>
                            </div>
                            <div class="fw-bold small text-nowrap checkout-line-total">₹{{ ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1)) }}</div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-bag-x fs-2 d-block mb-2"></i>
                            <p class="small mb-2">Your shopping bag is empty.</p>
                            <a href="{{ route('shop') }}" class="btn btn-sm btn-zyra-primary">Explore Collection</a>
                        </div>
                    @endforelse
                </div>

                <div class="zyra-summary-row">
                    <span class="text-muted">Subtotal</span>
                    <span id="checkoutSubtotal" class="fw-semibold">₹{{ $cartSubtotal ?? 0 }}</span>
                </div>

                <div class="zyra-summary-row text-success" id="checkoutDiscountRow" style="display: none;">
                    <span>Coupon Discount</span>
                    <span id="checkoutDiscount" class="fw-semibold">-₹0</span>
                </div>

                <div class="zyra-summary-row">
                    <span class="text-muted">GST ({{ $gstRate ?? 5 }}%)</span>
                    <span id="checkoutGst" class="fw-semibold">₹{{ $cartGst ?? 0 }}</span>
                </div>

                <div class="zyra-summary-row">
                    <span class="text-muted">Delivery</span>
                    <span id="checkoutShipping" class="fw-semibold">{{ ($cartShipping ?? 0) === 0 ? 'FREE' : '₹' . $cartShipping }}</span>
                </div>

                <div class="zyra-summary-row total-row">
                    <span>Total Amount</span>
                    <span id="checkoutTotal" class="fw-bold fs-4">₹{{ $cartTotal ?? 0 }}</span>
                </div>

                <!-- Coupon Code -->
                <div class="mt-4 pt-3 border-top">
                    <label for="checkoutCouponInput" class="form-label small fw-bold text-uppercase">Have a Promo Code?</label>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" id="checkoutCouponInput" class="form-control" placeholder="e.g. FIRSTORDER" style="text-transform: uppercase;">
                        <button class="btn btn-dark" type="button" id="checkoutCouponApplyBtn">Apply</button>
                    </div>
                    <div id="checkoutCouponMsg" class="small"></div>
                </div>

                <!-- Place Order Button -->
                <div class="d-grid mt-4">
                    <button type="button" id="placeOrderBtn" class="btn btn-zyra-primary py-3 fs-6">
                        Place Order <i class="bi bi-check-lg ms-1"></i>
                    </button>
                </div>

                <div class="text-center mt-3 text-muted small">
                    <i class="bi bi-shield-lock-fill text-success me-1"></i> Safe & Secure 256-Bit SSL Checkout
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<div id="zyraServerCart" data-cart='@json($cartItems ?? [])' hidden></div>
<meta name="razorpay-key" content="{{ $razorpayKey ?? '' }}">
<meta name="first-order-eligible" content="{{ ($isFirstOrderEligible ?? false) ? '1' : '0' }}">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    window.ZYRA_SERVER_CART = JSON.parse(document.getElementById('zyraServerCart')?.dataset.cart || '[]');
    window.ZYRA_GST_RATE = {{ $gstRate ?? 5 }};
</script>
@endpush
