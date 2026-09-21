@php
    $statusCode = $exception ?? null;
    $code = 500;
    if (($exception ?? null) instanceof \Throwable) {
        $code = method_exists($exception, 'getStatusCode')
            ? (int) $exception->getStatusCode()
            : (int) ($exception->getCode() ?: 500);
    } elseif (isset($statusCode) && is_int($statusCode)) {
        $code = (int) $statusCode;
    }
    if ($code < 100 || $code >= 600) {
        $code = 500;
    }

    $messages = [
        400 => ['Bad Request', 'The request could not be understood. Please try again or head back to the home page.'],
        405 => ['Method Not Allowed', 'This page cannot handle that action. Please head back to the home page.'],
        408 => ['Request Timeout', 'The request took too long. Please try again or head back to the home page.'],
        409 => ['Conflict', 'There was a conflict processing your request. Please go back to the home page.'],
        410 => ['Gone', 'This page no longer exists. Please head back to the home page.'],
        422 => ['Unprocessable Request', 'The request could not be processed. Please go back and try again.'],
        502 => ['Bad Gateway', 'One of our servers had a small hiccup. Please refresh and try again shortly.'],
        504 => ['Gateway Timeout', 'We took too long to respond. Please try again in a moment.'],
    ];
    $message = $messages[$code] ?? ['Unexpected Error', 'An unexpected error occurred. Please try again in a moment.'];

    $fallbackTitle = $message[0];
    $fallbackMessage = $message[1];
@endphp

@extends('layouts.app')

@section('robots', 'noindex, nofollow')
@section('title', $code . ' – Something Went Wrong | ' . config('seo.site_name'))
@section('meta_description', 'Something went wrong while loading this page. Head back to the ZYRA Lifestyle home page.')

@section('content')
    @include('errors._show', [
        'code' => $code,
        'title' => $fallbackTitle,
        'message' => $fallbackMessage,
    ])
@endsection