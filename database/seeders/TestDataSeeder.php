<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Features;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user first
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create test team with admin as owner
        $team = Team::create([
            'name' => 'Test Company',
            'personal_team' => false,
            'user_id' => $admin->id,
        ]);
        $team->users()->attach($admin, ['role' => 'admin']);
        $admin->switchTeam($team); // Set current team for admin

        // Create manager user
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.test',
            'password' => Hash::make('password'),
        ]);
        $manager->assignRole('manager');
        $team->users()->attach($manager, ['role' => 'admin']); // Give them admin team role but manager app role
        $manager->switchTeam($team);

        // Create estimator user
        $estimator = User::create([
            'name' => 'Estimator User',
            'email' => 'estimator@example.test',
            'password' => Hash::make('password'),
        ]);
        $estimator->assignRole('estimator');
        $team->users()->attach($estimator, ['role' => 'editor']); // Give them editor team role
        $estimator->switchTeam($team);

        // Create labor rates
        $standardRate = LaborRate::create([
            'team_id' => $team->id,
            'name' => 'Standard Rate',
            'cost_rate' => 50.00,
            'price_rate' => 75.00,
            'is_default' => true,
        ]);

        $premiumRate = LaborRate::create([
            'team_id' => $team->id,
            'name' => 'Premium Rate',
            'cost_rate' => 85.00,
            'price_rate' => 125.00,
            'is_default' => false,
        ]);

        // Create electrical materials
        $items = [
            // Wire and Cable
            [
                'name' => '12/2 NM-B Romex',
                'sku' => 'WIRE-12-2',
                'description' => '12/2 NM-B Romex with ground',
                'unit_of_measure' => 'FT',
                'material_cost' => 0.85,
                'material_price' => 1.25,
                'labor_minutes' => 0,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => '14/2 NM-B Romex',
                'sku' => 'WIRE-14-2',
                'description' => '14/2 NM-B Romex with ground',
                'unit_of_measure' => 'FT',
                'material_cost' => 0.65,
                'material_price' => 0.95,
                'labor_minutes' => 0,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => '12/3 NM-B Romex',
                'sku' => 'WIRE-12-3',
                'description' => '12/3 NM-B Romex with ground',
                'unit_of_measure' => 'FT',
                'material_cost' => 1.25,
                'material_price' => 1.85,
                'labor_minutes' => 0,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],

            // Boxes and Covers
            [
                'name' => 'Single Gang New Work Box',
                'sku' => 'BOX-SG-NW',
                'description' => 'Single gang new work box with mounting ears',
                'unit_of_measure' => 'EA',
                'material_cost' => 1.25,
                'material_price' => 1.85,
                'labor_minutes' => 2,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Single Gang Old Work Box',
                'sku' => 'BOX-SG-OW',
                'description' => 'Single gang old work box with mounting tabs',
                'unit_of_measure' => 'EA',
                'material_cost' => 2.25,
                'material_price' => 3.25,
                'labor_minutes' => 5,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => '4" Square Box',
                'sku' => 'BOX-4SQ',
                'description' => '4" square box with mounting ears',
                'unit_of_measure' => 'EA',
                'material_cost' => 2.85,
                'material_price' => 4.25,
                'labor_minutes' => 3,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],

            // Devices
            [
                'name' => '15A Duplex Receptacle',
                'sku' => 'REC-15A',
                'description' => '15A duplex receptacle, white',
                'unit_of_measure' => 'EA',
                'material_cost' => 2.25,
                'material_price' => 3.25,
                'labor_minutes' => 5,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => '20A Duplex Receptacle',
                'sku' => 'REC-20A',
                'description' => '20A duplex receptacle, white',
                'unit_of_measure' => 'EA',
                'material_cost' => 2.85,
                'material_price' => 4.25,
                'labor_minutes' => 5,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Single Pole Switch',
                'sku' => 'SW-SP',
                'description' => 'Single pole switch, white',
                'unit_of_measure' => 'EA',
                'material_cost' => 2.25,
                'material_price' => 3.25,
                'labor_minutes' => 5,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => '3-Way Switch',
                'sku' => 'SW-3W',
                'description' => '3-way switch, white',
                'unit_of_measure' => 'EA',
                'material_cost' => 3.25,
                'material_price' => 4.75,
                'labor_minutes' => 8,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],

            // Lighting
            [
                'name' => '4" LED Recessed Light',
                'sku' => 'LIGHT-4LED',
                'description' => '4" LED recessed light with trim, 3000K',
                'unit_of_measure' => 'EA',
                'material_cost' => 25.00,
                'material_price' => 35.00,
                'labor_minutes' => 15,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => '6" LED Recessed Light',
                'sku' => 'LIGHT-6LED',
                'description' => '6" LED recessed light with trim, 3000K',
                'unit_of_measure' => 'EA',
                'material_cost' => 35.00,
                'material_price' => 50.00,
                'labor_minutes' => 15,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Pancake Light',
                'sku' => 'LIGHT-PAN',
                'description' => 'Pancake light with trim, 3000K',
                'unit_of_measure' => 'EA',
                'material_cost' => 15.00,
                'material_price' => 25.00,
                'labor_minutes' => 10,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],

            // Accessories
            [
                'name' => 'Wire Nuts (25 pack)',
                'sku' => 'ACC-WN25',
                'description' => 'Red wire nuts, 25 pack',
                'unit_of_measure' => 'PK',
                'material_cost' => 3.25,
                'material_price' => 4.75,
                'labor_minutes' => 0,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Grounding Screws (100 pack)',
                'sku' => 'ACC-GS100',
                'description' => 'Grounding screws, 100 pack',
                'unit_of_measure' => 'PK',
                'material_cost' => 5.00,
                'material_price' => 7.50,
                'labor_minutes' => 0,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Single Gang Cover Plate',
                'sku' => 'ACC-SG-CP',
                'description' => 'Single gang cover plate, white',
                'unit_of_measure' => 'EA',
                'material_cost' => 0.85,
                'material_price' => 1.25,
                'labor_minutes' => 1,
                'labor_rate_id' => $standardRate->id,
                'is_template' => false,
                'is_active' => true,
            ],
        ];

        // Create all items
        foreach ($items as $item) {
            Item::create(array_merge($item, ['team_id' => $team->id]));
        }
    }
}
