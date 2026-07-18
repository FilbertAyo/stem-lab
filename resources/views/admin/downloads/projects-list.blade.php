@extends('admin.downloads.layout')

@section('content')
    <table>
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th>Name</th>
                <th>Category</th>
                <th>Difficulty</th>
                <th>Tags</th>
                <th>Equipment</th>
                <th>Published</th>
                <th>Active</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $index => $project)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->category?->name ?? '—' }}</td>
                    <td>{{ ucfirst($project->difficulty) }}</td>
                    <td>{{ $project->tags_count ?? 0 }}</td>
                    <td>{{ $project->equipment_count ?? 0 }}</td>
                    <td>{{ $project->is_published ? 'Published' : 'Draft' }}</td>
                    <td>{{ $project->is_active ? 'Active' : 'Inactive' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty">No records found for the selected export options.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection