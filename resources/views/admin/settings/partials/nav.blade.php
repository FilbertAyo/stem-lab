@php
    $tabs = [
        'versioning' => ['icon' => 'ti ti-code', 'label' => 'Versioning'],
        'social' => ['icon' => 'ti ti-brand-instagram', 'label' => 'Social Media'],
        'general' => ['icon' => 'ti ti-settings', 'label' => 'General'],
        'legal' => ['icon' => 'ti ti-file-text', 'label' => 'Legal'],
    ];
@endphp
<ul class="nav nav-tabs mb-4" role="navigation" aria-label="Settings sections">
    @foreach ($tabs as $key => $meta)
        <li class="nav-item">
            <a class="nav-link {{ ($activeTab ?? 'versioning') === $key ? 'active' : '' }}"
               href="{{ route('admin.settings.index', ['tab' => $key]) }}">
                <i class="{{ $meta['icon'] }}"></i> {{ $meta['label'] }}
            </a>
        </li>
    @endforeach
</ul>
