<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model3d_school_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model3d_id')->constrained('model3ds')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['model3d_id', 'school_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model3d_school_class');
    }
};
