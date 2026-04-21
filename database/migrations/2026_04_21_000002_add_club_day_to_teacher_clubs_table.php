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
        Schema::table('teacher_clubs', function (Blueprint $table) {
            $table->enum('club_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
                ->nullable()
                ->after('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_clubs', function (Blueprint $table) {
            $table->dropColumn('club_day');
        });
    }
};
