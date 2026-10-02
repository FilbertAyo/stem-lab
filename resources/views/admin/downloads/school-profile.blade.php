@extends('admin.downloads.layout')

@section('title', $school->name.' Profile')

@section('heading', $school->name)

@section('meta')
    <p class="meta">
        School profile
        &middot; Generated {{ ($generatedAt ?? now())->format('M d, Y h:i A') }}
        &middot; {{ $teacherCount }} {{ $teacherCount === 1 ? 'teacher' : 'teachers' }}
        &middot; {{ $studentCount }} {{ $studentCount === 1 ? 'student' : 'students' }}
    </p>
@endsection

@push('styles')
    <style>
        h2 { font-size: 14px; margin: 16px 0 6px; }
        h3 { font-size: 12px; margin: 12px 0 6px; }
        .map { width: 100%; max-height: 240px; margin: 0 0 8px; }
        .note { margin: 0 0 8px; }
        .students-table { margin-top: 18px; }
    </style>
@endpush

@section('content')
    <h2>School details</h2>
    <table>
        <tbody>
            <tr>
                <th>Status</th>
                <td>{{ $school->is_active ? 'Active' : 'Disabled' }}</td>
                <th>Review</th>
                <td>
                    @if($school->admin_reviewed_at)
                        Reviewed {{ $school->admin_reviewed_at->format('M d, Y') }}
                    @else
                        Needs review
                    @endif
                </td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $school->email ?: '—' }}</td>
                <th>Phone</th>
                <td>{{ $school->phone ?: '—' }}</td>
            </tr>
            <tr>
                <th>Region</th>
                <td>{{ $school->regionModel?->name ?: '—' }}</td>
                <th>District</th>
                <td>{{ $school->districtName() ?: '—' }}</td>
            </tr>
            <tr>
                <th>Ward</th>
                <td>{{ $school->ward ?: '—' }}</td>
                <th>Coordinates</th>
                <td>
                    @if($school->latitude !== null && $school->longitude !== null)
                        {{ $school->latitude }}, {{ $school->longitude }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <th>Registered</th>
                <td>{{ $school->created_at?->format('M d, Y') ?: '—' }}</td>
                <th>Clubs</th>
                <td>{{ $clubGroups->count() }}</td>
            </tr>
        </tbody>
    </table>

    @if($mapImage)
        <h2>Map</h2>
        <img class="map" src="{{ $mapImage }}" alt="Map of {{ $school->name }}">
        <p class="note">{{ $school->latitude }}, {{ $school->longitude }}</p>
    @elseif($school->latitude !== null && $school->longitude !== null)
        <h2>Map</h2>
        <p class="note">Map image was not available. Coordinates: {{ $school->latitude }}, {{ $school->longitude }}.</p>
    @endif

    <h2>Clubs, teachers, and students</h2>
    @forelse($clubGroups as $group)
        <h3>{{ $group['name'] }}</h3>
        <p class="note">
            Club day: {{ $group['day'] }}
            &middot; Leader: {{ $group['leader'] }}
            &middot; {{ $group['memberships']->count() }} {{ $group['memberships']->count() === 1 ? 'teacher' : 'teachers' }}
            &middot; {{ $group['students']->count() }} {{ $group['students']->count() === 1 ? 'student' : 'students' }}
        </p>

        <table>
            <thead>
                <tr>
                    <th class="sn">S/N</th>
                    <th>Teacher</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($group['memberships'] as $index => $membership)
                    <tr>
                        <td class="sn">{{ $index + 1 }}</td>
                        <td>{{ $membership->user?->name ?: '—' }}</td>
                        <td>{{ $membership->user?->email ?: '—' }}</td>
                        <td>{{ $membership->user?->phone ?: '—' }}</td>
                        <td>{{ $membership->is_leader ? 'Leader' : 'Member' }}</td>
                        <td>{{ ucfirst((string) $membership->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">No teachers in this club.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="students-table">
            <thead>
                <tr>
                    <th class="sn">S/N</th>
                    <th>Student</th>
                    <th>Admission no.</th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Age</th>
                </tr>
            </thead>
            <tbody>
                @forelse($group['students'] as $index => $student)
                    <tr>
                        <td class="sn">{{ $index + 1 }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->admission_number ?: '—' }}</td>
                        <td>{{ $student->schoolClass?->name ?: '—' }}</td>
                        <td>{{ $student->gender ? ucfirst($student->gender) : '—' }}</td>
                        <td>{{ $student->age ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">No students in this club.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @empty
        <p class="empty">No clubs, teachers, or students are linked to this school yet.</p>
    @endforelse

    <h2>Competitions</h2>
    <table>
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th>Competition</th>
                <th>Status</th>
                <th>Teacher</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $index => $registration)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td>{{ $registration->competition?->name ?: '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', (string) $registration->status)) }}</td>
                    <td>{{ $registration->teacher_name ?: '—' }}</td>
                    <td>{{ $registration->submitted_at?->format('M d, Y') ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">No competition registrations for this school.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
