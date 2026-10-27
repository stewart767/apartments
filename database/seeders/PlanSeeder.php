<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Ideal for individual landlords and single rental properties with up to 5 units.',
                'price_monthly' => 25000,
                'price_annual' => 250000,
                'unit_limit' => 5,
                'property_limit' => 1,
                'staff_limit' => 1,
                'features' => [
                    'basic_property_management',
                    'booking_applications',
                    'tenant_records',
                    'contracts_digital',
                    'payment_records',
                    'basic_occupancy',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'Designed for growing landlords managing up to 20 units with maintenance and check-ins.',
                'price_monthly' => 60000,
                'price_annual' => 600000,
                'unit_limit' => 20,
                'property_limit' => 3,
                'staff_limit' => 3,
                'features' => [
                    'basic_property_management',
                    'booking_applications',
                    'tenant_records',
                    'contracts_digital',
                    'payment_records',
                    'basic_occupancy',
                    'payment_verification',
                    'maintenance_tickets',
                    'checkin_checkout',
                    'asset_handover',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Hostel / Apartment',
                'slug' => 'hostel',
                'description' => 'Specialized for student hostels, multi-unit apartments, and shared accommodation up to 60 units/beds.',
                'price_monthly' => 120000,
                'price_annual' => 1200000,
                'unit_limit' => 60,
                'property_limit' => 10,
                'staff_limit' => 10,
                'features' => [
                    'basic_property_management',
                    'booking_applications',
                    'tenant_records',
                    'contracts_digital',
                    'payment_records',
                    'basic_occupancy',
                    'payment_verification',
                    'maintenance_tickets',
                    'checkin_checkout',
                    'asset_handover',
                    'room_bed_allocation',
                    'multi_staff_accounts',
                    'enhanced_reporting',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Full-scale property management companies and commercial operators managing large portfolios.',
                'price_monthly' => 300000,
                'price_annual' => 3000000,
                'unit_limit' => 500,
                'property_limit' => 50,
                'staff_limit' => 50,
                'features' => [
                    '*',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
