@extends('admin.downloads.layout')

@section('content')
    <table>
        <thead>
            <tr>
                <th class="sn">S/N</th>
                <th>Name</th>
                <th>Subject</th>
                <th>Classes</th>
                <th>Tags</th>
                <th>Questions</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($models as $index => $model)
                <tr>
                    <td class="sn">{{ $index + 1 }}</td>
                    <td>{{ $model->name }}</td>
                    <td>{{ $model->subject->subject_name ?? '—' }}</td>
                    <td>
                        @if($model->schoolClasses->isNotEmpty())
                            {{ $model->schoolClasses->pluck('name')->join(', ') }}
                        @else
                            All classes
                        @endif
                    </td>
                    <td>{{ $model->tags ?: '—' }}</td>
                    <td>{{ $model->questions_count ?? 0 }}</td>
                    <td>{{ $model->is_active ? 'Active' : 'Inactive' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">No records found for the selected export options.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
