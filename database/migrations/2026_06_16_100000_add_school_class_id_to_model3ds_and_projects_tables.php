<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model3ds', function (Blueprint $table) {
            $table->foreignId('school_class_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('school_classes')
                ->nullOnDelete();

            $table->index('school_class_id', 'model3ds_school_class_id_index');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('school_class_id')
                ->nullable()
                ->after('project_category_id')
                ->constrained('school_classes')
                ->nullOnDelete();

            $table->index('school_class_id', 'projects_school_class_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('model3ds', function (Blueprint $table) {
            $table->dropForeign(['school_class_id']);
            $table->dropIndex('model3ds_school_class_id_index');
            $table->dropColumn('school_class_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['school_class_id']);
            $table->dropIndex('projects_school_class_id_index');
            $table->dropColumn('school_class_id');
        });
    }
};
