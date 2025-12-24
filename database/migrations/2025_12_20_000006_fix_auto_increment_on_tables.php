<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixAutoIncrementOnTables extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix tables where id field doesn't have AUTO_INCREMENT
     * This causes "Field 'id' doesn't have a default value" errors
     *
     * @return void
     */
    public function up()
    {
        // List of tables that need AUTO_INCREMENT on id field
        $tables = [
            'accounts',
            'account_parents',
            'activities',
            'admin_menu',
        ];
        
        foreach ($tables as $table) {
            try {
                // Check if table exists
                if (!Schema::hasTable($table)) {
                    echo "Table $table does not exist, skipping...\n";
                    continue;
                }
                
                // Check if id column exists
                if (!Schema::hasColumn($table, 'id')) {
                    echo "Table $table does not have id column, skipping...\n";
                    continue;
                }
                
                // Get column info
                $columns = DB::select("SHOW COLUMNS FROM `$table` WHERE Field = 'id'");
                if (empty($columns)) {
                    continue;
                }
                
                $column = $columns[0];
                
                // Check if already has auto_increment
                if (stripos($column->Extra, 'auto_increment') !== false) {
                    echo "Table $table already has AUTO_INCREMENT, skipping...\n";
                    continue;
                }
                
                echo "Fixing AUTO_INCREMENT on table: $table\n";
                
                // Determine the column type
                $type = $column->Type;
                $null = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
                
                // Add AUTO_INCREMENT
                DB::statement("ALTER TABLE `$table` MODIFY COLUMN `id` $type $null AUTO_INCREMENT");
                
                echo "✓ Fixed AUTO_INCREMENT on $table\n";
                
            } catch (\Exception $e) {
                echo "Error fixing table $table: " . $e->getMessage() . "\n";
                // Continue with other tables
            }
        }
        
        // Also check and fix some ASP.NET tables that might need it
        $aspnetTables = [
            'acad_facultyresultsheets' => 'ID',
            'acad_failed_passes' => 'ID',
            'acad_graduation_clearance' => 'ID',
            'my_aspnet_classes' => 'ID',
        ];
        
        foreach ($aspnetTables as $table => $idColumn) {
            try {
                if (!Schema::hasTable($table)) {
                    continue;
                }
                
                if (!Schema::hasColumn($table, $idColumn)) {
                    continue;
                }
                
                // Get column info
                $columns = DB::select("SHOW COLUMNS FROM `$table` WHERE Field = '$idColumn'");
                if (empty($columns)) {
                    continue;
                }
                
                $column = $columns[0];
                
                // Check if already has auto_increment
                if (stripos($column->Extra, 'auto_increment') !== false) {
                    continue;
                }
                
                echo "Fixing AUTO_INCREMENT on table: $table (column: $idColumn)\n";
                
                $type = $column->Type;
                $null = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
                
                // Check if it's a primary key
                $isPrimary = $column->Key === 'PRI';
                
                if ($isPrimary) {
                    DB::statement("ALTER TABLE `$table` MODIFY COLUMN `$idColumn` $type $null AUTO_INCREMENT");
                    echo "✓ Fixed AUTO_INCREMENT on $table.$idColumn\n";
                }
                
            } catch (\Exception $e) {
                echo "Error fixing table $table: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // We don't reverse AUTO_INCREMENT changes as they are fixes, not features
        echo "AUTO_INCREMENT fixes are not reversed\n";
    }
}
