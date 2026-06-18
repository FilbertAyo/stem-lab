<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h4 class="mb-0">Notifications</h4>
            @if(auth()->user()->unreadNotifications->isNotEmpty())
                <form method="POST" action="{{ route('client.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">Mark all as read</button>
                </form>
            @endif
        </div>

        @include('elements.client-validation')

        <div class="card border shadow-none mt-3">
            <div class="card-body p-0">
                @if($notifications->isEmpty())
                    <div class="text-center py-5">
                        <p class="text-muted mb-0">No notifications yet.</p>
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            @php($data = $notification->data)
                            <li class="list-group-item {{ $notification->read_at ? '' : 'bg-light-soft' }}">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <p class="mb-1 {{ $notification->read_at ? 'text-muted' : 'fw-semibold' }}">
                                            {{ $data['message'] ?? 'Notification' }}
                                        </p>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if(! empty($data['url']))
                                        <form method="POST" action="{{ route('client.notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Open</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="p-3">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
