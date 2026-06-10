<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000000; }
        h1 { margin: 0 0 6px; font-size: 20px; color: #000000; }
        .meta { color: #000000; margin: 0 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000000; padding: 8px 10px; text-align: left; vertical-align: top; color: #000000; }
        th { font-weight: bold; }
        .sn { width: 48px; text-align: center; }
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
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="empty">No records found for the selected export options.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
