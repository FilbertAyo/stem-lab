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
        Schema::create('competition_participant_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_participant_id');
            $table->foreign('competition_participant_id', 'cps_cp_id_fk')
                ->references('id')
                ->on('competition_participants')
                ->onDelete('cascade');
            $table->foreignId('student_id');
            $table->foreign('student_id', 'cps_student_id_fk')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');
            
            // Role in the team (optional)
            $table->string('role')->nullable(); // e.g., "Team Leader", "Researcher", "Presenter"
            
            $table->timestamps();
            
            // A student can only be added once per participant entry
            $table->unique(['competition_participant_id', 'student_id'], 'participant_student_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_participant_students');
    }
};

