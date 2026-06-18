<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_progress_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_club_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('models_attempted')->default(0);
            $table->unsignedInteger('models_completed')->default(0);
            $table->unsignedInteger('vr_attempts')->default(0);
            $table->unsignedInteger('vr_passed')->default(0);
            $table->decimal('avg_vr_score', 5, 2)->nullable();
            $table->unsignedInteger('resources_accessed')->default(0);
            $table->unsignedInteger('projects_viewed')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->json('attention_flags')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_club_id', 'last_activity_at'], 'sps_club_activity_idx');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progress_summaries');
    }
};
