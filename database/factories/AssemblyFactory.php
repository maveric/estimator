<?php

namespace Database\Factories;

use App\Models\Assembly;
use App\Models\Item;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssemblyFactory extends Factory
{
    protected $model = Assembly::class;

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
        return $this->afterCreating(function (Assembly $assembly) {
            // Attach 2-5 random items to the assembly
            $items = Item::where('tenant_id', $assembly->tenant_id)->inRandomOrder()->limit($this->faker->numberBetween(2, 5))->get();
            
            foreach ($items as $item) {
                $quantity = match($item->unit_of_measure) {
                    'each' => $this->faker->numberBetween(1, 5),
                    'linear_feet' => $this->faker->numberBetween(5, 50),
                    'square_feet' => $this->faker->numberBetween(10, 200),
                    'pound' => $this->faker->numberBetween(1, 20),
                    'gallon' => $this->faker->numberBetween(1, 5),
                    'sheet' => $this->faker->numberBetween(1, 10),
                    'roll' => $this->faker->numberBetween(1, 5),
                    'tube' => $this->faker->numberBetween(1, 3),
                    default => $this->faker->numberBetween(1, 10),
                };

                $assembly->items()->attach($item->id, [
                    'quantity' => $quantity,
                    'tenant_id' => $assembly->tenant_id
                ]);
            }
        });
    }
} 