@extends('layouts.app')

@section('robots', 'noindex, nofollow')
@section('title', '404 – Page Not Found | ' . config('seo.site_name'))
@section('meta_description', 'The page you were looking for could not be found. Return to ZYRA Lifestyle and continue shopping.')

@section('content')
    @include('errors._show', [
        'code' => 404,
        'title' => 'Oops. This page wandered off.',
        'message' => 'The page you are looking for may have moved, been renamed, or simply does not exist. Let us get you back to the good stuff.',
    ])
@endsection