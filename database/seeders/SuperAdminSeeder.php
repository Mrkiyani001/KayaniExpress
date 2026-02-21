<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed the Super Admin user.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'api')->first();

        $user = User::firstOrCreate(
            ['email' => 'superadmin@kayaniexpress.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'is_verified' => true,
                'role_id' => $superAdminRole?->id,
            ]
        );

        if ($superAdminRole) {
            $user->assignRole($superAdminRole);
        }
    }
}
