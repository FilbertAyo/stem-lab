@props([
    'listRoute',
    'exportRoute',
    'entityLabel' => 'Users',
])

@php
    $hasFilters = request()->filled('q') || request()->filled('from_date') || request()->filled('to_date');
@endphp

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route($listRoute) }}" class="row g-3 align-items-end">
            <div class="col-md-12 col-lg-4">
                <label class="form-label small mb-1" for="filter_q">Search</label>
                <input type="search"
                    id="filter_q"
                    name="q"
                    class="form-control"
                    placeholder="Search by name or email..."
                    value="{{ request('q') }}"
                    autocomplete="off">
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label small mb-1" for="filter_from_date">From date</label>
                <input type="date"
                    id="filter_from_date"
                    name="from_date"
                    class="form-control"
                    value="{{ request('from_date') }}">
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label small mb-1" for="filter_to_date">To date</label>
                <input type="date"
                    id="filter_to_date"
                    name="to_date"
                    class="form-control"
                    value="{{ request('to_date') }}">
            </div>
            <div class="col-md-12 col-lg-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">
                    Apply
                </button>
                @if($hasFilters)
                    <a href="{{ route($listRoute) }}" class="btn btn-link text-secondary text-nowrap px-0">Reset</a>
                @endif
            </div>
        </form>

        <hr class="my-3">

        <form method="GET" action="{{ route($exportRoute) }}" class="row g-3 align-items-end">
            @if(request()->filled('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            @if(request()->filled('from_date'))
                <input type="hidden" name="from_date" value="{{ request('from_date') }}">
            @endif
            @if(request()->filled('to_date'))
                <input type="hidden" name="to_date" value="{{ request('to_date') }}">
            @endif
            <div class="col-md-8 col-lg-5">
                <label class="form-label small mb-1" for="export_scope">Export {{ $entityLabel }} as PDF</label>
                <select id="export_scope" name="scope" class="form-select">
                    <option value="range" @selected(request('scope', 'range') === 'range')>
                        Filtered list (applied search &amp; dates)
                    </option>
                    <option value="all" @selected(request('scope') === 'all')>
                        All {{ strtolower($entityLabel) }}
                    </option>
                </select>
            </div>
            <div class="col-md-4 col-lg-auto">
                <button type="submit" class="btn btn-danger">
                    <i class="ti ti-download"></i> Download PDF
                </button>
            </div>
            
        </form>
    </div>
</div>
