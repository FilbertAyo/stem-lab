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
        Schema::create('teacher_clubs', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();
            $table->string('club_code', 8);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->enum('club_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_leader')->default(false);
            $table->index('status');
            $table->index(['club_id', 'is_leader']);
            $table->index('club_code');

            $table->timestamps();

            // Ensure a teacher can only join a club once
            $table->unique(['user_id', 'club_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_clubs');
    }
};
