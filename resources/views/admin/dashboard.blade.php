<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Dashboard Overview</h5>
                    <p class="text-muted mb-0 small">System statistics and key metrics</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <!-- Admins Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-danger text-white rounded">
                                        <i class="ti ti-shield fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Total Admins</h6>
                                    <h4 class="mb-0">{{ $stats['total_admins'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teachers Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-primary text-white rounded">
                                        <i class="ti ti-user fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Total Teachers</h6>
                                    <h4 class="mb-0">{{ $stats['total_teachers'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-info text-white rounded">
                                        <i class="ti ti-user-circle fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Total Students</h6>
                                    <h4 class="mb-0">{{ $stats['total_students'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3D Models Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-success text-white rounded">
                                        <i class="ti ti-box fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">3D Models</h6>
                                    <h4 class="mb-0">{{ $stats['active_3d_models'] }} / {{ $stats['total_3d_models'] }}</h4>
                                    <small class="text-muted">Active / Total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Competitions Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-warning text-white rounded">
                                        <i class="ti ti-trophy fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Competitions</h6>
                                    <h4 class="mb-0">{{ $stats['active_competitions'] }} / {{ $stats['total_competitions'] }}</h4>
                                    <small class="text-muted">Active / Total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schools Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-secondary text-white rounded">
                                        <i class="ti ti-building fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Schools</h6>
                                    <h4 class="mb-0">{{ $stats['total_schools'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clubs Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-danger text-white rounded">
                                        <i class="ti ti-users-group fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Clubs</h6>
                                    <h4 class="mb-0">{{ $stats['total_clubs'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Answers Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-primary text-white rounded">
                                        <i class="ti ti-check fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Total Answers</h6>
                                    <h4 class="mb-0">{{ $stats['total_answers'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Correct Answers Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-success text-white rounded">
                                        <i class="ti ti-check-circle fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Correct Answers</h6>
                                    <h4 class="mb-0">{{ $stats['total_correct_answers'] }}</h4>
                                    @if($stats['total_answers'] > 0)
                                        <small class="text-muted">
                                            {{ number_format(($stats['total_correct_answers'] / $stats['total_answers']) * 100, 1) }}% accuracy
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest Active Admin & Recent Activities Row -->
            <div class="row mb-4">
                <!-- Latest Active Admin -->
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom">
                            <h6 class="mb-0">Latest Active Admin</h6>
                        </div>
                        <div class="card-body">
                            @if($latestActiveAdmin && $latestActiveAdmin['user'])
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-lg bg-danger text-white rounded-circle">
                                            <span class="fs-5">{{ substr($latestActiveAdmin['user']->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $latestActiveAdmin['user']->name }}</h6>
                                        <p class="text-muted mb-0 small">{{ $latestActiveAdmin['user']->email }}</p>
                                        @if($latestActiveAdmin['last_activity'])
                                            <small class="text-muted">
                                                <i class="ti ti-clock"></i>
                                                Active {{ $latestActiveAdmin['last_activity']->diffForHumans() }}
                                            </small>
                                        @else
                                            <small class="text-muted">
                                                <i class="ti ti-user"></i>
                                                Registered {{ $latestActiveAdmin['user']->created_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0 small">No admin activity found</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-md-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom">
                            <h6 class="mb-0">Recent Activities (Today)</h6>
                        </div>
                        <div class="card-body">
                            @if($recentActivities->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentActivities as $activity)
                                        <div class="list-group-item px-0 border-0 border-bottom">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-sm bg-{{ $activity['color'] }}-subtle text-{{ $activity['color'] }} rounded">
                                                        <i class="ti {{ $activity['icon'] }}"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1 small">{{ $activity['title'] }}</h6>
                                                    @if($activity['user'])
                                                        <p class="text-muted mb-0 small">
                                                            <i class="ti ti-user"></i> {{ $activity['user']->name }}
                                                        </p>
                                                    @endif
                                                    <small class="text-muted">
                                                        <i class="ti ti-clock"></i> {{ $activity['time']->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0 small text-center py-3">No activities today</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
