@php
    $photos = $media?->relationLoaded('images')
        ? $media->images
        : ($media?->images ?? collect());
    $disabled = $disabled ?? false;
    $hasVideo = filled($media?->video_path);
    $fieldId = $fieldId ?? 'prototypeMedia';
@endphp

<div class="border rounded p-3 p-md-4 mb-4">
    <h6 class="fw-semibold mb-2">Video and photos</h6>
    <p class="text-muted small mb-3">
        Upload the demo on this page. One video, up to 10 MB. Up to 5 photos, 2 MB each. Photos are optional — one is enough.
    </p>

    <div class="mb-3">
        <label class="form-label" for="{{ $fieldId }}Video">
            Demo video
            @unless($hasVideo)
                <span class="text-danger">*</span>
            @endunless
        </label>
        @if($hasVideo)
            <video controls preload="metadata" class="w-100 rounded border bg-dark mb-2" style="max-height: 280px;">
                <source src="{{ Storage::url($media->video_path) }}">
            </video>
            <div class="form-text mb-2">A video is already saved. Choose a new file only if you want to replace it.</div>
        @endif
        <input
            id="{{ $fieldId }}Video"
            type="file"
            name="prototype_video"
            accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov"
            class="form-control @error('prototype_video') is-invalid @enderror"
            @disabled($disabled)
        >
        @error('prototype_video')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">MP4, WebM, or MOV. Maximum 10 MB. Required when you submit.</div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="{{ $fieldId }}Photos">Photos</label>
        @if($photos->isNotEmpty())
            <div class="d-flex flex-wrap gap-3 mb-2">
                @foreach($photos as $photo)
                    <label class="border rounded p-2 mb-0" style="width: 124px;">
                        <img
                            src="{{ Storage::url($photo->file_path) }}"
                            alt="{{ $photo->original_filename ?: 'Prototype photo' }}"
                            class="rounded"
                            style="width: 100%; height: 88px; object-fit: cover;"
                        >
                        <div class="form-check mt-2 mb-0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="remove_image_ids[]"
                                value="{{ $photo->id }}"
                                data-existing-photo
                                @disabled($disabled)
                            >
                            <span class="form-check-label small">Remove</span>
                        </div>
                    </label>
                @endforeach
            </div>
        @endif
        <input
            id="{{ $fieldId }}Photos"
            type="file"
            name="prototype_images[]"
            accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp"
            class="form-control @error('prototype_images') is-invalid @enderror @error('prototype_images.*') is-invalid @enderror"
            data-prototype-photos
            multiple
            @disabled($disabled)
        >
        @error('prototype_images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('prototype_images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('remove_image_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <div class="form-text">JPEG, PNG, GIF, or WebP. Up to 5 photos in total, 2 MB each.</div>
    </div>

    <div class="mb-0">
        <label class="form-label" for="{{ $fieldId }}Notes">Brief note about the media (optional)</label>
        <textarea
            id="{{ $fieldId }}Notes"
            name="prototype_media_notes"
            rows="3"
            maxlength="2000"
            class="form-control @error('prototype_media_notes') is-invalid @enderror"
            @disabled($disabled)
        >{{ old('prototype_media_notes', $media?->prototype_media_notes) }}</textarea>
        @error('prototype_media_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('click', function (event) {
                const button = event.target.closest('form[data-prototype-media] button[type="submit"]');
                if (!button) {
                    return;
                }

                const form = button.closest('form');
                const message = prototypeMediaProblem(form);
                if (!message) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                window.alert(message);
            }, true);

            function prototypeMediaProblem(form) {
                const video = form.querySelector('input[name="prototype_video"]');
                const videoFile = video && video.files ? video.files[0] : null;
                if (videoFile && videoFile.size > 10 * 1024 * 1024) {
                    return 'The video must not be larger than 10 MB.';
                }

                const photos = form.querySelector('input[data-prototype-photos]');
                const files = photos && photos.files ? Array.from(photos.files) : [];
                for (let i = 0; i < files.length; i++) {
                    if (files[i].size > 2 * 1024 * 1024) {
                        return 'Each photo must not be larger than 2 MB.';
                    }
                }

                const existing = form.querySelectorAll('input[data-existing-photo]').length;
                const removing = form.querySelectorAll('input[data-existing-photo]:checked').length;
                if ((existing - removing) + files.length > 5) {
                    return 'You can keep at most 5 photos. Remove one before adding more.';
                }

                return '';
            }
        </script>
    @endpush
@endonce
