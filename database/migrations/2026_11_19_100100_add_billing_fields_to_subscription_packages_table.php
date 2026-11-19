<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->unsignedInteger('price_amount_tzs')->default(0)->after('price_compare');
            $table->unsignedSmallInteger('billing_period_days')->default(0)->after('price_amount_tzs');
            $table->boolean('requires_payment')->default(false)->after('billing_period_days');
        });

        DB::table('subscription_packages')->where('slug', 'individual')->update([
            'price_amount_tzs' => 1000,
            'billing_period_days' => 30,
            'requires_payment' => true,
        ]);

        DB::table('subscription_packages')->where('slug', 'free-trial')->update([
            'price_amount_tzs' => 0,
            'billing_period_days' => 7,
            'requires_payment' => false,
        ]);

        DB::table('subscription_packages')->where('slug', 'public-school')->update([
            'price_amount_tzs' => 0,
            'billing_period_days' => 0,
            'requires_payment' => false,
        ]);
    }

    public function down(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->dropColumn([
                'price_amount_tzs',
                'billing_period_days',
                'requires_payment',
            ]);
        });
    }
};
