<?php

namespace Database\Factories;

use App\Models\WarehouseLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseLocation>
 */
class WarehouseLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city().' Warehouse',
            'entity' => 'Warehouse',
            'is_active' => true,
            'navigation_enabled' => true,
        ];
    }
}
