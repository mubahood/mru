<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenceColumnsToCourseRegistrationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acad_course_registration', function (Blueprint $table) {
            // Add term_id reference to terms table (no foreign key constraint due to DB limitation)
            $table->unsignedBigInteger('term_id')->nullable()->after('semester');
            $table->index('term_id'); // Add index for performance
            
            // Add student_semester_enrollment_id reference to student_has_semeters table
            $table->unsignedBigInteger('student_semester_enrollment_id')->nullable()->after('term_id');
            $table->index('student_semester_enrollment_id'); // Add index for performance
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acad_course_registration', function (Blueprint $table) {
            $table->dropIndex(['term_id']);
            $table->dropColumn('term_id');
            
            $table->dropIndex(['student_semester_enrollment_id']);
            $table->dropColumn('student_semester_enrollment_id');
        });
    }
}
