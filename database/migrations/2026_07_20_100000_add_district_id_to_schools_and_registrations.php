<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->foreignId('district_id')
                ->nullable()
                ->after('region_id')
                ->constrained('districts')
                ->nullOnDelete();
        });

        Schema::table('competition_registrations', function (Blueprint $table) {
            $table->foreignId('district_id')
                ->nullable()
                ->after('region_id')
                ->constrained('districts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('competition_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('district_id');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropConstrainedForeignId('district_id');
        });
    }
};
