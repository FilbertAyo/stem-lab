<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Dashboard Overview</h5>
                    <p class="text-muted mb-0 small">System statistics and key metrics</p>
                </div>
            </div>

            <!-- Statistics Cards (equal height) -->
            <div class="row">
                <!-- Admins Card -->
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center">
                          
                            <div class="flex-grow-1 ms-3 min-w-0">
                                <h6 class="mb-0 text-muted">Total Admins</h6>
                                <h4 class="mb-0">{{ $stats['total_admins'] }}</h4>
                                <small class="text-muted">&nbsp;</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teachers Card -->
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1 ms-3 min-w-0">
                                <h6 class="mb-0 text-muted">Total Teachers</h6>
                                <h4 class="mb-0">{{ $stats['total_teachers'] }}</h4>
                                <small class="text-muted">&nbsp;</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Card -->
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1 ms-3 min-w-0">
                                <h6 class="mb-0 text-muted">Total Students</h6>
                                <h4 class="mb-0">{{ $stats['total_students'] }}</h4>
                                <small class="text-muted">&nbsp;</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3D Models Card -->
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center">
                         
                            <div class="flex-grow-1 ms-3 min-w-0">
                                <h6 class="mb-0 text-muted">3D Models</h6>
                                <h4 class="mb-0">{{ $stats['active_3d_models'] }} / {{ $stats['total_3d_models'] }}</h4>
                                <small class="text-muted">Active / Total</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VR Experiments Card -->
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1 ms-3 min-w-0">
                                <h6 class="mb-0 text-muted">VR Experiments</h6>
                                <h4 class="mb-0">{{ $stats['active_vr_experiments'] }} / {{ $stats['total_vr_experiments'] }}</h4>
                                <small class="text-muted">Active / Total</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Competitions Card -->
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1 ms-3 min-w-0">
                                <h6 class="mb-0 text-muted">Competitions</h6>
                                <h4 class="mb-0">{{ $stats['active_competitions'] }} / {{ $stats['total_competitions'] }}</h4>
                                <small class="text-muted">Active / Total</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schools Card -->
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1 ms-3 min-w-0">
                                <h6 class="mb-0 text-muted">STEM Clubs</h6>
                                <h4 class="mb-0">{{ $stats['total_schools'] }}</h4>
                                <small class="text-muted">&nbsp;</small>
                            </div>
                        </div>
                    </div>
                </div>
            
                   <!-- Unique Visitors Card -->
                   <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1 ms-3 min-w-0">
                                <h6 class="mb-0 text-muted">Unique Visitors</h6>
                                <h4 class="mb-0">{{ $stats['total_visitors'] }}</h4>
                                <small class="text-muted">{{ $stats['visitors_today'] }} today</small>
                            </div>
                        </div>
                    </div>
                </div>

                                <!-- Landing Page Views Card -->
                <div class="col-md-2 col-sm-6 mb-3">
                        <div class="card border shadow-none">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1 ms-3 min-w-0">
                                <h6 class="mb-0 text-muted">Landing Views</h6>
                                <h4 class="mb-0">{{ number_format($stats['total_landing_views']) }}</h4>
                                <small class="text-muted">{{ number_format($stats['visitors_not_converted']) }} not registered</small>
                            </div>
                        </div>
                    </div>
                </div>

           

            </div>

            <div class="row mb-2">
                <div class="col-lg-12 mb-4">
                    <div class="card border shadow-none">
                        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">Competition Phases & Actions</h6>
                            <small class="text-muted">Open a competition to view phase actions</small>
                        </div>
                        <div class="card-body">
                            @if($dashboardCompetitions->isEmpty())
                                <p class="text-muted mb-0 small">No competitions found.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Competition</th>
                                                <th>Club</th>
                                                <th>Phases</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dashboardCompetitions as $competition)
                                                <tr>
                                                    <td class="fw-semibold">{{ $competition->name }}</td>
                                                    <td class="text-muted small">{{ $competition->club?->name ?? '—' }}</td>
                                                    <td>{{ $competition->phases->count() }}</td>
                                                    <td class="text-end">
                                                        <button
                                                            class="btn btn-success btn-sm"
                                                            type="button"
                                                            data-bs-toggle="offcanvas"
                                                            data-bs-target="#competitionPhasesCanvas{{ $competition->id }}"
                                                            aria-controls="competitionPhasesCanvas{{ $competition->id }}"
                                                        >
                                                            View phases
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visitor charts per week (combined) -->
            <div class="row mb-2">
                <div class="col-lg-12 mb-4">
                    <div class="card border shadow-none">
                        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between gap-3">
                            <h6 class="mb-0">Visitors Overview (Weekly)</h6>

                            <ul class="nav nav-pills" id="visitor-week-view" role="tablist" aria-label="Visitor chart view">
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link active" data-vis-view="both">
                                        Both
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" data-vis-view="unique">
                                        Unique Visitors
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" data-vis-view="views">
                                        Landing Views
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div id="visitor-week-combined-chart" style="min-height: 280px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            

            <!-- Latest Active Admin & Recent Activities Row -->
            <div class="row mb-4">
                <!-- Latest Active Admin -->
                <div class="col-md-4 mb-4">
                    <div class="card border shadow-none">
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
                    <div class="card border shadow-none">
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

    @foreach($dashboardCompetitions as $competition)
        <div class="offcanvas offcanvas-end" tabindex="-1" id="competitionPhasesCanvas{{ $competition->id }}" aria-labelledby="competitionPhasesCanvasLabel{{ $competition->id }}" style="--bs-offcanvas-width: 640px;">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="competitionPhasesCanvasLabel{{ $competition->id }}">
                    {{ $competition->name }} - Phases
                </h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                @if($competition->phases->isEmpty())
                    <p class="text-muted small mb-0">No phases configured for this competition.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($competition->phases as $phase)
                            @php
                                $phaseAction = match ($phase->phase_key) {
                                    'registration' => [
                                        'label' => 'Registrations',
                                        'route' => route('admin.competitions.registrations.index', $competition),
                                        'btn' => 'btn-outline-success',
                                        'icon' => 'ti ti-users',
                                    ],
                                    'idea_submission', 'idea_shortlisting' => [
                                        'label' => 'Ideas',
                                        'route' => route('admin.competitions.ideas.index', $competition),
                                        'btn' => 'btn-outline-warning',
                                        'icon' => 'ti ti-bulb',
                                    ],
                                    'prototype_development', 'prototype_shortlisting' => [
                                        'label' => 'Prototype Submissions',
                                        'route' => route('admin.competitions.prototype-shortlisting.index', $competition),
                                        'btn' => 'btn-outline-info',
                                        'icon' => 'ti ti-list-check',
                                    ],
                                    'mentor_onboarding', 'prototype_modification' => [
                                        'label' => 'Prototype Modifications',
                                        'route' => route('admin.competitions.prototype-modification.index', $competition),
                                        'btn' => 'btn-outline-dark',
                                        'icon' => 'ti ti-adjustments',
                                    ],
                                    'pitch_winner' => [
                                        'label' => 'Pitch & Winner',
                                        'route' => route('admin.competitions.pitch-winner.index', $competition),
                                        'btn' => 'btn-outline-success',
                                        'icon' => 'ti ti-microphone',
                                    ],
                                    default => null,
                                };
                            @endphp
                            <li class="border rounded p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $phase->sequence }}. {{ $phase->name }}</div>
                                        <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $phase->status)) }}</div>
                                    </div>
                                    @if($phaseAction)
                                        <a href="{{ $phaseAction['route'] }}" class="btn btn-sm {{ $phaseAction['btn'] }}">
                                            <i class="{{ $phaseAction['icon'] }}"></i> {{ $phaseAction['label'] }}
                                        </a>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endforeach

    @push('scripts')
    <script type="application/json" id="visitor-week-chart-data">
        {!! json_encode($visitorChartData) !!}
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var chartData = JSON.parse(document.getElementById('visitor-week-chart-data').textContent);
            if (typeof ApexCharts === 'undefined') return;

            var combinedOpts = {
                chart: { type: 'line', height: 280, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                colors: ['#072649', '#13c2c2'],
                series: [
                    { name: 'Unique Visitors', data: chartData.unique_visitors },
                    { name: 'Landing Views', data: chartData.landing_views },
                ],
                xaxis: { categories: chartData.labels },
                yaxis: { title: { text: 'Count' } },
                grid: { strokeDashArray: 4 },
                legend: { position: 'top', horizontalAlign: 'left' },
            };

            var combinedChart = new ApexCharts(
                document.querySelector('#visitor-week-combined-chart'),
                combinedOpts
            );
            combinedChart.render();

            function setVisitorView(view) {
                if (view === 'unique') {
                    combinedChart.updateSeries([
                        { name: 'Unique Visitors', data: chartData.unique_visitors },
                    ]);
                    return;
                }

                if (view === 'views') {
                    combinedChart.updateSeries([
                        { name: 'Landing Views', data: chartData.landing_views },
                    ]);
                    return;
                }

                combinedChart.updateSeries([
                    { name: 'Unique Visitors', data: chartData.unique_visitors },
                    { name: 'Landing Views', data: chartData.landing_views },
                ]);
            }

            document.querySelectorAll('#visitor-week-view [data-vis-view]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var view = this.getAttribute('data-vis-view');
                    document.querySelectorAll('#visitor-week-view [data-vis-view]').forEach(function (b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');
                    setVisitorView(view);
                });
            });
        });
    </script>
    @endpush
</x-admin-layout>
