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
        // Set all existing records to approved status
        DB::table('teacher_clubs')->update(['status' => 'approved']);

        // Get all unique club IDs
        $clubIds = DB::table('teacher_clubs')->distinct()->pluck('club_id');

        // For each club, find the first teacher (earliest created_at) and set as leader
        foreach ($clubIds as $clubId) {
            $firstTeacherClub = DB::table('teacher_clubs')
                ->where('club_id', $clubId)
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
        // Reset all records to pending status
        DB::table('teacher_clubs')->update([
            'status' => 'pending',
            'is_leader' => false,
        ]);
    }
};
