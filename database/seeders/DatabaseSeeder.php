<?php

namespace Database\Seeders;

use App\Models\Assembly;
use App\Models\Category;
use App\Models\Estimate;
use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use App\Models\EstimateItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if required tables exist before proceeding
        if (!Schema::hasTable('tenants') || !Schema::hasTable('users') || 
            !Schema::hasTable('items') || !Schema::hasTable('assemblies')) {
            $this->command->error('Required tables do not exist. Please run migrations first.');
            return;
        }

        // Get columns for validation
        $itemColumns = Schema::getColumnListing('items');
        $requiredItemColumns = ['name', 'description', 'material_cost_rate', 'unit_of_measure', 'material_charge_rate', 'labor_units'];
        
        // Check if all required columns exist
        $missingColumns = array_diff($requiredItemColumns, $itemColumns);
        if (!empty($missingColumns)) {
            $this->command->error('Missing columns in items table: ' . implode(', ', $missingColumns));
            $this->command->info('Please run the migration to add these columns first.');
            return;
        }

        // Create two tenants
        $tenants = [
            [
                'name' => 'Demo Company',
            'subdomain' => 'demo',
            'settings' => [
                'company_address' => '123 Main St, Anytown, USA',
                'company_phone' => '555-123-4567',
            ],
                'admin_email' => 'admin@demo.com',
                'estimator_email' => 'estimator@demo.com',
            ],
            [
                'name' => 'Test Company',
                'subdomain' => 'test',
                'settings' => [
                    'company_address' => '456 Oak Ave, Somewhere, USA',
                    'company_phone' => '555-987-6543',
                ],
                'admin_email' => 'admin@test.com',
                'estimator_email' => 'estimator@test.com',
            ],
        ];

        foreach ($tenants as $tenantData) {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $tenantData['name'],
                'subdomain' => $tenantData['subdomain'],
                'settings' => $tenantData['settings'],
            ]);

            // Create admin user
            User::create([
            'tenant_id' => $tenant->id,
                'name' => $tenantData['name'] . ' Admin',
                'email' => $tenantData['admin_email'],
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            ]);

        // Create demo estimator
            User::create([
            'tenant_id' => $tenant->id,
                'name' => $tenantData['name'] . ' Estimator',
                'email' => $tenantData['estimator_email'],
            'password' => Hash::make('password'),
            'role' => 'estimator',
            'email_verified_at' => now(),
            ]);

        // Create labor rate
            LaborRate::create([
                'tenant_id' => $tenant->id,
                'name' => 'Standard Labor',
                'cost_rate' => 25.00,
                'charge_rate' => 45.00,
                'effective_from' => now(),
                'is_default' => true,
                'is_primary' => true,
            ]);

            // Create categories
            $categories = [
                [
                    'name' => 'Electrical',
                    'description' => 'Electrical components and materials',
                ],
                [
                    'name' => 'Plumbing',
                    'description' => 'Plumbing components and materials',
                ],
                [
                    'name' => 'Hardware',
                    'description' => 'General hardware items',
                ],
                [
                    'name' => 'Bathroom',
                    'description' => 'Bathroom assemblies and items',
                ],
                [
                    'name' => 'Kitchen',
                    'description' => 'Kitchen assemblies and items',
                ],
                [
                    'name' => 'General',
                    'description' => 'General purpose items and assemblies',
                ],
                [
                    'name' => 'Finishing',
                    'description' => 'Finishing materials and trim',
                ],
                [
                    'name' => 'Tools',
                    'description' => 'Tools and equipment',
                ],
                [
                    'name' => 'Painting',
                    'description' => 'Painting supplies and materials',
                ],
            ];

            foreach ($categories as $categoryData) {
                Category::create([
            'tenant_id' => $tenant->id,
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'is_active' => true,
                ]);
            }

            // Create 50 items
            Item::factory()
                ->count(50)
                ->create([
                'tenant_id' => $tenant->id,
            ]);
            
            // Create 20 assemblies with items
            Assembly::factory()
                ->count(20)
                ->create([
                'tenant_id' => $tenant->id,
            ]);
            
            // Assign categories to items and assemblies
            $items = Item::where('tenant_id', $tenant->id)->get();
            $assemblies = Assembly::where('tenant_id', $tenant->id)->get();
            $allCategories = Category::where('tenant_id', $tenant->id)->get();

            foreach ($items as $index => $item) {
                // Assign 1-3 random categories to each item
                $item->categories()->attach(
                    $allCategories->random($this->faker->numberBetween(1, 3))->pluck('id')->toArray(),
                    ['tenant_id' => $tenant->id]
                );
            }

            foreach ($assemblies as $index => $assembly) {
                // Assign 1-3 random categories to each assembly
                $assembly->categories()->attach(
                    $allCategories->random($this->faker->numberBetween(1, 3))->pluck('id')->toArray(),
                    ['tenant_id' => $tenant->id]
                );
            }
            
            // Create 10 packages with assemblies AFTER assemblies exist
            Package::factory()
                ->count(10)
                ->create([
                'tenant_id' => $tenant->id,
            ]);
            
            // Debug: Check if packages have assemblies after factory creation
            $packagesCheck = Package::where('tenant_id', $tenant->id)
                ->with(['assemblies'])
                ->get();
            foreach ($packagesCheck as $package) {
                \Illuminate\Support\Facades\Log::info('Package after factory creation', [
                    'package_name' => $package->name,
                    'assembly_count' => $package->assemblies->count(),
                ]);
            }
            
            // Create 5 estimates
            Estimate::factory()
                ->count(5)
                ->create([
                'tenant_id' => $tenant->id,
                    'user_id' => User::where('email', $tenantData['estimator_email'])->first()->id,
                    'labor_rate_id' => LaborRate::where('tenant_id', $tenant->id)->first()->id,
                ]);

            // Get all estimates for this tenant
            $estimates = Estimate::where('tenant_id', $tenant->id)->get();
            
            // Get assemblies and packages for this tenant
            $assemblies = Assembly::where('tenant_id', $tenant->id)->get();
            $packages = Package::where('tenant_id', $tenant->id)
                ->with(['assemblies']) // Eager load assemblies
                ->get();

            // Debug: Check packages before adding to estimates
            foreach ($packages as $package) {
                \Illuminate\Support\Facades\Log::info('Package before adding to estimate', [
                    'package_name' => $package->name,
                    'assembly_count' => $package->assemblies->count(),
                ]);
            }

            // Add assemblies and packages to each estimate
            foreach ($estimates as $estimate) {
                // Add 1-3 random assemblies
                $randomAssemblies = $assemblies->random($this->faker->numberBetween(1, 3));
                foreach ($randomAssemblies as $assembly) {
                    $estimateAssembly = $estimate->assemblies()->create([
                        'tenant_id' => $tenant->id,
                        'assembly_id' => $assembly->id,
                        'original_assembly_id' => $assembly->id,
                        'name' => $assembly->name,
                        'description' => $assembly->description,
                        'quantity' => $this->faker->numberBetween(1, 3),
                    ]);

                    // Copy items from assembly to estimate assembly
                    foreach ($assembly->items as $assemblyItem) {
                        EstimateItem::create([
                            'tenant_id' => $tenant->id,
                            'estimate_assembly_id' => $estimateAssembly->id,
                            'item_id' => $assemblyItem->id,
                            'original_item_id' => $assemblyItem->id,
                            'name' => $assemblyItem->name,
                            'description' => $assemblyItem->description,
                            'unit_of_measure' => $assemblyItem->unit_of_measure,
                            'quantity' => $assemblyItem->pivot->quantity,
                            'material_cost_rate' => $assemblyItem->material_cost_rate,
                            'material_charge_rate' => $assemblyItem->material_charge_rate,
                            'labor_units' => $assemblyItem->labor_units,
                            'original_cost_rate' => $assemblyItem->material_cost_rate,
                            'original_charge_rate' => $assemblyItem->material_charge_rate,
                            'labor_rate_id' => $estimate->labor_rate_id,
                        ]);
                    }
                }

                // Add 1-2 random packages
                $randomPackages = $packages->random($this->faker->numberBetween(1, 2));
                foreach ($randomPackages as $package) {
                    // Debug: Check package before creating estimate package
                    \Illuminate\Support\Facades\Log::info('Package being added to estimate', [
                        'package_name' => $package->name,
                        'assembly_count' => $package->assemblies->count(),
                    ]);

                    $estimatePackage = $estimate->packages()->create([
                        'tenant_id' => $tenant->id,
                        'package_id' => $package->id,
                        'original_package_id' => $package->id,
                        'name' => $package->name,
                        'description' => $package->description,
                        'quantity' => $this->faker->numberBetween(1, 2),
                    ]);

                    // Copy assemblies from package to estimate package
                    foreach ($package->assemblies as $assembly) {
                        $estimateAssembly = $estimatePackage->assemblies()->create([
                            'tenant_id' => $tenant->id,
                            'assembly_id' => $assembly->id,
                            'original_assembly_id' => $assembly->id,
                            'name' => $assembly->name,
                            'description' => $assembly->description,
                            'quantity' => $assembly->pivot->quantity,
                        ]);

                        // Copy items from assembly to estimate assembly
                        foreach ($assembly->items as $item) {
                            EstimateItem::create([
                                'tenant_id' => $tenant->id,
                                'estimate_assembly_id' => $estimateAssembly->id,
                                'item_id' => $item->id,
                                'original_item_id' => $item->id,
                                'name' => $item->name,
                                'description' => $item->description,
                                'unit_of_measure' => $item->unit_of_measure,
                                'quantity' => $item->pivot->quantity,
                                'material_cost_rate' => $item->material_cost_rate,
                                'material_charge_rate' => $item->material_charge_rate,
                                'labor_units' => $item->labor_units,
                                'original_cost_rate' => $item->material_cost_rate,
                                'original_charge_rate' => $item->material_charge_rate,
                                'labor_rate_id' => $estimate->labor_rate_id,
                            ]);
                        }
                    }
                }
            }
        }

        // Existing seeders
        $this->call([
            // ... other seeders ...
            UpdateItemPricesSeeder::class,
        ]);
    }
}
