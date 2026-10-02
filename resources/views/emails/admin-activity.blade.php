@extends('emails.layout')

@section('title', $headline)

@section('body')
    <p style="margin: 0 0 8px 0; font-size: 13px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: {{ config('mail-branding.primary_color') }};">Admin reminder</p>
    <p style="margin: 0 0 12px 0; font-size: 22px; font-weight: 700; line-height: 1.3;">{{ $headline }}</p>
    <p style="margin: 0 0 20px 0;">{{ $summary }}</p>

    @if($details !== [])
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 24px 0; background-color: #f4f8fa; border-radius: 8px;">
            @foreach($details as $detail)
                <tr>
                    <td style="padding: 8px 16px; width: 140px; color: rgba(31, 42, 46, 0.7); vertical-align: top;">{{ $detail['label'] }}</td>
                    <td style="padding: 8px 16px 8px 0; font-weight: 600; vertical-align: top;">{{ $detail['value'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if(filled($nextStep))
        <p style="margin: 0 0 24px 0; padding: 14px 16px; background-color: #fff7e8; border-radius: 8px;"><strong>What to do:</strong> {{ $nextStep }}</p>
    @endif

    @if(filled($actionUrl))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td align="center" style="padding-bottom: 24px;">
                    <a href="{{ $actionUrl }}" style="display: inline-block; padding: 14px 32px; background-color: {{ config('mail-branding.primary_color') }}; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 9999px;">{{ $actionLabel }}</a>
                </td>
            </tr>
        </table>
    @endif

    <p style="margin: 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">This was sent to every active admin, and to the shared support inbox, so the team still sees it if one person misses it.</p>
@endsection
