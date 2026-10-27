@extends('layouts.app')

@section('title', 'Frequently Asked Questions (FAQ) | ZYRA Lifestyle')
@section('meta_description', 'Find instant answers to common questions regarding shipping, returns, exchange, order modification, sizing, payments, and discounts at ZYRA Lifestyle.')

@push('schema')
    {!! \App\Support\Seo::breadcrumbSchema([
        ['name' => 'Home', 'url' => \App\Support\Seo::url('/')],
        ['name' => 'FAQ'],
    ]) !!}
    {!! \App\Support\Seo::faqSchema([
        [
            'question' => 'Where do you ship and is shipping free?',
            'answer' => 'We ship across India. Shipping is completely free inside Tamil Nadu on all orders.'
        ],
        [
            'question' => "What is ZYRA's Return and Exchange policy?",
            'answer' => "We do not offer cash refunds. If you ordered the wrong size or the wrong item, we will exchange it for eligible domestic orders raised within 24-48 hours of delivery, provided the product is unused, unwashed, unworn and in original condition with tags, packing and accessories intact. Exchanges are settled by adjusting the value of what you send back against what you receive: you pay the difference on a higher-priced item, and any difference on a lower-priced item is not refunded. Purchases made during a sale, a promotion, or with a discount or coupon code are not eligible for exchange, return or refund."
        ],
        [
            'question' => 'Is Cash on Delivery (COD) available?',
            'answer' => 'Currently, Cash on Delivery (COD) is not available. It will be available very soon.'
        ],
        [
            'question' => 'How do I choose the correct size?',
            'answer' => "Our garments are tailored according to standard Indian women's sizing from XS (32) to XXL (42). Click the 'Size Chart' button on any product details page for exact bust, waist, and hip measurements in inches."
        ],
        [
            'question' => 'Can I modify my order after placing it?',
            'answer' => 'Yes. Orders can be modified within 2 hours of placing them. Simply call or email our customer service team and we will help you.'
        ],
        [
            'question' => 'How do I get promo discount coupons?',
            'answer' => 'Promo coupons are announced on our social media pages. Keep an eye on our social handles once we update them for a limited time.'
        ]
    ]) !!}
@endpush

@section('content')

<x-breadcrumb :items="[['label' => 'FAQ', 'url' => '']]" />

