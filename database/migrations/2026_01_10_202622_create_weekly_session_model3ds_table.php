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
        Schema::create('weekly_session_model3ds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_session_id')->constrained('weekly_sessions')->onDelete('cascade');
            $table->foreignId('model3d_id')->constrained('model3ds')->onDelete('cascade');
            $table->timestamps();

            // Ensure a 3D model can only be assigned once per session
            $table->unique(['weekly_session_id', 'model3d_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_session_model3ds');
    }
};
