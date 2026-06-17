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
        Schema::create('vr_question_answers', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();
            $table->foreignId('attempt_id')->constrained('vr_assessment_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('vr_questions')->onDelete('cascade');
            $table->text('student_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->timestamps();

            // Index for faster queries
            $table->index(['attempt_id', 'question_id']);
            
            // Ensure one answer per question per attempt
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_question_answers');
    }
};

