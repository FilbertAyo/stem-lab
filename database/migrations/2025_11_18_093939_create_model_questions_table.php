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
        Schema::create('model_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('model3ds')->onDelete('cascade');
            $table->enum('question_type', ['mcq', 'true_false', 'short_answer'])->default('mcq');
            $table->text('question_text');
            $table->json('options')->nullable(); // For MCQ options
            $table->text('correct_answer');
            $table->text('explanation')->nullable();
            $table->integer('order')->default(0); // For reordering questions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_questions');
    }
};
