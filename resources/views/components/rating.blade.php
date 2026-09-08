@props(['rating' => 5, 'reviews' => null])

@php
    $full = (int) round($rating);
    $empty = 5 - $full;
@endphp

<div class="zyra-product-rating">
    <span class="text-warning">{!! str_repeat('★', $full) . str_repeat('☆', $empty) !!}</span>
    @if($reviews !== null)
        <span class="review-count">({{ $reviews }} reviews)</span>
    @endif
</div>
