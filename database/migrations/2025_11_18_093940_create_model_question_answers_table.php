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
        Schema::create('model_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('model_id')->constrained('model3ds')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('model_questions')->onDelete('cascade');
            $table->text('student_answer');
            $table->boolean('is_correct')->default(false);
            $table->integer('score')->default(0); // 1 for correct, 0 for incorrect
            $table->timestamps();

            // Performance indexes
            $table->index(['user_id', 'model_id']);
            $table->index('question_id', 'model_question_answers_question_id_index');
            $table->index('is_correct', 'model_question_answers_is_correct_index');
            $table->index(['user_id', 'is_correct'], 'model_question_answers_user_id_is_correct_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_question_answers');
    }
};
