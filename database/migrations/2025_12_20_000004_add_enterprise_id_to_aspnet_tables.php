<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddEnterpriseIdToAspnetTables extends Migration
{
    /**
     * Run the migrations.
     * Add enterprise_id to ASP.NET tables for multi-tenancy support.
     *
     * @return void
     */
    public function up()
    {
        // Tables that need enterprise_id for multi-tenancy
        $tables = [
            'my_aspnet_users',
            'my_aspnet_roles',
            'my_aspnet_applications',
            'my_aspnet_membership',
            'my_aspnet_paths',
            'my_aspnet_personalizationallusers',
            'my_aspnet_personalizationperuser',
            'my_aspnet_profile',
            'my_aspnet_schemaversions',
            'my_aspnet_sessions',
            'my_aspnet_usersinroles',
        ];

        foreach ($tables as $table) {
            // Check if table exists
            if (Schema::hasTable($table)) {
                // Check if enterprise_id column doesn't already exist
                if (!Schema::hasColumn($table, 'enterprise_id')) {
                    Schema::table($table, function (Blueprint $table_blueprint) use ($table) {
                        // Some tables don't have 'id' column (e.g., my_aspnet_membership uses userId)
                        if (Schema::hasColumn($table, 'id')) {
                            $table_blueprint->unsignedBigInteger('enterprise_id')->default(1)->after('id');
                        } else {
                            $table_blueprint->unsignedBigInteger('enterprise_id')->default(1);
                        }
                        $table_blueprint->index('enterprise_id');
                    });
                    
                    // Update all existing records to enterprise_id = 1 (Mutesa I Royal University)
                    DB::statement("UPDATE `{$table}` SET enterprise_id = 1 WHERE enterprise_id IS NULL OR enterprise_id = 0");
                    
                    echo "Added enterprise_id to {$table}\n";
                }
            }
        }
        
        // Add enterprise_id to core MRU tables
        $coreTables = [
            'accounts',
            'academic_classes',
            'academic_class_fees',
            'academic_class_sctreams',
            'classes',
            'class_fee_items',
            'courses',
            'exams',
            'exam_records',
            'marks',
            'mark_records',
            'participants',
            'reports',
            'report_cards',
            'semesters',
            'stock_categories',
            'stock_items',
            'stock_records',
            'students',
            'subjects',
            'teachers',
            'theology_classes',
            'theology_marks',
            'theology_mark_records',
            'theology_streams',
            'theology_subjects',
            'theology_termly_report_cards',
        ];

        foreach ($coreTables as $table) {
            if (Schema::hasTable($table)) {
                if (!Schema::hasColumn($table, 'enterprise_id')) {
                    Schema::table($table, function (Blueprint $table_blueprint) {
                        $table_blueprint->unsignedBigInteger('enterprise_id')->default(1);
                        $table_blueprint->index('enterprise_id');
                    });
                    
                    DB::statement("UPDATE `{$table}` SET enterprise_id = 1 WHERE enterprise_id IS NULL OR enterprise_id = 0");
                    
                    echo "Added enterprise_id to {$table}\n";
                }
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
        // Not reversing this migration as it would break multi-tenancy
        // If needed, manually drop enterprise_id columns
    }
}
