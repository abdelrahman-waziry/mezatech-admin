<?php

namespace Database\Seeders;

use App\Models\TradeInJourney;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TradeInJourneySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $examples = [
            [
                'user_id' => 101,
                'device_name' => 'iPhone 12',
                'variant_id' => 555,
                'serial_number' => 'SN123456789',
                'is_functioning' => true,
                'condition_rating' => 4,
                'parts_status' => ['screen' => true, 'battery' => true, 'camera' => true],
                'survey_payload' => ['answers' => ['power' => 'yes', 'condition' => 'good']],
                'estimated_price' => 18000.00,
                'currency' => 'EGP',
                'pricing_context' => ['calculator' => 'standard', 'discount' => 0],
                'status' => 'quoted',
                'notes' => 'User said it has original battery.',
                'logged_at' => $now->copy()->subHours(24),
                'processed_at' => $now->copy()->subHours(23),
            ],
            [
                'user_id' => 102,
                'device_name' => 'Samsung Galaxy S21',
                'variant_id' => 558,
                'serial_number' => 'SN987654321',
                'is_functioning' => false,
                'condition_rating' => 2,
                'parts_status' => ['screen' => true, 'battery' => false, 'camera' => true],
                'survey_payload' => ['answers' => ['power' => 'no', 'condition' => 'fair']],
                'estimated_price' => 9000.00,
                'currency' => 'EGP',
                'pricing_context' => ['calculator' => 'repair', 'discount' => 0.1],
                'status' => 'pending',
                'notes' => 'Battery failing.',
                'logged_at' => $now->copy()->subHours(12),
                'processed_at' => $now->copy()->subHours(10),
            ],
            [
                'user_id' => 103,
                'device_name' => 'iPhone XR',
                'variant_id' => 560,
                'serial_number' => 'SN111213141',
                'is_functioning' => true,
                'condition_rating' => 5,
                'parts_status' => ['screen' => true, 'battery' => true, 'camera' => true],
                'survey_payload' => ['answers' => ['power' => 'yes', 'condition' => 'excellent']],
                'estimated_price' => 14000.00,
                'currency' => 'EGP',
                'pricing_context' => ['calculator' => 'premium', 'discount' => 0],
                'status' => 'accepted',
                'notes' => 'Customer accepted quote.',
                'logged_at' => $now->copy()->subHours(6),
                'processed_at' => $now->copy()->subHours(5),
            ],
        ];

        foreach ($examples as $example) {
            TradeInJourney::updateOrCreate(
                ['serial_number' => $example['serial_number']],
                $example
            );
        }
    }
}