<div class="container py-5">
    
    <div class="row mb-5 text-center">
        <div class="col-lg-8 mx-auto">
            <span class="text-uppercase small fw-bold text-muted">Help & Information</span>
            <h1 class="display-6 fw-bold mt-1 mb-2">Frequently Asked Questions</h1>
            <p class="text-muted">Everything you need to know about shipping, delivery, returns, exchanges, and sizing at ZYRA.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <div class="accordion zyra-accordion" id="zyraFaqAccordion">
                
                <!-- 1. Shipping -->
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm" id="shipping">
                    <h2 class="accordion-header" id="headingShipping">
                        <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShipping" aria-expanded="true" aria-controls="collapseShipping">
                            <i class="bi bi-truck me-2 text-dark"></i> Where do you ship and is shipping free?
                        </button>
                    </h2>
                    <div id="collapseShipping" class="accordion-collapse collapse show" aria-labelledby="headingShipping" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            We ship <strong>across India</strong> to all states and union territories.<br><br>
                            &bull; <strong>Free Shipping:</strong> Shipping is completely free inside <strong>Tamil Nadu</strong> on all orders.
                        </div>
                    </div>
                </div>

                <!-- 2. Returns & Exchange -->
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm" id="returns">
                    <h2 class="accordion-header" id="headingReturns">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReturns" aria-expanded="false" aria-controls="collapseReturns">
                            <i class="bi bi-arrow-repeat me-2 text-dark"></i> What is ZYRA's Return and Exchange policy?
                        </button>
                    </h2>
                    <div id="collapseReturns" class="accordion-collapse collapse" aria-labelledby="headingReturns" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">

                            <div class="alert alert-light border-start border-warning border-3 rounded-3 mb-3">
                                <p class="fw-bold text-dark mb-1"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> The short version</p>
                                <p class="mb-0"><strong>We do not offer cash refunds of any kind.</strong> If you ordered the wrong size or the wrong item, we will exchange it for you — but the money does not come back, by cash, card, UPI or bank transfer.</p>
                            </div>

                            <p class="fw-bold text-dark">What You Can Ask For</p>
                            <ul class="mb-0">
                                <li><strong>A size exchange</strong> — the wrong size for the right one, on eligible domestic orders.</li>
                                <li><strong>An item exchange</strong> — a different product, of equal or higher value.</li>
                            </ul>

                            <p class="fw-bold text-dark mt-3">How the Price Difference Works</p>
                            <p>An exchange is settled by adjusting the value of what you send back against what you receive. No part of the purchase price is returned to you.</p>
                            <ul>
                                <li><strong>Exchanging for a higher-priced item?</strong> You pay the difference before the replacement is dispatched.</li>
                                <li><strong>Exchanging for a lower-priced item?</strong> The difference is <strong>forfeited</strong> — it is not refunded and not carried forward as store credit.</li>
                            </ul>
                            <p class="mb-0">For example, suppose you buy a product for <strong>₹10</strong> and realise afterwards that it is the wrong choice. You may still exchange it. A <strong>₹10</strong> replacement settles the exchange at exactly nothing more to pay. A <strong>₹15</strong> replacement means paying <strong>₹5</strong> more. A <strong>₹8</strong> replacement leaves <strong>₹2 with us — and that ₹2 is not refunded</strong>.</p>

                            <p class="fw-bold text-dark mt-3">Exchange Eligibility</p>
                            <ul class="mb-0">
                                <li>The exchange request must be raised within <strong>24–48 hours of delivery</strong>.</li>
                                <li>The product must be unused, unwashed, unworn, and free from stains, perfume, makeup or damage.</li>
                                <li>Returned in original condition with tags, packing &amp; accessories (if any) intact.</li>
                                <li>Products purchased during a <strong>SALE</strong>, a promotional campaign, or using a discount or coupon code are <strong>not eligible</strong> for exchange, return or refund.</li>
                            </ul>

                            <p class="fw-bold text-dark mt-3">How to Start an Exchange</p>
                            <p>Email <strong><a href="mailto:order@shopwithzyra.in" class="text-decoration-underline">order@shopwithzyra.in</a></strong> or call <strong><a href="tel:+919884125555" class="text-decoration-underline">+91 98841 25555</a></strong> with your Order ID, within the window above. We will confirm whether the item qualifies and send you the <strong>return address and packing instructions</strong> at that point. Items sent back without first requesting an exchange will not be accepted, and you are responsible for getting the parcel to us safely.</p>

                            <p class="fw-bold text-dark mt-3">Damages and Wrong Items Received</p>
                            <p class="mb-0">Please inspect your order as soon as it arrives. If an item reaches you defective, damaged, or simply not what you ordered, contact us immediately and we will put it right. That case is <strong>not</strong> treated as an exchange, and there is no cost to you.</p>

                            <p class="fw-bold text-dark mt-3">What Cannot Be Exchanged</p>
                            <ul class="mb-0">
                                <li>Sale items, and anything bought with a discount or coupon code.</li>
                                <li>Items that have been worn, washed, altered or damaged after delivery.</li>
                                <li>Items raised for exchange after the 24–48 hour window has closed.</li>
                            </ul>

                        </div>
                    </div>
                </div>

                <!-- 3. Cash on Delivery -->
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="headingCod">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCod" aria-expanded="false" aria-controls="collapseCod">
                            <i class="bi bi-credit-card me-2 text-dark"></i> Is Cash on Delivery (COD) available?
                        </button>
                    </h2>
                    <div id="collapseCod" class="accordion-collapse collapse" aria-labelledby="headingCod" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            Currently, <strong>Cash on Delivery (COD)</strong> is not available. <br><br>We are working on it — it will be available <strong>very soon</strong>. Keep an eye on our social media pages for updates.
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

                <!-- 5. Order Modification -->
                <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="headingModification">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModification" aria-expanded="false" aria-controls="collapseModification">
                            <i class="bi bi-x-octagon me-2 text-dark"></i> Can I modify my order after placing it?
                        </button>
                    </h2>
                    <div id="collapseModification" class="accordion-collapse collapse" aria-labelledby="headingModification" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            Yes! If you have placed an order and wish to modify it, you can do so within <strong>2 hours</strong> of placing your order.<br><br>
                            Simply <strong>call</strong> or <strong>email</strong> our customer service team with your Order ID and we will be happy to help you.
                        </div>
                    </div>
                </div>

                <!-- 6. Coupons -->
                <div class="accordion-item border rounded-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="headingCoupons">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCoupons" aria-expanded="false" aria-controls="collapseCoupons">
                            <i class="bi bi-tag me-2 text-dark"></i> How do I get promo discount coupons?
                        </button>
                    </h2>
                    <div id="collapseCoupons" class="accordion-collapse collapse" aria-labelledby="headingCoupons" data-bs-parent="#zyraFaqAccordion">
                        <div class="accordion-body text-muted small leading-relaxed">
                            Promo coupons are announced on our <strong>social media pages</strong>.<br><br>
                            Keep an eye on our social handles — new coupon codes are shared once we update them for a limited time.
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
                    <a href="mailto:order@shopwithzyra.in" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-envelope me-1"></i> Email Us
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>

@endsection