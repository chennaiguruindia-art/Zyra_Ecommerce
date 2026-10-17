@extends('layouts.app')

@section('title', 'Secure Checkout | ZYRA Lifestyle')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Complete your order securely with UPI, Cards, or Net Banking via Razorpay.')
@push('styles')
<style>
/* ── Payment Method Tabs ─────────────────────────────────────────── */
.zyra-pay-tabs {
    flex-wrap: nowrap;
}

.zyra-pay-tab {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 12px 8px;
    border: 2px solid #E8E0D8;
    border-radius: 14px;
    background: #FAFAF9;
    color: #6E6762;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    letter-spacing: 0.3px;
}

.zyra-pay-tab i {
    font-size: 1.25rem;
    line-height: 1;
}

.zyra-pay-tab:hover {
    border-color: #7D1D32;
    color: #7D1D32;
    background: #FDF5F7;
}

.zyra-pay-tab.active {
    border-color: #7D1D32;
    background: #7D1D32;
    color: #ffffff;
    box-shadow: 0 4px 16px rgba(125, 29, 50, 0.22);
}

/* ── Payment Panels ──────────────────────────────────────────────── */
.zyra-pay-panel-inner {
    border: 1.5px solid #EDE4DE;
    border-radius: 14px;
    background: #FDFBF9;
    padding: 20px 20px 16px;
    animation: payPanelIn 0.22s ease;
}

@keyframes payPanelIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.pay-panel-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #1E1A18;
    margin: 0 0 14px;
    display: flex;
    align-items: center;
}

/* UPI Apps */
.upi-app-grid {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.upi-app-chip {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    background: #fff;
    border: 1.5px solid #EDE4DE;
    border-radius: 10px;
    padding: 9px 14px;
    font-size: 0.72rem;
    font-weight: 600;
    color: #444;
    min-width: 68px;
    cursor: default;
    transition: border-color 0.15s ease;
}

.upi-app-chip img {
    width: 32px;
    height: 32px;
    object-fit: contain;
    border-radius: 6px;
}

.upi-app-chip:hover {
    border-color: #7D1D32;
}

/* Card Brands */
.card-brand-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.card-brand-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #fff;
    border: 1.5px solid #EDE4DE;
    border-radius: 8px;
    padding: 7px 14px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #333;
}

