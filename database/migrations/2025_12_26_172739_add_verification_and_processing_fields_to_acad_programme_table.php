<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificationAndProcessingFieldsToAcadProgrammeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acad_programme', function (Blueprint $table) {
            $table->string('is_verified', 10)->nullable()->default('No')->after('number_of_semester_12_courses');
            $table->string('is_processed', 10)->nullable()->default('No')->after('is_verified');
            $table->string('process_passed', 10)->nullable()->default('No')->after('is_processed');
            $table->text('error_mess')->nullable()->after('process_passed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acad_programme', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'is_processed', 'process_passed', 'error_mess']);
        });
    }
}
