@extends('layouts.app')

@section('title', 'Fashion Gallery | ZYRA')

@section('content')
<section class="ig-hero py-4" style="background:#faf7f5; border-bottom:1px solid #eee;">
    <div class="container">
        <h1 class="fw-bold">Fashion Gallery <span class="text-muted fw-normal fs-5">/ Instagram Feed</span></h1>
        <p class="text-muted mb-0">
            Paste an Instagram post, reel, or TV link below. Photos and videos render automatically in the gallery
            (videos appear as covered previews and play inline when clicked).
        </p>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <form method="POST" action="{{ route('instagram.store') }}" class="d-flex gap-2 mb-3" style="max-width:640px;">
            @csrf
            @if (session('instagram_status') === 'added')
                <div class="alert alert-success py-1 px-3 mb-1" style="font-size:.85rem;">Link added successfully.</div>
            @elseif (session('instagram_status') === 'already')
                <div class="alert alert-warning py-1 px-3 mb-1" style="font-size:.85rem;">This link is already in the gallery.</div>
            @elseif (session('instagram_status') === 'removed')
                <div class="alert alert-info py-1 px-3 mb-1" style="font-size:.85rem;">Link removed from the gallery.</div>
            @endif
            <input type="url" name="url" class="form-control" placeholder="https://www.instagram.com/p/..." value="{{ old('url') }}" required>
            <button type="submit" class="btn btn-dark text-nowrap">Add to Gallery</button>
        </form>
        @error('url')
            <div class="alert alert-danger py-1 px-3 mb-3" style="font-size:.85rem;">{{ $message }}</div>
        @enderror

        <x-instagram-gallery :items="$items" :show-heading="false" :show-delete="true" />
    </div>
</section>
@endsection