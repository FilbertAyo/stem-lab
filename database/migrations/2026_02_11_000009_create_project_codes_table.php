<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('language');         // e.g. Arduino C, Python, Scratch, JavaScript
            $table->longText('code');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['project_id', 'sort_order'], 'project_codes_project_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_codes');
    }
};
