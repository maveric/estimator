<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'sku' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'unit_of_measure' => $this->faker->randomElement(['EA', 'LF', 'SF', 'SY', 'CY', 'TON', 'GAL', 'HR']),
            'material_cost' => $this->faker->randomFloat(4, 1, 100),
            'material_price' => function (array $attributes) {
                return number_format($attributes['material_cost'] * $this->faker->randomFloat(2, 1.2, 1.5), 4);
            },
            'labor_minutes' => $this->faker->randomFloat(2, 5, 120),
            'labor_rate_id' => LaborRate::factory(),
            'is_template' => false,
            'is_active' => true,
        ];
    }

    public function template(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_template' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
} 