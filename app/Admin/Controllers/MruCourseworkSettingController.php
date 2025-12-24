<?php

namespace App\Admin\Controllers;

use App\Models\MruCourseworkSetting;
use App\Models\MruCourse;
use App\Models\MruProgramme;
use App\Models\MruAcademicYear;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * MruCourseworkSettingController
 * 
 * Manages coursework configuration for each course offering
 * Table: acad_coursework_settings (17,983 records)
 */
class MruCourseworkSettingController extends AdminController
{
    protected $title = 'MRU Coursework Settings';

    protected function grid()
    {
        $grid = new Grid(new MruCourseworkSetting());
        
        // Eager load relationships
        $grid->model()->with(['course', 'programme'])
            ->orderBy('ID', 'desc');
        $grid->paginate(20);

        $grid->column('ID', __('ID'))->sortable();
        
        $grid->column('course_info', __('Course'))->display(function () {
            $course = $this->course;
            $courseName = $course ? $course->courseName : 'N/A';
            return "<div><strong>{$this->courseID}</strong><br><small style='font-size:11px;color:#666;'>{$courseName}</small></div>";
        });

        $grid->column('academic_period', __('Period'))->display(function () {
            return "<div style='line-height:1.6;'>
                <strong>{$this->courseID}</strong> {$this->acadyear} Sem {$this->semester}
            </div>";
        });

        $grid->column('assignments', __('Assignments Max'))->display(function () {
            return "A1:{$this->max_assn_1} | A2:{$this->max_assn_2} | A3:{$this->max_assn_3} | A4:{$this->max_assn_4}";
        });

        $grid->column('tests', __('Tests Max'))->display(function () {
            return "T1:{$this->max_test_1} | T2:{$this->max_test_2} | T3:{$this->max_test_3}";
        });

        $grid->column('total_mark', __('Total'))->sortable();

        $grid->column('cw_approve_status', __('Status'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('courseID', __('Course ID'));
            $filter->equal('progID', __('Programme'))->select(MruProgramme::pluck('progname', 'progcode'));
            $filter->equal('acadyear', __('Academic Year'))->select(MruAcademicYear::orderBy('acadyear', 'desc')->pluck('acadyear', 'acadyear'));
            $filter->equal('semester', __('Semester'))->select([1 => 'Sem 1', 2 => 'Sem 2', 3 => 'Sem 3']);
        });

        $grid->quickSearch('courseID', 'progID');
        $grid->disableBatchActions();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(MruCourseworkSetting::findOrFail($id));
        
        $show->field('ID', __('ID'));
        $show->field('courseID', __('Course ID'));
        $show->field('course.CourseName', __('Course Name'));
        $show->field('acadyear', __('Academic Year'));
        $show->field('semester', __('Semester'));
        $show->field('progID', __('Programme'));
        $show->field('study_yr', __('Study Year'));
        
        $show->divider('Assignments Configuration');
        $show->field('max_assn_1', __('Max Assignment 1'));
        $show->field('max_assn_2', __('Max Assignment 2'));
        $show->field('max_assn_3', __('Max Assignment 3'));
        $show->field('max_assn_4', __('Max Assignment 4'));
        
        $show->divider('Tests Configuration');
        $show->field('max_test_1', __('Max Test 1'));
        $show->field('max_test_2', __('Max Test 2'));
        $show->field('max_test_3', __('Max Test 3'));
        
        $show->divider('Summary');
        $show->field('total_mark', __('Total Marks'));
        $show->field('comp_type', __('Computation Type'));
        $show->field('cw_approve_status', __('Approval Status'));
        $show->field('approved_by', __('Approved By'));
        $show->field('approval_date', __('Approval Date'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new MruCourseworkSetting());

        $form->select('courseID', __('Course'))
            ->options(MruCourse::orderBy('CourseID')->pluck('CourseName', 'CourseID'))
            ->required();

        $form->select('acadyear', __('Academic Year'))
            ->options(MruAcademicYear::orderBy('acadyear', 'desc')->pluck('acadyear', 'acadyear'))
            ->required();

        $form->select('semester', __('Semester'))
            ->options([1 => 'Semester 1', 2 => 'Semester 2', 3 => 'Semester 3'])
            ->required();

        $form->select('progID', __('Programme'))
            ->options(MruProgramme::orderBy('progname')->pluck('progname', 'progcode'))
            ->required();

        $form->number('study_yr', __('Study Year'))->min(1)->max(7)->default(1);

        $form->divider('Assignments Configuration');
        $form->decimal('max_assn_1', __('Max Assignment 1'))->default(0);
        $form->decimal('max_assn_2', __('Max Assignment 2'))->default(0);
        $form->decimal('max_assn_3', __('Max Assignment 3'))->default(0);
        $form->decimal('max_assn_4', __('Max Assignment 4'))->default(0);

        $form->divider('Tests Configuration');
        $form->decimal('max_test_1', __('Max Test 1'))->default(0);
        $form->decimal('max_test_2', __('Max Test 2'))->default(0);
        $form->decimal('max_test_3', __('Max Test 3'))->default(0);

        $form->divider('Summary');
        $form->decimal('total_mark', __('Total Marks'))->default(30)->required();
        $form->text('comp_type', __('Computation Type'));

        $form->select('stud_session', __('Student Session'))
            ->options(['DAY' => 'DAY', 'WEEKEND' => 'WEEKEND'])
            ->default('DAY');

        $form->select('cw_approve_status', __('Approval Status'))
            ->options(['PENDING' => 'PENDING', 'APPROVED' => 'APPROVED', 'REJECTED' => 'REJECTED'])
            ->default('PENDING');

        $form->text('lecturerID', __('Lecturer ID'));
        $form->text('approved_by', __('Approved By'));
        $form->datetime('approval_date', __('Approval Date'));

        return $form;
    }
}
