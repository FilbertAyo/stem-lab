<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Session Summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1, h2, h3 { margin: 0 0 8px; }
        .muted { color: #6b7280; }
        .section { margin-top: 18px; }
        .stats { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .stats th, .stats td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
        .stats th { background: #f3f4f6; }
        ul { margin: 6px 0 0 16px; padding: 0; }
        li { margin-bottom: 4px; }
    </style>
</head>
<body>
    <h1>Weekly Session Report Summary</h1>
    <p class="muted">
        Week: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
    </p>

    <div class="section">
        <h3>Key Statistics</h3>
        <table class="stats">
            <tr>
                <th>Total Reports</th>
                <th>Total Attendance</th>
                <th>Average Attendance</th>
            </tr>
            <tr>
                <td>{{ $stats['count'] }}</td>
                <td>{{ $stats['attendance_total'] }}</td>
                <td>{{ $stats['attendance_average'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>School Breakdown</h3>
        <table class="stats">
            <tr>
                <th>School</th>
                <th>Reports</th>
                <th>Total Attendance</th>
                <th>Average Attendance</th>
            </tr>
            @forelse($schoolBreakdown as $school => $row)
                <tr>
                    <td>{{ $school }}</td>
                    <td>{{ $row['reports'] }}</td>
                    <td>{{ $row['attendance_total'] }}</td>
                    <td>{{ $row['attendance_avg'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No school data available for this week.</td>
                </tr>
            @endforelse
        </table>
    </div>

    <div class="section">
        <h3>Challenges</h3>
        @if($challenges->isNotEmpty())
            <ul>
                @foreach($challenges as $challenge)
                    <li>{{ $challenge }}</li>
                @endforeach
            </ul>
        @else
            <p class="muted">No challenges recorded.</p>
        @endif
    </div>

    <div class="section">
        <h3>Other Issues / Notes</h3>
        @if($otherIssues->isNotEmpty())
            <ul>
                @foreach($otherIssues as $issue)
                    <li>{{ $issue }}</li>
                @endforeach
            </ul>
        @else
            <p class="muted">No additional issues recorded.</p>
        @endif
    </div>

    <div class="section">
        <h3>Detailed Entries</h3>
        <table class="stats">
            <tr>
                <th>Date</th>
                <th>School</th>
                <th>Club</th>
                <th>Teacher</th>
                <th>Attendance</th>
                <th>Challenges</th>
            </tr>
            @forelse($reports as $report)
                <tr>
                    <td>{{ $report->weeklySession?->session_date?->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ $report->teacherClub?->school?->name ?? 'N/A' }}</td>
                    <td>{{ $report->weeklySession?->club?->name ?? 'N/A' }}</td>
                    <td>{{ $report->teacher?->name ?? 'N/A' }}</td>
                    <td>{{ $report->attendance_count ?? '-' }}</td>
                    <td>{{ $report->challenge_description ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No reports submitted in this week range.</td>
                </tr>
            @endforelse
        </table>
    </div>
</body>
</html>