/* Bank Grid */
.bank-chip-grid {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.bank-chip {
    display: inline-block;
    background: #fff;
    border: 1.5px solid #EDE4DE;
    border-radius: 7px;
    padding: 5px 12px;
    font-size: 0.74rem;
    font-weight: 600;
    color: #555;
    cursor: default;
    transition: border-color 0.15s, color 0.15s;
}

.bank-chip:hover {
    border-color: #7D1D32;
    color: #7D1D32;
}

/* Note / Caption */
.pay-panel-note {
    font-size: 0.76rem;
    color: #7A736D;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Secure Bar */
.pay-secure-bar {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: #888;
    margin-top: 12px;
}

.pay-secure-bar i {
    color: #28a745;
}

@media (max-width: 480px) {
    .zyra-pay-tab span { font-size: 0.7rem; }
    .upi-app-chip { min-width: 58px; padding: 7px 10px; }
}
</style>
@endpush

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
                        Payment Method
                    </h5>

                    <!-- Hidden radio inputs (used by JS for form submit) -->
                    <input type="radio" name="paymentMethod" id="pm_upi"        value="upi"        checked hidden>
                    <input type="radio" name="paymentMethod" id="pm_card"       value="card"       hidden>
                    <input type="radio" name="paymentMethod" id="pm_netbanking" value="netbanking" hidden>

                    <!-- Method Selector Pills -->
                    <div class="zyra-pay-tabs d-flex gap-2 mb-3" role="tablist">
                        <button type="button" class="zyra-pay-tab active" data-method="upi" aria-label="Pay via UPI">
                            <i class="bi bi-qr-code-scan"></i>
                            <span>UPI</span>
                        </button>
                        <button type="button" class="zyra-pay-tab" data-method="card" aria-label="Pay via Card">
                            <i class="bi bi-credit-card-2-front"></i>
                            <span>Card</span>
                        </button>
                        <button type="button" class="zyra-pay-tab" data-method="netbanking" aria-label="Pay via Net Banking">
                            <i class="bi bi-bank2"></i>
                            <span>Net Banking</span>
                        </button>
                    </div>

                    <!-- UPI Panel -->
                    <div class="zyra-pay-panel" id="panel_upi">
                        <div class="zyra-pay-panel-inner">
                            <p class="pay-panel-title"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Pay via UPI</p>
                            <div class="upi-app-grid">
                                <div class="upi-app-chip"><img src="{{ asset('images/upi/gpay.png') }}" onerror="this.style.display='none'" alt=""><span>Google Pay</span></div>
                                <div class="upi-app-chip"><img src="{{ asset('images/upi/phonepe.png') }}" onerror="this.style.display='none'" alt=""><span>PhonePe</span></div>
                                <div class="upi-app-chip"><img src="{{ asset('images/upi/paytm.png') }}" onerror="this.style.display='none'" alt=""><span>Paytm</span></div>
                                <div class="upi-app-chip"><img src="{{ asset('images/upi/bhim.png') }}" onerror="this.style.display='none'" alt=""><span>BHIM</span></div>
                            </div>
                            <p class="pay-panel-note"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Instant payment — scan QR or enter UPI ID at Razorpay checkout.</p>
                        </div>
                    </div>

                    <!-- Card Panel -->
                    <div class="zyra-pay-panel d-none" id="panel_card">
                        <div class="zyra-pay-panel-inner">
                            <p class="pay-panel-title"><i class="bi bi-credit-card-2-front me-2 text-dark"></i>Credit / Debit Card</p>
                            <div class="card-brand-row">
                                <span class="card-brand-chip"><i class="bi bi-credit-card-fill text-primary"></i> Visa</span>
                                <span class="card-brand-chip"><i class="bi bi-credit-card-fill text-danger"></i> Mastercard</span>
                                <span class="card-brand-chip"><i class="bi bi-credit-card-fill text-warning"></i> RuPay</span>
                                <span class="card-brand-chip"><i class="bi bi-credit-card-fill text-info"></i> Amex</span>
                            </div>
                            <p class="pay-panel-note"><i class="bi bi-shield-lock-fill text-success me-1"></i>Your card details are entered securely on the Razorpay 256-bit SSL page — never stored by us.</p>
                        </div>
                    </div>

                    <!-- Net Banking Panel -->
                    <div class="zyra-pay-panel d-none" id="panel_netbanking">
                        <div class="zyra-pay-panel-inner">
                            <p class="pay-panel-title"><i class="bi bi-bank2 me-2 text-secondary"></i>Net Banking</p>
                            <div class="bank-chip-grid">
                                <span class="bank-chip">SBI</span>
                                <span class="bank-chip">HDFC</span>
                                <span class="bank-chip">ICICI</span>
                                <span class="bank-chip">Axis</span>
                                <span class="bank-chip">Kotak</span>
                                <span class="bank-chip">PNB</span>
                                <span class="bank-chip">BOB</span>
                                <span class="bank-chip">Canara</span>
                                <span class="bank-chip">Union</span>
                                <span class="bank-chip">Yes Bank</span>
                                <span class="bank-chip">IndusInd</span>
                                <span class="bank-chip">+ More</span>
                            </div>
                            <p class="pay-panel-note"><i class="bi bi-shield-lock-fill text-success me-1"></i>Select your bank at the Razorpay checkout page. All major Indian banks supported.</p>
                        </div>
                    </div>

                    <div class="pay-secure-bar">
                        <i class="bi bi-lock-fill"></i> Payments processed securely via <strong>Razorpay</strong>
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
                        <div class="checkout-item-row d-flex align-items-center gap-3 py-2 border-bottom" data-id="{{ $item['id'] ?? 0 }}" data-price="{{ (float) ($item['price'] ?? 0) }}" data-size="{{ $item['size'] ?? '' }}" data-color="{{ $item['color'] ?? '' }}" data-dupatta="{{ isset($item['dupatta']) ? ($item['dupatta'] ? '1' : '0') : '' }}">
                            <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['name'] ?? 'Item' }}" style="width: 50px; height: 65px; object-fit: cover; border-radius: 4px;">
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="text-truncate fw-semibold small">{{ $item['name'] ?? 'Item' }}</div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Size: {{ $item['size'] ?? 'M' }} | Color: {{ $item['color'] ?? 'Standard' }}@if(array_key_exists('dupatta', $item)) | {{ $item['dupatta'] ? 'With Dupatta' : 'Without Dupatta' }}@endif</small>
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
</script>
@endpush
