@extends('layouts.app')

@section('robots', 'noindex, nofollow')
@section('title', '503 – Be Right Back | ' . config('seo.site_name'))
@section('meta_description', 'ZYRA Lifestyle is taking a short break for maintenance. Please check back in a few minutes.')

@section('content')
    @include('errors._show', [
        'code' => 503,
        'title' => 'We will be right back.',
        'message' => 'ZYRA Lifestyle is undergoing a quick maintenance session. We will be back online in just a few minutes.',
    ])
@endsection