<?php

namespace Database\Factories;

use App\Models\Assembly;
use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'tenant_id' => function () {
                return Tenant::factory();
            },
            'name' => $this->faker->unique()->words(4, true),
            'description' => $this->faker->paragraph(),
            'is_active' => true,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Package $package) {
            // Attach 2-4 random assemblies to the package
            $assemblies = Assembly::where('tenant_id', $package->tenant_id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit($this->faker->numberBetween(2, 4))
                ->get();
            
            foreach ($assemblies as $assembly) {
                $package->assemblies()->attach($assembly->id, [
                    'quantity' => $this->faker->numberBetween(1, 5),
                    'tenant_id' => $package->tenant_id
                ]);
            }
        });
    }
} 