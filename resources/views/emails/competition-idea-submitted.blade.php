@extends('emails.layout')

@section('title', 'Congratulations — idea submitted')

@section('body')
    <p style="margin: 0 0 20px 0;">Hi {{ $teacherName }},</p>
    <p style="margin: 0 0 16px 0;">
        Congratulations! Your idea for <strong>{{ $schoolName }}</strong> in
        <strong>{{ $competitionName }}</strong> has been <strong>submitted successfully</strong>.
    </p>
    <p style="margin: 0 0 16px 0;">
        <strong>Problem title:</strong> {{ $problemTitle }}
    </p>
    <p style="margin: 0 0 24px 0;">
        Please wait for the organisers to review and approve your submission for the next stage
        (regional shortlisting). You will be notified when a decision is recorded.
    </p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding-bottom: 24px;">
                <a href="{{ $competitionUrl }}" style="display: inline-block; padding: 14px 32px; background-color: {{ config('mail-branding.primary_color') }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 9999px;">View competition</a>
            </td>
        </tr>
    </table>
    <p style="margin: 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">
        If you have questions, reply to this email or contact us at {{ config('mail.from.address') }}.
    </p>
@endsection
