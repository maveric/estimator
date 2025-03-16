<?php

namespace Database\Factories;

use App\Models\LaborRate;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LaborRate>
 */
class LaborRateFactory extends Factory
{
    protected $model = LaborRate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->words(3, true),
            'cost_rate' => $this->faker->randomFloat(2, 15, 50),
            'price_rate' => function (array $attributes) {
                return $attributes['cost_rate'] * $this->faker->randomFloat(2, 1.2, 1.5);
            },
            'is_default' => false,
            'is_active' => true,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
