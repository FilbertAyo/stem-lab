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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('terms_and_conditions')->nullable();
            $table->longText('instructions')->nullable(); // How to participate
            $table->string('cover_image')->nullable();
            
            // Club association - competition is tied to a specific club
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            
            // Participant limitations
            $table->integer('min_members')->default(1);
            $table->integer('max_members')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            
            // Important dates
            $table->dateTime('registration_start')->nullable();
            $table->dateTime('registration_deadline')->nullable();
            $table->dateTime('competition_start');
            $table->dateTime('competition_end');
            $table->dateTime('results_announcement_date')->nullable();
            
            // Status and visibility
            $table->enum('status', ['draft', 'published', 'ongoing', 'judging', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_active')->default(true);
            
            // Created by admin
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
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

