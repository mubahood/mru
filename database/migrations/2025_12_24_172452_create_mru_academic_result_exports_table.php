<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMruAcademicResultExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mru_academic_result_exports', function (Blueprint $table) {
            $table->id();
            $table->string('export_name');
            $table->enum('export_type', ['excel', 'pdf', 'both'])->default('excel');
            $table->string('academic_year')->nullable();
            $table->string('semester')->nullable();
            $table->string('programme_id')->nullable();
            $table->string('faculty_code')->nullable();
            $table->boolean('include_coursework')->default(true);
            $table->boolean('include_practical')->default(true);
            $table->boolean('include_summary')->default(true);
            $table->enum('sort_by', ['student', 'course', 'grade', 'programme'])->default('student');
            $table->string('excel_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('total_records')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->index('academic_year');
            $table->index('semester');
            $table->index('programme_id');
            $table->index('status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mru_academic_result_exports');
    }
}
