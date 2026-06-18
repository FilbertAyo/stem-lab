<div {{ $attributes->merge(['class' => 'card h-100 border card-skeleton']) }} role="status">
    <span class="visually-hidden">Loading project card</span>

    <div class="ratio ratio-16x10 overflow-hidden placeholder-glow" style="height: 190px;">
        <span class="placeholder col-12 rounded-0 d-block w-100 h-100" data-skeleton-image aria-hidden="true"></span>
    </div>

    <div class="card-body d-flex flex-column placeholder-glow">
        <x-client.skeleton.line width="col-9" class="mb-2" height="20px" />

        <x-client.skeleton.line width="col-12" class="mb-1" height="14px" />
        <x-client.skeleton.line width="col-10" class="mb-2 flex-grow-1" height="14px" />

        <div class="d-flex flex-wrap gap-1 mb-2">
            <x-client.skeleton.line width="" class="rounded" height="22px" style="width: 72px;" />
            <x-client.skeleton.line width="" class="rounded" height="22px" style="width: 64px;" />
            <x-client.skeleton.line width="" class="rounded" height="22px" style="width: 56px;" />
        </div>
    </div>
</div>
