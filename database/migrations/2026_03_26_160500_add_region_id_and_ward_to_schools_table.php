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
        Schema::table('schools', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('district')->constrained('regions')->nullOnDelete();
            $table->string('ward')->nullable()->after('region_id');
        });

        // Backfill region_id from legacy schools.region text where possible.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE schools
                SET region_id = (
                    SELECT r.id FROM regions r WHERE r.name = schools.region LIMIT 1
                )
                WHERE region IS NOT NULL AND region_id IS NULL
            ');
        } else {
            DB::statement('
                UPDATE schools s
                INNER JOIN regions r ON r.name = s.region
                SET s.region_id = r.id
                WHERE s.region IS NOT NULL AND s.region_id IS NULL
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
            $table->dropColumn('ward');
        });
    }
};
