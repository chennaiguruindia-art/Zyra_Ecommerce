@extends('layouts.app')

@section('title', 'Contact Us | ZYRA Concierge & Support')
@section('meta_description', 'Get in touch with the ZYRA customer concierge for order inquiries, sizing assistance, exchange requests, and styling consultations.')

@section('content')

<x-breadcrumb :items="[['label' => 'Contact Us', 'url' => '']]" />

<div class="container py-5">
    
    <div class="row mb-5 text-center">
        <div class="col-lg-8 mx-auto">
            <span class="text-uppercase small fw-bold text-muted">We're Here For You</span>
            <h1 class="display-6 fw-bold mt-1 mb-3">Get In Touch With ZYRA</h1>
            <p class="text-muted fs-6">
                Have questions about sizing, styling recommendations, order tracking, or bulk inquiries? Our personal styling concierge team is always delighted to assist.
            </p>
        </div>
    </div>

    <div class="row g-5">
        
        <!-- Left: Contact Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4 bg-white">
                <h4 class="fw-bold mb-4">Send Us a Message</h4>
                
                <form id="zyraContactForm" novalidate>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="contactName" class="form-label small fw-semibold">Your Full Name *</label>
                            <input type="text" id="contactName" class="form-control" placeholder="e.g. Radhika Iyer" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="contactEmail" class="form-label small fw-semibold">Email Address *</label>
                            <input type="email" id="contactEmail" class="form-control" placeholder="e.g. radhika@example.com" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="contactPhone" class="form-label small fw-semibold">Phone Number</label>
                            <input type="tel" id="contactPhone" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-sm-6">
                            <label for="contactSubject" class="form-label small fw-semibold">Subject *</label>
                            <select id="contactSubject" class="form-select" required>
                                <option value="" selected disabled>Select an inquiry topic</option>
                                <option value="order">Order Status & Tracking</option>
                                <option value="return">Return & Exchange Request</option>
                                <option value="size">Size & Styling Advice</option>
                                <option value="general">General Inquiries</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="contactMessage" class="form-label small fw-semibold">Your Message *</label>
                            <textarea id="contactMessage" rows="5" class="form-control" placeholder="Tell us how we can help..." required></textarea>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-zyra-primary px-4 py-2">
                                Send Message <i class="bi bi-send ms-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Store Details & Channels -->
        <div class="col-lg-5">
            <div class="d-flex flex-column gap-4">
                
                <div class="card border-0 shadow-sm p-4 rounded-4" style="background-color: var(--zyra-bg-beige);">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-white p-3 text-dark shadow-sm">
                            <i class="bi bi-geo-alt fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Studio & Headquarters</h6>
                            <p class="small text-muted mb-0 leading-relaxed">
                                ZYRA Fashion Private Limited<br>
                                142, 100ft Road, HAL 2nd Stage,<br>
                                Indiranagar, Bengaluru, Karnataka - 560038
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4 rounded-4" style="background-color: var(--zyra-bg-beige);">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-white p-3 text-dark shadow-sm">
                            <i class="bi bi-telephone fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Phone & WhatsApp Support</h6>
                            <p class="small text-muted mb-1">Direct Line: +91 9884125555</p>
                            <p class="small text-muted mb-0">WhatsApp Concierge: +91 98765 43211</p>
                            <span class="badge bg-success-subtle text-success border border-success-subtle mt-2">Available 9 AM – 8 PM IST</span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4 rounded-4" style="background-color: var(--zyra-bg-beige);">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-white p-3 text-dark shadow-sm">
                            <i class="bi bi-envelope fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email Inquiries</h6>
                            <p class="small text-muted mb-1">Customer Care: <strong>order@shipwithzyra.in</strong></p>
                            <p class="small text-muted mb-0">Press & Partnerships: <strong>press@zyrafashion.com</strong></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
