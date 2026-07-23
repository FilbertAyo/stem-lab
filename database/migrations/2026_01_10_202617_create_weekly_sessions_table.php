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
        Schema::create('weekly_sessions', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->foreignId('vr_experiment_id')->nullable()->constrained('vr_experiments')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('session_date');
            $table->date('session_end_date')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'completed'])->default('draft');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('project_id')->nullable();
            $table->string('project_pdf')->nullable();
            $table->index('subject_id');
            $table->timestamps();

            // Indexes for performance
            $table->index('session_date');
            $table->index('session_end_date');
            $table->index('status');
            $table->index('club_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_sessions');
    }
};
