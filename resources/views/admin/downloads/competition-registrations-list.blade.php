@extends('admin.downloads.layout')

@section('content')
    <table>
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th>School</th>
                <th>District</th>
                <th>Ward</th>
                <th>Teacher</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Club Code</th>
                <th>Representatives</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $index => $registration)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td>{{ $registration->school_name ?: '—' }}</td>
                    <td>{{ $registration->school_district ?: '—' }}</td>
                    <td>{{ $registration->school_ward ?: '—' }}</td>
                    <td>{{ $registration->resolvedTeacherName() ?: '—' }}</td>
                    <td>{{ $registration->resolvedTeacherEmail() ?: '—' }}</td>
                    <td>{{ $registration->resolvedTeacherPhone() ?: '—' }}</td>
                    <td>{{ $registration->teacherClub?->club_code ?: '—' }}</td>
                    <td>
                        {{ $registration->representativeStudents->count() }}/{{ max(1, (int) ($competition->representative_count ?? 10)) }}
                    </td>
                    <td>{{ $registration->status === 'draft' ? 'Incomplete' : ucfirst($registration->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="empty">No registrations match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
