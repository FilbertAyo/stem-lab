<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_resources', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->string('title');
            $table->enum('type', ['pdf', 'image', 'link', 'file'])->default('link');
            $table->string('file_path')->nullable(); // stored path (pdf / image / file)
            $table->string('url')->nullable();       // external link or download URL
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['project_id', 'sort_order'], 'project_resources_project_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_resources');
    }
};
