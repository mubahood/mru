<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDefaultValuesToRequiredColumns extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add default values or make columns nullable where appropriate
     * to prevent "Field doesn't have a default value" errors
     *
     * @return void
     */
    public function up()
    {
        // Fix accounts table - name should be nullable or have a default
        Schema::table('accounts', function (Blueprint $table) {
            // Change name from NOT NULL to nullable
            DB::statement("ALTER TABLE accounts MODIFY COLUMN name TEXT NULL");
        });
        
        echo "✓ Fixed accounts.name to be nullable\n";
        
        // Note: We don't change enterprise_id, administrator_id as they should be explicitly set
        // These are foreign keys and should always have valid values
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            DB::statement("ALTER TABLE accounts MODIFY COLUMN name TEXT NOT NULL");
        });
    }
}
