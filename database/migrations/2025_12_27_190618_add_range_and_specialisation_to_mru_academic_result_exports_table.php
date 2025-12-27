<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRangeAndSpecialisationToMruAcademicResultExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mru_academic_result_exports', function (Blueprint $table) {
            $table->integer('start_range')->default(1)->after('sort_by');
            $table->integer('end_range')->default(100)->after('start_range');
            $table->integer('specialisation_id')->nullable()->after('programme_id');
            
            $table->index('specialisation_id');
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
            $table->dropColumn(['start_range', 'end_range', 'specialisation_id']);
        });
    }
}
