<?php

use App\Services\ShortCodeGenerator;
use App\Services\TeacherClubCodeService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('admission_number', 8)->nullable()->after('id');
        });

        Schema::table('teacher_clubs', function (Blueprint $table) {
            $table->string('club_code', 8)->nullable()->after('id');
        });

        $this->backfillAdmissionNumbers();
        $this->backfillClubCodes();

        Schema::table('students', function (Blueprint $table) {
            $table->string('admission_number', 8)->nullable(false)->change();
            $table->unique('admission_number');
        });

        Schema::table('teacher_clubs', function (Blueprint $table) {
            $table->string('club_code', 8)->nullable(false)->change();
            $table->index('club_code');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['admission_number']);
            $table->dropColumn('admission_number');
        });

        Schema::table('teacher_clubs', function (Blueprint $table) {
            $table->dropIndex(['club_code']);
            $table->dropColumn('club_code');
        });
    }

    private function backfillAdmissionNumbers(): void
    {
        $generator = app(ShortCodeGenerator::class);

        DB::table('students')
            ->select('id')
            ->whereNull('admission_number')
            ->orderBy('id')
            ->each(function ($student) use ($generator) {
                DB::table('students')
                    ->where('id', $student->id)
                    ->update([
                        'admission_number' => $generator->generate('students', 'admission_number'),
                    ]);
            });
    }

    private function backfillClubCodes(): void
    {
        $clubCodeService = app(TeacherClubCodeService::class);

        DB::table('teacher_clubs')
            ->select('id', 'club_id', 'school_id')
            ->whereNull('club_code')
            ->where(function ($query) {
                $query->whereNull('school_id')
                    ->orWhereNull('club_id');
            })
            ->orderBy('id')
            ->each(function ($membership) use ($clubCodeService) {
                DB::table('teacher_clubs')
                    ->where('id', $membership->id)
                    ->update([
                        'club_code' => $clubCodeService->generateUniqueClubCode(
                            $membership->club_id !== null ? (int) $membership->club_id : null,
                            null,
                        ),
                    ]);
            });

        $clubCodeService->backfillAllGroups();
    }
};
