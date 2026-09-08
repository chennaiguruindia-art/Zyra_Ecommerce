@extends('layouts.app')

@section('title', 'My Profile | ZYRA')

@section('content')
<div class="container py-5" style="min-height: 60vh;">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Profile</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mx-auto mb-3 position-relative" style="width: 120px; height: 120px;">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center w-100 h-100">
                            <i class="bi bi-person" style="font-size: 3.5rem; color: #adb5bd;"></i>
                        </div>
                    @endif
                </div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-0">{{ $user->email }}</p>
                @if ($user->role)
                    <span class="badge bg-dark text-uppercase mt-2" style="letter-spacing: 0.5px;">{{ $user->role }}</span>
                @endif
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-4"><i class="bi bi-info-circle me-2"></i>Profile Information</h5>

                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i> Your profile has been updated successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('patch')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="avatar" class="form-label fw-semibold">Profile Picture</label>
                        <input id="avatar" name="avatar" type="file" class="form-control @error('avatar') is-invalid @enderror" accept="image/*" onchange="previewAvatar(this)">
                        <div class="form-text">JPG, PNG, WebP or GIF. Max 2MB.</div>
                        @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        <div class="mt-3" id="avatarPreviewWrap" style="display: none;">
                            <img id="avatarPreview" src="" alt="Preview" style="width: 90px; height: 90px; object-fit: cover; border-radius: 50%; border: 2px solid #e9ecef;">
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-zyra-primary px-4">Save Changes</button>
                        <a href="{{ route('home') }}" class="text-muted text-decoration-none small">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order History -->
    @include('profile.partials.my-orders', ['orders' => $orders])
</div>
@endsection

@push('scripts')
<script>
    function previewAvatar(input) {
        const wrap = document.getElementById('avatarPreviewWrap');
        const preview = document.getElementById('avatarPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                wrap.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            wrap.style.display = 'none';
        }
    }
</script>
@endpush
