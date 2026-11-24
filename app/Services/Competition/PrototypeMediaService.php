<?php

namespace App\Services\Competition;

use App\Models\CompetitionPrototypeImage;
use App\Models\CompetitionPrototypeModification;
use App\Models\CompetitionPrototypeSubmission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PrototypeMediaService
{
    public const VIDEO_MAX_KB = 10240;

    public const IMAGE_MAX_KB = 2048;

    public const MAX_IMAGES = 5;

    public const NOTE_MAX = 2000;

    /**
     * @return array<string, list<string>>
     */
    public function rules(bool $requireVideo): array
    {
        return [
            'prototype_video' => [
                $requireVideo ? 'required' : 'nullable',
                'file',
                'mimes:mp4,webm,mov',
                'max:'.self::VIDEO_MAX_KB,
            ],
            'prototype_images' => ['nullable', 'array', 'max:'.self::MAX_IMAGES],
            'prototype_images.*' => [
                'file',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:'.self::IMAGE_MAX_KB,
            ],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
            'prototype_media_notes' => ['nullable', 'string', 'max:'.self::NOTE_MAX],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'prototype_video.required' => 'Upload a prototype demo video before submitting.',
            'prototype_video.mimes' => 'The video must be an MP4, WebM, or MOV file.',
            'prototype_video.max' => 'The video must not be larger than 10 MB.',
            'prototype_video.uploaded' => 'The video must not be larger than 10 MB.',
            'prototype_images.max' => 'You can upload at most 5 photos.',
            'prototype_images.*.image' => 'Each photo must be an image (JPEG, PNG, GIF, or WebP).',
            'prototype_images.*.mimes' => 'Each photo must be a JPEG, PNG, GIF, or WebP image.',
            'prototype_images.*.max' => 'Each photo must not be larger than 2 MB.',
            'prototype_images.*.uploaded' => 'Each photo must not be larger than 2 MB.',
            'prototype_media_notes.max' => 'The media note must not be longer than 2000 characters.',
        ];
    }

    /**
     * Store a replacement video and new photos, then persist the parent.
     * New files are removed if the database write fails. Previous files are
     * removed only after the new rows are committed.
     *
     * @param  list<UploadedFile|null>  $newImages
     * @param  list<int|string>  $removeImageIds
     */
    public function sync(
        CompetitionPrototypeSubmission|CompetitionPrototypeModification $owner,
        string $directoryPrefix,
        ?UploadedFile $video,
        array $newImages,
        array $removeImageIds,
        ?string $notes,
        bool $requireVideo,
    ): void {
        if (blank($owner->ulid)) {
            $owner->ulid = (string) Str::ulid();
        }

        $ownedImages = $owner->exists
            ? $owner->images()->orderBy('sort_order')->get()
            : new Collection;
        $removeIds = $this->normalizeIds($removeImageIds);
        $ownedIds = $ownedImages->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $foreignIds = array_values(array_diff($removeIds, $ownedIds));

        if ($foreignIds !== []) {
            throw ValidationException::withMessages([
                'remove_image_ids' => 'You can only remove photos that belong to this prototype.',
            ]);
        }

        $incoming = array_values(array_filter(
            $newImages,
            fn (mixed $file): bool => $file instanceof UploadedFile
        ));
        $keptCount = $ownedImages->reject(
            fn (CompetitionPrototypeImage $image): bool => in_array((int) $image->id, $removeIds, true)
        )->count();

        if ($keptCount + count($incoming) > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'prototype_images' => 'You can keep at most 5 photos. Remove one before adding more.',
            ]);
        }

        $hasVideo = filled($owner->video_path) || $video instanceof UploadedFile;
        if ($requireVideo && ! $hasVideo) {
            throw ValidationException::withMessages([
                'prototype_video' => 'Upload a prototype demo video before submitting.',
            ]);
        }

        $directory = trim($directoryPrefix, '/').'/'.$owner->ulid;
        $storedNew = [];

        try {
            $newVideoPath = null;
            if ($video instanceof UploadedFile) {
                $newVideoPath = $video->store($directory.'/video', 'public');
                if (! is_string($newVideoPath) || $newVideoPath === '') {
                    throw new \RuntimeException('Unable to store the prototype video.');
                }
                $storedNew[] = $newVideoPath;
            }

            $storedImages = [];
            foreach ($incoming as $image) {
                $path = $image->store($directory.'/photos', 'public');
                if (! is_string($path) || $path === '') {
                    throw new \RuntimeException('Unable to store a prototype photo.');
                }
                $storedNew[] = $path;
                $original = trim($image->getClientOriginalName());
                $storedImages[] = [
                    'path' => $path,
                    'name' => $original === '' ? null : Str::limit($original, 255, ''),
                ];
            }

            [$replacedVideoPath, $removedPhotoPaths] = DB::transaction(function () use ($owner, $newVideoPath, $storedImages, $removeIds, $notes): array {
                $replacedVideo = null;
                if ($newVideoPath !== null) {
                    $previous = $owner->video_path;
                    if (is_string($previous) && $previous !== '' && $previous !== $newVideoPath) {
                        $replacedVideo = $previous;
                    }
                    $owner->video_path = $newVideoPath;
                }

                $owner->prototype_media_notes = $this->normalizeNotes($notes);
                $owner->save();

                $removedPhotos = [];
                if ($removeIds !== []) {
                    $removing = CompetitionPrototypeImage::query()
                        ->where('imageable_type', $owner->getMorphClass())
                        ->where('imageable_id', $owner->getKey())
                        ->whereIn('id', $removeIds)
                        ->get();

                    $removedPhotos = $removing
                        ->pluck('file_path')
                        ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
                        ->values()
                        ->all();

                    CompetitionPrototypeImage::query()
                        ->whereIn('id', $removing->pluck('id')->all())
                        ->delete();
                }

                $sort = (int) CompetitionPrototypeImage::query()
                    ->where('imageable_type', $owner->getMorphClass())
                    ->where('imageable_id', $owner->getKey())
                    ->max('sort_order');

                foreach ($storedImages as $stored) {
                    $owner->images()->create([
                        'file_path' => $stored['path'],
                        'original_filename' => $stored['name'],
                        'sort_order' => ++$sort,
                    ]);
                }

                return [$replacedVideo, $removedPhotos];
            });
        } catch (Throwable $exception) {
            $this->deleteStoredFiles($storedNew);

            throw $exception;
        }

        $this->deleteStoredFiles(array_values(array_filter(
            array_merge([$replacedVideoPath], $removedPhotoPaths),
            fn (mixed $path): bool => is_string($path) && $path !== ''
        )));
    }

    /**
     * @param  list<int|string>  $ids
     * @return list<int>
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = [];

        foreach ($ids as $id) {
            $value = (int) $id;
            if ($value > 0) {
                $normalized[$value] = $value;
            }
        }

        return array_values($normalized);
    }

    private function normalizeNotes(?string $notes): ?string
    {
        if ($notes === null) {
            return null;
        }

        $notes = trim($notes);

        return $notes === '' ? null : $notes;
    }

    /**
     * @param  list<string>  $paths
     */
    private function deleteStoredFiles(array $paths): void
    {
        $disk = Storage::disk('public');

        foreach ($paths as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}
