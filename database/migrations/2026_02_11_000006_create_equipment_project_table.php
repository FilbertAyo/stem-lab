<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();

            $table->string('quantity')->nullable(); // "1", "2", "1 pack", etc.
            $table->string('notes')->nullable(); // e.g. "Optional", "Any brand"

            $table->timestamps();

            $table->unique(['project_id', 'equipment_id']);
            $table->index('equipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_project');
    }
};

