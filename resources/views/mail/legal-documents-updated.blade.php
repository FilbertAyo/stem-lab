@extends('emails.layout')

@section('title', 'Privacy Policy and Terms updated')

@section('body')
    <p style="margin: 0 0 16px 0;">Hello {{ $user->name }},</p>

    <p style="margin: 0 0 16px 0;">
        We have recently updated our <strong>Privacy Policy</strong> and/or <strong>Terms &amp; Conditions</strong> on {{ config('app.name') }}.
        These documents describe how we collect and use information, what you can expect when you use our platform and services, and the rules that apply to your account.
    </p>

    <p style="margin: 0 0 16px 0;">
        You are receiving this message because we want to make sure you are aware of the changes and have an easy way to read the current versions in full.
        We encourage you to set aside a few minutes to review both pages, especially if you use the site regularly or manage students or classes through your account.
    </p>

    <p style="margin: 0 0 20px 0;">
        If anything in the updated documents is unclear, you can reach us using the contact options on our website or reply to this email if your mail client allows it.
        Continuing to use our services after these updates means you acknowledge the revised terms and privacy practices described on those pages.
    </p>

    <p style="margin: 0 0 8px 0; font-weight: 600;">Read the latest documents</p>
    <p style="margin: 0 0 12px 0;">
        <a href="{{ url('/privacy-policy') }}" style="color: #0d78b8; text-decoration: underline;">View Privacy Policy</a>
        <span style="color: rgba(31, 42, 46, 0.5);"> &mdash; </span>
        how we handle personal information and your rights.
    </p>
    <p style="margin: 0 0 24px 0;">
        <a href="{{ url('/terms-conditions') }}" style="color: #0d78b8; text-decoration: underline;">View Terms &amp; Conditions</a>
        <span style="color: rgba(31, 42, 46, 0.5);"> &mdash; </span>
        rules for using our services, subscriptions where applicable, and related responsibilities.
    </p>

    <p style="margin: 0 0 12px 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">
        Thank you for being part of {{ config('app.name') }}.
    </p>
    <p style="margin: 0; font-size: 13px; color: rgba(31, 42, 46, 0.75);">
        This is an automated notice about policy or terms updates. You do not need to reply unless you have a specific question for our team.
    </p>
    <p style="margin: 16px 0 0 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">
        Thanks,<br>
        <strong>{{ config('app.name') }}</strong>
    </p>
@endsection
