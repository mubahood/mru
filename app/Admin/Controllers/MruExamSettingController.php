<?php

namespace App\Admin\Controllers;

use App\Models\MruExamSetting;
use App\Models\MruCourse;
use App\Models\MruProgramme;
use App\Models\MruAcademicYear;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * MruExamSettingController
 * 
 * Manages exam configuration and mark distribution
 * Table: acad_examsettings (15,229 records)
 */
class MruExamSettingController extends AdminController
{
    protected $title = 'MRU Exam Settings';

    protected function grid()
    {
        $grid = new Grid(new MruExamSetting());
        
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
                <strong>{$this->courseID}</strong> {$this->acad_year} Sem {$this->semester}
            </div>";
        });

        $grid->column('weight_distribution', __('Distribution'))->display(function () {
            $hasPractical = $this->has_practical;
            $parts = ["Exam:{$this->exam_percent}%", "CW:{$this->cw_percent}%"];
            if ($hasPractical) $parts[] = "Prac:{$this->practical_percent}%";
            return implode(' | ', $parts);
        });

        $grid->column('questions', __('Questions Max'))->display(function () {
            $total = $this->total_exam_marks;
            return "Total: <strong>{$total}</strong> marks<br><small style='color:#999;'>Q1-Q10 configured</small>";
        });

        $grid->column('final_total', __('Final Total'))->sortable();

        $grid->column('ExamFormat', __('Format'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('courseID', __('Course ID'));
            $filter->equal('prog_id', __('Programme'))->select(MruProgramme::pluck('progname', 'progcode'));
            $filter->equal('acad_year', __('Academic Year'))->select(MruAcademicYear::orderBy('acadyear', 'desc')->pluck('acadyear', 'acadyear'));
            $filter->equal('semester', __('Semester'))->select([1 => 'Sem 1', 2 => 'Sem 2', 3 => 'Sem 3']);
            $filter->where(function ($query) {
                $query->where('practical_percent', '>', 0);
            }, __('Has Practical'))->checkbox();
        });

        $grid->quickSearch('courseID', 'prog_id');
        $grid->disableBatchActions();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(MruExamSetting::findOrFail($id));
        
        $show->field('ID', __('ID'));
        $show->field('courseID', __('Course ID'));
        $show->field('course.CourseName', __('Course Name'));
        $show->field('acad_year', __('Academic Year'));
        $show->field('semester', __('Semester'));
        $show->field('prog_id', __('Programme'));
        $show->field('study_yr', __('Study Year'));
        
        $show->divider('Questions Configuration');
        $show->field('max_Q1', __('Max Q1'));
        $show->field('max_Q2', __('Max Q2'));
        $show->field('max_Q3', __('Max Q3'));
        $show->field('max_Q4', __('Max Q4'));
        $show->field('max_Q5', __('Max Q5'));
        $show->field('max_Q6', __('Max Q6'));
        $show->field('max_Q7', __('Max Q7'));
        $show->field('max_Q8', __('Max Q8'));
        $show->field('max_Q9', __('Max Q9'));
        $show->field('max_Q10', __('Max Q10'));
        
        $show->divider('Weight Distribution');
        $show->field('exam_percent', __('Exam Percentage'))->as(function ($value) {
            return $value . '%';
        });
        $show->field('cw_percent', __('Coursework Percentage'))->as(function ($value) {
            return $value . '%';
        });
        $show->field('practical_percent', __('Practical Percentage'))->as(function ($value) {
            return $value . '%';
        });
        $show->field('final_total', __('Final Total'));
        $show->field('ExamFormat', __('Exam Format'));
        $show->field('sheet_status', __('Sheet Status'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new MruExamSetting());

        $form->select('courseID', __('Course'))
            ->options(MruCourse::orderBy('CourseID')->pluck('CourseName', 'CourseID'))
            ->required();

        $form->select('acad_year', __('Academic Year'))
            ->options(MruAcademicYear::orderBy('acadyear', 'desc')->pluck('acadyear', 'acadyear'))
            ->required();

        $form->select('semester', __('Semester'))
            ->options([1 => 'Semester 1', 2 => 'Semester 2', 3 => 'Semester 3'])
            ->required();

        $form->select('prog_id', __('Programme'))
            ->options(MruProgramme::orderBy('progname')->pluck('progname', 'progcode'))
            ->required();

        $form->number('study_yr', __('Study Year'))->min(1)->max(7)->default(1);

        $form->divider('Questions Configuration (Max Marks)');
        $form->decimal('max_Q1', __('Question 1'))->default(0);
        $form->decimal('max_Q2', __('Question 2'))->default(0);
        $form->decimal('max_Q3', __('Question 3'))->default(0);
        $form->decimal('max_Q4', __('Question 4'))->default(0);
        $form->decimal('max_Q5', __('Question 5'))->default(0);
        $form->decimal('max_Q6', __('Question 6'))->default(0);
        $form->decimal('max_Q7', __('Question 7'))->default(0);
        $form->decimal('max_Q8', __('Question 8'))->default(0);
        $form->decimal('max_Q9', __('Question 9'))->default(0);
        $form->decimal('max_Q10', __('Question 10'))->default(0);

        $form->divider('Weight Distribution (%)');
        $form->decimal('exam_percent', __('Exam Percentage'))->default(70)->required()
            ->help('e.g., 70 for 70%');
        $form->decimal('cw_percent', __('Coursework Percentage'))->default(30)->required()
            ->help('e.g., 30 for 30%');
        $form->decimal('practical_percent', __('Practical Percentage'))->default(0)
            ->help('0 if no practical component');

        $form->decimal('final_total', __('Final Total'))->default(100)->required();

        $form->text('ExamFormat', __('Exam Format'))->default('WRITTEN');
        $form->text('sheet_status', __('Sheet Status'))->default('ACTIVE');

        $form->select('stud_session', __('Student Session'))
            ->options(['DAY' => 'DAY', 'WEEKEND' => 'WEEKEND'])
            ->default('DAY');

        $form->text('empCode', __('Employee Code'));

        return $form;
    }
}
