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
        Schema::create('competition_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->onDelete('cascade');
            $table->foreignId('teacher_club_id')->constrained('teacher_clubs')->onDelete('cascade');
            
            // Team information
            $table->string('team_name')->nullable();
            $table->text('team_description')->nullable();
            
            // Registration status
            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn', 'disqualified'])->default('pending');
            $table->text('rejection_reason')->nullable();
            
            // Award won (if any)
            $table->foreignId('award_id')->nullable()->constrained('competition_awards')->onDelete('set null');
            
            // Scoring
            $table->decimal('final_score', 8, 2)->nullable();
            $table->integer('rank')->nullable();
            
            $table->dateTime('registered_at');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            
            // A teacher-club can only register once per competition
            $table->unique(['competition_id', 'teacher_club_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_participants');
    }
};

