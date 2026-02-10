<x-app-layout>
    <div class="col-lg-12">

        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="title mb-1">My Profile</h2>
                <p class="text-muted mb-0">Manage your account settings and preferences</p>
            </div>
        </div>

        <section class="row">
            <!-- Profile Overview Column -->
            <div class="col-lg-4 col-12 mb-4">
                <div class="card border shadow-none">
                    <div class="card-body text-center py-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                            <span class="text-primary fw-bold" style="font-size: 2.5rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                        <h4 class="fw-semibold mb-1">{{ $user->name }}</h4>
                        <p class="text-muted mb-2">{{ $user->email }}</p>
                        <span class="badge {{ $user->role === 'teacher' ? 'bg-success' : ($user->role === 'student' ? 'bg-info' : 'bg-secondary') }} px-3 py-2">
                            <i class="bi {{ $user->role === 'teacher' ? 'bi-mortarboard' : 'bi-person' }} me-1"></i>
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                    <div class="card-footer bg-light-soft">
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <span class="text-muted small d-block">Status</span>
                                <span class="badge {{ $user->is_active ? 'bg-primary' : 'bg-danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Member Since</span>
                                <span class="fw-semibold small">{{ $user->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($user->role === 'teacher' && $user->teacherClubs->count() > 0)
                    <div class="card border shadow-none mt-4">
                        <div class="card-header bg-light-soft">
                            <h6 class="mb-0"><i class="bi bi-house me-2"></i>My Clubs</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($user->teacherClubs as $teacherClub)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold">{{ $teacherClub->club->name ?? 'Unknown Club' }}</span>
                                            @if($teacherClub->school)
                                                <small class="text-muted d-block">{{ $teacherClub->school->name }}</small>
                                            @endif
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            {{ $teacherClub->students->count() }} students
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Settings Column -->
            <div class="col-lg-8 col-12">
                <!-- Profile Information Card -->
                <div class="card border shadow-none mb-4">
                    <div class="card-header bg-light-soft d-flex align-items-center">
                        <i class="bi bi-person-circle fs-5 me-2 text-primary"></i>
                        <h5 class="mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Update Password Card -->
                <div class="card border shadow-none mb-4">
                    <div class="card-header bg-light-soft d-flex align-items-center">
                        <i class="bi bi-shield-lock fs-5 me-2 text-primary"></i>
                        <h5 class="mb-0">Update Password</h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Delete Account Card -->
              
            </div>
        </section>
    </div>
</x-app-layout>
