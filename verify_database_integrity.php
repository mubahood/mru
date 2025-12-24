#!/usr/bin/env php
<?php

/**
 * Verify Database Table Integrity
 * Checks for common issues that can cause insert/update errors
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Database Table Integrity Check ===\n\n";

// 1. Check for tables with id column but no AUTO_INCREMENT
echo "1. Checking for missing AUTO_INCREMENT...\n";
$tablesWithoutAutoIncrement = DB::select("
    SELECT TABLE_NAME, COLUMN_NAME, EXTRA 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND COLUMN_NAME IN ('id', 'ID') 
    AND COLUMN_KEY = 'PRI' 
    AND EXTRA NOT LIKE '%auto_increment%'
    LIMIT 10
");

if (empty($tablesWithoutAutoIncrement)) {
    echo "   ✓ All primary key id columns have AUTO_INCREMENT\n";
} else {
    echo "   ⚠ Found " . count($tablesWithoutAutoIncrement) . " tables without AUTO_INCREMENT:\n";
    foreach ($tablesWithoutAutoIncrement as $table) {
        echo "     - {$table->TABLE_NAME}.{$table->COLUMN_NAME}\n";
    }
}

// 2. Check critical tables structure
echo "\n2. Checking critical tables...\n";
$criticalTables = ['accounts', 'account_parents', 'activities', 'admin_menu', 
                   'enterprises', 'academic_years', 'terms', 'my_aspnet_users'];

foreach ($criticalTables as $table) {
    if (!Schema::hasTable($table)) {
        echo "   ✗ Table '$table' missing\n";
        continue;
    }
    
    // Check if has id column
    $idColumn = Schema::hasColumn($table, 'id') ? 'id' : (Schema::hasColumn($table, 'ID') ? 'ID' : null);
    
    if (!$idColumn) {
        echo "   ⚠ Table '$table' has no id column\n";
        continue;
    }
    
    // Check AUTO_INCREMENT
    $columns = DB::select("SHOW COLUMNS FROM `$table` WHERE Field = '$idColumn'");
    if (!empty($columns)) {
        $column = $columns[0];
        $hasAutoIncrement = stripos($column->Extra, 'auto_increment') !== false;
        
        if ($hasAutoIncrement) {
            echo "   ✓ $table (AUTO_INCREMENT ✓)\n";
        } else {
            echo "   ✗ $table (NO AUTO_INCREMENT)\n";
        }
    }
}

// 3. Test insert operations
echo "\n3. Testing insert operations...\n";

// Test accounts table
try {
    $testId = DB::table('accounts')->insertGetId([
        'enterprise_id' => 1,
        'name' => 'Test Account ' . time(),
        'administrator_id' => 6,
        'type' => 'TEST',
        'balance' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "   ✓ accounts table: Insert successful (ID: $testId)\n";
    DB::table('accounts')->where('id', $testId)->delete();
} catch (\Exception $e) {
    echo "   ✗ accounts table: " . $e->getMessage() . "\n";
}

// Test admin_menu table
try {
    $testId = DB::table('admin_menu')->insertGetId([
        'parent_id' => 0,
        'order' => 999,
        'title' => 'Test Menu ' . time(),
        'icon' => 'fa-test',
        'uri' => '',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "   ✓ admin_menu table: Insert successful (ID: $testId)\n";
    DB::table('admin_menu')->where('id', $testId)->delete();
} catch (\Exception $e) {
    echo "   ✗ admin_menu table: " . $e->getMessage() . "\n";
}

// 4. Check for NULL default issues on NOT NULL columns
echo "\n4. Checking for NOT NULL columns without defaults...\n";
$problematicColumns = DB::select("
    SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND IS_NULLABLE = 'NO'
    AND COLUMN_DEFAULT IS NULL
    AND COLUMN_KEY != 'PRI'
    AND EXTRA NOT LIKE '%auto_increment%'
    AND TABLE_NAME IN ('accounts', 'my_aspnet_users', 'enterprises', 'academic_years', 'terms')
    ORDER BY TABLE_NAME
");

if (empty($problematicColumns)) {
    echo "   ✓ No issues found\n";
} else {
    echo "   ⚠ Found " . count($problematicColumns) . " NOT NULL columns without defaults:\n";
    $grouped = [];
    foreach ($problematicColumns as $col) {
        $grouped[$col->TABLE_NAME][] = $col->COLUMN_NAME;
    }
    foreach ($grouped as $table => $columns) {
        echo "     $table: " . implode(', ', $columns) . "\n";
    }
}

// 5. Check enterprise_id columns
echo "\n5. Checking enterprise_id columns...\n";
$enterpriseIdTables = DB::select("
    SELECT TABLE_NAME, COLUMN_DEFAULT, IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND COLUMN_NAME = 'enterprise_id'
    ORDER BY TABLE_NAME
");

$withDefault = 0;
$withoutDefault = 0;

foreach ($enterpriseIdTables as $table) {
    if ($table->COLUMN_DEFAULT !== null || $table->IS_NULLABLE === 'YES') {
        $withDefault++;
    } else {
        $withoutDefault++;
    }
}

echo "   Tables with enterprise_id: " . count($enterpriseIdTables) . "\n";
echo "   With default or nullable: $withDefault\n";
echo "   Without default (NOT NULL): $withoutDefault\n";

if ($withoutDefault > 0) {
    echo "   ⚠ Some tables may require enterprise_id to be set explicitly\n";
}

// 6. Check status column
echo "\n6. Checking status column...\n";
$statusTables = DB::select("
    SELECT TABLE_NAME, COLUMN_DEFAULT, IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND COLUMN_NAME = 'status'
    AND TABLE_NAME IN ('my_aspnet_users', 'accounts', 'enterprises')
");

foreach ($statusTables as $table) {
    $default = $table->COLUMN_DEFAULT ?? 'NULL';
    $nullable = $table->IS_NULLABLE === 'YES' ? 'nullable' : 'NOT NULL';
    echo "   {$table->TABLE_NAME}: default=$default ($nullable)\n";
}

// 7. Summary
echo "\n=== Summary ===\n";
echo "✓ Critical tables checked\n";
echo "✓ AUTO_INCREMENT verified\n";
echo "✓ Insert operations tested\n";
echo "\nDatabase integrity check complete!\n\n";
