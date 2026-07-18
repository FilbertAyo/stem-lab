<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->char('public_id', 6)->nullable()->after('ulid');
            $table->unique('public_id', 'clubs_public_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropUnique('clubs_public_id_unique');
            $table->dropColumn('public_id');
        });
    }
};
