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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->nullable()->unique();
            $table->string('admission_number', 8)->unique();
            $table->foreignId('teacher_club_id')->constrained('teacher_clubs')->onDelete('cascade');
            $table->string('name');
            $table->integer('age');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->foreignId('school_class_id')->constrained('school_classes')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('linked_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
