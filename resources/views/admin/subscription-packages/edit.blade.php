<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-10">
            <h5 class="mb-3">Edit Subscription Package</h5>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.subscription-packages.update', $package) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" value="{{ $package->slug }}" readonly disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tier key</label>
                                <input type="text" class="form-control" value="{{ $package->tier_key }}" readonly disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $package->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $package->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="price_heading" class="form-label">Price heading</label>
                                <input type="text" class="form-control @error('price_heading') is-invalid @enderror" id="price_heading" name="price_heading" value="{{ old('price_heading', $package->price_heading) }}">
                                @error('price_heading')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="price_subheading" class="form-label">Price subheading</label>
                                <input type="text" class="form-control @error('price_subheading') is-invalid @enderror" id="price_subheading" name="price_subheading" value="{{ old('price_subheading', $package->price_subheading) }}">
                                @error('price_subheading')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="price_compare" class="form-label">Compare price</label>
                                <input type="text" class="form-control @error('price_compare') is-invalid @enderror" id="price_compare" name="price_compare" value="{{ old('price_compare', $package->price_compare) }}">
                                @error('price_compare')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="badge_label" class="form-label">Badge label</label>
                                <input type="text" class="form-control @error('badge_label') is-invalid @enderror" id="badge_label" name="badge_label" value="{{ old('badge_label', $package->badge_label) }}">
                                @error('badge_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cta_label" class="form-label">CTA label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('cta_label') is-invalid @enderror" id="cta_label" name="cta_label" value="{{ old('cta_label', $package->cta_label) }}" required>
                                @error('cta_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cta_action" class="form-label">CTA action <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('cta_action') is-invalid @enderror" id="cta_action" name="cta_action" value="{{ old('cta_action', $package->cta_action) }}" required>
                                @error('cta_action')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Used by client JS (e.g. free-trial, individual, public-school).</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="features" class="form-label">Features</label>
                            <textarea class="form-control @error('features') is-invalid @enderror" id="features" name="features" rows="6">{{ old('features', implode("\n", $package->features ?? [])) }}</textarea>
                            @error('features')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">One feature per line.</small>
                        </div>

                        @if ($package->slug === 'public-school')
                            <hr class="my-4">
                            <h6 class="mb-3">Linked student active plan</h6>

                            <div class="mb-3">
                                <label for="linked_title" class="form-label">Linked title</label>
                                <input type="text" class="form-control @error('linked_title') is-invalid @enderror" id="linked_title" name="linked_title" value="{{ old('linked_title', $package->linked_title) }}">
                                @error('linked_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="linked_description" class="form-label">Linked description</label>
                                <textarea class="form-control @error('linked_description') is-invalid @enderror" id="linked_description" name="linked_description" rows="3">{{ old('linked_description', $package->linked_description) }}</textarea>
                                @error('linked_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="linked_features" class="form-label">Linked features</label>
                                <textarea class="form-control @error('linked_features') is-invalid @enderror" id="linked_features" name="linked_features" rows="4">{{ old('linked_features', implode("\n", $package->linked_features ?? [])) }}</textarea>
                                @error('linked_features')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">One feature per line.</small>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="sort_order" class="form-label">Sort order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $package->sort_order) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check"></i> Update Package
                            </button>
                            <a href="{{ route('admin.subscription-packages.index') }}" class="btn btn-secondary">
                                <i class="ti ti-x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
