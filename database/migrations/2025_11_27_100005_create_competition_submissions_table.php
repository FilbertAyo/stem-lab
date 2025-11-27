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
        Schema::create('competition_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_participant_id')->constrained('competition_participants')->onDelete('cascade');
            
            // Submission content
            $table->string('title');
            $table->longText('description')->nullable(); // Detailed explanation of the experiment/project
            $table->longText('methodology')->nullable(); // How the experiment was conducted
            $table->longText('results')->nullable(); // Results/findings
            $table->longText('conclusion')->nullable(); // Conclusions drawn
            
            // Additional details
            $table->text('materials_used')->nullable(); // Materials/equipment used
            $table->text('challenges_faced')->nullable(); // Challenges encountered
            $table->text('learning_outcomes')->nullable(); // What students learned
            
            // Submission status
            $table->enum('status', ['draft', 'submitted', 'under_review', 'reviewed', 'revision_requested'])->default('draft');
            $table->dateTime('submitted_at')->nullable();
            
            // Judging/scoring
            $table->decimal('score', 8, 2)->nullable();
            $table->longText('judge_feedback')->nullable();
            $table->foreignId('judged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('judged_at')->nullable();
            
            // Version tracking (in case of revisions)
            $table->integer('version')->default(1);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_submissions');
    }
};

