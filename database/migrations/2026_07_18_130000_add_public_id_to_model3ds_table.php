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
        Schema::table('model3ds', function (Blueprint $table) {
            $table->char('public_id', 6)->nullable()->after('ulid');
            $table->unique('public_id', 'model3ds_public_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model3ds', function (Blueprint $table) {
            $table->dropUnique('model3ds_public_id_unique');
            $table->dropColumn('public_id');
        });
    }
};
