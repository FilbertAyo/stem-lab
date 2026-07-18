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
        Schema::create('vr_assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();
            $table->char('public_id', 6)->nullable()->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('question_bank_id')->constrained('vr_question_banks')->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->decimal('score', 5, 2)->default(0)->comment('Score as percentage');
            $table->integer('correct_answers')->default(0);
            $table->integer('total_questions')->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamps();

            // Index for faster queries
            $table->index(['user_id', 'question_bank_id']);
            $table->index(['question_bank_id', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_assessment_attempts');
    }
};

