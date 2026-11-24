@php
    $videoPath = $media?->video_path;
    $photos = $media?->images ?? collect();
    $note = $media?->prototype_media_notes;
@endphp

<div>
    <div class="small text-muted mb-1"><strong>Demo video</strong></div>
    @if(filled($videoPath))
        <video controls preload="metadata" class="w-100 rounded border bg-dark mb-1" style="max-height: 320px;">
            <source src="{{ Storage::url($videoPath) }}">
        </video>
        <a href="{{ Storage::url($videoPath) }}" target="_blank" rel="noopener noreferrer" class="small">Open video</a>
    @else
        <div class="text-muted small">No video uploaded.</div>
    @endif
</div>

<div class="mt-3">
    <div class="small text-muted mb-1"><strong>Photos</strong></div>
    @if($photos->isEmpty())
        <div class="text-muted small">No photos uploaded.</div>
    @else
        <div class="d-flex flex-wrap gap-2">
            @foreach($photos as $photo)
                <a href="{{ Storage::url($photo->file_path) }}" target="_blank" rel="noopener noreferrer">
                    <img
                        src="{{ Storage::url($photo->file_path) }}"
                        alt="{{ $photo->original_filename ?: 'Prototype photo' }}"
                        class="rounded border"
                        style="width: 96px; height: 96px; object-fit: cover;"
                    >
                </a>
            @endforeach
        </div>
    @endif
</div>

<div class="mt-3">
    <div class="small text-muted mb-1"><strong>Note about the media</strong></div>
    <div class="small text-break">{!! filled($note) ? nl2br(e($note)) : '—' !!}</div>
</div>
