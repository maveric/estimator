<?php

namespace App\Console\Commands;

use App\Models\Assembly;
use App\Models\Estimate;
use App\Models\EstimateAssembly;
use App\Models\EstimateItem;
use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedEstimates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:estimates {count=10} {--tenant=} {--with-versions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with sample estimates';

    /**
     * Sample customer data for more realistic estimates
     */
    protected $customers = [
        [
            'name' => 'John Smith',
            'email' => 'john.smith@example.com',
            'phone' => '555-123-4567',
            'address' => '123 Main St, Anytown, USA',
            'project' => 'Kitchen Renovation'
        ],
        [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '555-234-5678',
            'address' => '456 Oak Ave, Somewhere, USA',
            'project' => 'Bathroom Remodel'
        ],
        [
            'name' => 'Robert Johnson',
            'email' => 'robert.johnson@example.com',
            'phone' => '555-345-6789',
            'address' => '789 Pine Rd, Elsewhere, USA',
            'project' => 'Basement Finishing'
        ],
        [
            'name' => 'Sarah Williams',
            'email' => 'sarah.williams@example.com',
            'phone' => '555-456-7890',
            'address' => '101 Cedar Ln, Nowhere, USA',
            'project' => 'Home Office Addition'
        ],
        [
            'name' => 'Michael Brown',
            'email' => 'michael.brown@example.com',
            'phone' => '555-567-8901',
            'address' => '202 Maple Dr, Anywhere, USA',
            'project' => 'Deck Construction'
        ],
        [
            'name' => 'Emily Davis',
            'email' => 'emily.davis@example.com',
            'phone' => '555-678-9012',
            'address' => '303 Birch Blvd, Someplace, USA',
            'project' => 'Living Room Renovation'
        ],
        [
            'name' => 'David Miller',
            'email' => 'david.miller@example.com',
            'phone' => '555-789-0123',
            'address' => '404 Elm St, Othertown, USA',
            'project' => 'Garage Conversion'
        ],
        [
            'name' => 'Jennifer Wilson',
            'email' => 'jennifer.wilson@example.com',
            'phone' => '555-890-1234',
            'address' => '505 Walnut Ave, Sometown, USA',
            'project' => 'Attic Renovation'
        ],
        [
            'name' => 'James Taylor',
            'email' => 'james.taylor@example.com',
            'phone' => '555-901-2345',
            'address' => '606 Spruce Rd, Anotherplace, USA',
            'project' => 'Window Replacement'
        ],
        [
            'name' => 'Lisa Anderson',
            'email' => 'lisa.anderson@example.com',
            'phone' => '555-012-3456',
            'address' => '707 Fir Dr, Lasttown, USA',
            'project' => 'Flooring Installation'
        ],
        [
            'name' => 'Thomas Wilson',
            'email' => 'thomas.wilson@example.com',
            'phone' => '555-123-7890',
            'address' => '808 Redwood Ct, Newtown, USA',
            'project' => 'Patio Installation'
        ],
        [
            'name' => 'Patricia Moore',
            'email' => 'patricia.moore@example.com',
            'phone' => '555-234-8901',
            'address' => '909 Aspen Way, Oldtown, USA',
            'project' => 'Kitchen Countertop Replacement'
        ],
        [
            'name' => 'Christopher Lee',
            'email' => 'christopher.lee@example.com',
            'phone' => '555-345-9012',
            'address' => '1010 Willow Ln, Uptown, USA',
            'project' => 'Bathroom Tile Work'
        ],
        [
            'name' => 'Elizabeth Clark',
            'email' => 'elizabeth.clark@example.com',
            'phone' => '555-456-0123',
            'address' => '1111 Poplar St, Downtown, USA',
            'project' => 'Fence Installation'
        ],
        [
            'name' => 'Daniel Lewis',
            'email' => 'daniel.lewis@example.com',
            'phone' => '555-567-1234',
            'address' => '1212 Sycamore Ave, Midtown, USA',
            'project' => 'Roof Repair'
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->argument('count');
        $tenantId = $this->option('tenant');
        $withVersions = $this->option('with-versions');
        
        $this->info("Seeding {$count} estimates...");

        // Get tenants based on option
        if ($tenantId) {
            $tenants = Tenant::where('id', $tenantId)->get();
            if ($tenants->isEmpty()) {
                $this->error("Tenant with ID {$tenantId} not found.");
                return 1;
            }
        } else {
            $tenants = Tenant::all();
            if ($tenants->isEmpty()) {
                $this->error('No tenants found. Please seed tenants first.');
                return 1;
            }
        }

        $bar = $this->output->createProgressBar($count * $tenants->count());
        $bar->start();

        foreach ($tenants as $tenant) {
            $items = Item::where('tenant_id', $tenant->id)->get();
            $assemblies = Assembly::where('tenant_id', $tenant->id)->get();
            
            if ($items->isEmpty()) {
                $this->error('No items found for tenant ' . $tenant->id . '. Please seed items first.');
                continue;
            }
            
            // Ensure standard labor rate exists for this tenant
            $laborRate = LaborRate::firstOrCreate(
                ['name' => 'Standard Labor', 'tenant_id' => $tenant->id],
                [
                    'cost_rate' => 25.00,
                    'charge_rate' => 45.00,
                    'effective_from' => now(),
                    'is_default' => true,
                ]
            );
            
            // Create a premium labor rate for variety
            $premiumLaborRate = LaborRate::firstOrCreate(
                ['name' => 'Premium Labor', 'tenant_id' => $tenant->id],
                [
                    'cost_rate' => 35.00,
                    'charge_rate' => 65.00,
                    'effective_from' => now(),
                    'is_default' => false,
                ]
            );
            
            // Create estimates
            for ($i = 1; $i <= $count; $i++) {
                DB::beginTransaction();
                try {
                    // Get a random customer
                    $customer = $this->customers[array_rand($this->customers)];
                    
                    // Create varied statuses with weighted distribution
                    $statusOptions = ['draft', 'draft', 'sent', 'sent', 'sent', 'approved', 'approved', 'declined'];
                    $status = $statusOptions[array_rand($statusOptions)];
                    
                    // Create varied markup and discount rates
                    $markup = rand(0, 25);
                    $discount = rand(0, 15);
                    
                    // Create estimate with varied valid_until dates
                    $estimate = Estimate::create([
                        'tenant_id' => $tenant->id,
                        'customer_name' => $customer['name'],
                        'customer_email' => $customer['email'],
                        'customer_phone' => $customer['phone'],
                        'customer_address' => $customer['address'],
                        'status' => $status,
                        'markup_percentage' => $markup,
                        'discount_percentage' => $discount,
                        'notes' => $customer['project'] . ' - ' . fake()->paragraph(),
                        'valid_until' => now()->addDays(rand(14, 60)),
                        'version' => 1,
                    ]);

                    // Add varied number of items to the estimate (2-6 items)
                    $itemCount = rand(2, 6);
                    $randomItems = $items->random(min($itemCount, $items->count()));
                    foreach ($randomItems as $item) {
                        // Use different labor rates randomly
                        $selectedLaborRate = rand(0, 1) ? $laborRate : $premiumLaborRate;
                        
                        // Create varied quantities including decimals
                        $quantity = rand(1, 20) + (rand(0, 9) / 10);
                        
                        EstimateItem::create([
                            'tenant_id' => $tenant->id,
                            'estimate_id' => $estimate->id,
                            'item_id' => $item->id,
                            'original_item_id' => $item->id,
                            'labor_rate_id' => $selectedLaborRate->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'unit_of_measure' => $item->unit_of_measure,
                            'quantity' => $quantity,
                            'material_cost_rate' => $item->material_cost_rate,
                            'material_charge_rate' => $item->material_charge_rate,
                            'labor_units' => $item->labor_units,
                            'original_cost_rate' => $item->material_cost_rate,
                            'original_charge_rate' => $item->material_charge_rate,
                        ]);
                    }

                    // Add varied number of assemblies to the estimate (1-4 assemblies)
                    if (!$assemblies->isEmpty()) {
                        $assemblyCount = rand(1, 4);
                        $randomAssemblies = $assemblies->random(min($assemblyCount, $assemblies->count()));
                        foreach ($randomAssemblies as $assembly) {
                            // Create varied quantities including decimals
                            $quantity = rand(1, 5) + (rand(0, 9) / 10);
                            
                            $estimateAssembly = EstimateAssembly::create([
                                'tenant_id' => $tenant->id,
                                'estimate_id' => $estimate->id,
                                'assembly_id' => $assembly->id,
                                'original_assembly_id' => $assembly->id,
                                'name' => $assembly->name,
                                'description' => $assembly->description,
                                'quantity' => $quantity,
                            ]);

                            // Add assembly items with varied labor rates
                            foreach ($assembly->items as $item) {
                                // Use different labor rates randomly
                                $selectedLaborRate = rand(0, 1) ? $laborRate : $premiumLaborRate;
                                
                                // Occasionally modify the quantity from the original assembly
                                $originalQuantity = $item->pivot->quantity;
                                $modifiedQuantity = rand(0, 5) === 0 
                                    ? $originalQuantity * (rand(80, 120) / 100) // Modify by ±20%
                                    : $originalQuantity;
                                
                                EstimateItem::create([
                                    'tenant_id' => $tenant->id,
                                    'estimate_assembly_id' => $estimateAssembly->id,
                                    'item_id' => $item->id,
                                    'original_item_id' => $item->id,
                                    'labor_rate_id' => $selectedLaborRate->id,
                                    'name' => $item->name,
                                    'description' => $item->description,
                                    'unit_of_measure' => $item->unit_of_measure,
                                    'quantity' => $modifiedQuantity,
                                    'material_cost_rate' => $item->material_cost_rate,
                                    'material_charge_rate' => $item->material_charge_rate,
                                    'labor_units' => $item->labor_units,
                                    'original_cost_rate' => $item->material_cost_rate,
                                    'original_charge_rate' => $item->material_charge_rate,
                                ]);
                            }
                        }
                    }
                    
                    // Create version history if requested
                    if ($withVersions && rand(0, 2) === 0) {
                        // Create 1-3 versions
                        $versionCount = rand(1, 3);
                        for ($v = 0; $v < $versionCount; $v++) {
                            $estimate->createVersionSnapshot();
                            
                            // Make some changes to the estimate for the next version
                            $estimate->update([
                                'markup_percentage' => $markup + rand(-5, 5),
                                'discount_percentage' => $discount + rand(-3, 3),
                                'notes' => $customer['project'] . ' - ' . fake()->paragraph(),
                            ]);
                        }
                    }
                    
                    DB::commit();
                    $bar->advance();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error creating estimate: " . $e->getMessage());
                }
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Estimates seeded successfully!');

        return 0;
    }
} 