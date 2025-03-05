<?php

namespace Database\Seeders;

use App\Models\Assembly;
use App\Models\Item;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AssemblySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $items = Item::where('tenant_id', $tenant->id)->get();

        // Interior Wall Assembly (8' x 10' section)
        $interiorWall = Assembly::create([
            'tenant_id' => $tenant->id,
            'name' => 'Interior Wall Section 8x10',
            'description' => 'Standard interior wall section including framing, drywall, and electrical',
        ]);

        $interiorWall->items()->attach([
            $items->firstWhere('name', '2x4 Lumber')->id => ['tenant_id' => $tenant->id, 'quantity' => 12], // Studs and plates
            $items->firstWhere('name', '16d Nails')->id => ['tenant_id' => $tenant->id, 'quantity' => 2], // Pounds of nails
            $items->firstWhere('name', 'Drywall Sheet')->id => ['tenant_id' => $tenant->id, 'quantity' => 2], // Both sides
            $items->firstWhere('name', 'Joint Compound')->id => ['tenant_id' => $tenant->id, 'quantity' => 1],
            $items->firstWhere('name', 'R-13 Insulation')->id => ['tenant_id' => $tenant->id, 'quantity' => 1],
            $items->firstWhere('name', 'Electrical Box')->id => ['tenant_id' => $tenant->id, 'quantity' => 2],
            $items->firstWhere('name', 'Romex 14/2')->id => ['tenant_id' => $tenant->id, 'quantity' => 20],
        ]);

        // Basic Room Paint Job
        $paintJob = Assembly::create([
            'tenant_id' => $tenant->id,
            'name' => 'Room Paint Package',
            'description' => 'Complete room painting including walls and ceiling (12x12 room)',
        ]);

        $paintJob->items()->attach([
            $items->firstWhere('name', 'Paint')->id => ['tenant_id' => $tenant->id, 'quantity' => 3],
            $items->firstWhere('name', 'Joint Compound')->id => ['tenant_id' => $tenant->id, 'quantity' => 0.5], // Touch-ups
        ]);

        // Subfloor Assembly
        $subfloor = Assembly::create([
            'tenant_id' => $tenant->id,
            'name' => 'Subfloor Section 8x8',
            'description' => 'Standard subfloor assembly for residential construction',
        ]);

        $subfloor->items()->attach([
            $items->firstWhere('name', 'Plywood 3/4"')->id => ['tenant_id' => $tenant->id, 'quantity' => 2],
            $items->firstWhere('name', '16d Nails')->id => ['tenant_id' => $tenant->id, 'quantity' => 1],
        ]);

        // Basic Electrical Run
        $electrical = Assembly::create([
            'tenant_id' => $tenant->id,
            'name' => 'Basic Electrical Circuit',
            'description' => 'Standard electrical circuit with 3 outlets',
        ]);

        $electrical->items()->attach([
            $items->firstWhere('name', 'Romex 14/2')->id => ['tenant_id' => $tenant->id, 'quantity' => 50],
            $items->firstWhere('name', 'Electrical Box')->id => ['tenant_id' => $tenant->id, 'quantity' => 3],
        ]);

        // Inactive Assembly for testing
        $outdated = Assembly::create([
            'tenant_id' => $tenant->id,
            'name' => 'Outdated Wall Assembly',
            'description' => 'Old wall assembly specification (no longer used)',
            'is_active' => false,
        ]);

        $outdated->items()->attach([
            $items->firstWhere('name', '2x4 Lumber')->id => ['tenant_id' => $tenant->id, 'quantity' => 10],
            $items->firstWhere('name', 'Drywall Sheet')->id => ['tenant_id' => $tenant->id, 'quantity' => 2],
        ]);
    }
}