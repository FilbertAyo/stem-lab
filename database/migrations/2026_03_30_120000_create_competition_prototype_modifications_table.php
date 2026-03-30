<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('competition_prototype_modifications')) {
            return;
        }

        Schema::create('competition_prototype_modifications', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();

            $table->unsignedBigInteger('competition_id');
            $table->foreign('competition_id', 'cpm_comp_fk')
                ->references('id')
                ->on('competitions')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('competition_phase_id')->nullable();
            $table->foreign('competition_phase_id', 'cpm_phase_fk')
                ->references('id')
                ->on('competition_phases')
                ->nullOnDelete();

            $table->unsignedBigInteger('competition_registration_id');
            $table->foreign('competition_registration_id', 'cpm_reg_fk')
                ->references('id')
                ->on('competition_registrations')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('competition_idea_id');
            $table->foreign('competition_idea_id', 'cpm_idea_fk')
                ->references('id')
                ->on('competition_ideas')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('competition_prototype_submission_id')->nullable();
            $table->foreign('competition_prototype_submission_id', 'cpm_psub_fk')
                ->references('id')
                ->on('competition_prototype_submissions')
                ->nullOnDelete();

            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->foreign('submitted_by', 'cpm_sub_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->string('status', 32)->default('draft'); // draft|submitted
            $table->timestamp('submitted_at')->nullable();

            $table->string('prototype_title', 255)->nullable();
            $table->longText('prototype_concept')->nullable();
            $table->longText('prototype_objectives')->nullable();
            $table->longText('prototype_design_description')->nullable();
            $table->longText('prototype_materials')->nullable();
            $table->longText('prototype_build_process')->nullable();
            $table->longText('prototype_challenges')->nullable();
            $table->longText('prototype_test_results')->nullable();
            $table->longText('prototype_next_steps')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('competition_idea_id', 'cpm_idea_unique');
            $table->index(['competition_id', 'status'], 'cpm_comp_status_idx');
            $table->index(['competition_phase_id'], 'cpm_phase_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_prototype_modifications');
    }
};

