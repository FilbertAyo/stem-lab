<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('subscription_expires_at')->nullable()->after('club_linked_at');
            $table->timestamp('trial_used_at')->nullable()->after('subscription_expires_at');
            $table->timestamp('subscription_renewal_reminded_at')->nullable()->after('trial_used_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_expires_at',
                'trial_used_at',
                'subscription_renewal_reminded_at',
            ]);
        });
    }
};
