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
        Schema::create('submission_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_submission_id')->constrained('competition_submissions')->onDelete('cascade');
            
            // File information
            $table->enum('type', ['pdf', 'image', 'document', 'video', 'other'])->default('other');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // in bytes
            
            // Metadata
            $table->string('caption')->nullable(); // Brief description of the attachment
            $table->text('description')->nullable(); // Detailed description
            $table->integer('order')->default(0); // For ordering attachments
            
            // For images - categorization
            $table->string('category')->nullable(); // e.g., "experiment_setup", "results", "team_photo", "equipment"
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_attachments');
    }
};

