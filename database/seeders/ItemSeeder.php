<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Tag;
use App\Models\Team;
use App\Models\LaborRate;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        // Get the test team (created by TestDataSeeder)
        $team = Team::whereHas('users', function($query) {
            $query->where('email', 'admin@example.test');
        })->first();

        if (!$team) {
            throw new \Exception('Test team not found. Make sure TestDataSeeder has been run first.');
        }

        // Get the standard labor rate (created by TestDataSeeder)
        $standardRate = LaborRate::where('team_id', $team->id)
            ->where('name', 'Standard Rate')
            ->first();

        if (!$standardRate) {
            throw new \Exception('Standard labor rate not found. Make sure TestDataSeeder has been run first.');
        }

        // Create service items with tags
        $serviceItems = [
            [
                'name' => 'Basic Service Call',
                'description' => 'Standard service call including travel time and basic diagnostics',
                'sku' => 'SVC-001',
                'unit_of_measure' => 'each',
                'material_cost' => 0,
                'material_price' => 0,
                'labor_minutes' => 60,
                'labor_rate_id' => $standardRate->id,
                'is_template' => true,
                'is_active' => true,
                'tags' => ['service', 'residential', 'commercial'],
            ],
            [
                'name' => 'Emergency Service Call',
                'description' => 'After-hours emergency service call with priority response',
                'sku' => 'SVC-002',
                'unit_of_measure' => 'each',
                'material_cost' => 0,
                'material_price' => 0,
                'labor_minutes' => 60,
                'labor_rate_id' => $standardRate->id,
                'is_template' => true,
                'is_active' => true,
                'tags' => ['service', 'emergency', 'residential', 'commercial'],
            ],
            [
                'name' => 'Old Work Receptacle',
                'description' => 'Standard receptacle replacement in existing wall',
                'sku' => 'REC-001',
                'unit_of_measure' => 'each',
                'material_cost' => 2.50,
                'material_price' => 5.00,
                'labor_minutes' => 30,
                'labor_rate_id' => $standardRate->id,
                'is_template' => true,
                'is_active' => true,
                'tags' => ['old work', 'residential'],
            ],
            [
                'name' => 'New Work Receptacle',
                'description' => 'Receptacle installation in new construction',
                'sku' => 'REC-002',
                'unit_of_measure' => 'each',
                'material_cost' => 2.50,
                'material_price' => 5.00,
                'labor_minutes' => 15,
                'labor_rate_id' => $standardRate->id,
                'is_template' => true,
                'is_active' => true,
                'tags' => ['new work', 'residential'],
            ],
        ];

        // Create electrical materials
        $electricalItems = [
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
        ];

        // Create service items with tags
        foreach ($serviceItems as $itemData) {
            $tags = $itemData['tags'];
            unset($itemData['tags']);
            
            $item = Item::create(array_merge($itemData, ['team_id' => $team->id]));
            $item->attachTags($tags);
        }

        // Create electrical materials
        foreach ($electricalItems as $itemData) {
            Item::factory()->create(array_merge($itemData, ['team_id' => $team->id]));
        }
    }
} 