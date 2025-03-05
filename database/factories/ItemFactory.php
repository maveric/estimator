<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $units = ['each', 'linear_feet', 'square_feet', 'pound', 'gallon', 'sheet', 'roll', 'tube'];
        $unit = $this->faker->randomElement($units);
        
        $materialCostRate = match($unit) {
            'each' => $this->faker->randomFloat(2, 10, 500),
            'linear_feet' => $this->faker->randomFloat(2, 0.5, 10),
            'square_feet' => $this->faker->randomFloat(2, 1, 20),
            'pound' => $this->faker->randomFloat(2, 0.5, 5),
            'gallon' => $this->faker->randomFloat(2, 15, 100),
            'sheet' => $this->faker->randomFloat(2, 10, 100),
            'roll' => $this->faker->randomFloat(2, 15, 150),
            'tube' => $this->faker->randomFloat(2, 2, 10),
            default => $this->faker->randomFloat(2, 1, 50),
        };

        return [
            'tenant_id' => function () {
                return Tenant::factory();
            },
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'unit_of_measure' => $unit,
            'material_cost_rate' => $materialCostRate,
            'material_charge_rate' => $materialCostRate * 1.5,
            'labor_units' => $this->faker->randomFloat(1, 1, 180),
        ];
    }
} 