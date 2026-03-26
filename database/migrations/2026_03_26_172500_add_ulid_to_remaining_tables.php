<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'users',
            'subjects',
            'model3ds',
            'model_questions',
            'model_question_answers',
            'school_classes',
            'teacher_clubs',
            'students',
            'vr_experiments',
            'vr_question_banks',
            'vr_questions',
            'vr_assessment_attempts',
            'vr_question_answers',
            'competitions',
            'competition_awards',
            'competition_participants',
            'competition_participant_students',
            'competition_submissions',
            'submission_attachments',
            'weekly_sessions',
            'weekly_session_classes',
            'weekly_session_model3ds',
            'weekly_session_reports',
            'weekly_session_report_photos',
            'project_categories',
            'project_tags',
            'projects',
            'project_steps',
            'project_resources',
            'project_codes',
            'platforms',
            'settings',
            'smiley_tutor_credentials',
            'regions',
            'visitors',
            'visitor_weekly_stats',
            'competition_participants',
            'competition_awards',
            'competition_submissions',
            'project_tag',
            'equipment',
            'equipment_project',
            'project_platform',
            'schools',
            'clubs',
        ];

        foreach (array_unique($tables) as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id') || Schema::hasColumn($table, 'ulid')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table): void {
                $table->char('ulid', 26)->nullable()->unique()->after('id');
            });

            DB::table($table)
                ->select('id')
                ->whereNull('ulid')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->update(['ulid' => (string) Str::ulid()]);
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'subjects',
            'model3ds',
            'model_questions',
            'model_question_answers',
            'school_classes',
            'teacher_clubs',
            'students',
            'vr_experiments',
            'vr_question_banks',
            'vr_questions',
            'vr_assessment_attempts',
            'vr_question_answers',
            'competitions',
            'competition_awards',
            'competition_participants',
            'competition_participant_students',
            'competition_submissions',
            'submission_attachments',
            'weekly_sessions',
            'weekly_session_classes',
            'weekly_session_model3ds',
            'weekly_session_reports',
            'weekly_session_report_photos',
            'project_categories',
            'project_tags',
            'projects',
            'project_steps',
            'project_resources',
            'project_codes',
            'platforms',
            'settings',
            'smiley_tutor_credentials',
            'regions',
            'visitors',
            'visitor_weekly_stats',
            'project_tag',
            'equipment',
            'equipment_project',
            'project_platform',
            'schools',
            'clubs',
        ];

        foreach (array_unique($tables) as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id') || !Schema::hasColumn($table, 'ulid')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table): void {
                $table->dropUnique(['ulid']);
                $table->dropColumn('ulid');
            });
        }
    }
};
