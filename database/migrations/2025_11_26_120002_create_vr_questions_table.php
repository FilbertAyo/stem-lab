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
        Schema::create('vr_questions', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();
            $table->foreignId('question_bank_id')->constrained('vr_question_banks')->onDelete('cascade');
            $table->enum('question_type', ['mcq', 'true_false', 'short_answer'])->default('mcq');
            $table->text('question_text');
            $table->json('options')->nullable()->comment('JSON array of options for MCQ');
            $table->text('correct_answer');
            $table->text('explanation')->nullable()->comment('Explanation shown after answering');
            $table->integer('points')->default(1)->comment('Points awarded for correct answer');
            $table->integer('order')->default(0)->comment('Display order of question');
            $table->timestamps();

            // Index for faster queries
            $table->index(['question_bank_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_questions');
    }
};

