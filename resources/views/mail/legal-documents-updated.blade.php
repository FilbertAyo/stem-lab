@extends('emails.layout')

@section('title', 'Privacy Policy and Terms updated')

@section('body')
    <p style="margin: 0 0 16px 0;">Hello {{ $user->name }},</p>
    <p style="margin: 0 0 20px 0;">We have updated our <strong>Privacy Policy</strong> and/or <strong>Terms &amp; Conditions</strong>. Please review the latest versions on our website.</p>
    <p style="margin: 0 0 12px 0;">
        <a href="{{ url('/privacy-policy') }}" style="color: #0d6efd;">View Privacy Policy</a>
    </p>
    <p style="margin: 0 0 24px 0;">
        <a href="{{ url('/terms-conditions') }}" style="color: #0d6efd;">View Terms &amp; Conditions</a>
    </p>
    <p style="margin: 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">Thanks,<br>{{ config('app.name') }}</p>
@endsection
