@extends('admin.downloads.layout')

@section('content')
    <table>
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th>Name</th>
                <th>Subject</th>
                <th>Classes</th>
                <th>External URL</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vrExperiments as $index => $experiment)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td>{{ $experiment->name }}</td>
                    <td>{{ $experiment->subject->subject_name ?? '—' }}</td>
                    <td>
                        @if($experiment->schoolClasses->isNotEmpty())
                            {{ $experiment->schoolClasses->pluck('name')->join(', ') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $experiment->external_url ?: '—' }}</td>
                    <td>{{ $experiment->is_active ? 'Active' : 'Inactive' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">No records found for the selected export options.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
