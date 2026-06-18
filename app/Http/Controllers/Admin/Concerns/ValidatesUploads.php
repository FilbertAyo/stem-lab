<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

trait ValidatesUploads
{
    /**
     * @param  array<string, array{label: string, max_kb: int, extensions?: list<string>}>  $fields
     */
    protected function validateUploadedFiles(Request $request, array $fields): ?RedirectResponse
    {
        foreach ($fields as $field => $config) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            $label = $config['label'];
            $maxKb = $config['max_kb'];
            $maxBytes = $maxKb * 1024;

            if (! $file->isValid()) {
                $message = match ($file->getError()) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => sprintf(
                        'The %s is too large. Maximum allowed size is %s.',
                        strtolower($label),
                        self::formatMaxSizeLabel($maxKb)
                    ),
                    default => sprintf('The %s could not be uploaded. Please try again.', strtolower($label)),
                };

                return back()->withErrors([$field => $message])->withInput();
            }

            if ($file->getSize() > $maxBytes) {
                return back()->withErrors([
                    $field => sprintf(
                        'The %s must not be larger than %s.',
                        strtolower($label),
                        self::formatMaxSizeLabel($maxKb)
                    ),
                ])->withInput();
            }

            if (! empty($config['extensions'])) {
                $extension = strtolower($file->getClientOriginalExtension());

                if (! in_array($extension, $config['extensions'], true)) {
                    return back()->withErrors([
                        $field => sprintf(
                            'The %s must be a %s file.',
                            strtolower($label),
                            strtoupper(implode(', ', $config['extensions']))
                        ),
                    ])->withInput();
                }
            }
        }

        return null;
    }

    /**
     * @return list<\Closure|string>
     */
    protected function imageUploadRules(int $maxKb): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg,gif,webp',
            'max:'.$maxKb,
        ];
    }

    /**
     * @return list<mixed>
     */
    protected function glbUploadRules(int $maxKb): array
    {
        return [
            'nullable',
            'file',
            'max:'.$maxKb,
            function (string $attribute, mixed $value, \Closure $fail) use ($maxKb): void {
                if (! $value instanceof UploadedFile) {
                    return;
                }

                if ($value->isValid()) {
                    if (strtolower($value->getClientOriginalExtension()) !== 'glb') {
                        $fail('The file must be a GLB file.');

                        return;
                    }

                    $maxBytes = $maxKb * 1024;

                    if ($value->getSize() > $maxBytes) {
                        $fail(sprintf(
                            'The file must not be larger than %s.',
                            self::formatMaxSizeLabel($maxKb)
                        ));
                    }

                    return;
                }

                $message = match ($value->getError()) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => sprintf(
                        'The file is too large. Maximum allowed size is %s.',
                        self::formatMaxSizeLabel($maxKb)
                    ),
                    default => 'File upload failed. Please try again.',
                };

                $fail($message);
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function imageUploadMessages(string $field, int $maxKb): array
    {
        $maxLabel = self::formatMaxSizeLabel($maxKb);

        return [
            "{$field}.image" => 'The file must be an image (JPEG, PNG, GIF, or WebP).',
            "{$field}.mimes" => 'The file must be a JPEG, PNG, GIF, or WebP image.',
            "{$field}.max" => "The file must not be larger than {$maxLabel}. Please choose a smaller image.",
            "{$field}.uploaded" => "The file must not be larger than {$maxLabel}. Please choose a smaller image.",
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function glbUploadMessages(int $maxKb): array
    {
        $maxLabel = self::formatMaxSizeLabel($maxKb);

        return [
            'file.max' => "The GLB file must not be larger than {$maxLabel}.",
            'file.uploaded' => "The GLB file must not be larger than {$maxLabel}.",
        ];
    }

    protected static function formatMaxSizeLabel(int $maxKb): string
    {
        if ($maxKb >= 1024 && $maxKb % 1024 === 0) {
            return ($maxKb / 1024).' MB';
        }

        if ($maxKb >= 1024) {
            return round($maxKb / 1024, 1).' MB';
        }

        return $maxKb.' KB';
    }
}
