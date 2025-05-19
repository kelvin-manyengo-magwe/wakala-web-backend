<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Creating roles
    $adminRole = Role::create(['name' => 'admin']);
    $wakalaRole = Role::create(['name' => 'wakala']);

    // Creating permissions
    $permissions = [
        'manage users',
        'manage roles',
        'manage settings'
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    // Assign all permissions to admin
                    $adminRole->givePermissionTo(Permission::all());

                    // Assign specific permissions to wakala
                    $wakalaRole->givePermissionTo('manage users');

                    // Create admin user
                    $admin = User::create([
                    'name' => 'Admin',
                    'email' => 'admin@example.com',
                    'password' => bcrypt('password'),
                    ]);
                    $admin->assignRole('admin');

                    // Create wakala user
                    $wakala = User::create([
                    'name' => 'Wakala User',
                    'email' => 'wakala@example.com',
                    'password' => bcrypt('password'),
                    ]);
                    $wakala->assignRole('wakala');
    }
}
