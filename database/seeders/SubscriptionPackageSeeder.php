<?php

namespace Database\Seeders;

use App\Models\SubscriptionPackage;
use Illuminate\Database\Seeder;

class SubscriptionPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'slug' => 'free-trial',
                'tier_key' => 'trial',
                'name' => 'Free trial',
                'description' => 'Ideal for anyone who wants to explore the platform before subscribing. Full access to STEM resources for one week.',
                'price_heading' => '7 days',
                'price_subheading' => 'free',
                'price_compare' => null,
                'price_amount_tzs' => 0,
                'billing_period_days' => 7,
                'requires_payment' => false,
                'badge_label' => null,
                'cta_label' => 'Start free trial',
                'cta_action' => 'free-trial',
                'features' => [
                    'Full platform access for 7 days',
                    '3D models and VR experiments',
                    'VR quizzes and assessments',
                    'Projects and learning resources',
                ],
                'linked_title' => null,
                'linked_description' => null,
                'linked_features' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'individual',
                'tier_key' => 'individual',
                'name' => 'Individual',
                'description' => 'Perfect for teachers, students, and parents who want ongoing access to all STEM contents',
                'price_heading' => '1,000',
                'price_subheading' => 'TZS / 30 days',
                'price_compare' => '1,500',
                'price_amount_tzs' => 1000,
                'billing_period_days' => 30,
                'requires_payment' => true,
                'badge_label' => 'Most popular',
                'cta_label' => 'Subscribe now',
                'cta_action' => 'individual',
                'features' => [
                    'For teachers, students & parents',
                    'All 3D models and VR experiments',
                    'Competitions and club features (teachers)',
                    'Weekly sessions and student management',
                ],
                'linked_title' => null,
                'linked_description' => null,
                'linked_features' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'public-school',
                'tier_key' => 'public_school',
                'name' => 'Public secondary school',
                'description' => 'Fully subsidised access for government-run secondary schools. Same platform features at no cost to support public education.',
                'price_heading' => '0',
                'price_subheading' => 'TZS /month',
                'price_compare' => null,
                'price_amount_tzs' => 0,
                'billing_period_days' => 0,
                'requires_payment' => false,
                'badge_label' => null,
                'cta_label' => 'Learn more',
                'cta_action' => 'public-school',
                'features' => [
                    'Free access for registered public schools',
                    'All STEM resources and activities',
                    '3D models, VR experiments & quizzes',
                    'Eligibility verified by school registration',
                ],
                'linked_title' => 'Public secondary school — free access',
                'linked_description' => 'Your account is linked to your school STEM club. You have subsidised access at 0 TZS/month.',
                'linked_features' => [
                    'All STEM resources and activities',
                    '3D models, VR experiments & quizzes',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $package) {
            SubscriptionPackage::query()->updateOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }
    }
}
