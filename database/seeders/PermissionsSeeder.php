<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'view_dashboard']);
        Permission::create(['name' => 'view_products']);
        Permission::create(['name' => 'create_products']);
        Permission::create(['name' => 'edit_products']);
        Permission::create(['name' => 'delete_products']);
        Permission::create(['name' => 'create_categories']);
        Permission::create(['name' => 'edit_categories']);
        Permission::create(['name' => 'delete_categories']);
        Permission::create(['name' => 'view_users']);
        Permission::create(['name' => 'create_users']);
        Permission::create(['name' => 'edit_users']);
        Permission::create(['name' => 'delete_users']);

        // create roles and assign existing permissions
        $super_admin = Role::create(['name' => 'super_admin']);
        $super_admin->givePermissionTo('view_dashboard');
        $product_manager= Role::create(['name' => 'product_manager']);
        $user_manager= Role::create(['name'=>'user_manager']);
       
        // create demo users
        $user = \App\Models\User::factory()->create([
            'name' => 'Example User',
            'email' => 'tester@example.com',
        ]);
        $user->assignRole($super_admin);

        $user = \App\Models\User::factory()->create([
            'name' => 'Example Admin User',
            'email' => 'admin@example.com',
        ]);
        $user->assignRole($product_manager);

        $user = \App\Models\User::factory()->create([
            'name' => 'Example Super-Admin User',
            'email' => 'superadmin@example.com',
        ]);
        $user->assignRole($user_manager);
    }
}
