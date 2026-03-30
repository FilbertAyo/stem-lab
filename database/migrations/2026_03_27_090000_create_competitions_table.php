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
        Schema::create('competitions', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('terms_and_conditions')->nullable();
            $table->longText('instructions')->nullable();
            $table->string('cover_image')->nullable();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->unsignedSmallInteger('min_members')->default(1);
            $table->unsignedSmallInteger('max_members')->nullable();
            $table->unsignedInteger('representative_count')->default(10);
            $table->unsignedSmallInteger('min_age')->nullable();
            $table->unsignedSmallInteger('max_age')->nullable();
            $table->timestamp('registration_start')->nullable();
            $table->timestamp('registration_deadline')->nullable();
            $table->timestamp('competition_start')->nullable();
            $table->timestamp('competition_end')->nullable();
            $table->timestamp('results_announcement_date')->nullable();
            $table->string('status', 32)->default('draft');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('club_id', 'competitions_club_id_index');
            $table->index('status', 'competitions_status_index');
            $table->index(['status', 'is_active'], 'competitions_status_is_active_index');
            $table->index('competition_start', 'competitions_competition_start_index');
            $table->index('competition_end', 'competitions_competition_end_index');
            $table->index(
                ['registration_start', 'registration_deadline'],
                'competitions_registration_window_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
