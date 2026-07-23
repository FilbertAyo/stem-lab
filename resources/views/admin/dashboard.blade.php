<x-admin-layout>
    <style>
        .dash-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .75rem; }
        .dash-head h5 { margin: 0; font-weight: 700; }
        .dash-head p { margin: 0; font-size: .75rem; }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .6rem;
            margin-bottom: .9rem;
        }
        @media (min-width: 576px) {
            .stat-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (min-width: 992px) {
            .stat-grid { grid-template-columns: repeat(6, 1fr); }
        }
        .stat-card {
            position: relative;
            background: var(--bs-card-bg, #fff);
            border: 1px solid var(--bs-border-color, #e9ecef);
            border-radius: 0;
            padding: .65rem .75rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            min-width: 0;
            overflow: hidden;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(7, 38, 73, .08); }
        .stat-card .stat-icon {
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: .25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }
        .stat-card .stat-label {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--bs-secondary-color, #6c757d);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
        }
        .stat-card .stat-value { font-size: 1.15rem; font-weight: 700; line-height: 1.15; margin: 0; }
        .stat-card .stat-sub { font-size: .65rem; color: var(--bs-secondary-color, #98a2b3); margin: 0; white-space: nowrap; }

        .dash-card { border: 1px solid var(--bs-border-color, #e9ecef); border-radius: 0; box-shadow: none; }
        .dash-card .card-header { background: transparent; padding: .6rem .85rem; font-size: .8rem; font-weight: 600; }
        .dash-card .card-body { padding: .75rem .85rem; }

        .activity-item { display: flex; align-items: flex-start; gap: .55rem; padding: .45rem 0; border-bottom: 1px dashed var(--bs-border-color, #eef0f2); }
        .activity-item:last-child { border-bottom: 0; }
        .activity-item .avatar { width: 26px; height: 26px; font-size: .8rem; display: flex; align-items: center; justify-content: center; }
        .activity-item h6 { font-size: .74rem; margin: 0 0 .1rem; }
        .activity-item small, .activity-item p { font-size: .66rem; margin: 0; }

        #visitor-week-view .nav-link { padding: .2rem .6rem; font-size: .7rem; }

        #schools-map { height: 70vh; width: 100%; min-height: 400px; }
        #schoolsMapModal .modal-body { padding: 0; }
        #schoolsMapModal .map-empty { padding: 3rem 1.5rem; }

        .dash-map-btn {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-weight: 600;
            padding: .45rem 1rem;
            box-shadow: 0 2px 8px rgba(7, 38, 73, .18);
        }
        .dash-map-btn i { font-size: 1.15rem; }
    </style>

    <div class="dash-head">
        <div>
            <h5>Dashboard Overview</h5>
            <p class="text-muted">System statistics and key metrics</p>
        </div>
        <button type="button" class="btn btn-primary dash-map-btn" data-bs-toggle="modal" data-bs-target="#schoolsMapModal">
            <i class="ti ti-map-2"></i> Schools Map
        </button>
    </div>

    <!-- Schools Map Modal -->
    <div class="modal fade" id="schoolsMapModal" tabindex="-1" aria-labelledby="schoolsMapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="schoolsMapModalLabel">
                        Schools Map
                        <span class="text-muted fw-normal fs-6">({{ $mapSchools->count() }} {{ $mapSchools->count() === 1 ? 'school' : 'schools' }})</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($mapSchools->isEmpty())
                        <div class="map-empty text-center text-muted">
                            <i class="ti ti-map-off" style="font-size: 2rem;"></i>
                            <p class="mb-0 mt-2">No schools with coordinates yet. Add latitude and longitude when creating or editing a school.</p>
                        </div>
                    @else
                        <div id="schools-map"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Compact stat cards -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="ti ti-shield-check"></i></div>
            <div class="min-w-0">
                <p class="stat-label">Admins</p>
                <p class="stat-value">{{ $stats['total_admins'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="ti ti-user-check"></i></div>
            <div class="min-w-0">
                <p class="stat-label">Teachers</p>
                <p class="stat-value">{{ $stats['total_teachers'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="ti ti-school"></i></div>
            <div class="min-w-0">
                <p class="stat-label">Students</p>
                <p class="stat-value">{{ $stats['total_students'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="ti ti-users-group"></i></div>
            <div class="min-w-0">
                <p class="stat-label">Students in Club</p>
                <p class="stat-value">{{ $stats['students_in_club'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="ti ti-3d-cube-sphere"></i></div>
            <div class="min-w-0">
                <p class="stat-label">3D Models</p>
                <p class="stat-value">{{ $stats['active_3d_models'] }}<span class="text-muted fw-normal fs-6">/{{ $stats['total_3d_models'] }}</span></p>
                <p class="stat-sub">Active / Total</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="ti ti-headset"></i></div>
            <div class="min-w-0">
                <p class="stat-label">VR Experiments</p>
                <p class="stat-value">{{ $stats['active_vr_experiments'] }}<span class="text-muted fw-normal fs-6">/{{ $stats['total_vr_experiments'] }}</span></p>
                <p class="stat-sub">Active / Total</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="ti ti-trophy"></i></div>
            <div class="min-w-0">
                <p class="stat-label">Competitions</p>
                <p class="stat-value">{{ $stats['active_competitions'] }}<span class="text-muted fw-normal fs-6">/{{ $stats['total_competitions'] }}</span></p>
                <p class="stat-sub">Active / Total</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="ti ti-building-community"></i></div>
            <div class="min-w-0">
                <p class="stat-label">STEM Clubs</p>
                <p class="stat-value">{{ $stats['total_schools'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="ti ti-users"></i></div>
            <div class="min-w-0">
                <p class="stat-label">Unique Visitors</p>
                <p class="stat-value">{{ $stats['total_visitors'] }}</p>
                <p class="stat-sub">{{ $stats['visitors_today'] }} today</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="ti ti-eye"></i></div>
            <div class="min-w-0">
                <p class="stat-label">Landing Views</p>
                <p class="stat-value">{{ number_format($stats['total_landing_views']) }}</p>
                <p class="stat-sub">{{ number_format($stats['visitors_not_converted']) }} not registered</p>
            </div>
        </div>
    </div>

    <!-- Chart + Recent Activities -->
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card dash-card h-100 mb-0">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <span>Visitors Overview (Weekly)</span>
                    <ul class="nav nav-pills" id="visitor-week-view" role="tablist" aria-label="Visitor chart view">
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link active" data-vis-view="both">Both</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" data-vis-view="unique">Unique</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" data-vis-view="views">Views</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div id="visitor-week-combined-chart" style="min-height: 260px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card dash-card h-100 mb-0">
                <div class="card-header border-bottom">Recent Activities (Today)</div>
                <div class="card-body py-2">
                    @if($recentActivities->count() > 0)
                        @foreach($recentActivities as $activity)
                            <div class="activity-item">
                                <div class="avatar bg-{{ $activity['color'] }}-subtle text-{{ $activity['color'] }} rounded flex-shrink-0">
                                    <i class="ti {{ $activity['icon'] }}"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h6 class="text-truncate">{{ $activity['title'] }}</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($activity['user'])
                                            <p class="text-muted text-truncate"><i class="ti ti-user"></i> {{ $activity['user']->name }}</p>
                                        @endif
                                        <small class="text-muted"><i class="ti ti-clock"></i> {{ $activity['time']->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0 small text-center py-3">No activities today</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Competitions -->
    <div class="card dash-card mb-3">
        <div class="card-header border-bottom">Competitions</div>
        <div class="card-body">
            @if($dashboardCompetitions->isEmpty())
                <p class="text-muted mb-0 small">No competitions found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0 dash-table">
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
                                    <td class="text-muted">{{ $competition->club?->name ?? '—' }}</td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $competition->phases->count() }}</span></td>
                                    <td class="text-end">
                                        <button
                                            class="btn btn-outline-success btn-sm py-0 px-2"
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script type="application/json" id="schools-map-data">
        {!! json_encode($mapSchools->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'latitude' => (float) $s->latitude,
            'longitude' => (float) $s->longitude,
            'district' => $s->district,
            'ward' => $s->ward,
        ])->values()) !!}
    </script>
    <script type="application/json" id="visitor-week-chart-data">
        {!! json_encode($visitorChartData) !!}
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Schools map (Leaflet) — init when modal opens
            var schoolsMap = null;
            var schoolsMapModal = document.getElementById('schoolsMapModal');
            if (schoolsMapModal) {
                schoolsMapModal.addEventListener('shown.bs.modal', function () {
                    var mapEl = document.getElementById('schools-map');
                    if (!mapEl || typeof L === 'undefined') return;

                    var schools = [];
                    try {
                        schools = JSON.parse(document.getElementById('schools-map-data').textContent);
                    } catch (e) {
                        schools = [];
                    }

                    if (!schoolsMap) {
                        schoolsMap = L.map('schools-map').setView([-6.3690, 34.8888], 6);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        }).addTo(schoolsMap);

                        var bounds = [];
                        function escapeHtml(str) {
                            if (!str) return '';
                            return String(str)
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;');
                        }

                        schools.forEach(function (school) {
                            var lat = school.latitude;
                            var lng = school.longitude;
                            if (typeof lat !== 'number' || typeof lng !== 'number') return;

                            var meta = [];
                            if (school.district) meta.push(escapeHtml(school.district));
                            if (school.ward) meta.push(escapeHtml(school.ward));
                            var popup = '<strong>' + escapeHtml(school.name) + '</strong>';
                            if (meta.length) {
                                popup += '<br><span class="text-muted">' + meta.join(' · ') + '</span>';
                            }

                            L.marker([lat, lng]).addTo(schoolsMap).bindPopup(popup);
                            bounds.push([lat, lng]);
                        });

                        if (bounds.length === 1) {
                            schoolsMap.setView(bounds[0], 14);
                        } else if (bounds.length > 1) {
                            schoolsMap.fitBounds(bounds, { padding: [40, 40] });
                        }
                    }

                    setTimeout(function () {
                        schoolsMap.invalidateSize();
                    }, 100);
                });
            }

            var chartData = JSON.parse(document.getElementById('visitor-week-chart-data').textContent);
            if (typeof ApexCharts === 'undefined') return;

            var combinedOpts = {
                chart: { type: 'line', height: 260, toolbar: { show: false } },
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
