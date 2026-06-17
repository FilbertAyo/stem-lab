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
        Schema::create('weekly_session_reports', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();
            $table->foreignId('weekly_session_id')->nullable()->constrained('weekly_sessions')->onDelete('cascade');
            $table->foreignId('teacher_club_id')->constrained('teacher_clubs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('attendance_count')->nullable();
            $table->text('feedback')->nullable();
            $table->text('engagement_notes')->nullable();
            $table->datetime('conducted_at')->nullable();
            $table->timestamp('submitted_at')->nullable();

                      // Attendance Section (keeping attendance_count for backward compatibility, adding new fields)
                      $table->integer('total_students_registered')->nullable();
                      $table->integer('girls_present')->nullable();
                      $table->boolean('had_absent_students')->nullable();
                      $table->json('absence_reasons')->nullable();
                      $table->string('absence_reason_other')->nullable();
                      
                      // Session Details
                      $table->enum('activity_type', ['hands_on_experiment', 'project_work', 'group_discussion', 'competition_challenge', 'guest_speaker', 'video_demonstration'])->nullable();
                      $table->enum('activity_completed', ['fully_completed', 'partially_completed', 'not_completed'])->nullable();
                      $table->text('incompletion_reason')->nullable();
                      
                      // Student Engagement
                      $table->enum('engagement_level', ['very_engaged', 'moderately_engaged', 'low_engagement'])->nullable();
                      $table->enum('questions_participation', ['most_students_75pct', 'about_half_50_75pct', 'some_students_25_50pct', 'very_few_25pct'])->nullable();
                      $table->enum('girls_participation', ['very_active', 'moderately', 'not_much', 'no_girls_present'])->nullable();
                      $table->boolean('students_special_interest')->nullable();
                      $table->text('special_interest_details')->nullable();
                      
                      // Learning Outcomes
                      $table->enum('learning_understanding', ['all_understood', 'most_understood', 'some_understood', 'struggled'])->nullable();
                      $table->json('understanding_check_methods')->nullable();
                      $table->string('understanding_check_other')->nullable();
                      $table->text('students_learned')->nullable();
                      
                      // Challenges
                      $table->boolean('had_challenges')->nullable();
                      $table->json('challenge_types')->nullable();
                      $table->string('challenge_other')->nullable();
                      $table->text('challenge_description')->nullable();
                      
                      // Resources
                      $table->text('resources_used')->nullable();
                      $table->boolean('needs_additional_materials')->nullable();
                      $table->text('additional_materials_needed')->nullable();
                      
                      // Student Confidence & Attitudes
                      $table->enum('student_confidence', ['very_confident', 'somewhat_confident', 'not_very_confident', 'hesitant_afraid'])->nullable();
                      $table->enum('teamwork_quality', ['excellent_teamwork', 'good_teamwork', 'teamwork_issues', 'preferred_alone', 'not_applicable'])->nullable();
                      $table->enum('stem_attitudes', ['excited_positive', 'interested_neutral', 'bored_uninterested'])->nullable();
                      
                      // Projects & Competitions
                      $table->boolean('has_active_projects')->nullable();
                      $table->text('project_description')->nullable();
                      $table->enum('project_completion_status', ['just_started_0_25', 'in_progress_25_75', 'almost_done_75_100', 'completed', 'not_applicable'])->nullable();
                      $table->boolean('competition_preparation')->nullable();
                      $table->string('competition_name')->nullable();
                      
                      // Highlights
                      $table->text('session_highlight')->nullable();
                      $table->text('interesting_questions')->nullable();
                      
                      // Additional Comments
                      $table->text('additional_comments')->nullable();
                      $table->boolean('needs_support')->nullable();
                      $table->text('support_needed')->nullable();
                      
                      // Teacher Reflection
                      $table->enum('session_satisfaction', ['very_satisfied', 'satisfied', 'neutral', 'not_satisfied'])->nullable();
                      $table->text('would_do_differently')->nullable();
                      $table->text('what_worked_well')->nullable();

                      $table->string('pdf_file')->nullable();
                      
                      // Keep old fields (feedback, engagement_notes, attendance_count) for backward compatibility
            $table->timestamps();

            // Ensure one report per session per teacher_club
            $table->unique(['weekly_session_id', 'teacher_club_id']);

            // Indexes for performance
            $table->index('weekly_session_id');
            $table->index('user_id');
            $table->index('teacher_club_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_session_reports');
    }
};
