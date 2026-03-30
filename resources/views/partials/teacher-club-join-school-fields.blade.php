{{--
  Shared school / club join fields (same names as client.clubs.join).
  Expects: $schools (collection), $regions (collection), $existingSchool (School|null)
  Optional: $authInputStyle (bool) — minimal underline inputs to match client guest auth forms.
--}}
@php
  $authInputStyle = $authInputStyle ?? false;
  $inputClass = $authInputStyle
    ? 'form-control border-0 border-bottom rounded-0 px-0 bg-transparent shadow-none'
    : 'form-control border p-2';
  $selectClass = $authInputStyle
    ? 'form-select border-0 border-bottom rounded-0 px-0 bg-transparent shadow-none'
    : 'form-select border p-2';
  $searchClass = $authInputStyle
    ? 'form-control border-0 border-bottom rounded-0 px-0 bg-transparent shadow-none mb-3'
    : 'form-control border bg-light-soft p-2 mb-2';
@endphp
@if($existingSchool)
  <div class="alert alert-info mb-4">
    <div class="d-flex align-items-center">
      <i class="bi bi-info-circle me-2 fs-5"></i>
      <div>
        <strong>Your school:</strong> {{ $existingSchool->name }}
        @if($existingSchool->district || $existingSchool->regionModel?->name || $existingSchool->region)
          <span class="text-muted">
            ({{ trim(($existingSchool->district ?? '').($existingSchool->district && ($existingSchool->regionModel?->name || $existingSchool->region) ? ', ' : '').($existingSchool->regionModel?->name ?? $existingSchool->region ?? '')) }})
          </span>
        @endif
      </div>
    </div>
    <p class="mb-0 mt-2 small">You will join using your existing school registration.</p>
  </div>
  <input type="hidden" name="use_existing_school" value="1">
@else
  <div class="@if($authInputStyle) mb-3 @else mb-4 @endif">
    @unless($authInputStyle)
      <label for="school_search" class="form-label">Search school</label>
    @endunless
    <input type="text"
           class="{{ $searchClass }}"
           id="school_search"
           placeholder="{{ $authInputStyle ? 'Search school name, district, or region…' : 'Type school name, district, or region...' }}">

    <label for="school_id" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">
      Select school <span class="text-danger">*</span>
    </label>
    <select class="{{ $selectClass }} @error('school_id') is-invalid @enderror"
            id="school_id"
            name="school_id"
            required>
      <option value="">-- Select a school --</option>
      @foreach($schools as $school)
        <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
          {{ $school->name }}
          @if($school->district || $school->regionModel?->name || $school->region)
            ({{ trim(($school->district ?? '').($school->district && ($school->regionModel?->name || $school->region) ? ', ' : '').($school->regionModel?->name ?? $school->region ?? '')) }})
          @endif
        </option>
      @endforeach
    </select>
    @error('school_id')
      <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="text-muted @if($authInputStyle) small @endif">If your school is not in the list, use “Add new school” below.</small>
  </div>

  <div class="mb-1">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" id="is_new_school" name="is_new_school" value="1" {{ old('is_new_school') ? 'checked' : '' }}>
      <label class="form-check-label @if($authInputStyle) small @endif" for="is_new_school">
        Add new school
      </label>
    </div>
  </div>
@endif

<div id="newSchoolFields" style="display: none;">
  <hr class="my-4">
  <h6 class="mb-3 @if($authInputStyle) fs-6 fw-medium @endif">New school information</h6>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="school_name" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">
        School name <span class="text-danger">*</span>
      </label>
      <input type="text"
             class="{{ $inputClass }} @error('school_name') is-invalid @enderror"
             id="school_name"
             name="school_name"
             value="{{ old('school_name') }}">
      @error('school_name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
    <div class="col-md-6 mb-3">
      <label for="school_phone" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">School phone</label>
      <input type="text"
             class="{{ $inputClass }} @error('school_phone') is-invalid @enderror"
             id="school_phone"
             name="school_phone"
             value="{{ old('school_phone') }}">
      @error('school_phone')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6 mb-3">
      <label for="school_email" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">School email</label>
      <input type="email"
             class="{{ $inputClass }} @error('school_email') is-invalid @enderror"
             id="school_email"
             name="school_email"
             value="{{ old('school_email') }}">
      @error('school_email')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6 mb-3">
      <label for="school_region_id" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">Region</label>
      <select class="{{ $selectClass }} @error('school_region_id') is-invalid @enderror"
              id="school_region_id"
              name="school_region_id">
        <option value="">-- Select region --</option>
        @foreach($regions as $region)
          <option value="{{ $region->id }}" {{ (string) old('school_region_id') === (string) $region->id ? 'selected' : '' }}>
            {{ $region->name }}
          </option>
        @endforeach
      </select>
      @error('school_region_id')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6 mb-3">
      <label for="school_district" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">District</label>
      <input type="text"
             class="{{ $inputClass }} @error('school_district') is-invalid @enderror"
             id="school_district"
             name="school_district"
             value="{{ old('school_district') }}">
      @error('school_district')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6 mb-3">
      <label for="school_ward" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">Ward</label>
      <input type="text"
             class="{{ $inputClass }} @error('school_ward') is-invalid @enderror"
             id="school_ward"
             name="school_ward"
             value="{{ old('school_ward') }}">
      @error('school_ward')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="mb-3">
    <label for="school_address" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">School address</label>
    <textarea class="{{ $inputClass }} @error('school_address') is-invalid @enderror"
              id="school_address"
              name="school_address"
              rows="3">{{ old('school_address') }}</textarea>
    @error('school_address')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
</div>
