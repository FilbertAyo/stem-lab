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
        // The previous run may have created the table partially, but failed to add FKs
        // due to MySQL constraint name length limits.
        // Since this migration is still `Pending`, it's safe for dev to recreate cleanly.
        if (Schema::hasTable('competition_prototype_submissions')) {
            Schema::dropIfExists('competition_prototype_submissions');
        }

        Schema::create('competition_prototype_submissions', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();

            // Explicit FK constraint names (MySQL has a 64-char limit).
            $table->unsignedBigInteger('competition_id');
            $table->foreign('competition_id', 'cps_comp_fk')
                ->references('id')
                ->on('competitions')
                ->cascadeOnDelete();

            // For this MVP, prototype development is tied 1:1 with an idea.
            $table->unsignedBigInteger('competition_phase_id')->nullable();
            $table->foreign('competition_phase_id', 'cps_phase_fk')
                ->references('id')
                ->on('competition_phases')
                ->nullOnDelete();

            $table->unsignedBigInteger('competition_registration_id');
            $table->foreign('competition_registration_id', 'cps_reg_fk')
                ->references('id')
                ->on('competition_registrations')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('competition_idea_id');
            $table->foreign('competition_idea_id', 'cps_idea_fk')
                ->references('id')
                ->on('competition_ideas')
                ->cascadeOnDelete();

            // Filled when idea shortlisting has been decided and we have a shortlist row.
            $table->unsignedBigInteger('competition_shortlist_id')->nullable();
            $table->foreign('competition_shortlist_id', 'cps_short_fk')
                ->references('id')
                ->on('competition_shortlists')
                ->nullOnDelete();

            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->foreign('submitted_by', 'cps_sub_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->string('status', 32)->default('draft'); // draft|submitted
            $table->timestamp('submitted_at')->nullable();

            // Prototype "comprehensive form" content
            $table->string('prototype_title', 255)->nullable();
            $table->longText('prototype_concept')->nullable();
            $table->longText('prototype_objectives')->nullable();
            $table->longText('prototype_design_description')->nullable();
            $table->longText('prototype_materials')->nullable();
            $table->longText('prototype_build_process')->nullable();
            $table->longText('prototype_challenges')->nullable();
            $table->longText('prototype_test_results')->nullable();
            $table->longText('prototype_next_steps')->nullable();

            // We avoid local media storage by collecting external links (e.g. Google Forms / Drive)
            $table->string('prototype_media_upload_link', 2048)->nullable();
            $table->text('prototype_media_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('competition_idea_id', 'competition_prototype_submissions_idea_unique');
            $table->index(['competition_id', 'status'], 'competition_prototype_submissions_status_index');
            $table->index(['competition_phase_id'], 'competition_prototype_submissions_phase_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_prototype_submissions');
    }
};

