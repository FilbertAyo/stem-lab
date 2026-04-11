<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('competition_phase_evidence_forms')) {
            return;
        }

        Schema::create('competition_phase_evidence_forms', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();

            $table->unsignedBigInteger('competition_phase_id');
            $table->foreign('competition_phase_id', 'cpef_phase_fk')
                ->references('id')
                ->on('competition_phases')
                ->cascadeOnDelete();

            $table->string('google_form_url', 2048)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by', 'cpef_upd_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('competition_phase_id', 'cpef_phase_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_phase_evidence_forms');
    }
};

