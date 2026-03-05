<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_category_id')
                ->nullable()
                ->constrained('project_categories')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->unsignedSmallInteger('estimated_time')->nullable(); // minutes

            // Media & links
            $table->string('cover_image')->nullable();
            $table->string('circuit_image')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('external_link')->nullable();

            // Content
            $table->longText('description')->nullable();
            $table->longText('guide')->nullable(); // rich-text overview / fallback guide
            $table->longText('embedded_code')->nullable(); // quick single code block fallback

            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index(['project_category_id', 'is_published', 'is_active'], 'projects_category_publish_active_idx');
            $table->index(['difficulty', 'is_published', 'is_active'], 'projects_difficulty_publish_active_idx');
            $table->index('name', 'projects_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

