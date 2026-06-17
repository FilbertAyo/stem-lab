<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model3d_school_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model3d_id')->constrained('model3ds')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['model3d_id', 'school_class_id']);
        });

        if (Schema::hasColumn('model3ds', 'school_class_id')) {
            $models = DB::table('model3ds')
                ->whereNotNull('school_class_id')
                ->get(['id', 'school_class_id']);

            foreach ($models as $model) {
                DB::table('model3d_school_class')->insert([
                    'model3d_id' => $model->id,
                    'school_class_id' => $model->school_class_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('model3ds', function (Blueprint $table) {
                $table->dropForeign(['school_class_id']);
                $table->dropIndex('model3ds_school_class_id_index');
                $table->dropColumn('school_class_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('model3ds', function (Blueprint $table) {
            $table->foreignId('school_class_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('school_classes')
                ->nullOnDelete();

            $table->index('school_class_id', 'model3ds_school_class_id_index');
        });

        $assignments = DB::table('model3d_school_class')
            ->select('model3d_id', DB::raw('MIN(school_class_id) as school_class_id'))
            ->groupBy('model3d_id')
            ->get();

        foreach ($assignments as $assignment) {
            DB::table('model3ds')
                ->where('id', $assignment->model3d_id)
                ->update(['school_class_id' => $assignment->school_class_id]);
        }

        Schema::dropIfExists('model3d_school_class');
    }
};
