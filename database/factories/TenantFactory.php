<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        
        return [
            'name' => $name,
            'subdomain' => strtolower(str_replace([' ', '.'], '', $name)),
            'settings' => [
                'company_address' => $this->faker->address(),
                'company_phone' => $this->faker->phoneNumber(),
            ],
        ];
    }
} 