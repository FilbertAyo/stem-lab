@extends('emails.layout')

@section('title', 'Daily digest · '.$categoryLabel)

@section('body')
    <p style="margin: 0 0 8px 0; font-size: 13px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: {{ config('mail-branding.primary_color') }};">Daily admin digest</p>
    <p style="margin: 0 0 12px 0; font-size: 22px; font-weight: 700; line-height: 1.3;">{{ $categoryLabel }}</p>
    <p style="margin: 0 0 24px 0;">Here is what still needs attention in your queue for {{ now()->format('M d, Y') }}.</p>

    @foreach($sections as $section)
        <p style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700;">{{ $section['title'] }} ({{ $section['count'] }})</p>
        @if(! empty($section['items']))
            <ul style="margin: 0 0 16px 0; padding-left: 20px; color: #1f2a2e;">
                @foreach($section['items'] as $item)
                    <li style="margin-bottom: 6px;">{{ $item }}</li>
                @endforeach
            </ul>
        @else
            <p style="margin: 0 0 16px 0; color: rgba(31, 42, 46, 0.75);">{{ $section['count'] }} item(s) waiting.</p>
        @endif
        @if(! empty($section['action_url']))
            <p style="margin: 0 0 24px 0;">
                <a href="{{ $section['action_url'] }}" style="color: {{ config('mail-branding.primary_color') }}; font-weight: 600; text-decoration: none;">{{ $section['action_label'] ?? 'Open in admin' }} →</a>
            </p>
        @endif
    @endforeach

    <p style="margin: 0; font-size: 14px; color: rgba(31, 42, 46, 0.8);">This digest was sent to admins assigned to {{ $categoryLabel }}, and to the shared support inbox.</p>
@endsection
