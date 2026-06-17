@php
    $user = auth()->user();
    $needsClass = $user?->role === 'student' && $user->resolvedSchoolClassId() === null;
@endphp

@if($needsClass)
    <div class="alert alert-light border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div class="small text-muted mb-0">
            Add your class in profile to see resources matched to your grade level.
        </div>
        <a href="{{ route('profile.edit') }}" class="badge bg-light text-dark border text-decoration-none">
            Set your class
        </a>
    </div>
@endif
