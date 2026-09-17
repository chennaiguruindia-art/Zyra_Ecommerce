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
            'answer' => "We have a 30-day return policy. To be eligible for a return, your item must be unused, unworn, with tags, and in original packaging along with proof of purchase. Contact us at order@shopwithzyra.in to start a return. At Zyra we do not offer returns for products once sold and delivered, but we do provide size exchanges for eligible domestic orders raised within 24-48 hours of delivery, provided the product is unused, unwashed, unworn, and in original condition with tags, packing and accessories intact."
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

                            <p class="fw-bold text-dark">Return Policy</p>
                            <p>We have a <strong>30-day return policy</strong>, which means you have 30 days after receiving your item to request a return.</p>
                            <p>To be eligible for a return, your item must be in the same condition that you received it, unworn or unused, with tags, and in its original packaging. You'll also need the receipt or proof of purchase.</p>
                            <p>To start a return, you can contact us at <strong><a href="mailto:order@shopwithzyra.in" class="text-decoration-underline">order@shopwithzyra.in</a></strong>. Please note that returns will need to be sent to the following address: <em>[INSERT RETURN ADDRESS]</em></p>
                            <p>If your return is accepted, we'll send you a return shipping label, as well as instructions on how and where to send your package. Items sent back to us without first requesting a return will not be accepted.</p>
                            <p>You can always contact us for any return question at <strong><a href="mailto:order@shopwithzyra.in" class="text-decoration-underline">order@shopwithzyra.in</a></strong>.</p>

                            <p class="fw-bold text-dark mt-3">Damages and Issues</p>
                            <p>Please inspect your order upon reception and contact us immediately if the item is defective, damaged or if you receive the wrong item, so that we can evaluate the issue and make it right.</p>

                            <p class="fw-bold text-dark mt-3">Exceptions / Non-Returnable Items</p>
                            <p>Certain types of items cannot be returned, like perishable goods (such as food, flowers, or plants), custom products (such as special orders or personalized items), and personal care goods (such as beauty products). We also do not accept returns for hazardous materials, flammable liquids, or gases. Please get in touch if you have questions or concerns about your specific item.</p>
                            <p>Unfortunately, we cannot accept returns on sale items or gift cards.</p>

                            <p class="fw-bold text-dark mt-3">Exchanges</p>
                            <p>The fastest way to ensure you get what you want is to return the item you have, and once the return is accepted, make a separate purchase for the new item.</p>

                            <p class="fw-bold text-dark mt-3">European Union 14-Day Cooling Off Period</p>
                            <p>Notwithstanding the above, if the merchandise is being shipped into the European Union, you have the right to cancel or return your order within 14 days, for any reason and without a justification. As above, your item must be in the same condition that you received it, unworn or unused, with tags, and in its original packaging. You'll also need the receipt or proof of purchase.</p>

                            <p class="fw-bold text-dark mt-3">Refunds</p>
                            <p>We will notify you once we've received and inspected your return, and let you know if the refund was approved or not. If approved, you'll be automatically refunded on your original payment method within <strong>10 business days</strong>. Please remember it can take some time for your bank or credit card company to process and post the refund too.</p>
                            <p>If more than <strong>15 business days</strong> have passed since we've approved your return, please contact us at <strong><a href="mailto:order@shopwithzyra.in" class="text-decoration-underline">order@shopwithzyra.in</a></strong>.</p>

                            <hr>

                            <p class="fw-bold text-dark">Returns &amp; Exchange — ZYRA Policy</p>
                            <p>At Zyra, we strive to provide premium-quality products and a seamless shopping experience for our customers worldwide. Every product listing is carefully curated with detailed descriptions, sizing information, fabric details, and care instructions to help customers make informed purchasing decisions. All orders undergo multiple quality inspections before dispatch to ensure they reach you in excellent condition. Please read our policy carefully before placing order.</p>
                            <ol class="mb-0">
                                <li><strong>Return Policy.</strong> At this time, we do not offer returns for products once sold and delivered. However, we do provide <strong>size exchanges</strong> for eligible domestic orders subject to the conditions mentioned below.</li>
                                <li><strong>Exchange Eligibility.</strong>
                                    <ul class="mt-1">
                                        <li>The exchange request must be raised within <strong>24–48 hours of delivery</strong>.</li>
                                        <li>The product must be unused, unwashed, unworn, free from stains, perfume, makeup or damage.</li>
                                        <li>Returned in original condition with tags, packing &amp; accessories (if any) intact.</li>
                                        <li>Products purchased during SALE, promotional campaigns, or using discount/coupon codes are not eligible for exchange, return, or refund.</li>
                                    </ul>
                                </li>
                            </ol>

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