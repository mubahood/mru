<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinimumPassesRequiredToMruAcademicResultExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mru_academic_result_exports', function (Blueprint $table) {
            $table->integer('minimum_passes_required')->default(0)->after('study_year');
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
            $table->dropColumn('minimum_passes_required');
        });
    }
}
