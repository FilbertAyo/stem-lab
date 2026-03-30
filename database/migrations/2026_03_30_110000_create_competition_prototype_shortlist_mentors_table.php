<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('competition_prototype_shortlist_mentors')) {
            return;
        }

        Schema::create('competition_prototype_shortlist_mentors', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();

            $table->unsignedBigInteger('competition_id');
            $table->foreign('competition_id', 'cpsm_comp_fk')
                ->references('id')
                ->on('competitions')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('competition_prototype_shortlist_id');
            $table->foreign('competition_prototype_shortlist_id', 'cpsm_sl_fk')
                ->references('id')
                ->on('competition_prototype_shortlists')
                ->cascadeOnDelete();

            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();

            // Use a URL/string to avoid local storage complexity in this MVP.
            $table->string('picture_url', 2048)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by', 'cpsm_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['competition_id', 'competition_prototype_shortlist_id'], 'cpsm_comp_sl_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_prototype_shortlist_mentors');
    }
};

