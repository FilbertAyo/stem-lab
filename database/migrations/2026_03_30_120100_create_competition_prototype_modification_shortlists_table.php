<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('competition_prototype_modification_shortlists')) {
            return;
        }

        Schema::create('competition_prototype_modification_shortlists', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();

            $table->unsignedBigInteger('competition_id');
            $table->foreign('competition_id', 'cpms_comp_fk')
                ->references('id')
                ->on('competitions')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('competition_phase_id')->nullable();
            $table->foreign('competition_phase_id', 'cpms_phase_fk')
                ->references('id')
                ->on('competition_phases')
                ->nullOnDelete();

            $table->unsignedBigInteger('competition_prototype_modification_id');
            $table->foreign('competition_prototype_modification_id', 'cpms_mod_fk')
                ->references('id')
                ->on('competition_prototype_modifications')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('region_id')->nullable();
            $table->foreign('region_id', 'cpms_reg_fk')
                ->references('id')
                ->on('regions')
                ->nullOnDelete();

            $table->unsignedSmallInteger('rank')->nullable();
            $table->string('status', 32)->default('shortlisted'); // shortlisted|waitlist|rejected
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->foreign('decided_by', 'cpms_dec_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->longText('feedback')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('competition_prototype_modification_id', 'cpms_mod_unique');
            $table->index(['competition_id', 'status'], 'cpms_comp_status_idx');
            $table->index(['competition_phase_id', 'region_id'], 'cpms_phase_region_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_prototype_modification_shortlists');
    }
};

