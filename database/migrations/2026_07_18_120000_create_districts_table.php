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
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['region_id', 'name']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->foreign('district_id')
                ->references('id')
                ->on('districts')
                ->nullOnDelete();
        });

        Schema::table('competition_registrations', function (Blueprint $table) {
            $table->foreign('district_id')
                ->references('id')
                ->on('districts')
                ->nullOnDelete();
        });

        Schema::table('competition_ideas', function (Blueprint $table) {
            $table->foreign('community_district_id')
                ->references('id')
                ->on('districts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_ideas', function (Blueprint $table) {
            $table->dropForeign(['community_district_id']);
        });

        Schema::table('competition_registrations', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
        });

        Schema::dropIfExists('districts');
    }
};
