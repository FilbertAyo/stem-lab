@php
    $studentSchoolClass = $studentSchoolClass ?? null;
    $classFilterActive = $classFilterActive ?? false;
@endphp

@if($classFilterActive && $studentSchoolClass)
    <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div class="small mb-0">
            Showing resources tailored for <strong>{{ $studentSchoolClass->name }}</strong>.
            Update your class in profile if this is incorrect.
        </div>
        <a href="{{ route('profile.edit') }}" class="badge bg-info text-dark border-0 text-decoration-none">
            Update class
        </a>
    </div>
@elseif(auth()->user()?->role === 'student' && ! $classFilterActive)
    <div class="alert alert-light border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div class="small text-muted mb-0">
            Add your class in profile to see resources matched to your grade level.
        </div>
        <a href="{{ route('profile.edit') }}" class="badge bg-light text-dark border text-decoration-none">
            Set your class
        </a>
    </div>
@endif
