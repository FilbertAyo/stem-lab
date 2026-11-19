@extends('emails.layout')

@section('title', 'Payment due')

@section('body')
    <p style="margin: 0 0 16px 0;">Hi {{ $userName }},</p>
    <p style="margin: 0 0 16px 0;">
        Your invoice for the <strong>{{ $packageName }}</strong> plan
        (<strong>{{ $periodDays }} days</strong>) is ready.
    </p>
    <p style="margin: 0 0 16px 0;">
        Amount due: <strong>{{ $amount }} {{ $currency }}</strong>
        @if ($expiresAt)
            — pay by <strong>{{ $expiresAt }}</strong>
        @endif
    </p>
    <p style="margin: 0 0 24px 0;">
        <a href="{{ $payUrl }}" style="display: inline-block; background: #0f766e; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
            Pay now
        </a>
    </p>
    <p style="margin: 0;">If you already paid, you can ignore this email — access activates automatically.</p>
@endsection
