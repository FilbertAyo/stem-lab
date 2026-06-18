<div {{ $attributes->merge(['class' => 'card h-100 border shadow-none card-skeleton']) }} role="status">
    <span class="visually-hidden">Loading experiment card</span>

    <div class="position-relative placeholder-glow" style="overflow: hidden; height: 190px;">
        <span class="placeholder col-12 rounded-0 d-block w-100 h-100" data-skeleton-image aria-hidden="true"></span>
        <span class="position-absolute top-0 end-0 m-2 placeholder rounded" style="width: 48px; height: 22px;" aria-hidden="true"></span>
    </div>

    <div class="card-body d-flex flex-column placeholder-glow">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <x-client.skeleton.line width="col-4" height="22px" />
            <x-client.skeleton.line width="col-2" height="14px" />
        </div>

        <x-client.skeleton.line width="col-8" class="mb-3" height="20px" />

        <div class="mt-auto d-flex justify-content-between align-items-center">
            <x-client.skeleton.line width="col-3" height="14px" />
            <x-client.skeleton.line width="" class="rounded" height="38px" style="width: 90px;" />
        </div>
    </div>
</div>
