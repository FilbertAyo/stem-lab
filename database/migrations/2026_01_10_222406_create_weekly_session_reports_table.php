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
        Schema::create('weekly_session_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_session_id')->constrained('weekly_sessions')->onDelete('cascade');
            $table->foreignId('teacher_club_id')->constrained('teacher_clubs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('attendance_count')->nullable();
            $table->text('feedback')->nullable();
            $table->text('engagement_notes')->nullable();
            $table->datetime('conducted_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // Ensure one report per session per teacher_club
            $table->unique(['weekly_session_id', 'teacher_club_id']);

            // Indexes for performance
            $table->index('weekly_session_id');
            $table->index('user_id');
            $table->index('teacher_club_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_session_reports');
    }
};
