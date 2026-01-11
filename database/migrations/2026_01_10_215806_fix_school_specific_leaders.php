<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset all leader flags first
        DB::table('teacher_clubs')->update(['is_leader' => false]);

        // Get all unique club-school combinations
        $clubSchoolCombos = DB::table('teacher_clubs')
            ->select('club_id', 'school_id')
            ->whereNotNull('school_id')
            ->distinct()
            ->get();

        // For each club-school combination, find the first teacher (earliest created_at) and set as leader
        foreach ($clubSchoolCombos as $combo) {
            $firstTeacherClub = DB::table('teacher_clubs')
                ->where('club_id', $combo->club_id)
                ->where('school_id', $combo->school_id)
                ->where('status', 'approved')
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstTeacherClub) {
                DB::table('teacher_clubs')
                    ->where('id', $firstTeacherClub->id)
                    ->update(['is_leader' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all leader flags
        DB::table('teacher_clubs')->update(['is_leader' => false]);

        // Revert to old logic: first teacher per club (not per school)
        $clubIds = DB::table('teacher_clubs')->distinct()->pluck('club_id');

        foreach ($clubIds as $clubId) {
            $firstTeacherClub = DB::table('teacher_clubs')
                ->where('club_id', $clubId)
                ->where('status', 'approved')
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstTeacherClub) {
                DB::table('teacher_clubs')
                    ->where('id', $firstTeacherClub->id)
                    ->update(['is_leader' => true]);
            }
        }
    }
};
