<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vr_experiment_school_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vr_experiment_id')->constrained('vr_experiments')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['vr_experiment_id', 'school_class_id'], 'vr_exp_school_class_unique');
        });

        $now = now();

        foreach (DB::table('vr_experiments')->orderBy('id')->get() as $experiment) {
            if ($experiment->school_class_id === null) {
                continue;
            }

            DB::table('vr_experiment_school_class')->insertOrIgnore([
                'vr_experiment_id' => $experiment->id,
                'school_class_id' => $experiment->school_class_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $groups = DB::table('vr_experiments')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($experiment) => $experiment->external_url.'|'.$experiment->subject_id);

        foreach ($groups as $group) {
            if ($group->count() <= 1) {
                continue;
            }

            $keepId = (int) $group->first()->id;
            $duplicateIds = $group
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id !== $keepId)
                ->values()
                ->all();

            if ($duplicateIds === []) {
                continue;
            }

            $classIds = DB::table('vr_experiment_school_class')
                ->whereIn('vr_experiment_id', array_merge([$keepId], $duplicateIds))
                ->pluck('school_class_id')
                ->unique()
                ->values();

            foreach ($classIds as $classId) {
                DB::table('vr_experiment_school_class')->insertOrIgnore([
                    'vr_experiment_id' => $keepId,
                    'school_class_id' => $classId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('vr_question_banks')
                ->whereIn('vr_experiment_id', $duplicateIds)
                ->update(['vr_experiment_id' => $keepId]);

            DB::table('weekly_sessions')
                ->whereIn('vr_experiment_id', $duplicateIds)
                ->update(['vr_experiment_id' => $keepId]);

            DB::table('vr_experiment_school_class')
                ->whereIn('vr_experiment_id', $duplicateIds)
                ->delete();

            DB::table('vr_experiments')
                ->whereIn('id', $duplicateIds)
                ->delete();
        }

        Schema::table('vr_experiments', function (Blueprint $table) {
            $table->dropForeign(['school_class_id']);
            $table->dropColumn('school_class_id');
        });
    }

    public function down(): void
    {
        Schema::table('vr_experiments', function (Blueprint $table) {
            $table->foreignId('school_class_id')->nullable()->after('thumbnail')->constrained('school_classes')->cascadeOnDelete();
        });

        $pivotRows = DB::table('vr_experiment_school_class')
            ->orderBy('vr_experiment_id')
            ->orderBy('id')
            ->get()
            ->groupBy('vr_experiment_id');

        foreach ($pivotRows as $experimentId => $rows) {
            DB::table('vr_experiments')
                ->where('id', $experimentId)
                ->update(['school_class_id' => $rows->first()->school_class_id]);
        }

        Schema::dropIfExists('vr_experiment_school_class');
    }
};
