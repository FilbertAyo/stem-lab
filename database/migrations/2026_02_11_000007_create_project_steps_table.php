<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_steps', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('step_number'); // 1, 2, 3 …
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('image')->nullable();       // stored path
            $table->text('code_snippet')->nullable();  // inline short snippet for this step

            $table->timestamps();

            $table->index(['project_id', 'step_number'], 'project_steps_project_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_steps');
    }
};
