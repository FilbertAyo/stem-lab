<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_registrations', function (Blueprint $table): void {
            $table->unsignedSmallInteger('eligibility_reminder_count')->default(0)->after('approved_by');
            $table->timestamp('last_eligibility_reminder_sent_at')->nullable()->after('eligibility_reminder_count');
        });
    }

    public function down(): void
    {
        Schema::table('competition_registrations', function (Blueprint $table): void {
            $table->dropColumn(['eligibility_reminder_count', 'last_eligibility_reminder_sent_at']);
        });
    }
};
