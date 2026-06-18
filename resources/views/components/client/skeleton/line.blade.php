@props([
    'width' => 'col-8',
    'class' => '',
    'height' => null,
])

<span
    {{ $attributes->merge(['class' => 'placeholder rounded ' . $width . ' ' . $class]) }}
    @if($height) style="height: {{ $height }};" @endif
    aria-hidden="true"
></span>
