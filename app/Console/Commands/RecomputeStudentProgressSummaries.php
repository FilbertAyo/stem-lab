<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\Learning\StudentProgressSummaryService;
use Illuminate\Console\Command;

class RecomputeStudentProgressSummaries extends Command
{
    protected $signature = 'progress:recompute-summaries {--student= : Recompute for a single student ID}';

    protected $description = 'Recompute denormalized student progress summaries for teacher dashboards';

    public function handle(StudentProgressSummaryService $summaryService): int
    {
        $studentId = $this->option('student');

        if ($studentId) {
            $student = Student::query()->findOrFail($studentId);
            $summaryService->recomputeForStudent($student);
            $this->info("Recomputed summary for student #{$student->id}.");

            return self::SUCCESS;
        }

        $count = 0;

        Student::query()->orderBy('id')->chunkById(100, function ($students) use ($summaryService, &$count): void {
            foreach ($students as $student) {
                $summaryService->recomputeForStudent($student);
                $count++;
            }
        });

        $this->info("Recomputed {$count} student summaries.");

        return self::SUCCESS;
    }
}
