<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('weekly_sessions', function (Blueprint $table) {
            $table->date('session_end_date')->nullable()->after('session_date');
            $table->foreignId('project_id')->nullable()->after('subject_id')
                ->constrained('projects')->nullOnDelete();
            $table->string('project_pdf')->nullable()->after('project_id');
            $table->index('session_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('weekly_sessions', 'project_id')) {
            Schema::table('weekly_sessions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('project_id');
            });
        }

        if (Schema::hasColumn('weekly_sessions', 'project_pdf')) {
            Schema::table('weekly_sessions', function (Blueprint $table) {
                $table->dropColumn('project_pdf');
            });
        }

        if (Schema::hasColumn('weekly_sessions', 'session_end_date')) {
            Schema::table('weekly_sessions', function (Blueprint $table) {
                $table->dropIndex(['session_end_date']);
                $table->dropColumn('session_end_date');
            });
        }
    }
};
