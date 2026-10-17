@extends('layouts.app')

@section('title', 'Secure Checkout | ZYRA Lifestyle')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Complete your order securely with UPI, Cards, or Net Banking via Razorpay.')

@push('styles')
<style>
/* ════════════════════════════════════════════════════════════
   ZYRA CHECKOUT – FULL PAGE REDESIGN
   ════════════════════════════════════════════════════════════ */
:root {
    --ck-burg:       #7D1D32;
    --ck-burg-dk:    #581020;
    --ck-burg-lt:    #fdf4f6;
    --ck-cream:      #FAF8F5;
    --ck-border:     #EAE2DA;
    --ck-text:       #1C1917;
    --ck-muted:      #78716C;
    --ck-radius:     18px;
    --ck-card-shad:  0 2px 20px rgba(0,0,0,.06);
}

body { background: var(--ck-cream); }

/* Breadcrumb override */
.zyra-breadcrumb { background: transparent !important; }

/* ── Page wrapper ───────────────────────────────────────── */
.ck-page { padding: 2rem 0 4rem; }

/* ── Section Card ───────────────────────────────────────── */
.ck-card {
    background: #fff;
    border-radius: var(--ck-radius);
    box-shadow: var(--ck-card-shad);
    padding: 28px 30px;
    margin-bottom: 18px;
    border: 1px solid var(--ck-border);
}

/* ── Step Badge ─────────────────────────────────────────── */
.ck-step-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
}
.ck-step-num {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--ck-burg);
    color: #fff;
    font-size: 0.78rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ck-step-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--ck-text);
    margin: 0;
}

/* ── Form Controls ──────────────────────────────────────── */
.ck-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--ck-muted);
    margin-bottom: 5px;
}

.ck-input {
    width: 100%;
    border: 1.5px solid var(--ck-border);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.9rem;
    color: var(--ck-text);
    background: #fff;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
.ck-input:focus {
    border-color: var(--ck-burg);
    box-shadow: 0 0 0 3px rgba(125,29,50,.1);
}
.ck-input::placeholder { color: #C0B8B2; }
.ck-input:disabled, .ck-input[readonly] {
    background: #F7F4F1;
    color: var(--ck-muted);
    cursor: not-allowed;
}

select.ck-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23888' d='M6 8L0 0h12z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }

/* ── Delivery card ──────────────────────────────────────── */
.ck-delivery-box {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--ck-cream);
    border: 1.5px solid var(--ck-border);
    border-radius: 12px;
    padding: 14px 18px;
}
.ck-delivery-icon {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: var(--ck-burg-lt);
    display: flex; align-items: center; justify-content: center;
    color: var(--ck-burg);
    font-size: 1.2rem;
    flex-shrink: 0;
}
.ck-delivery-label { font-size: .92rem; font-weight: 700; color: var(--ck-text); }
.ck-delivery-sub   { font-size: .76rem; color: var(--ck-muted); margin-top: 1px; }
.ck-delivery-badge {
    margin-left: auto;
    background: #D1FAE5; color: #065F46;
    font-size: .7rem; font-weight: 700;
    padding: 4px 10px; border-radius: 999px;
    text-transform: uppercase; letter-spacing: .4px;
}

/* ══ Payment Tabs ════════════════════════════════════════ */
.zyra-pay-tabs { display: flex; gap: 10px; flex-wrap: nowrap; }

.zyra-pay-tab {
    flex: 1;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 5px;
    padding: 14px 8px;
    border: 2px solid var(--ck-border);
    border-radius: 14px;
    background: #FAFAF9;
    color: var(--ck-muted);
    font-size: 0.76rem; font-weight: 700;
    cursor: pointer; letter-spacing: .3px;
    transition: all .2s ease;
}
.zyra-pay-tab i { font-size: 1.3rem; line-height: 1; }
.zyra-pay-tab:hover { border-color: var(--ck-burg); color: var(--ck-burg); background: var(--ck-burg-lt); }
.zyra-pay-tab.active {
    border-color: var(--ck-burg);
    background: var(--ck-burg);
    color: #fff;
    box-shadow: 0 4px 16px rgba(125,29,50,.25);
}

