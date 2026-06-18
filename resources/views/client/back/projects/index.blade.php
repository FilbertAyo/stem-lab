@php
    $hasActiveCategory = $activeCategory !== null;
@endphp

<x-app-layout>
    <div class="col-lg-12">
        <div class="row">
            <!-- Categories sidebar -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <div class="dashboard-sidebar">
                    <ul class="category-list" id="projects-category-list">
                        <li>
                            <a href="{{ route('client.projects.index') }}"
                               class="{{ $hasActiveCategory ? '' : 'active' }}">
                                All Projects
                                @if(!$hasActiveCategory && isset($projects->total))
                                    <span class="badge bg-light bg-opacity-10 text-dark border">{{ $projects->total() }}</span>
                                @endif
                                <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('client.projects.index', ['category' => $category->slug]) }}"
                                   class="{{ $activeCategory && $activeCategory->id === $category->id ? 'active' : '' }}"
                                   data-category-name="{{ $category->name }}">
                                    {{ $category->name }}
                                    <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Projects grid -->
            <div class="col-lg-9">
                <h4 class="mb-4" id="projects-grid-title">
                    {{ $activeCategory ? $activeCategory->name : 'All Projects' }}
                </h4>

                @include('client.back.partials.class-filter-notice')

                <div id="card-grid-host"
                     data-grid-type="project"
                     data-index-url="{{ route('client.projects.index') }}">
                    @include('client.back.projects._cards-grid', [
                        'loading' => $loading ?? false,
                        'projects' => $projects,
                        'activeCategory' => $activeCategory,
                    ])
                </div>

                <template id="card-grid-skeleton-template">
                    @include('client.back.projects._cards-grid', [
                        'loading' => true,
                        'projects' => collect(),
                    ])
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('client/assets/js/card-grid-loader.js') }}"></script>
    @endpush
</x-app-layout>
