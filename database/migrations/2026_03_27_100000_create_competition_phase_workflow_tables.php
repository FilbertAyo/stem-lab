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
        Schema::create('competition_phases', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->string('phase_key', 64);
            $table->unsignedSmallInteger('phase_order')->nullable();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sequence');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->string('status', 32)->default('upcoming');
            $table->boolean('is_default')->default(true);
            $table->boolean('is_required_action')->default(false);
            $table->string('action_type', 32)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['competition_id', 'phase_key'], 'cph_comp_phase_key_uq');
            $table->index(['competition_id', 'sequence'], 'cph_comp_seq_idx');
            $table->index(['competition_id', 'status'], 'cph_comp_status_idx');
            $table->index(['opens_at', 'closes_at'], 'cph_window_idx');
        });

        Schema::create('competition_class_requirements', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['competition_id', 'school_class_id'],
                'ccr_comp_class_uq'
            );
            $table->index('school_class_id', 'ccr_class_idx');
        });

        Schema::create('competition_registrations', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('teacher_club_id')->constrained('teacher_clubs')->cascadeOnDelete();
            $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('district_id')->nullable();
            $table->string('status', 32)->default('pending');
            $table->string('teacher_name', 255);
            $table->string('teacher_email', 255);
            $table->string('teacher_phone', 50)->nullable();
            $table->string('school_name', 255);
            $table->string('school_district', 255)->nullable();
            $table->string('school_ward', 255)->nullable();
            $table->unsignedSmallInteger('student_count_snapshot')->default(0);
            $table->unsignedSmallInteger('girls_count_snapshot')->default(0);
            $table->decimal('girls_ratio_snapshot', 5, 2)->default(0);
            $table->boolean('eligibility_met')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('eligibility_reminder_count')->default(0);
            $table->timestamp('last_eligibility_reminder_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['competition_id', 'teacher_club_id'],
                'cr_comp_tclub_uq'
            );
            $table->index(
                ['competition_id', 'status'],
                'cr_comp_status_idx'
            );
            $table->index(['school_id', 'status'], 'cr_school_status_idx');
            $table->index('region_id', 'cr_region_idx');
        });

        Schema::create('competition_registration_students', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->unsignedBigInteger('competition_registration_id');
            $table->unsignedBigInteger('student_id');
            $table->string('role', 120)->nullable();
            $table->boolean('is_representative')->default(true);
            $table->timestamps();

            $table->foreign('competition_registration_id', 'crs_reg_fk')
                ->references('id')
                ->on('competition_registrations')
                ->cascadeOnDelete();
            $table->foreign('student_id', 'crs_student_fk')
                ->references('id')
                ->on('students')
                ->cascadeOnDelete();

            $table->unique(
                ['competition_registration_id', 'student_id'],
                'crs_reg_student_uq'
            );
            $table->index(
                ['competition_registration_id', 'is_representative'],
                'crs_reg_rep_idx'
            );
        });

        Schema::create('competition_ideas', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('competition_registration_id')->constrained('competition_registrations')->cascadeOnDelete();
            $table->string('status', 32)->default('draft');
            $table->string('problem_title', 255)->nullable();
            $table->longText('problem_statement')->nullable();
            $table->longText('proposed_solution')->nullable();
            $table->string('stem_category', 120)->nullable();
            $table->foreignId('community_region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('community_district_id')->nullable();
            $table->string('community_location', 255)->nullable();
            $table->longText('supporting_notes')->nullable();
            $table->json('attachments_meta')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('competition_registration_id', 'ci_reg_uq');
            $table->index(['competition_id', 'status'], 'ci_comp_status_idx');
            $table->index('community_region_id', 'ci_region_idx');
            $table->index('submitted_at', 'ci_submitted_idx');
        });

        Schema::create('competition_judge_assignments', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('competition_phase_id')
                ->nullable()
                ->constrained('competition_phases', 'id', 'cja_phase_fk')
                ->nullOnDelete();
            $table->foreignId('judge_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['competition_id', 'competition_phase_id', 'judge_user_id', 'region_id'],
                'cja_comp_phase_judge_region_uq'
            );
            $table->index(['competition_id', 'region_id'], 'cja_comp_region_idx');
        });

        Schema::create('competition_idea_scores', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('competition_idea_id')->constrained('competition_ideas')->cascadeOnDelete();
            $table->foreignId('judge_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('stem_score', 5, 2)->default(0);
            $table->decimal('product_potential_score', 5, 2)->default(0);
            $table->decimal('community_relevance_score', 5, 2)->default(0);
            $table->decimal('creativity_score', 5, 2)->default(0);
            $table->decimal('local_material_use_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(
                ['competition_idea_id', 'judge_user_id'],
                'cis_idea_judge_uq'
            );
            $table->index(['competition_id', 'total_score'], 'cis_comp_total_idx');
        });

        Schema::create('competition_shortlists', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('competition_phase_id')
                ->nullable()
                ->constrained('competition_phases', 'id', 'csl_phase_fk')
                ->nullOnDelete();
            $table->foreignId('competition_idea_id')->constrained('competition_ideas')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->unsignedSmallInteger('rank')->nullable();
            $table->string('status', 32)->default('shortlisted');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('competition_idea_id', 'csl_idea_uq');
            $table->index(['competition_id', 'region_id', 'status'], 'csl_comp_region_status_idx');
        });

        Schema::create('competition_stage_events', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('competition_phase_id')
                ->nullable()
                ->constrained('competition_phases', 'id', 'cse_phase_fk')
                ->nullOnDelete();
            $table->string('event_type', 64);
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['competition_id', 'created_at'], 'cse_comp_time_idx');
            $table->index('event_type', 'cse_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_stage_events');
        Schema::dropIfExists('competition_shortlists');
        Schema::dropIfExists('competition_idea_scores');
        Schema::dropIfExists('competition_judge_assignments');
        Schema::dropIfExists('competition_ideas');
        Schema::dropIfExists('competition_registration_students');
        Schema::dropIfExists('competition_registrations');
        Schema::dropIfExists('competition_class_requirements');
        Schema::dropIfExists('competition_phases');
    }
};
