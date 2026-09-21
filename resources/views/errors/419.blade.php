@extends('layouts.app')

@section('robots', 'noindex, nofollow')
@section('title', '419 – Page Expired | ' . config('seo.site_name'))
@section('meta_description', 'Your session expired. Refresh and try again, or head back to the ZYRA Lifestyle home page.')

@section('content')
    @include('errors._show', [
        'code' => 419,
        'title' => 'Your session took a coffee break.',
        'message' => 'If you were in the middle of placing an order, it is safe — nothing was charged. Head back to the home page and try again.',
    ])
@endsection