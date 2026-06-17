<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentCsvBulkImportService
{
    /**
     * @return array{success_count: int, errors: string[]}
     */
    public function import(
        TeacherClub $teacherClub,
        UploadedFile $file,
        ?Competition $competition = null
    ): array {
        $path = $file->getRealPath();
        if ($path === false) {
            return ['success_count' => 0, 'errors' => ['Could not read the uploaded file.']];
        }

        $allowedClassIds = [];
        if ($competition !== null) {
            $allowedClassIds = $competition->classRequirements()->pluck('school_class_id')->all();
        }

        $classes = SchoolClass::where('is_active', true)
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim($name)) => $id]);

        $errors = [];
        $successCount = 0;
        $lineNumber = 0;
        $tempPath = null;
        $handle = null;

        try {
            $content = file_get_contents($path);
            if ($content === false) {
                return ['success_count' => 0, 'errors' => ['Failed to read the CSV file.']];
            }

            if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                $content = substr($content, 3);
            }

            $tempPath = sys_get_temp_dir().'/csv_upload_'.uniqid('', true).'.csv';
            file_put_contents($tempPath, $content);

            $handle = fopen($tempPath, 'r');
            if ($handle === false) {
                @unlink($tempPath);

                return ['success_count' => 0, 'errors' => ['Failed to read the CSV file.']];
            }

            $header = fgetcsv($handle);
            $lineNumber++;

            if ($header === false || empty($header)) {
                fclose($handle);
                @unlink($tempPath);

                return ['success_count' => 0, 'errors' => ['The CSV file is empty.']];
            }

            $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

            $baseRequiredColumns = ['name', 'gender', 'class_name'];
            $missingBaseColumns = array_diff($baseRequiredColumns, $header);
            $hasDobColumn = in_array('date_of_birth', $header, true);
            $hasAgeColumn = in_array('age', $header, true);

            if (! empty($missingBaseColumns) || (! $hasDobColumn && ! $hasAgeColumn)) {
                $missingColumns = $missingBaseColumns;
                if (! $hasDobColumn && ! $hasAgeColumn) {
                    $missingColumns[] = 'date_of_birth (or age)';
                }
                fclose($handle);
                @unlink($tempPath);

                return [
                    'success_count' => 0,
                    'errors' => [
                        'CSV file is missing required columns: '.implode(', ', $missingColumns).'. Found columns: '.implode(', ', $header),
                    ],
                ];
            }

            $nameIndex = array_search('name', $header, true);
            $ageIndex = array_search('age', $header, true);
            $dobIndex = array_search('date_of_birth', $header, true);
            $genderIndex = array_search('gender', $header, true);
            $classIndex = array_search('class_name', $header, true);

            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if (empty(array_filter($row))) {
                    continue;
                }

                if (count($row) < count($header)) {
                    $errors[] = "Line {$lineNumber}: Row has insufficient columns.";

                    continue;
                }

                $name = trim((string) ($row[$nameIndex] ?? ''));
                $age = null;
                $dob = null;
                $gender = strtolower(trim((string) ($row[$genderIndex] ?? '')));
                $className = trim((string) ($row[$classIndex] ?? ''));

                if ($dobIndex !== false) {
                    $dob = trim((string) ($row[$dobIndex] ?? ''));
                }
                if ($ageIndex !== false) {
                    $age = trim((string) ($row[$ageIndex] ?? ''));
                }

                if ($name === '') {
                    $errors[] = "Line {$lineNumber}: Student name is required.";

                    continue;
                }

                if (strlen($name) > 255) {
                    $errors[] = "Line {$lineNumber}: Student name exceeds 255 characters.";

                    continue;
                }

                $dobDateForStore = null;
                if ($dob !== '') {
                    $parsedDob = $this->parseDateOfBirth($dob);
                    if ($parsedDob === null) {
                        $errors[] = "Line {$lineNumber}: Invalid date_of_birth. Use DD-MM-YYYY, DD/MM/YYYY, or YYYY-MM-DD.";

                        continue;
                    }
                    if ($parsedDob->isFuture()) {
                        $errors[] = "Line {$lineNumber}: Date of birth cannot be in the future.";

                        continue;
                    }
                    $age = $parsedDob->age;
                    $dobDateForStore = $parsedDob->format('Y-m-d');
                } elseif ($age !== '' && is_numeric($age)) {
                    $age = (int) $age;
                } else {
                    $errors[] = "Line {$lineNumber}: Provide date_of_birth (preferred) or a valid age.";

                    continue;
                }

                if ($age < 1 || $age > 150) {
                    $errors[] = "Line {$lineNumber}: Computed age must be between 1 and 150.";

                    continue;
                }

                if ($competition !== null) {
                    if ($competition->min_age !== null && $age < $competition->min_age) {
                        $errors[] = "Line {$lineNumber}: Age must be at least {$competition->min_age} for this competition.";

                        continue;
                    }
                    if ($competition->max_age !== null && $age > $competition->max_age) {
                        $errors[] = "Line {$lineNumber}: Age must be at most {$competition->max_age} for this competition.";

                        continue;
                    }
                }

                if ($gender === '' || ! in_array($gender, ['male', 'female', 'other'], true)) {
                    $errors[] = "Line {$lineNumber}: Gender must be 'male', 'female', or 'other'.";

                    continue;
                }

                if ($className === '') {
                    $errors[] = "Line {$lineNumber}: Class name is required.";

                    continue;
                }

                $classKey = strtolower($className);
                $schoolClassId = $classes[$classKey] ?? null;

                if (! $schoolClassId) {
                    $errors[] = "Line {$lineNumber}: Class '{$className}' not found. Please ensure the class exists and is active.";

                    continue;
                }

                if ($competition !== null && $allowedClassIds !== []) {
                    if (! in_array((int) $schoolClassId, array_map('intval', $allowedClassIds), true)) {
                        $errors[] = "Line {$lineNumber}: Class '{$className}' is not in the allowed class range for this competition.";

                        continue;
                    }
                }

                try {
                    Student::create([
                        'teacher_club_id' => $teacherClub->id,
                        'name' => $name,
                        'date_of_birth' => $dobDateForStore,
                        'age' => $age,
                        'gender' => $gender,
                        'school_class_id' => $schoolClassId,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to create student on line {$lineNumber}: ".$e->getMessage());
                    $errors[] = "Line {$lineNumber}: Failed to create student — ".$e->getMessage();
                }
            }

            fclose($handle);
            @unlink($tempPath);

            if ($errors !== []) {
                DB::rollBack();

                return ['success_count' => 0, 'errors' => $errors];
            }

            DB::commit();

            return ['success_count' => $successCount, 'errors' => []];
        } catch (\Exception $e) {
            if (isset($handle) && $handle !== false) {
                fclose($handle);
            }
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            DB::rollBack();
            Log::error('Bulk upload error: '.$e->getMessage());

            return ['success_count' => 0, 'errors' => ['An error occurred during bulk upload: '.$e->getMessage()]];
        }
    }

    public function formatErrorsForFlash(array $errors, int $maxLines = 10): string
    {
        $slice = array_slice($errors, 0, $maxLines);
        $message = "Bulk upload completed with errors:\n".implode("\n", $slice);
        if (count($errors) > $maxLines) {
            $message .= "\n... and ".(count($errors) - $maxLines).' more errors.';
        }

        return $message;
    }

    /**
     * Parse a date_of_birth value. Tries ISO first, then day-first formats common in spreadsheets.
     */
    public function parseDateOfBirth(string $dob): ?Carbon
    {
        $dob = trim($dob);
        if ($dob === '') {
            return null;
        }

        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y'];
        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $dob);
                if ($parsed->format($format) !== $dob) {
                    continue;
                }

                return $parsed;
            } catch (\Exception) {
                continue;
            }
        }

        return null;
    }
}
