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
            'view labor rates',
            'create labor rates',
            'edit labor rates',
            'delete labor rates',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Estimates
        Permission::create(['name' => 'view estimates']);
        Permission::create(['name' => 'create estimates']);
        Permission::create(['name' => 'edit estimates']);
        Permission::create(['name' => 'delete estimates']);
        Permission::create(['name' => 'approve estimates']);

        // Items
        Permission::create(['name' => 'view items']);
        Permission::create(['name' => 'create items']);
        Permission::create(['name' => 'edit items']);
        Permission::create(['name' => 'delete items']);

        // Assemblies
        Permission::create(['name' => 'view assemblies']);
        Permission::create(['name' => 'create assemblies']);
        Permission::create(['name' => 'edit assemblies']);
        Permission::create(['name' => 'delete assemblies']);

        // Packages
        Permission::create(['name' => 'view packages']);
        Permission::create(['name' => 'create packages']);
        Permission::create(['name' => 'edit packages']);
        Permission::create(['name' => 'delete packages']);

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
        ]);

        // Estimator role
        $estimatorRole = Role::create(['name' => 'estimator']);
        $estimatorRole->givePermissionTo([
            'view labor rates',
        ]);
    }
}
