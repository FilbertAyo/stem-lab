<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000000; }
        h1 { margin: 0 0 6px; font-size: 20px; color: #000000; }
        .meta { color: #000000; margin: 0 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000000; padding: 6px 8px; text-align: left; vertical-align: top; color: #000000; }
        th { font-weight: bold; }
        .sn { width: 36px; text-align: center; }
        .empty { padding: 16px; text-align: center; color: #000000; }
    </style>
</head>
<body>
    <h1>{{ $title }} List</h1>
    <p class="meta">
        Generated {{ $generatedAt->format('M d, Y h:i A') }}
        @if($filterSummary)
            &middot; {{ $filterSummary }}
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th>School Name</th>
                <th>Contact</th>
                <th>District</th>
                <th>Ward</th>
                <th>Region</th>
                <th>Status</th>
                <th>Teachers</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schools as $index => $school)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td>{{ $school->name }}</td>
                    <td>
                        @if($school->phone)
                            {{ $school->phone }}
                        @endif
                        @if($school->phone && $school->email)
                            <br>
                        @endif
                        @if($school->email)
                            {{ $school->email }}
                        @endif
                        @if(! $school->phone && ! $school->email)
                            —
                        @endif
                    </td>
                    <td>{{ $school->district ?? '—' }}</td>
                    <td>{{ $school->ward ?? '—' }}</td>
                    <td>{{ $school->regionModel?->name ?? $school->region ?? '—' }}</td>
                    <td>
                        {{ $school->is_active ? 'Active' : 'Disabled' }}
                        @if($school->needs_review)
                            (New)
                        @endif
                    </td>
                    <td>{{ $school->teacher_clubs_count ?? 0 }}</td>
                    <td>{{ $school->updated_at?->format('d M Y, H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty">No records found for the selected export options.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
