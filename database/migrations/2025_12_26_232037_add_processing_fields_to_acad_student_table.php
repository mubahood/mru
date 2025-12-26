<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcessingFieldsToAcadStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acad_student', function (Blueprint $table) {
            $table->enum('is_processed', ['Yes', 'No'])->default('No')->after('progid');
            $table->enum('is_processed_successful', ['Yes', 'No'])->default('No')->after('is_processed');
            $table->text('processing_reason')->nullable()->after('is_processed_successful');
            
            $table->index('is_processed');
            $table->index('is_processed_successful');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acad_student', function (Blueprint $table) {
            $table->dropIndex(['is_processed']);
            $table->dropIndex(['is_processed_successful']);
            $table->dropColumn(['is_processed', 'is_processed_successful', 'processing_reason']);
        });
    }
}
