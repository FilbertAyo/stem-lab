@extends('emails.layout')

@section('title', 'Welcome to ' . config('mail-branding.app_name'))

@section('body')
    <p style="margin: 0 0 20px 0;">Hi {{ $userName }},</p>
    <p style="margin: 0 0 24px 0;">Welcome to <strong>{{ config('mail-branding.app_name') }}</strong>! We’re glad you’ve joined us.</p>
    <p style="margin: 0 0 28px 0;">You can now sign in and explore our STEM resources, 3D models, and activities.</p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding-bottom: 24px;">
                <a href="{{ url('/login') }}" style="display: inline-block; padding: 14px 32px; background-color: {{ config('mail-branding.primary_color') }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 9999px;">Sign In</a>
            </td>
        </tr>
    </table>
    <p style="margin: 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">If you have any questions, just reply to this email or contact us at {{ config('mail.from.address') }}.</p>
@endsection
