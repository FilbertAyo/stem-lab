@php
    /** @var \Illuminate\Database\Eloquent\Model $record */
    $compact = $compact ?? false;
    $createdByName = method_exists($record, 'createdByName')
        ? $record->createdByName()
        : ($record->createdBy?->name ?? 'Legacy / unavailable');
    $updatedByName = method_exists($record, 'updatedByName')
        ? $record->updatedByName()
        : ($record->updatedBy?->name ?? 'Legacy / unavailable');
    $updatedAt = $record->updated_at?->format('M j, Y g:i A') ?? '—';
@endphp

@if($compact)
    <div class="small text-muted {{ $class ?? 'mt-1' }}">
        <div>Created by: {{ $createdByName }}</div>
        <div>Last updated by: {{ $updatedByName }}</div>
        <div>Last updated: {{ $updatedAt }}</div>
    </div>
@else
    <div class="{{ $class ?? 'border rounded bg-light p-3' }}">
        <div class="small text-muted mb-1">Record ownership</div>
        <div class="mb-1"><strong>Created by:</strong> {{ $createdByName }}</div>
        <div class="mb-1"><strong>Last updated by:</strong> {{ $updatedByName }}</div>
        <div class="mb-0"><strong>Last updated:</strong> {{ $updatedAt }}</div>
    </div>
@endif
