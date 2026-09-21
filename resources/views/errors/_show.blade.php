<section class="zyra-error-section" style="min-height: 68vh; display: flex; align-items: center; padding: 4rem 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9 text-center">

                <div class="position-relative" style="display:inline-block;">
                    <span class="fw-bold lh-1" aria-hidden="true"
                          style="font-family: 'Playfair Display', serif; font-size: clamp(7rem, 20vw, 12rem); color: rgba(141, 90, 90, 0.12);">{{ $code ?? 500 }}</span>
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <svg width="170" height="145" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Fashion illustration">
                            <path d="M70 42 C70 27 100 22 100 22 C100 22 130 27 130 42"
                                  stroke="#8d5a5a" stroke-width="6" stroke-linecap="round" />
                            <line x1="100" y1="42" x2="100" y2="72" stroke="#8d5a5a" stroke-width="6" stroke-linecap="round" />
                            <path d="M100 84 C84 78 70 76 66 76 L72 130 L128 130 L134 76 C130 76 116 78 100 84 Z"
                                  fill="#f7ede7" stroke="#8d5a5a" stroke-width="5" stroke-linejoin="round" />
                            <path d="M78 130 L100 112 L122 130" stroke="#c96f6f" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                            <path d="M100 102 C100 98 103 94 108 94 C113 94 115 99 115 102 C115 109 100 118 100 118 C100 118 85 109 85 102 C85 99 87 94 92 94 C97 94 100 98 100 102 Z"
                                  fill="#c96f6f" />
                        </svg>
                    </div>
                </div>

                <h1 class="fw-semibold mt-3" style="font-family: 'Cormorant Garamond', serif; color: #2b2323;">
                    {{ $title ?? 'Something went wrong.' }}
                </h1>
                <p class="text-muted mx-auto mb-4" style="max-width: 520px; font-size: 1.05rem;">
                    {{ $message ?? 'An unexpected error occurred while loading this page. Please try again in a moment.' }}
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ url('/') }}" class="btn btn-lg px-4" style="background:#18181b; color:#fff; border-radius:40px; font-weight:600;">
                        <i class="bi bi-house-door-fill me-2"></i>Back to Home
                    </a>
                    <a href="{{ url('/shop') }}" class="btn btn-lg btn-outline-dark px-4" style="border-radius:40px; font-weight:600;">
                        Continue Shopping <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>