@extends('layouts.app')

@section('robots', 'noindex, nofollow')
@section('title', '500 – Server Error | ' . config('seo.site_name'))
@section('meta_description', 'Something went wrong on our side. Your cart is safe — please try again or head back to the ZYRA Lifestyle home page.')

@section('content')
    @include('errors._show', [
        'code' => 500,
        'title' => 'Something went wrong on our end.',
        'message' => 'An unexpected error occurred while loading this page. Your cart and account are safe — please try again in a moment.',
    ])
@endsection