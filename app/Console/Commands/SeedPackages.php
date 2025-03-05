<?php

namespace App\Console\Commands;

use App\Models\Assembly;
use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:packages {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with sample packages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');
        
        $this->info("Seeding packages...");

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

        // Define packages
        $packages = [
            [
                'name' => 'Standard Bedroom Package',
                'description' => 'Complete bedroom setup including walls, flooring, window, door, and paint',
                'assemblies' => [
                    'Basic Wall Construction',
                    'Flooring Installation',
                    'Window Installation',
                    'Interior Door Installation',
                    'Room Painting',
                ]
            ],
            [
                'name' => 'Standard Bathroom Package',
                'description' => 'Complete bathroom setup including fixtures, tile, and plumbing',
                'assemblies' => [
                    'Bathroom Installation',
                    'Bathroom Tile Installation',
                    'Interior Door Installation',
                    'Room Painting',
                ]
            ],
            [
                'name' => 'Kitchen Essentials Package',
                'description' => 'Basic kitchen setup with sink and flooring',
                'assemblies' => [
                    'Kitchen Sink Installation',
                    'Flooring Installation',
                    'Room Painting',
                ]
            ],
            [
                'name' => 'Electrical Room Package',
                'description' => 'Standard electrical setup for a room including ceiling fan and outlets',
                'assemblies' => [
                    'Ceiling Fan Installation',
                    'Basic Wall Construction',
                ]
            ],
            [
                'name' => 'Finishing Package',
                'description' => 'Finishing touches for a room including crown molding and paint',
                'assemblies' => [
                    'Crown Molding Installation',
                    'Room Painting',
                ]
            ],
            [
                'name' => 'Window and Door Package',
                'description' => 'Standard window and door installation',
                'assemblies' => [
                    'Window Installation',
                    'Interior Door Installation',
                ]
            ],
            [
                'name' => 'Complete Room Renovation',
                'description' => 'Full room renovation including walls, flooring, window, door, and finishing',
                'assemblies' => [
                    'Basic Wall Construction',
                    'Flooring Installation',
                    'Window Installation',
                    'Interior Door Installation',
                    'Crown Molding Installation',
                    'Room Painting',
                ]
            ],
            [
                'name' => 'Standard Electrical Package',
                'description' => 'Standard electrical setup for a room with 6 outlets, 2 switches, and 4 high hats',
                'assemblies' => [
                    'Ceiling Fan Installation',
                    'Basic Wall Construction',
                ]
            ],
        ];

        // Check if package_assemblies table has tenant_id column
        $packageAssembliesColumns = Schema::getColumnListing('package_assemblies');
        $hasPackageTenantIdColumn = in_array('tenant_id', $packageAssembliesColumns);

        $bar = $this->output->createProgressBar(count($packages) * $tenants->count());
        $bar->start();

        foreach ($tenants as $tenant) {
            $assemblies = Assembly::where('tenant_id', $tenant->id)->get();
            
            if ($assemblies->isEmpty()) {
                $this->error('No assemblies found for tenant ' . $tenant->id . '. Please seed assemblies first.');
                continue;
            }
            
            // Create packages
            foreach ($packages as $packageData) {
                DB::beginTransaction();
                try {
                    $package = Package::firstOrCreate(
                        ['name' => $packageData['name'], 'tenant_id' => $tenant->id],
                        [
                            'tenant_id' => $tenant->id,
                            'description' => $packageData['description'],
                            'is_active' => true,
                        ]
                    );
                    
                    // Attach assemblies to package
                    foreach ($packageData['assemblies'] as $assemblyName) {
                        $assembly = Assembly::where('name', $assemblyName)
                            ->where('tenant_id', $tenant->id)
                            ->first();
                        
                        if ($assembly) {
                            // Prepare the pivot data
                            $pivotData = [
                                'quantity' => rand(1, 3) + (rand(0, 9) / 10), // Random quantity between 1 and 3 with decimal
                            ];
                            
                            // Add tenant_id only if the column exists
                            if ($hasPackageTenantIdColumn) {
                                $pivotData['tenant_id'] = $tenant->id;
                            }
                            
                            // Check if the assembly is already attached to avoid duplicates
                            if (!$package->assemblies()->where('assembly_id', $assembly->id)->exists()) {
                                $package->assemblies()->attach($assembly->id, $pivotData);
                            }
                        }
                    }
                    
                    DB::commit();
                    $bar->advance();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error creating package: " . $e->getMessage());
                }
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Packages seeded successfully!');

        return 0;
    }
} 