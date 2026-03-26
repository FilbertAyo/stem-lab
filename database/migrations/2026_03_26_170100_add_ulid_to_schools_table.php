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
        Schema::table('schools', function (Blueprint $table) {
            $table->char('ulid', 26)->nullable()->unique()->after('id');
        });

        DB::table('schools')
            ->select('id')
            ->whereNull('ulid')
            ->orderBy('id')
            ->chunkById(200, function ($schools): void {
                foreach ($schools as $school) {
                    DB::table('schools')
                        ->where('id', $school->id)
                        ->update(['ulid' => (string) Str::ulid()]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropUnique(['ulid']);
            $table->dropColumn('ulid');
        });
    }
};
