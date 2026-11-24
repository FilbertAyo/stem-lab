<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_registrations', function (Blueprint $table) {
            $table->unsignedSmallInteger('idea_submission_reminder_count')->default(0)->after('last_eligibility_reminder_sent_at');
            $table->timestamp('last_idea_submission_reminder_sent_at')->nullable()->after('idea_submission_reminder_count');
        });
    }

    public function down(): void
    {
        Schema::table('competition_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'idea_submission_reminder_count',
                'last_idea_submission_reminder_sent_at',
            ]);
        });
    }
};
