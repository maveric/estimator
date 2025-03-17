<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Labor Rates
            'view labor rates',
            'create labor rates',
            'edit labor rates',
            'delete labor rates',
            
            // Items
            'view items',
            'create items',
            'edit items',
            'delete items',
            
            // Estimates
            'view estimates',
            'create estimates',
            'edit estimates',
            'delete estimates',
            'approve estimates',
            
            // Assemblies
            'view assemblies',
            'create assemblies',
            'edit assemblies',
            'delete assemblies',
            
            // Packages
            'view packages',
            'create packages',
            'edit packages',
            'delete packages',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin role - gets all permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);

        // Manager role
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view labor rates',
            'create labor rates',
            'edit labor rates',
            'view items',
            'create items',
            'edit items',
            'view estimates',
            'create estimates',
            'edit estimates',
            'view assemblies',
            'create assemblies',
            'edit assemblies',
            'view packages',
            'create packages',
            'edit packages',
        ]);

        // Estimator role
        $estimatorRole = Role::create(['name' => 'estimator']);
        $estimatorRole->givePermissionTo([
            'view labor rates',
            'view items',
            'view estimates',
            'view assemblies',
            'view packages',
        ]);
    }
}