/* Payment Panels */
.zyra-pay-panel-inner {
    border: 1.5px solid var(--ck-border);
    border-radius: 14px;
    background: #FDFBF9;
    padding: 20px;
    animation: payIn .22s ease;
}
@keyframes payIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

.pay-panel-title { font-size: .9rem; font-weight: 700; color: var(--ck-text); margin: 0 0 14px; display:flex; align-items:center; }

.upi-app-grid   { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
.upi-app-chip   { display:flex; flex-direction:column; align-items:center; gap:5px; background:#fff; border:1.5px solid var(--ck-border); border-radius:10px; padding:9px 14px; font-size:.72rem; font-weight:600; color:#444; min-width:70px; transition:border-color .15s; }
.upi-app-chip img { width:30px; height:30px; object-fit:contain; border-radius:6px; }
.upi-app-chip:hover { border-color: var(--ck-burg); }

.card-brand-row { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
.card-brand-chip { display:inline-flex; align-items:center; gap:5px; background:#fff; border:1.5px solid var(--ck-border); border-radius:8px; padding:7px 14px; font-size:.78rem; font-weight:600; color:#333; }

.bank-chip-grid { display:flex; gap:7px; flex-wrap:wrap; margin-bottom:12px; }
.bank-chip { display:inline-block; background:#fff; border:1.5px solid var(--ck-border); border-radius:7px; padding:5px 12px; font-size:.74rem; font-weight:600; color:#555; transition:border-color .15s, color .15s; }
.bank-chip:hover { border-color:var(--ck-burg); color:var(--ck-burg); }

.pay-panel-note { font-size:.75rem; color:var(--ck-muted); margin:0; display:flex; align-items:center; gap:4px; }

.pay-secure-bar { display:flex; align-items:center; gap:6px; font-size:.74rem; color:#9E9089; margin-top:14px; }
.pay-secure-bar i { color:#16a34a; }

/* ════════════════════════════════════════════════════════
   RIGHT SIDEBAR – Order Summary
   ════════════════════════════════════════════════════════ */
.ck-summary {
    background: #fff;
    border-radius: var(--ck-radius);
    border: 1px solid var(--ck-border);
    box-shadow: var(--ck-card-shad);
    overflow: hidden;
    position: sticky;
    top: 90px;
}

.ck-summary-head {
    background: var(--ck-burg);
    color: #fff;
    padding: 18px 24px;
    font-size: .85rem;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    display: flex; align-items: center; gap: 8px;
}

.ck-summary-body { padding: 0 20px 20px; }

/* Items list */
.ck-items-list {
    max-height: 260px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #DDD #FFF;
    padding-top: 12px;
}
.ck-items-list::-webkit-scrollbar { width: 4px; }
.ck-items-list::-webkit-scrollbar-thumb { background: #DDD; border-radius: 4px; }

.ck-item-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #F4EDE8;
}
.ck-item-img {
    width: 52px; height: 66px;
    object-fit: cover;
    border-radius: 10px;
    flex-shrink: 0;
    border: 1px solid var(--ck-border);
}
.ck-item-name  { font-size: .82rem; font-weight: 700; color: var(--ck-text); line-height: 1.25; }
.ck-item-meta  { font-size: .72rem; color: var(--ck-muted); margin-top: 2px; }
.ck-item-price { font-size: .85rem; font-weight: 700; color: var(--ck-text); white-space: nowrap; margin-left: auto; }

/* Qty Stepper – compact */
.ck-qty {
    display: inline-flex;
    align-items: center;
    border: 1.5px solid var(--ck-border);
    border-radius: 8px;
    overflow: hidden;
    margin-top: 6px;
    height: 28px;
}
.ck-qty-btn {
    width: 28px; height: 28px;
    border: none; background: #F7F3EF;
    color: var(--ck-burg); font-size: .95rem; font-weight: 700;
    cursor: pointer; display:flex; align-items:center; justify-content:center;
    transition: background .15s;
    padding: 0;
}
.ck-qty-btn:hover { background: var(--ck-burg-lt); }
.ck-qty-btn:disabled { opacity: .35; cursor: not-allowed; }
.ck-qty-val {
    width: 32px; text-align: center;
    font-size: .82rem; font-weight: 700; color: var(--ck-text);
    border: none; border-left: 1px solid var(--ck-border); border-right: 1px solid var(--ck-border);
    background: #fff; outline: none;
    pointer-events: none; height: 28px; line-height: 28px;
    -moz-appearance: textfield;
}

/* Remove btn */
.ck-item-remove {
    background: none; border: none; padding: 0;
    color: #C0B8B2; font-size: .95rem; cursor: pointer;
    transition: color .15s;
    display: flex; align-items: center;
    margin-left: 4px;
}
.ck-item-remove:hover { color: #e53e3e; }

/* Summary rows */
.ck-sum-rows { padding-top: 14px; border-top: 1.5px dashed var(--ck-border); margin-top: 10px; }
.ck-sum-row  { display:flex; justify-content:space-between; align-items:center; font-size:.86rem; color:var(--ck-muted); margin-bottom: 8px; }
.ck-sum-row .val { font-weight: 600; color: var(--ck-text); }
.ck-sum-row.discount .val { color: #16a34a; }
.ck-sum-total { display:flex; justify-content:space-between; align-items:center; padding: 14px 0 2px; border-top: 2px solid var(--ck-text); margin-top:4px; }
.ck-sum-total .lbl { font-size:.9rem; font-weight:700; color:var(--ck-text); }
.ck-sum-total .amt { font-size:1.4rem; font-weight:800; color:var(--ck-burg); }

/* Coupon */
.ck-coupon-wrap { padding: 14px 0 4px; border-top: 1px solid var(--ck-border); margin-top: 10px; }
.ck-coupon-lbl  { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--ck-muted); margin-bottom:7px; display:block; }
.ck-coupon-row  { display:flex; gap:8px; }
.ck-coupon-input {
    flex: 1;
    border: 1.5px solid var(--ck-border);
    border-radius: 10px;
    padding: 9px 14px;
    font-size: .84rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .8px;
    outline: none;
    background: #FAFAF9;
    color: var(--ck-text);
    transition: border-color .2s;
}
.ck-coupon-input:focus { border-color: var(--ck-burg); background:#fff; }
.ck-coupon-btn {
    padding: 9px 18px;
    border-radius: 10px;
    border: none;
    background: var(--ck-text);
    color: #fff;
    font-size: .82rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s;
    white-space: nowrap;
}
.ck-coupon-btn:hover { background: var(--ck-burg); }

/* Place Order Button */
.ck-order-btn {
    display: block; width: 100%;
    padding: 15px;
    border: none;
    border-radius: 12px;
    background: var(--ck-burg);
    color: #fff;
    font-size: 1rem; font-weight: 700;
    letter-spacing: .4px;
    cursor: pointer;
    margin-top: 16px;
    transition: background .2s, transform .15s, box-shadow .2s;
    box-shadow: 0 6px 20px rgba(125,29,50,.28);
}
.ck-order-btn:hover:not(:disabled) {
    background: var(--ck-burg-dk);
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(125,29,50,.3);
}
.ck-order-btn:disabled { opacity: .6; cursor: not-allowed; }

.ck-ssl-note { text-align:center; font-size:.72rem; color:var(--ck-muted); margin-top:10px; display:flex; align-items:center; justify-content:center; gap:5px; }

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width:767px) {
    .ck-card { padding: 20px 18px; }
    .zyra-pay-tab span { font-size: .68rem; }
    .ck-summary { position: static; margin-top: 16px; }
    .ck-summary-head { padding: 15px 18px; }
    .ck-summary-body { padding: 0 16px 18px; }
}
</style>
@endpush

@section('content')
<x-breadcrumb :items="[
    ['label' => 'Cart', 'url' => route('cart')],
    ['label' => 'Checkout', 'url' => '']
]" />

<div class="ck-page">
<div class="container">
<div class="row g-4 g-lg-5">

    {{-- ══════════════ LEFT COLUMN ══════════════ --}}
    <div class="col-lg-7">
        <form id="checkoutForm" novalidate>

            {{-- 1. Customer Info --}}
            <div class="ck-card">
                <div class="ck-step-head">
                    <div class="ck-step-num">1</div>
                    <h5 class="ck-step-title">Customer Information</h5>
                </div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="ck-label">First Name *</label>
                        <input id="firstName" type="text" class="ck-input" placeholder="e.g. Aditi" required value="{{ old('first_name', $customer['first_name'] ?? '') }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="ck-label">Last Name</label>
                        <input id="lastName" type="text" class="ck-input" placeholder="e.g. Sharma" value="{{ old('last_name', $customer['last_name'] ?? '') }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="ck-label">Email Address *</label>
                        <input id="email" type="email" class="ck-input" placeholder="you@example.com" required value="{{ old('email', $customer['email'] ?? '') }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="ck-label">Phone Number *</label>
                        <input id="phone" type="tel" class="ck-input" placeholder="10-digit mobile" pattern="[0-9]{10}" required value="{{ old('phone', $customer['phone'] ?? '') }}">
                    </div>
                </div>
            </div>

            {{-- 2. Shipping Address --}}
            <div class="ck-card">
                <div class="ck-step-head">
                    <div class="ck-step-num">2</div>
                    <h5 class="ck-step-title">Shipping Address</h5>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="ck-label">Street Address *</label>
                        <input id="address" type="text" class="ck-input" placeholder="House/Flat No., Street, Landmark" required value="{{ old('address', $customer['address'] ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="ck-label">Apartment / Suite (Optional)</label>
                        <input id="apartment" type="text" class="ck-input" placeholder="Apartment name, Tower, etc." value="{{ old('apartment', $customer['apartment'] ?? '') }}">
                    </div>
                    <div class="col-sm-4">
                        <label class="ck-label">City *</label>
                        <input id="city" type="text" class="ck-input" placeholder="e.g. Chennai" required value="{{ old('city', $customer['city'] ?? '') }}">
                    </div>
                    <div class="col-sm-4">
                        <label class="ck-label">State *</label>
                        @php $currentState = old('state', $customer['state'] ?? ''); @endphp
                        <select id="state" class="ck-input" required>
                            <option value="" {{ empty($currentState) ? 'selected' : '' }} disabled>Select State</option>
                            @foreach(['Andhra Pradesh','Karnataka','Kerala','Tamil Nadu','Telangana','Maharashtra','Gujarat','Rajasthan','Delhi NCR','West Bengal','Uttar Pradesh','Punjab','Madhya Pradesh','Bihar','Odisha','Assam','Jharkhand','Haryana','Uttarakhand','Chhattisgarh','Himachal Pradesh','Goa'] as $st)
                                <option value="{{ $st }}" {{ $currentState === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="ck-label">PIN Code *</label>
                        <input id="pincode" type="text" class="ck-input" placeholder="6 digits" maxlength="6" pattern="[0-9]{6}" required value="{{ old('pincode', $customer['pincode'] ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="ck-label">Country</label>
                        <input type="text" class="ck-input" value="India" readonly disabled>
                    </div>
                </div>
            </div>

            {{-- 3. Delivery --}}
            <div class="ck-card">
                <div class="ck-step-head">
                    <div class="ck-step-num">3</div>
                    <h5 class="ck-step-title">Delivery Method</h5>
                </div>
                <div class="ck-delivery-box">
                    <div class="ck-delivery-icon"><i class="bi bi-truck-front-fill"></i></div>
                    <div>
                        <div class="ck-delivery-label">Standard Express Delivery</div>
                        <div class="ck-delivery-sub">Estimated arrival within 3–7 business days · Fully tracked</div>
                    </div>
                    <span class="ck-delivery-badge">FREE</span>
                </div>
            </div>

            {{-- 4. Payment --}}
            <div class="ck-card">
                <div class="ck-step-head">
                    <div class="ck-step-num">4</div>
                    <h5 class="ck-step-title">Payment Method</h5>
                </div>

                {{-- Hidden radios synced by JS --}}
                <input type="radio" name="paymentMethod" id="pm_upi"        value="upi"        checked hidden>
                <input type="radio" name="paymentMethod" id="pm_card"       value="card"       hidden>
                <input type="radio" name="paymentMethod" id="pm_netbanking" value="netbanking" hidden>

                {{-- Tabs --}}
                <div class="zyra-pay-tabs mb-3" role="tablist">
                    <button type="button" class="zyra-pay-tab active" data-method="upi">
                        <i class="bi bi-qr-code-scan"></i><span>UPI</span>
                    </button>
                    <button type="button" class="zyra-pay-tab" data-method="card">
                        <i class="bi bi-credit-card-2-front"></i><span>Card</span>
                    </button>
                    <button type="button" class="zyra-pay-tab" data-method="netbanking">
                        <i class="bi bi-bank2"></i><span>Net Banking</span>
                    </button>
                </div>

                {{-- UPI Panel --}}
                <div class="zyra-pay-panel" id="panel_upi">
                    <div class="zyra-pay-panel-inner">
                        <p class="pay-panel-title"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Pay via UPI</p>
                        <div class="upi-app-grid">
                            <div class="upi-app-chip"><i class="bi bi-google fs-4 text-danger"></i><span>Google Pay</span></div>
                            <div class="upi-app-chip"><i class="bi bi-phone-fill fs-4" style="color:#5f259f"></i><span>PhonePe</span></div>
                            <div class="upi-app-chip"><i class="bi bi-wallet2 fs-4 text-primary"></i><span>Paytm</span></div>
                            <div class="upi-app-chip"><i class="bi bi-qr-code fs-4 text-dark"></i><span>BHIM</span></div>
                        </div>
                        <p class="pay-panel-note"><i class="bi bi-lightning-charge-fill text-warning"></i>Instant — scan QR or enter UPI ID at Razorpay checkout.</p>
                    </div>
                </div>

                {{-- Card Panel --}}
                <div class="zyra-pay-panel d-none" id="panel_card">
                    <div class="zyra-pay-panel-inner">
                        <p class="pay-panel-title"><i class="bi bi-credit-card-2-front me-2 text-dark"></i>Credit / Debit Card</p>
                        <div class="card-brand-row">
                            <span class="card-brand-chip"><i class="bi bi-credit-card-fill text-primary"></i> Visa</span>
                            <span class="card-brand-chip"><i class="bi bi-credit-card-fill text-danger"></i> Mastercard</span>
                            <span class="card-brand-chip"><i class="bi bi-credit-card-fill text-warning"></i> RuPay</span>
                            <span class="card-brand-chip"><i class="bi bi-credit-card-fill text-info"></i> Amex</span>
                        </div>
                        <p class="pay-panel-note"><i class="bi bi-shield-lock-fill text-success"></i>Card details entered on Razorpay's 256-bit SSL page — never stored by us.</p>
                    </div>
                </div>

                {{-- Net Banking Panel --}}
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
                        <p class="pay-panel-note"><i class="bi bi-shield-lock-fill text-success"></i>Select your bank at Razorpay checkout. All major Indian banks supported.</p>
                    </div>
                </div>

                <div class="pay-secure-bar">
                    <i class="bi bi-lock-fill"></i> Payments processed securely via <strong>Razorpay</strong>
                </div>
            </div>

        </form>
    </div>{{-- /LEFT --}}

    {{-- ══════════════ RIGHT COLUMN – Order Summary ══════════════ --}}
    <div class="col-lg-5">
        <div class="ck-summary">
            <div class="ck-summary-head">
                <i class="bi bi-bag-heart-fill"></i> Your Order
            </div>

            <div class="ck-summary-body">

                {{-- Items --}}
                <div class="ck-items-list" id="checkoutItemsList">
                    @forelse(($cartItems ?? []) as $item)
                        <div class="ck-item-row checkout-item-row"
                             data-id="{{ $item['id'] ?? 0 }}"
                             data-price="{{ (float)($item['price'] ?? 0) }}"
                             data-size="{{ $item['size'] ?? '' }}"
                             data-color="{{ $item['color'] ?? '' }}"
                             data-dupatta="{{ isset($item['dupatta']) ? ($item['dupatta'] ? '1' : '0') : '' }}">

                            <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['name'] ?? 'Item' }}" class="ck-item-img">

                            <div class="flex-grow-1 overflow-hidden">
                                <div class="ck-item-name text-truncate">{{ $item['name'] ?? 'Item' }}</div>
                                <div class="ck-item-meta">
                                    {{ $item['size'] ?? 'M' }} · {{ $item['color'] ?? 'Standard' }}
                                    @if(array_key_exists('dupatta', $item)) · {{ $item['dupatta'] ? 'With Dupatta' : 'Without Dupatta' }}@endif
                                </div>
                                <div class="ck-qty">
                                    <button type="button" class="ck-qty-btn checkout-qty-btn" data-dir="-1" data-id="{{ $item['id'] ?? 0 }}" data-size="{{ $item['size'] ?? '' }}" data-color="{{ $item['color'] ?? '' }}">−</button>
                                    <input class="ck-qty-val checkout-qty" value="{{ (int)($item['quantity'] ?? 1) }}" readonly>
                                    <button type="button" class="ck-qty-btn checkout-qty-btn" data-dir="1"  data-id="{{ $item['id'] ?? 0 }}" data-size="{{ $item['size'] ?? '' }}" data-color="{{ $item['color'] ?? '' }}">+</button>
                                </div>
                            </div>

                            <div class="d-flex flex-column align-items-end gap-1">
                                <span class="ck-item-price checkout-line-total">₹{{ ((float)($item['price'] ?? 0)) * ((int)($item['quantity'] ?? 1)) }}</span>
                                <button type="button" class="ck-item-remove checkout-remove-btn" title="Remove item" data-id="{{ $item['id'] ?? 0 }}" data-size="{{ $item['size'] ?? '' }}" data-color="{{ $item['color'] ?? '' }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-bag-x fs-2 d-block mb-2"></i>
                            <p class="small mb-2">Your shopping bag is empty.</p>
                            <a href="{{ route('shop') }}" class="btn btn-sm btn-dark rounded-pill px-4">Explore Collection</a>
                        </div>
                    @endforelse
                </div>

                {{-- Pricing rows --}}
                <div class="ck-sum-rows">
                    <div class="ck-sum-row">
                        <span>Subtotal</span>
                        <span class="val" id="checkoutSubtotal">₹{{ $cartSubtotal ?? 0 }}</span>
                    </div>
                    <div class="ck-sum-row discount" id="checkoutDiscountRow" style="display:none;">
                        <span>Coupon Discount</span>
                        <span class="val" id="checkoutDiscount">-₹0</span>
                    </div>
                    <div class="ck-sum-row">
                        <span>Delivery</span>
                        <span class="val" id="checkoutShipping">{{ ($cartShipping ?? 0) === 0 ? 'FREE' : '₹'.$cartShipping }}</span>
                    </div>
                </div>

                <div class="ck-sum-total">
                    <span class="lbl">Total</span>
                    <span class="amt" id="checkoutTotal">₹{{ $cartTotal ?? 0 }}</span>
                </div>

                {{-- Coupon --}}
                <div class="ck-coupon-wrap">
                    <span class="ck-coupon-lbl"><i class="bi bi-tag-fill me-1"></i>Have a promo code?</span>
                    <div class="ck-coupon-row">
                        <input type="text" id="checkoutCouponInput" class="ck-coupon-input" placeholder="e.g. FIRSTORDER">
                        <button type="button" id="checkoutCouponApplyBtn" class="ck-coupon-btn">Apply</button>
                    </div>
                    <div id="checkoutCouponMsg" class="small mt-2"></div>
                </div>

                {{-- Place Order --}}
                <button type="button" id="placeOrderBtn" class="ck-order-btn">
                    <i class="bi bi-lock-fill me-2"></i> Place Order Securely
                </button>
                <div class="ck-ssl-note">
                    <i class="bi bi-shield-lock-fill text-success"></i> Safe &amp; Secure 256-Bit SSL Checkout
                </div>

            </div>{{-- /body --}}
        </div>{{-- /summary --}}
    </div>{{-- /RIGHT --}}

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
