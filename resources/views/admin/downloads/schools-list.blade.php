@extends('admin.downloads.layout')

@section('content')
    <table>
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th>School Name</th>
                <th>Contact</th>
                <th>Region</th>
                <th>District</th>
                <th>Ward</th>
                <th>Coordinates</th>
                <th>Status</th>
                <th>Teachers</th>
                <th>Students</th>
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
                    <td>{{ $school->regionModel?->name ?? '—' }}</td>
                    <td>{{ $school->districtName() ?? '—' }}</td>
                    <td>{{ $school->ward ?? '—' }}</td>
                    <td>
                        @if($school->latitude !== null && $school->longitude !== null)
                            {{ $school->latitude }}, {{ $school->longitude }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        {{ $school->is_active ? 'Active' : 'Disabled' }}
                        @if($school->needs_review)
                            (New)
                        @endif
                    </td>
                    <td>{{ $school->teacher_clubs_count ?? 0 }}</td>
                    <td>{{ $school->students_count ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="empty">No records found for the selected export options.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
