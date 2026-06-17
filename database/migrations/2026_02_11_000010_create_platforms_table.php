<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('icon')->nullable(); // e.g. SVG filename or icon class
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'platforms_active_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
