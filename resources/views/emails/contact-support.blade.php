@extends('emails.layout')

@section('title', 'Contact / Support form submission')

@section('body')
    <p style="margin: 0 0 20px 0;">A new message was submitted via the Contact &amp; Support form.</p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 8px 0;"><strong>From:</strong> {{ $senderName }} &lt;{{ $senderEmail }}&gt;</td>
        </tr>
        <tr>
            <td style="padding: 12px 0 4px 0;"><strong>Message:</strong></td>
        </tr>
        <tr>
            <td style="padding: 12px; white-space: pre-wrap; background-color: #f4f8fa; border-radius: 8px;">{{ $messageBody }}</td>
        </tr>
    </table>
    <p style="margin: 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">You can reply directly to this email to respond to {{ $senderName }}.</p>
@endsection
