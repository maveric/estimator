<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Jetstream\Features;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user and team
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Create the shared team
        $team = Team::factory()->create([
            'name' => 'Test Company',
            'user_id' => $admin->id,
            'personal_team' => true,
        ]);
        $admin->switchTeam($team);

        // Create manager user
        $manager = User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.test',
            'password' => bcrypt('password'),
        ]);
        $manager->assignRole('manager');
        $team->users()->attach($manager, ['role' => 'admin']); // Give them admin team role but manager app role
        $manager->switchTeam($team);

        // Create estimator user
        $estimator = User::factory()->create([
            'name' => 'Estimator User',
            'email' => 'estimator@example.test',
            'password' => bcrypt('password'),
        ]);
        $estimator->assignRole('estimator');
        $team->users()->attach($estimator, ['role' => 'editor']); // Give them editor team role
        $estimator->switchTeam($team);

        // Create labor rates for the team
        // Default labor rate
        $defaultRate = LaborRate::factory()->create([
            'team_id' => $team->id,
            'name' => 'Standard Rate',
            'cost_rate' => 50.00,
            'price_rate' => 75.00,
            'is_default' => true,
            'is_active' => true,
        ]);

        // Additional labor rates
        $premiumRate = LaborRate::factory()->create([
            'team_id' => $team->id,
            'name' => 'Premium Rate',
            'cost_rate' => 85.00,
            'price_rate' => 125.00,
            'is_active' => true,
        ]);

        // Create some test items
        Item::factory()->create([
            'team_id' => $team->id,
            'name' => 'Basic Installation',
            'description' => 'Standard installation service',
            'sku' => 'INST-001',
            'unit_of_measure' => 'EA',
            'material_cost' => 50.00,
            'material_price' => 75.00,
            'labor_minutes' => 60,
            'labor_rate_id' => $defaultRate->id,
            'is_template' => false,
            'is_active' => true,
        ]);

        Item::factory()->create([
            'team_id' => $team->id,
            'name' => 'Premium Installation',
            'description' => 'Premium installation with extended warranty',
            'sku' => 'INST-002',
            'unit_of_measure' => 'EA',
            'material_cost' => 100.00,
            'material_price' => 150.00,
            'labor_minutes' => 90,
            'labor_rate_id' => $premiumRate->id,
            'is_template' => false,
            'is_active' => true,
        ]);

        Item::factory()->create([
            'team_id' => $team->id,
            'name' => 'Maintenance Service',
            'description' => 'Regular maintenance service',
            'sku' => 'MAINT-001',
            'unit_of_measure' => 'HR',
            'material_cost' => 25.00,
            'material_price' => 40.00,
            'labor_minutes' => 30,
            'labor_rate_id' => $defaultRate->id,
            'is_template' => true,
            'is_active' => true,
        ]);

        Item::factory()->create([
            'team_id' => $team->id,
            'name' => 'Discontinued Service',
            'description' => 'No longer offered service',
            'sku' => 'DISC-001',
            'unit_of_measure' => 'EA',
            'material_cost' => 75.00,
            'material_price' => 100.00,
            'labor_minutes' => 45,
            'labor_rate_id' => $defaultRate->id,
            'is_template' => false,
            'is_active' => false,
        ]);
    }
}
