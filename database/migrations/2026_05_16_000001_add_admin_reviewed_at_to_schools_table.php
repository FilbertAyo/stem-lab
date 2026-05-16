<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            $table->timestamp('admin_reviewed_at')->nullable()->after('is_active');
            $table->index('admin_reviewed_at', 'schools_admin_reviewed_at_idx');
        });

        // Teacher-linked schools should be usable immediately; leave admin_reviewed_at null for review queue.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE schools
                SET is_active = 1
                WHERE is_active = 0
                AND EXISTS (
                    SELECT 1 FROM teacher_clubs WHERE teacher_clubs.school_id = schools.id
                )
            ');
        } else {
            DB::statement('
                UPDATE schools
                SET is_active = 1
                WHERE is_active = 0
                AND EXISTS (
                    SELECT 1 FROM teacher_clubs WHERE teacher_clubs.school_id = schools.id
                )
            ');
        }
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            $table->dropIndex('schools_admin_reviewed_at_idx');
            $table->dropColumn('admin_reviewed_at');
        });
    }
};
