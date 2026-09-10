@extends('layouts.app')

@section('title', 'My Orders | ZYRA')
@section('robots', 'noindex, nofollow')

@section('content')
<div class="container py-5" style="min-height: 60vh;">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profile.edit') }}" class="text-decoration-none">My Profile</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Orders</li>
        </ol>
    </nav>

    @include('profile.partials.my-orders', ['orders' => $orders, 'wrapClass' => 'row', 'accordionSuffix' => 'OrdersPage'])
</div>
@endsection