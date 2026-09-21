@extends('layouts.app')

@section('robots', 'noindex, nofollow')
@section('title', '403 – Access Denied | ' . config('seo.site_name'))
@section('meta_description', 'You do not have permission to view this page. Return to the ZYRA Lifestyle home page.')

@section('content')
    @include('errors._show', [
        'code' => 403,
        'title' => 'This area is off limits.',
        'message' => 'You do not have permission to view this page. If you believe this is a mistake, please go back to the home page and try again.',
    ])
@endsection