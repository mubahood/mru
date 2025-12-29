<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMruSpecializationCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mru_specialization_courses', function (Blueprint $table) {
            $table->id();
            
            // Core relationships
            $table->unsignedInteger('specialization_id');
            $table->string('course_code', 15);
            $table->string('prog_id', 10);
            $table->string('faculty_code', 20);
            
            // Course details
            $table->unsignedTinyInteger('year')->comment('1, 2, 3, 4');
            $table->unsignedTinyInteger('semester')->comment('1, 2');
            $table->decimal('credits', 5, 2)->default(0);
            $table->enum('type', ['mandatory', 'elective'])->default('mandatory');
            
            // Lecturer assignment
            $table->unsignedBigInteger('lecturer_id')->nullable();
            
            // Status and approval
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('specialization_id');
            $table->index('course_code');
            $table->index('prog_id');
            $table->index('faculty_code');
            $table->index('lecturer_id');
            $table->index(['year', 'semester']);
            $table->index('approval_status');
            
            // Unique constraint to prevent duplicates
            $table->unique(['specialization_id', 'course_code', 'year', 'semester'], 'unique_spec_course');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mru_specialization_courses');
    }
}
