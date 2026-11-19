@extends('emails.layout')

@section('title', 'Plan expired')

@section('body')
    <p style="margin: 0 0 16px 0;">Hi {{ $userName }},</p>
    <p style="margin: 0 0 16px 0;">
        Your prepaid Adilisha STEM Labs plan has expired. Learning content is paused until you renew
        or link a public-school STEM club for free access.
    </p>
    <p style="margin: 0 0 24px 0;">
        <a href="{{ $dashboardUrl }}" style="display: inline-block; background: #0f766e; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
            Choose a package
        </a>
    </p>
@endsection
