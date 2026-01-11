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
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('school_id');
            $table->boolean('is_leader')->default(false)->after('status');
            $table->index('status');
            $table->index(['club_id', 'is_leader']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_clubs', function (Blueprint $table) {
            $table->dropIndex(['club_id', 'is_leader']);
            $table->dropIndex(['status']);
            $table->dropColumn(['is_leader', 'status']);
        });
    }
};
