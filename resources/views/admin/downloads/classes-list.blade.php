@extends('admin.downloads.layout')

@section('content')
    <table>
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classes as $index => $class)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td>{{ $class->name }}</td>
                    <td>{{ $class->description ?: '—' }}</td>
                    <td>{{ $class->is_active ? 'Active' : 'Disabled' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty">No records found for the selected export options.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
