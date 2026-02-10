<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="title mb-1">Pending Join Requests</h2>
                <p class="text-muted mb-0">Approve or reject requests from teachers who want to join your clubs.</p>
            </div>
            <div>
                <a href="{{ route('client.clubs.index') }}" class="btn bg-light-soft text-dark">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Clubs
                </a>
            </div>
        </div>

        <section class="row">
            <div class="col-12">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($clubsWithPendingRequests->isEmpty())
                    <div class="card border-0 shadow-none">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-check-circle display-4 text-success mb-3"></i>
                            <h5 class="fw-semibold mb-2">No pending requests</h5>
                            <p class="text-muted mb-3">
                                All join requests have been processed. New requests will appear here.
                            </p>
                            <a href="{{ route('client.clubs.index') }}" class="btn btn-primary text-white">
                                Back to Clubs
                            </a>
                        </div>
                    </div>
                @else
                    @foreach($clubsWithPendingRequests as $clubData)
                        <div class="card border-0 shadow-none mb-4">
                            <div class="card-header bg-light-soft">
                                <h5 class="mb-0">
                                    <i class="bi bi-school me-2"></i>
                                    {{ $clubData['club']->name }}
                                </h5>
                                @if(isset($clubData['school']))
                                    <small class="text-muted">School: {{ $clubData['school']->name }}</small>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead class="table-light-soft">
                                            <tr>
                                                <th>#</th>
                                                <th>Teacher</th>
                                                <th>Email</th>
                                                <th>School</th>
                                                <th>Request Date</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($clubData['pendingRequests'] as $request)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="fw-semibold">{{ $request->user->name }}</td>
                                                    <td>{{ $request->user->email }}</td>
                                                    <td>{{ $request->school->name ?? 'N/A' }}</td>
                                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                                    <td class="text-end">
                                                        <form method="POST" action="{{ route('client.clubs.approve-request', $request) }}" class="d-inline me-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success text-white" onclick="return confirm('Approve this request?')">
                                                                <i class="bi bi-check-circle me-1"></i>
                                                                Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('client.clubs.reject-request', $request) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this request?')">
                                                                <i class="bi bi-x-circle me-1"></i>
                                                                Reject
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </section>
    </div>
</x-app-layout>