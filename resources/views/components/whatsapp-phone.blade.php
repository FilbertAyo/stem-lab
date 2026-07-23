@props(['phone' => null])

@php
    $display = filled($phone) ? trim((string) $phone) : null;
    $url = $display ? \App\Support\TanzaniaPhone::whatsappUrl($display) : null;
@endphp

@if($display && $url)
    <a
        href="{{ $url }}"
        target="_blank"
        rel="noopener noreferrer"
        class="text-success text-decoration-none"
        title="Message on WhatsApp"
        {{ $attributes }}
    >
        <i class="ti ti-brand-whatsapp" aria-hidden="true"></i>
        {{ $display }}
    </a>
@elseif($display)
    <span {{ $attributes }}>{{ $display }}</span>
@else
    <span {{ $attributes }}>—</span>
@endif
