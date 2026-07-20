@props([
    'exportRoute' => 'admin.schools.export',
    'entityLabel' => 'Schools',
])

<form method="GET" action="{{ route($exportRoute) }}" class="row g-3 align-items-end">
    @foreach(request()->only(['search', 'status', 'review', 'region_id', 'district_id']) as $key => $value)
        @if($value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <div class="col-md-8 col-lg-5">
        <label class="form-label small mb-1" for="export_scope">Export {{ $entityLabel }} as PDF</label>
        <select id="export_scope" name="scope" class="form-select">
            <option value="range" @selected(request('scope', 'range') === 'range')>
                Filtered list (applied search &amp; filters)
            </option>
            <option value="all" @selected(request('scope') === 'all')>
                All {{ strtolower($entityLabel) }}
            </option>
        </select>
    </div>
    <div class="col-md-4 col-lg-auto">
        <button type="submit" class="btn btn-sm btn-danger">
            <i class="ti ti-download"></i> Download PDF
        </button>
    </div>
</form>
