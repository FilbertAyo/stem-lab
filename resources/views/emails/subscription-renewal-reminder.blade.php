@extends('emails.layout')

@section('title', 'Plan expiring soon')

@section('body')
    <p style="margin: 0 0 16px 0;">Hi {{ $userName }},</p>
    <p style="margin: 0 0 16px 0;">
        Your Adilisha STEM Labs prepaid plan expires on <strong>{{ $expiresAt }}</strong>.
    </p>
    <p style="margin: 0 0 24px 0;">
        Renew with mobile money to keep uninterrupted access to STEM learning content.
    </p>
    <p style="margin: 0 0 24px 0;">
        <a href="{{ $dashboardUrl }}" style="display: inline-block; background: #0f766e; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
            Renew your plan
        </a>
    </p>
@endsection
