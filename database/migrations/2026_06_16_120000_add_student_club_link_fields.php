<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('teacher_club_id')->constrained()->nullOnDelete();
            $table->timestamp('linked_at')->nullable()->after('user_id');
            $table->unique('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_tier', 32)->nullable()->after('role');
            $table->timestamp('club_linked_at')->nullable()->after('subscription_tier');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn(['user_id', 'linked_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_tier', 'club_linked_at']);
        });
    }
};
