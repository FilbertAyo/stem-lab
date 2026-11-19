@extends('emails.layout')

@section('title', $isReminder ? 'Reminder: idea shortlisted' : 'Idea shortlisted')

@section('body')
    <p style="margin: 0 0 20px 0;">Hi {{ $teacherName }},</p>
    <p style="margin: 0 0 16px 0;">
        @if($isReminder)
            This is a reminder that <strong>{{ $schoolName }}</strong> is shortlisted for
            <strong>{{ $competitionName }}</strong>.
        @else
            Congratulations! <strong>{{ $schoolName }}</strong> has been <strong>shortlisted</strong> for
            <strong>{{ $competitionName }}</strong>.
        @endif
    </p>
    <p style="margin: 0 0 16px 0;">
        Idea: <strong>{{ $ideaTitle }}</strong>
    </p>
    <p style="margin: 0 0 24px 0;">
        When the prototype development phase is open, continue on the competition page. Describe what you built,
        and submit video evidence through the Google Form link on that page.
    </p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding-bottom: 24px;">
                <a href="{{ $competitionUrl }}" style="display: inline-block; padding: 14px 32px; background-color: {{ config('mail-branding.primary_color') }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 9999px;">Open competition</a>
            </td>
        </tr>
    </table>
    <p style="margin: 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">
        If you have questions, reply to this email or contact us at {{ config('mail.from.address') }}.
    </p>
@endsection
