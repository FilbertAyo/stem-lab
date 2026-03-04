@extends('emails.layout')

@section('title', 'Reset Your Password – ' . config('mail-branding.app_name'))

@section('body')
    <p style="margin: 0 0 20px 0;">Hello,</p>
    <p style="margin: 0 0 24px 0;">You are receiving this email because we received a password reset request for your account.</p>
    <p style="margin: 0 0 28px 0;">Click the button below to choose a new password:</p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding-bottom: 24px;">
                <a href="{{ $resetUrl }}" style="display: inline-block; padding: 14px 32px; background-color: {{ config('mail-branding.primary_color') }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 9999px;">Reset Password</a>
            </td>
        </tr>
    </table>
    <p style="margin: 0 0 12px 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">If you did not request a password reset, you can safely ignore this email. The link will expire in 60 minutes.</p>
    <p style="margin: 0; font-size: 13px; color: rgba(31, 42, 46, 0.65);">If the button doesn’t work, copy and paste this link into your browser:</p>
    <p style="margin: 8px 0 0 0; word-break: break-all; font-size: 13px;"><a href="{{ $resetUrl }}" style="color: {{ config('mail-branding.primary_color') }};">{{ $resetUrl }}</a></p>
@endsection
