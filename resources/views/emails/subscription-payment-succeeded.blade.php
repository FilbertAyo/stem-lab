@extends('emails.layout')

@section('title', 'Payment confirmed')

@section('body')
    <p style="margin: 0 0 16px 0;">Hi {{ $userName }},</p>
    <p style="margin: 0 0 16px 0;">
        Your payment of <strong>{{ $amount }} {{ $currency }}</strong> for the
        <strong>{{ $packageName }}</strong> plan was successful.
    </p>
    <p style="margin: 0 0 16px 0;">
        Your prepaid access is active until <strong>{{ $expiresAt }}</strong>.
    </p>
    <p style="margin: 0 0 24px 0;">
        <a href="{{ $dashboardUrl }}" style="display: inline-block; background: #0f766e; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
            Go to dashboard
        </a>
    </p>
    <p style="margin: 0;">Thank you for supporting Adilisha STEM Labs.</p>
@endsection
