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
        Schema::create('competition_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->onDelete('cascade');
            
            $table->integer('position'); // 1st, 2nd, 3rd, etc.
            $table->string('title'); // e.g., "Gold Medal", "First Place", "Best Innovation"
            $table->text('description')->nullable();
            $table->string('prize_type')->nullable(); // cash, certificate, trophy, scholarship, etc.
            $table->decimal('prize_value', 10, 2)->nullable(); // monetary value if applicable
            $table->string('prize_image')->nullable(); // image of the prize/trophy
            
            $table->timestamps();
            
            // Ensure unique position per competition
            $table->unique(['competition_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_awards');
    }
};

