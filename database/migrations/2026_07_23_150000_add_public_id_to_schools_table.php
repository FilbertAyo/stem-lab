<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Additive for production: create_schools_table already includes public_id
     * for fresh installs. This only adds the column when missing.
     */
    public function up(): void
    {
        if (! Schema::hasTable('schools') || Schema::hasColumn('schools', 'public_id')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->char('public_id', 6)->nullable()->unique()->after('ulid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('schools') || ! Schema::hasColumn('schools', 'public_id')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
