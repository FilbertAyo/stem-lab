<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('subscription_packages')->where('slug', 'individual-yearly')->exists();

        if ($exists) {
            return;
        }

        DB::table('subscription_packages')->insert([
            'slug' => 'individual-yearly',
            'tier_key' => 'individual',
            'name' => 'Individual yearly',
            'description' => 'Prepaid annual access with a discount versus paying month by month.',
            'price_heading' => '10,000',
            'price_subheading' => 'TZS / year',
            'price_compare' => '12,000',
            'price_amount_tzs' => 10000,
            'billing_period_days' => 365,
            'requires_payment' => true,
            'badge_label' => 'Save 2 months',
            'cta_label' => 'Pay yearly',
            'cta_action' => 'individual-yearly',
            'features' => json_encode([
                'For teachers, students & parents',
                'All 3D models and VR experiments',
                'Competitions and club features (teachers)',
                'Weekly sessions and student management',
            ]),
            'linked_title' => null,
            'linked_description' => null,
            'linked_features' => null,
            'is_active' => true,
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('subscription_packages')->where('slug', 'individual-yearly')->delete();
    }
};
