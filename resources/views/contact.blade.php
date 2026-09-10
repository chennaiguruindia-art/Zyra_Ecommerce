@extends('layouts.app')

@section('title', 'Contact Us | ZYRA Lifestyle Support & Concierge')
@section('meta_description', 'Get in touch with the ZYRA Lifestyle customer concierge for order inquiries, sizing assistance, exchange requests, and styling consultations.')

@push('schema')
    {!! \App\Support\Seo::organizationSchema() !!}
    {!! \App\Support\Seo::localBusinessSchema() !!}
    {!! \App\Support\Seo::breadcrumbSchema([
        ['name' => 'Home', 'url' => \App\Support\Seo::url('/')],
        ['name' => 'Contact Us'],
    ]) !!}
@endpush

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

                @if (session('query_submitted'))
                    <div class="alert alert-success border-0 shadow-sm">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Thank you! Your query has been submitted. Our team will reach out to you shortly.
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="zyraContactForm" method="POST" action="{{ route('contact.submit') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="orderId" class="form-label small fw-semibold">Order ID (if applicable)</label>
                            <input type="text" name="order_id" id="orderId" class="form-control" placeholder="e.g. ZYRA-ABC123" value="{{ old('order_id') }}">
                        </div>
                        <div class="col-sm-6">
                            <label for="contactName" class="form-label small fw-semibold">Your Full Name *</label>
                            <input type="text" name="name" id="contactName" class="form-control" placeholder="e.g. Radhika Iyer" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="contactEmail" class="form-label small fw-semibold">Email Address *</label>
                            <input type="email" name="email" id="contactEmail" class="form-control" placeholder="e.g. radhika@example.com" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="contactPhone" class="form-label small fw-semibold">Phone Number *</label>
                            <input type="tel" name="phone_number" id="contactPhone" class="form-control" placeholder="10-digit mobile number" value="{{ old('phone_number') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="queryStatus" class="form-label small fw-semibold">Query Status *</label>
                            <select name="query_status" id="queryStatus" class="form-select" required>
                                <option value="" selected disabled>Select the inquiry type</option>
                                <option value="order" {{ old('query_status') == 'order' ? 'selected' : '' }}>Order Status & Tracking</option>
                                <option value="return" {{ old('query_status') == 'return' ? 'selected' : '' }}>Return & Exchange Request</option>
                                <option value="size" {{ old('query_status') == 'size' ? 'selected' : '' }}>Size & Styling Advice</option>
                                <option value="general" {{ old('query_status') == 'general' ? 'selected' : '' }}>General Inquiries</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="querySubject" class="form-label small fw-semibold">Query Subject *</label>
                            <input type="text" name="query_subject" id="querySubject" class="form-control" placeholder="Brief subject of your query" value="{{ old('query_subject') }}" required>
                        </div>
                        <div class="col-12">
                            <label for="contactMessage" class="form-label small fw-semibold">Your Message</label>
                            <textarea name="message" id="contactMessage" rows="5" class="form-control" placeholder="Tell us how we can help...">{{ old('message') }}</textarea>
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
                            <h6 class="fw-bold mb-1">Zyra Lifestyle</h6>
                            <p class="small text-muted mb-0 leading-relaxed">
                                ZYRA Fashion Private Limited<br>
                                1st Floor, F 200, 1st St, Block F,<br>
                                Annanagar East, Chennai, Tamil Nadu 600102
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
                            <p class="small text-muted mb-0">WhatsApp Concierge: +91 9884125555</p>
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
                            <p class="small text-muted mb-0">Press & Partnerships: <strong>hello@shipwithzyra.in</strong></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
