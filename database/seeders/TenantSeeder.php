<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the tenants table exists
        if (!Schema::hasTable('tenants')) {
            $this->command->error('Tenants table does not exist. Please run migrations first.');
            return;
        }

        // Get the columns from the tenants table
        $columns = Schema::getColumnListing('tenants');
        
        // Prepare data based on available columns
        $data = [
            'name' => 'Demo Company',
        ];
        
        // Add domain or subdomain based on which column exists
        if (in_array('subdomain', $columns)) {
            $data['subdomain'] = 'demo';
        } elseif (in_array('domain', $columns)) {
            $data['domain'] = 'demo';
        }
        
        // Add settings if the column exists
        if (in_array('settings', $columns)) {
            $data['settings'] = json_encode([
                'company_address' => '123 Main St, Anytown, USA',
                'company_phone' => '555-123-4567'
            ]);
        }
        
        // Add timestamps
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        // Check if a tenant with this name already exists
        $existingTenant = DB::table('tenants')->where('name', $data['name'])->first();
        
        if (!$existingTenant) {
            // Insert the tenant
            DB::table('tenants')->insert($data);
            $this->command->info('Demo tenant created successfully.');
        } else {
            $this->command->info('Demo tenant already exists. Skipping creation.');
        }
    }
} 