<?php

namespace Database\Seeders;

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
        LaborRate::factory()->create([
            'team_id' => $team->id,
            'name' => 'Standard Rate',
            'cost_rate' => 50.00,
            'price_rate' => 75.00,
            'is_default' => true,
            'is_active' => true,
        ]);

        // Additional labor rates
        LaborRate::factory()->count(3)->create([
            'team_id' => $team->id,
        ]);

        // Inactive labor rate
        LaborRate::factory()->create([
            'team_id' => $team->id,
            'is_active' => false,
            'name' => 'Inactive Rate',
        ]);
    }
}
