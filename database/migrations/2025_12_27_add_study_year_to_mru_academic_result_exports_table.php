<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudyYearToMruAcademicResultExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mru_academic_result_exports', function (Blueprint $table) {
            // Add study_year column after semester
            // Making it required (no default) to enforce year selection
            $table->integer('study_year')->after('semester');
            
            // Add index for faster filtering
            $table->index('study_year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mru_academic_result_exports', function (Blueprint $table) {
            $table->dropIndex(['study_year']);
            $table->dropColumn('study_year');
        });
    }
}
