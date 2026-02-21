<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin - has all permissions
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'api']);
        $role->givePermissionTo(Permission::all());

        // Admin - permissions assign manually
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'api']);

        // Seller - permissions assign manually
        Role::firstOrCreate(['name' => 'Seller', 'guard_name' => 'api']);

        // Customer - permissions assign manually
        Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'api']);
    }
}
