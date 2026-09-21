@extends('layouts.app')

@section('robots', 'noindex, nofollow')
@section('title', '429 – Too Many Requests | ' . config('seo.site_name'))
@section('meta_description', 'You are moving a little too fast. Wait a moment and try again, or head back to the ZYRA Lifestyle home page.')

@section('content')
    @include('errors._show', [
        'code' => 429,
        'title' => 'You are going too fast!',
        'message' => 'Too many requests came in from your connection. Give it a few seconds, then refresh or head back to the home page.',
    ])
@endsection