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
                <!-- Users Card -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-primary text-white rounded">
                                        <i class="ti ti-users fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Total Users</h6>
                                    <h4 class="mb-0">{{ $stats['total_users'] }}</h4>
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
        </div>
    </div>
</x-admin-layout>
