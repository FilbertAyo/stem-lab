@extends('emails.layout')

@section('title', 'Plan activated')

@section('body')
    <p style="margin: 0 0 16px 0;">Hi {{ $userName }},</p>
    <p style="margin: 0 0 16px 0;">
        Your <strong>{{ $packageName }}</strong> plan is now active
        @if ($expiresAt)
            until <strong>{{ $expiresAt }}</strong>
        @endif.
    </p>
    <p style="margin: 0 0 24px 0;">
        You can start learning STEM content, competitions, and weekly sessions right away.
    </p>
    <p style="margin: 0 0 24px 0;">
        <a href="{{ $dashboardUrl }}" style="display: inline-block; background: #0f766e; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
            Go to dashboard
        </a>
    </p>
@endsection
