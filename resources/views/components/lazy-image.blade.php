@props([
    'src',
    'alt' => '',
    'class' => '',
    'style' => '',
    'width' => null,
    'height' => null,
    'loading' => 'lazy',
    'fetchpriority' => 'auto',
])

@php
    // Determine if image is above the fold (should load immediately)
    $isAboveFold = $fetchpriority === 'high' || $loading === 'eager';
    $loadingAttr = $isAboveFold ? 'eager' : 'lazy';
@endphp

<img 
    src="{{ $src }}"
    alt="{{ $alt }}"
    @if($class)class="{{ $class }}"@endif
    @if($style)style="{{ $style }}"@endif
    @if($width)width="{{ $width }}"@endif
    @if($height)height="{{ $height }}"@endif
    loading="{{ $loadingAttr }}"
    @if($fetchpriority !== 'auto')fetchpriority="{{ $fetchpriority }}"@endif
    decoding="async"
    {{ $attributes }}
>

