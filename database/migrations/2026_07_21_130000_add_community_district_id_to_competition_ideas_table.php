<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_ideas', function (Blueprint $table) {
            $table->foreignId('community_district_id')
                ->nullable()
                ->after('community_region_id')
                ->constrained('districts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('competition_ideas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('community_district_id');
        });
    }
};
