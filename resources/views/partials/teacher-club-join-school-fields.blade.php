{{--
  Shared school / club join fields (same names as client.clubs.join).
  Expects: $schools (collection), $regions (collection), $existingSchool (School|null)
  Optional: $districtsByRegion (array regionId => [{id, name}]), $authInputStyle (bool)
--}}
@php
  $authInputStyle = $authInputStyle ?? false;
  $districtsByRegion = $districtsByRegion ?? [];
  $inputClass = $authInputStyle
    ? 'form-control border-0 border-bottom rounded-0 px-0 bg-transparent shadow-none'
    : 'form-control border p-2';
  $selectClass = $authInputStyle
    ? 'form-select border-0 border-bottom rounded-0 px-0 bg-transparent shadow-none'
    : 'form-select border p-2';
  $searchClass = $authInputStyle
    ? 'form-control border-0 border-bottom rounded-0 px-0 bg-transparent shadow-none mb-3'
    : 'form-control border bg-light-soft p-2 mb-2';
  $oldRegionId = old('school_region_id');
  $oldDistrictId = old('school_district_id');
@endphp
@if($existingSchool)
  <div class="alert alert-info mb-4">
    <div class="d-flex align-items-center">
      <i class="bi bi-info-circle me-2 fs-5"></i>
      <div>
        <strong>Your school:</strong> {{ $existingSchool->name }}
        @if($existingSchool->districtName() || $existingSchool->regionModel?->name)
          <span class="text-muted">
            ({{ trim(($existingSchool->districtName() ?? '').($existingSchool->districtName() && $existingSchool->regionModel?->name ? ', ' : '').($existingSchool->regionModel?->name ?? '')) }})
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
          @if($school->districtName() || $school->regionModel?->name)
            ({{ trim(($school->districtName() ?? '').($school->districtName() && $school->regionModel?->name ? ', ' : '').($school->regionModel?->name ?? '')) }})
          @endif
        </option>
      @endforeach
    </select>
    @error('school_id')
      <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="text-danger @if($authInputStyle) small @endif">If your school is not in the list, use “Add new school” below.</small>
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
      <label for="school_district_id" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">District</label>
      <select class="{{ $selectClass }} @error('school_district_id') is-invalid @enderror"
              id="school_district_id"
              name="school_district_id"
              data-old-district-id="{{ $oldDistrictId }}"
              @if(! $oldRegionId) disabled @endif>
        <option value="">{{ $oldRegionId ? '-- Select district --' : '-- Select region first --' }}</option>
        @if($oldRegionId && isset($districtsByRegion[$oldRegionId]))
          @foreach($districtsByRegion[$oldRegionId] as $districtOption)
            <option value="{{ $districtOption['id'] }}" {{ (string) $oldDistrictId === (string) $districtOption['id'] ? 'selected' : '' }}>
              {{ $districtOption['name'] }}
            </option>
          @endforeach
        @endif
      </select>
      @error('school_district_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
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

  <div class="row">
    <div class="col-md-6 mb-3">
      <label for="school_latitude" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">Latitude</label>
      <input type="number"
             class="{{ $inputClass }} @error('school_latitude') is-invalid @enderror"
             id="school_latitude"
             name="school_latitude"
             value="{{ old('school_latitude') }}"
             min="-90"
             max="90"
             step="0.0000001"
             placeholder="-6.7923540">
      @error('school_latitude')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
    <div class="col-md-6 mb-3">
      <label for="school_longitude" class="form-label @if($authInputStyle) small text-muted fw-medium @endif">Longitude</label>
      <input type="number"
             class="{{ $inputClass }} @error('school_longitude') is-invalid @enderror"
             id="school_longitude"
             name="school_longitude"
             value="{{ old('school_longitude') }}"
             min="-180"
             max="180"
             step="0.0000001"
             placeholder="39.2083284">
      @error('school_longitude')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const regionSelect = document.getElementById('school_region_id');
    const districtSelect = document.getElementById('school_district_id');
    if (!regionSelect || !districtSelect) {
      return;
    }

    const districtsByRegion = @json($districtsByRegion);

    function populateDistricts(regionId, selectedDistrictId) {
      const districts = districtsByRegion[regionId] || [];
      districtSelect.innerHTML = '';

      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = regionId ? '-- Select district --' : '-- Select region first --';
      districtSelect.appendChild(placeholder);

      districts.forEach(function (district) {
        const option = document.createElement('option');
        option.value = district.id;
        option.textContent = district.name;
        if (selectedDistrictId && String(selectedDistrictId) === String(district.id)) {
          option.selected = true;
        }
        districtSelect.appendChild(option);
      });

      if (regionId && districts.length) {
        districtSelect.removeAttribute('disabled');
      } else {
        districtSelect.setAttribute('disabled', 'disabled');
      }
    }

    regionSelect.addEventListener('change', function () {
      populateDistricts(this.value, null);
    });

    const initialRegion = regionSelect.value;
    const initialDistrictId = districtSelect.getAttribute('data-old-district-id') || '';
    if (initialRegion) {
      populateDistricts(initialRegion, initialDistrictId);
    }
  });
</script>
