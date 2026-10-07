<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $philipsModels = [
            '272E1CA' => '27" Curved Full HD VA Monitor, 75Hz FreeSync, HDMI/VGA',
            'BDM4350UC' => '43" 4K UHD IPS Professional Commercial Display Panel',
            '346E2CUAE' => '34" UltraWide WQHD Curved Display with USB-C Docking',
            '55BDL4050D' => '55" D-Line Signage Display Android Powered 450cd/m²',
            '65BDL3552T' => '65" T-Line Multi-Touch Interactive Display 4K UHD',
            '241B8QJEB' => '24" Full HD Business Display with SmartErgoBase',
            '328P6VUBREB' => '32" 4K HDR Professional Monitor, USB-C Docking Station',
            '499P9H' => '49" SuperWide Dual QHD Curved LCD with Windows Hello Webcam',
            '221V8L' => '21.5" Full HD VA Monitor with Adaptive Sync',
            '75BDL3511Q' => '75" Q-Line 4K UHD Digital Signage Display with FailOver',
        ];

        $model = fake()->randomElement(array_keys($philipsModels));
        $description = $philipsModels[$model];

        return [
            'category' => 'EOL PHILIPS UNITS',
            'manufacturer' => 'Philips',
            'check_in_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'model' => $model,
            'item_description' => $description,
            'quantity' => fake()->numberBetween(1, 35),
            'location' => 'Bay '.fake()->randomElement(['A', 'B', 'C', 'D']).'-'.fake()->numberBetween(1, 20),
            'status' => fake()->randomElement(['in_stock', 'in_stock', 'in_stock', 'low_stock', 'eol']),
            'created_by' => User::factory(),
        ];
    }
}
