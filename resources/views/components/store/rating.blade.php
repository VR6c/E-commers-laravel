@props([
    'rating' => 0,
    'count' => null,
])

@php
    $score = (float) $rating;
    $fullStars = (int) floor($score);
    $halfStar = ($score - $fullStars) >= 0.5;
    $emptyStars = max(0, 5 - $fullStars - ($halfStar ? 1 : 0));
@endphp

<div {{ $attributes->merge(['class' => 'xsf-rating']) }} aria-label="{{ $score }} out of 5 stars">
    <div class="xsf-rating__stars">
        @for ($i = 0; $i < $fullStars; $i++)
            <i class="fa-solid fa-star" aria-hidden="true"></i>
        @endfor
        @if ($halfStar)
            <i class="fa-solid fa-star-half-stroke" aria-hidden="true"></i>
        @endif
        @for ($i = 0; $i < $emptyStars; $i++)
            <i class="fa-regular fa-star is-empty" aria-hidden="true"></i>
        @endfor
    </div>
    @if ($count !== null)
        <span class="xsf-rating__count">({{ $count }})</span>
    @endif
</div>
