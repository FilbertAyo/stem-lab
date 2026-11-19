<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Additive columns only. Does not delete rows or existing URLs.
     */
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table): void {
            if (! Schema::hasColumn('competitions', 'sms_code')) {
                $table->string('sms_code', 6)->nullable()->unique()->after('slug');
            }
        });

        Schema::table('competition_ideas', function (Blueprint $table): void {
            if (! Schema::hasColumn('competition_ideas', 'admin_feedback')) {
                $table->text('admin_feedback')->nullable()->after('supporting_notes');
            }
        });

        Schema::table('competition_shortlists', function (Blueprint $table): void {
            if (! Schema::hasColumn('competition_shortlists', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('decided_at');
            }
            if (! Schema::hasColumn('competition_shortlists', 'reminder_count')) {
                $table->unsignedSmallInteger('reminder_count')->default(0)->after('notified_at');
            }
            if (! Schema::hasColumn('competition_shortlists', 'last_reminder_sent_at')) {
                $table->timestamp('last_reminder_sent_at')->nullable()->after('reminder_count');
            }
        });

        $this->backfillSmsCodes();
    }

    public function down(): void
    {
        Schema::table('competition_shortlists', function (Blueprint $table): void {
            if (Schema::hasColumn('competition_shortlists', 'last_reminder_sent_at')) {
                $table->dropColumn('last_reminder_sent_at');
            }
            if (Schema::hasColumn('competition_shortlists', 'reminder_count')) {
                $table->dropColumn('reminder_count');
            }
            if (Schema::hasColumn('competition_shortlists', 'notified_at')) {
                $table->dropColumn('notified_at');
            }
        });

        Schema::table('competition_ideas', function (Blueprint $table): void {
            if (Schema::hasColumn('competition_ideas', 'admin_feedback')) {
                $table->dropColumn('admin_feedback');
            }
        });

        Schema::table('competitions', function (Blueprint $table): void {
            if (Schema::hasColumn('competitions', 'sms_code')) {
                $table->dropUnique(['sms_code']);
                $table->dropColumn('sms_code');
            }
        });
    }

    private function backfillSmsCodes(): void
    {
        $reserved = [
            'create', 'export', 'delete', 'update', 'search', 'import', 'status', 'result', 'report',
        ];

        $used = array_fill_keys(
            DB::table('competitions')->whereNotNull('sms_code')->pluck('sms_code')->all(),
            true
        );

        $ids = DB::table('competitions')->whereNull('sms_code')->orderBy('id')->pluck('id');

        foreach ($ids as $id) {
            $code = $this->makeSmsCode($used, $reserved);
            DB::table('competitions')->where('id', $id)->update(['sms_code' => $code]);
            $used[$code] = true;
        }
    }

    /**
     * @param  array<string, true>  $used
     * @param  list<string>  $reserved
     */
    private function makeSmsCode(array $used, array $reserved): string
    {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $candidate = strtolower(Str::random(6));

            if (! preg_match('/[a-z]/', $candidate)) {
                continue;
            }

            if (isset($used[$candidate]) || in_array($candidate, $reserved, true)) {
                continue;
            }

            return $candidate;
        }

        throw new RuntimeException('Unable to generate a unique competition SMS code.');
    }
};
