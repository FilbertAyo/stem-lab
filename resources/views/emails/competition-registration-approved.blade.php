@extends('emails.layout')

@section('title', 'Congratulations — registration approved')

@section('body')
    <p style="margin: 0 0 20px 0;">Hi {{ $teacherName }},</p>
    <p style="margin: 0 0 16px 0;">
        Congratulations! Your school <strong>{{ $schoolName }}</strong> has been
        <strong>approved</strong> for <strong>{{ $competitionName }}</strong>.
    </p>
    <p style="margin: 0 0 24px 0;">
        You have successfully passed the <strong>registration</strong> phase and may continue to the next stage:
        <strong>idea submission</strong>. When that phase is open, submit one idea for your school — the problem,
        your proposed STEM solution, and your student representatives.
    </p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding-bottom: 24px;">
                <a href="{{ $competitionUrl }}" style="display: inline-block; padding: 14px 32px; background-color: {{ config('mail-branding.primary_color') }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 9999px;">Go to competition</a>
            </td>
        </tr>
    </table>
    <p style="margin: 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">
        If you have questions, reply to this email or contact us at {{ config('mail.from.address') }}.
    </p>
@endsection
