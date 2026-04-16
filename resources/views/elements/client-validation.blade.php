@if (session('status'))
    <div class="alert alert-dismissible fade show text-success-emphasis bg-success-subtle border border-success-subtle" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-dismissible fade show text-danger-emphasis bg-danger-subtle border border-danger-subtle" role="alert">
        <pre class="mb-0" style="white-space: pre-wrap;">{{ session('error') }}</pre>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

