#!/usr/bin/env php
<?php

/**
 * Setup Laravel-Admin Permissions, Roles, and Menu System
 * This script initializes the complete Laravel-Admin RBAC system
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Setting up Laravel-Admin Permission System ===\n\n";

DB::beginTransaction();

try {
    // 1. Create Permissions
    echo "Step 1: Creating Permissions...\n";
    
    $permissions = [
        ['id' => 1, 'name' => 'All permission', 'slug' => '*', 'http_method' => '', 'http_path' => '*'],
        ['id' => 2, 'name' => 'Dashboard', 'slug' => 'dashboard', 'http_method' => 'GET', 'http_path' => '/'],
        ['id' => 3, 'name' => 'Login', 'slug' => 'auth.login', 'http_method' => '', 'http_path' => '/auth/login\r\n/auth/logout'],
        ['id' => 4, 'name' => 'User setting', 'slug' => 'auth.setting', 'http_method' => 'GET,PUT', 'http_path' => '/auth/setting'],
        ['id' => 5, 'name' => 'Auth management', 'slug' => 'auth.management', 'http_method' => '', 'http_path' => '/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs'],
    ];
    
    foreach ($permissions as $permission) {
        $exists = DB::table('admin_permissions')->where('id', $permission['id'])->exists();
        if (!$exists) {
            DB::table('admin_permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            echo "  ✓ Created permission: {$permission['name']}\n";
        } else {
            echo "  - Permission exists: {$permission['name']}\n";
        }
    }
    
    // 2. Create/Update Roles
    echo "\nStep 2: Creating Roles...\n";
    
    $roles = [
        ['id' => 1, 'name' => 'Administrator', 'slug' => 'administrator'],
        ['id' => 2, 'name' => 'Super Administrator', 'slug' => 'super-admin'],
    ];
    
    foreach ($roles as $role) {
        $exists = DB::table('admin_roles')->where('id', $role['id'])->first();
        if (!$exists) {
            DB::table('admin_roles')->insert(array_merge($role, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            echo "  ✓ Created role: {$role['name']}\n";
        } else {
            // Update existing role
            DB::table('admin_roles')->where('id', $role['id'])->update([
                'name' => $role['name'],
                'slug' => $role['slug'],
                'updated_at' => now(),
            ]);
            echo "  - Updated role: {$role['name']}\n";
        }
    }
    
    // 3. Assign Permissions to Roles
    echo "\nStep 3: Assigning Permissions to Roles...\n";
    
    // Administrator role gets all permissions
    $adminPermissions = [1]; // All permission
    DB::table('admin_role_permissions')->where('role_id', 1)->delete();
    foreach ($adminPermissions as $permId) {
        DB::table('admin_role_permissions')->insert([
            'role_id' => 1,
            'permission_id' => $permId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    echo "  ✓ Administrator role: All permissions assigned\n";
    
    // Super Administrator role gets all permissions
    DB::table('admin_role_permissions')->where('role_id', 2)->delete();
    foreach ($adminPermissions as $permId) {
        DB::table('admin_role_permissions')->insert([
            'role_id' => 2,
            'permission_id' => $permId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    echo "  ✓ Super Administrator role: All permissions assigned\n";
    
    // 4. Create Menu Structure
    echo "\nStep 4: Creating Menu Structure...\n";
    
    $menus = [
        ['id' => 1, 'parent_id' => 0, 'order' => 1, 'title' => 'Dashboard', 'icon' => 'fa-bar-chart', 'uri' => '/', 'permission' => NULL],
        ['id' => 2, 'parent_id' => 0, 'order' => 2, 'title' => 'Admin', 'icon' => 'fa-tasks', 'uri' => '', 'permission' => NULL],
        ['id' => 3, 'parent_id' => 2, 'order' => 3, 'title' => 'Users', 'icon' => 'fa-users', 'uri' => 'auth/users', 'permission' => NULL],
        ['id' => 4, 'parent_id' => 2, 'order' => 4, 'title' => 'Roles', 'icon' => 'fa-user', 'uri' => 'auth/roles', 'permission' => NULL],
        ['id' => 5, 'parent_id' => 2, 'order' => 5, 'title' => 'Permission', 'icon' => 'fa-ban', 'uri' => 'auth/permissions', 'permission' => NULL],
        ['id' => 6, 'parent_id' => 2, 'order' => 6, 'title' => 'Menu', 'icon' => 'fa-bars', 'uri' => 'auth/menu', 'permission' => NULL],
        ['id' => 7, 'parent_id' => 2, 'order' => 7, 'title' => 'Operation log', 'icon' => 'fa-history', 'uri' => 'auth/logs', 'permission' => NULL],
    ];
    
    foreach ($menus as $menu) {
        $exists = DB::table('admin_menu')->where('id', $menu['id'])->exists();
        if (!$exists) {
            DB::table('admin_menu')->insert(array_merge($menu, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            echo "  ✓ Created menu: {$menu['title']}\n";
        } else {
            echo "  - Menu exists: {$menu['title']}\n";
        }
    }
    
    // 5. Assign Menu to Roles
    echo "\nStep 5: Assigning Menu to Roles...\n";
    
    // Both Administrator and Super Administrator get all menu items
    foreach ([1, 2] as $roleId) {
        DB::table('admin_role_menu')->where('role_id', $roleId)->delete();
        foreach ($menus as $menu) {
            DB::table('admin_role_menu')->insert([
                'role_id' => $roleId,
                'menu_id' => $menu['id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $roleName = $roleId == 1 ? 'Administrator' : 'Super Administrator';
        echo "  ✓ {$roleName}: All menu items assigned\n";
    }
    
    // 6. Assign Roles to Users
    echo "\nStep 6: Assigning Roles to Users...\n";
    
    // Get user 'ggg'
    $user = DB::table('my_aspnet_users')->where('name', 'ggg')->first();
    if ($user) {
        // Clear existing role assignments
        DB::table('admin_role_users')->where('user_id', $user->id)->delete();
        
        // Assign both Administrator and Super Administrator roles
        foreach ([1, 2] as $roleId) {
            DB::table('admin_role_users')->insert([
                'role_id' => $roleId,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "  ✓ User 'ggg' (ID: {$user->id}): Administrator & Super Administrator roles assigned\n";
    } else {
        echo "  ⚠ User 'ggg' not found\n";
    }
    
    DB::commit();
    
    echo "\n=== Setup Complete ===\n";
    echo "\nSummary:\n";
    echo "  - Permissions: " . DB::table('admin_permissions')->count() . "\n";
    echo "  - Roles: " . DB::table('admin_roles')->count() . "\n";
    echo "  - Menu Items: " . DB::table('admin_menu')->count() . "\n";
    echo "  - Role-Permission Links: " . DB::table('admin_role_permissions')->count() . "\n";
    echo "  - Role-Menu Links: " . DB::table('admin_role_menu')->count() . "\n";
    echo "  - User-Role Links: " . DB::table('admin_role_users')->count() . "\n";
    
    if ($user) {
        echo "\nUser 'ggg' Roles:\n";
        $userRoles = DB::table('admin_role_users')
            ->join('admin_roles', 'admin_roles.id', '=', 'admin_role_users.role_id')
            ->where('admin_role_users.user_id', $user->id)
            ->select('admin_roles.name', 'admin_roles.slug')
            ->get();
        foreach ($userRoles as $role) {
            echo "  - {$role->name} ({$role->slug})\n";
        }
    }
    
    echo "\n✓ Laravel-Admin RBAC system is now fully configured!\n";
    echo "  Please refresh your dashboard page.\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
