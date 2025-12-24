<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdPrimaryKeyToAcadStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Step 1: Drop existing primary key on regno
        DB::statement('ALTER TABLE acad_student DROP PRIMARY KEY');
        
        // Step 2: Add auto-increment ID column as new primary key
        Schema::table('acad_student', function (Blueprint $table) {
            $table->id('ID')->first();
        });
        
        // Step 3: Add unique index on regno
        Schema::table('acad_student', function (Blueprint $table) {
            $table->unique('regno', 'unique_regno');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Step 1: Drop unique index on regno
        Schema::table('acad_student', function (Blueprint $table) {
            $table->dropUnique('unique_regno');
        });
        
        // Step 2: Drop ID column
        Schema::table('acad_student', function (Blueprint $table) {
            $table->dropColumn('ID');
        });
        
        // Step 3: Restore regno as primary key
        DB::statement('ALTER TABLE acad_student ADD PRIMARY KEY (regno)');
    }
}
