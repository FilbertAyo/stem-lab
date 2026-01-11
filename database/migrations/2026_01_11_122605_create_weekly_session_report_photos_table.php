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
        Schema::create('weekly_session_report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_session_report_id')->constrained('weekly_session_reports')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            // Index for performance
            $table->index('weekly_session_report_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_session_report_photos');
    }
};
