<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('project_steps');
    }

    public function down(): void
    {
        // Steps are no longer used; guide HTML on projects holds instructions.
    }
};
