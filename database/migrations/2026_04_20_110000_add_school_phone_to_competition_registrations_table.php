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
        Schema::table('competition_registrations', function (Blueprint $table): void {
            $table->string('school_phone', 50)->nullable()->after('teacher_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_registrations', function (Blueprint $table): void {
            $table->dropColumn('school_phone');
        });
    }
};
