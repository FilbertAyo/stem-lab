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
        Schema::table('weekly_session_reports', function (Blueprint $table) {
            // Attendance Section (keeping attendance_count for backward compatibility, adding new fields)
            $table->integer('total_students_registered')->nullable()->after('attendance_count');
            $table->integer('girls_present')->nullable()->after('total_students_registered');
            $table->boolean('had_absent_students')->nullable()->after('girls_present');
            $table->json('absence_reasons')->nullable()->after('had_absent_students');
            $table->string('absence_reason_other')->nullable()->after('absence_reasons');
            
            // Session Details
            $table->enum('activity_type', ['hands_on_experiment', 'project_work', 'group_discussion', 'competition_challenge', 'guest_speaker', 'video_demonstration'])->nullable()->after('absence_reason_other');
            $table->enum('activity_completed', ['fully_completed', 'partially_completed', 'not_completed'])->nullable()->after('activity_type');
            $table->text('incompletion_reason')->nullable()->after('activity_completed');
            
            // Student Engagement
            $table->enum('engagement_level', ['very_engaged', 'moderately_engaged', 'low_engagement'])->nullable()->after('incompletion_reason');
            $table->enum('questions_participation', ['most_students_75pct', 'about_half_50_75pct', 'some_students_25_50pct', 'very_few_25pct'])->nullable()->after('engagement_level');
            $table->enum('girls_participation', ['very_active', 'moderately', 'not_much', 'no_girls_present'])->nullable()->after('questions_participation');
            $table->boolean('students_special_interest')->nullable()->after('girls_participation');
            $table->text('special_interest_details')->nullable()->after('students_special_interest');
            
            // Learning Outcomes
            $table->enum('learning_understanding', ['all_understood', 'most_understood', 'some_understood', 'struggled'])->nullable()->after('special_interest_details');
            $table->json('understanding_check_methods')->nullable()->after('learning_understanding');
            $table->string('understanding_check_other')->nullable()->after('understanding_check_methods');
            $table->text('students_learned')->nullable()->after('understanding_check_other');
            
            // Challenges
            $table->boolean('had_challenges')->nullable()->after('students_learned');
            $table->json('challenge_types')->nullable()->after('had_challenges');
            $table->string('challenge_other')->nullable()->after('challenge_types');
            $table->text('challenge_description')->nullable()->after('challenge_other');
            
            // Resources
            $table->text('resources_used')->nullable()->after('challenge_description');
            $table->boolean('needs_additional_materials')->nullable()->after('resources_used');
            $table->text('additional_materials_needed')->nullable()->after('needs_additional_materials');
            
            // Student Confidence & Attitudes
            $table->enum('student_confidence', ['very_confident', 'somewhat_confident', 'not_very_confident', 'hesitant_afraid'])->nullable()->after('additional_materials_needed');
            $table->enum('teamwork_quality', ['excellent_teamwork', 'good_teamwork', 'teamwork_issues', 'preferred_alone', 'not_applicable'])->nullable()->after('student_confidence');
            $table->enum('stem_attitudes', ['excited_positive', 'interested_neutral', 'bored_uninterested'])->nullable()->after('teamwork_quality');
            
            // Projects & Competitions
            $table->boolean('has_active_projects')->nullable()->after('stem_attitudes');
            $table->text('project_description')->nullable()->after('has_active_projects');
            $table->enum('project_completion_status', ['just_started_0_25', 'in_progress_25_75', 'almost_done_75_100', 'completed', 'not_applicable'])->nullable()->after('project_description');
            $table->boolean('competition_preparation')->nullable()->after('project_completion_status');
            $table->string('competition_name')->nullable()->after('competition_preparation');
            
            // Highlights
            $table->text('session_highlight')->nullable()->after('competition_name');
            $table->text('interesting_questions')->nullable()->after('session_highlight');
            
            // Additional Comments
            $table->text('additional_comments')->nullable()->after('interesting_questions');
            $table->boolean('needs_support')->nullable()->after('additional_comments');
            $table->text('support_needed')->nullable()->after('needs_support');
            
            // Teacher Reflection
            $table->enum('session_satisfaction', ['very_satisfied', 'satisfied', 'neutral', 'not_satisfied'])->nullable()->after('support_needed');
            $table->text('would_do_differently')->nullable()->after('session_satisfaction');
            $table->text('what_worked_well')->nullable()->after('would_do_differently');
            
            // Keep old fields (feedback, engagement_notes, attendance_count) for backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_session_reports', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'total_students_registered',
                'girls_present',
                'had_absent_students',
                'absence_reasons',
                'absence_reason_other',
                'activity_type',
                'activity_completed',
                'incompletion_reason',
                'engagement_level',
                'questions_participation',
                'girls_participation',
                'students_special_interest',
                'special_interest_details',
                'learning_understanding',
                'understanding_check_methods',
                'understanding_check_other',
                'students_learned',
                'had_challenges',
                'challenge_types',
                'challenge_other',
                'challenge_description',
                'resources_used',
                'needs_additional_materials',
                'additional_materials_needed',
                'student_confidence',
                'teamwork_quality',
                'stem_attitudes',
                'has_active_projects',
                'project_description',
                'project_completion_status',
                'competition_preparation',
                'competition_name',
                'session_highlight',
                'interesting_questions',
                'additional_comments',
                'needs_support',
                'support_needed',
                'session_satisfaction',
                'would_do_differently',
                'what_worked_well',
            ]);
        });
    }
};
