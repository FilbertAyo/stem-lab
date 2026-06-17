<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tag', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_tag_id')->constrained('project_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'project_tag_id']);
            $table->index('project_tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_tag');
    }
};

