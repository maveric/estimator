<?php

namespace Database\Factories;

use App\Models\Estimate;
use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estimate>
 */
class EstimateFactory extends Factory
{
    protected $model = Estimate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'name' => $this->faker->unique()->words(4, true),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->email(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['draft', 'sent', 'approved', 'declined']),
            'markup_percentage' => $this->faker->numberBetween(10, 30),
            'discount_percentage' => $this->faker->numberBetween(0, 10),
            'notes' => $this->faker->paragraph(),
            'valid_until' => $this->faker->dateTimeBetween('now', '+30 days'),
            'version' => 1,
            'labor_rate_id' => LaborRate::factory(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Estimate $estimate) {
            // Add 2-5 random items
            $items = Item::where('tenant_id', $estimate->tenant_id)->inRandomOrder()->limit($this->faker->numberBetween(2, 5))->get();
            
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

                $estimate->items()->create([
                    'tenant_id' => $estimate->tenant_id,
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'unit_of_measure' => $item->unit_of_measure,
                    'quantity' => $quantity,
                    'material_cost_rate' => $item->material_cost_rate,
                    'material_charge_rate' => $item->material_charge_rate,
                    'labor_units' => $item->labor_units,
                    'original_cost_rate' => $item->material_cost_rate,
                    'original_charge_rate' => $item->material_charge_rate,
                    'labor_rate_id' => $estimate->labor_rate_id,
                ]);
            }
        });
    }
} 