<?php

namespace Database\Factories;

use App\Models\LaborRate;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaborRateFactory extends Factory
{
    protected $model = LaborRate::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'cost_rate' => $this->faker->randomFloat(2, 15, 50),
            'charge_rate' => $this->faker->randomFloat(2, 30, 100),
            'effective_from' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'effective_until' => $this->faker->optional(0.3)->dateTimeBetween('now', '+1 year'),
            'is_default' => false,
        ];
    }
} 