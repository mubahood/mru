<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempMruSpecializationCoursesTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates temporary table for curriculum generation.
     * This table mirrors mru_specialization_courses structure but is used
     * for temporary storage during automatic curriculum generation.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_mru_specialization_courses', function (Blueprint $table) {
            $table->id();
            
            // Core relationships
            $table->unsignedInteger('specialization_id')->index();
            $table->string('course_code', 15)->index();
            $table->string('prog_id', 10)->index();
            $table->string('faculty_code', 20)->index();
            
            // Course scheduling
            $table->unsignedTinyInteger('year')->index()->comment('Year taught (1, 2, 3, 4)');
            $table->unsignedTinyInteger('semester')->comment('Semester taught (1, 2)');
            
            // Course details
            $table->decimal('credits', 5, 2)->default(3.00);
            $table->enum('type', ['mandatory', 'elective'])->default('mandatory');
            
            // Assignment and approval
            $table->unsignedBigInteger('lecturer_id')->nullable()->index();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance with custom short names
            $table->index(['specialization_id', 'year', 'semester'], 'temp_spec_yr_sem_idx');
            $table->index(['prog_id', 'year', 'semester'], 'temp_prog_yr_sem_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_mru_specialization_courses');
    }
}
