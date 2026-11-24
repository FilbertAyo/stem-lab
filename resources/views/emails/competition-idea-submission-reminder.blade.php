@extends('emails.layout')

@section('title', 'Submit your competition idea')

@section('body')
    <p style="margin: 0 0 20px 0;">Hi {{ $teacherName }},</p>
    <p style="margin: 0 0 24px 0;">
        Your school <strong>{{ $schoolName }}</strong> is approved for <strong>{{ $competitionName }}</strong>,
        but your idea has not been submitted yet. Please complete and submit your idea so your team can move
        into regional shortlisting.
    </p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding-bottom: 24px;">
                <a href="{{ $competitionUrl }}" style="display: inline-block; padding: 14px 32px; background-color: {{ config('mail-branding.primary_color') }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 9999px;">Submit idea</a>
            </td>
        </tr>
    </table>
    <p style="margin: 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">
        If you have questions, reply to this email or contact us at {{ config('mail.from.address') }}.
    </p>
@endsection
