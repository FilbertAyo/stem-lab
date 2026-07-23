@extends('admin.downloads.layout')

@section('title', $title)
@section('heading', $title)

@section('content')
    <table>
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th>Name</th>
                <th>Email</th>
                <th>School</th>
                <th>Club Code</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td>{{ $row->user->name ?? '—' }}</td>
                    <td>{{ $row->user->email ?? '—' }}</td>
                    <td>{{ $row->school->name ?? 'N/A' }}</td>
                    <td>{{ $row->club_code ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">{{ $emptyMessage ?? 'No records found.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
