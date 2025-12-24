#!/usr/bin/env php
<?php

/**
 * Assign Super Admin role to user 'ggg'
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Assigning Super Admin Role to User 'ggg' ===\n\n";

// Get user ggg
$user = DB::table('my_aspnet_users')->where('name', 'ggg')->first();
if (!$user) {
    die("Error: User 'ggg' not found\n");
}

echo "User found:\n";
echo "  ID: {$user->id}\n";
echo "  Name: {$user->name}\n";
echo "  Email: {$user->email}\n\n";

// Check if admin_roles table exists and fix structure if needed
$rolesExist = DB::select("SHOW TABLES LIKE 'admin_roles'");
if (!empty($rolesExist)) {
    echo "Checking admin_roles table structure...\n";
    $columns = DB::select("SHOW COLUMNS FROM admin_roles LIKE 'id'");
    if (!empty($columns)) {
        $column = $columns[0];
        // Check if id column has AUTO_INCREMENT
        if (stripos($column->Extra, 'auto_increment') === false) {
            echo "Fixing admin_roles table - adding AUTO_INCREMENT to id column...\n";
            DB::statement("ALTER TABLE admin_roles MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
        }
    }
} else {
    echo "Creating admin_roles table...\n";
    DB::statement("
        CREATE TABLE IF NOT EXISTS admin_roles (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            slug VARCHAR(50) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )
    ");
}

// Check if admin_role_users table exists
$roleUsersExist = DB::select("SHOW TABLES LIKE 'admin_role_users'");
if (empty($roleUsersExist)) {
    echo "Creating admin_role_users table...\n";
    DB::statement("
        CREATE TABLE IF NOT EXISTS admin_role_users (
            role_id INT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            PRIMARY KEY (role_id, user_id)
        )
    ");
}

// Get or create Super Admin role
$superAdminRole = DB::table('admin_roles')->where('slug', 'super-admin')->first();

if (!$superAdminRole) {
    echo "Creating Super Admin role...\n";
    $roleId = DB::table('admin_roles')->insertGetId([
        'name' => 'Super Administrator',
        'slug' => 'super-admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ Super Admin role created (ID: {$roleId})\n\n";
} else {
    $roleId = $superAdminRole->id;
    echo "✓ Super Admin role found (ID: {$roleId})\n\n";
}

// Check if user already has the role
$existingAssignment = DB::table('admin_role_users')
    ->where('user_id', $user->id)
    ->where('role_id', $roleId)
    ->first();

if ($existingAssignment) {
    echo "✓ User 'ggg' already has Super Admin role\n";
} else {
    echo "Assigning Super Admin role to user 'ggg'...\n";
    DB::table('admin_role_users')->insert([
        'user_id' => $user->id,
        'role_id' => $roleId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ Super Admin role assigned successfully\n";
}

// Show current role assignments
echo "\nCurrent roles for user 'ggg':\n";
$userRoles = DB::table('admin_role_users')
    ->join('admin_roles', 'admin_roles.id', '=', 'admin_role_users.role_id')
    ->where('admin_role_users.user_id', $user->id)
    ->select('admin_roles.id', 'admin_roles.name', 'admin_roles.slug')
    ->get();

if ($userRoles->isEmpty()) {
    echo "  (No roles assigned)\n";
} else {
    foreach ($userRoles as $role) {
        echo "  - {$role->name} ({$role->slug})\n";
    }
}

echo "\n=== Done ===\n";
echo "User 'ggg' is now a Super Administrator!\n";
echo "Please refresh the dashboard page.\n\n";
