<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCreatedToTempMruSpecializationCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_mru_specialization_courses', function (Blueprint $table) {
            $table->boolean('is_created')->default(false)->after('rejection_reason')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_mru_specialization_courses', function (Blueprint $table) {
            $table->dropColumn('is_created');
        });
    }
}
