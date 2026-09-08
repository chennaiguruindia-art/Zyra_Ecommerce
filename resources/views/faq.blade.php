@extends('layouts.app')

@section('title', 'Frequently Asked Questions (FAQ) | ZYRA Fashion')
@section('meta_description', 'Find instant answers to common questions regarding shipping, returns, order cancellation, sizing, payments, and discounts.')

@section('content')

<x-breadcrumb :items="[['label' => 'FAQ', 'url' => '']]" />

<div class="container py-5">
    
    <div class="row mb-5 text-center">
        <div class="col-lg-8 mx-auto">
            <span class="text-uppercase small fw-bold text-muted">Help & Information</span>
            <h1 class="display-6 fw-bold mt-1 mb-2">Frequently Asked Questions</h1>
            <p class="text-muted">Everything you need to know about shopping, delivery, exchanges, and sizing at ZYRA.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <div class="accordion zyra-accordion" id="zyraFaqAccordion">
                
                <!-- 1. Shipping -->
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="headingShipping">
                        <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShipping" aria-expanded="true" aria-controls="collapseShipping">
                            <i class="bi bi-truck me-2 text-dark"></i> How long does delivery take and what are the shipping fees?
                        </button>
                    </h2>
                    <div id="collapseShipping" class="accordion-collapse collapse show" aria-labelledby="headingShipping" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            We offer <strong>Free Standard Shipping</strong> across India on all orders above ₹999. For orders below ₹999, a flat shipping rate of ₹99 applies.<br><br>
                            &bull; <strong>Metro Cities (Delhi, Mumbai, Bengaluru, Chennai, Kolkata, Hyderabad):</strong> 2–4 business days.<br>
                            &bull; <strong>Rest of India:</strong> 4–7 business days.<br>
                            Tracking links are dispatched via SMS & Email as soon as your order leaves our Mumbai warehouse.
                        </div>
                    </div>
                </div>

                <!-- 2. Returns & Exchange -->
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="headingReturns">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReturns" aria-expanded="false" aria-controls="collapseReturns">
                            <i class="bi bi-arrow-repeat me-2 text-dark"></i> What is ZYRA's Return and Exchange policy?
                        </button>
                    </h2>
                    <div id="collapseReturns" class="accordion-collapse collapse" aria-labelledby="headingReturns" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            We provide a seamless <strong>15-Day Doorstep Exchange & Return Policy</strong>. If an item doesn't fit or meet your expectation, simply WhatsApp our concierge team at <strong>+91 98765 43211</strong> or email <strong>care@zyrafashion.com</strong> with your Order ID.<br><br>
                            We will arrange a free reverse pickup from your doorstep. Please ensure the tags remain attached and items are unworn and unwashed.
                        </div>
                    </div>
                </div>

                <!-- 3. Payments & COD -->
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="headingPayments">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayments" aria-expanded="false" aria-controls="collapsePayments">
                            <i class="bi bi-credit-card me-2 text-dark"></i> What payment options are accepted? Is COD available?
                        </button>
                    </h2>
                    <div id="collapsePayments" class="accordion-collapse collapse" aria-labelledby="headingPayments" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            Yes! We accept <strong>Cash on Delivery (COD)</strong> across 19,000+ PIN codes in India at no additional surcharge.<br><br>
                            We also accept all major prepaid payment modes including:
                            <ul class="mb-0 mt-2">
                                <li>Instant UPI (Google Pay, PhonePe, Paytm, BHIM)</li>
                                <li>Credit and Debit Cards (Visa, MasterCard, RuPay, Maestro)</li>
                                <li>Net Banking across 50+ major Indian banks</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 4. Sizing -->
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="headingSizing">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSizing" aria-expanded="false" aria-controls="collapseSizing">
                            <i class="bi bi-rulers me-2 text-dark"></i> How do I choose the correct size?
                        </button>
                    </h2>
                    <div id="collapseSizing" class="accordion-collapse collapse" aria-labelledby="headingSizing" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            Our garments are tailored according to standard Indian women's sizing from XS (32) to XXL (42). You can click the <strong>"Size Chart"</strong> button on any product details page for exact bust, waist, and hip measurements in inches.<br><br>
                            If you find yourself in between two sizes, we generally recommend selecting the larger size for relaxed everyday comfort.
                        </div>
                    </div>
                </div>

                <!-- 5. Order Cancellation -->
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="headingCancellation">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCancellation" aria-expanded="false" aria-controls="collapseCancellation">
                            <i class="bi bi-x-octagon me-2 text-dark"></i> Can I modify or cancel my order after placing it?
                        </button>
                    </h2>
                    <div id="collapseCancellation" class="accordion-collapse collapse" aria-labelledby="headingCancellation" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            Orders can be cancelled or modified within <strong>4 hours</strong> of placing the order before our fulfillment warehouse begins packaging. Simply contact customer care or message us on WhatsApp with your Order ID for instant cancellation and 100% refund.
                        </div>
                    </div>
                </div>

                <!-- 6. Coupons -->
                <div class="accordion-item border rounded-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="headingCoupons">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCoupons" aria-expanded="false" aria-controls="collapseCoupons">
                            <i class="bi bi-tag me-2 text-dark"></i> How do I apply promo discount coupons?
                        </button>
                    </h2>
                    <div id="collapseCoupons" class="accordion-collapse collapse" aria-labelledby="headingCoupons" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            You can enter coupon codes like <strong>WELCOME10</strong> (10% OFF), <strong>SAVE20</strong> (20% OFF), or <strong>FASHION15</strong> (15% OFF) in the coupon code field located in your Shopping Bag page. The discount will instantly be deducted from your payable total!
                        </div>
                    </div>
                </div>

            </div>

            <!-- Still Have Questions Box -->
            <div class="p-4 bg-light rounded-4 text-center mt-5">
                <h5 class="fw-bold mb-2">Still Have Questions?</h5>
                <p class="text-muted small mb-3">Our concierge styling team is available to assist you 6 days a week.</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('contact') }}" class="btn btn-sm btn-zyra-primary">Contact Support</a>
                    <a href="https://wa.me/919876543210" target="_blank" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-whatsapp me-1 text-success"></i> WhatsApp Us
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>

@endsection
