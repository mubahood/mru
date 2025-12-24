<?php

namespace App\Admin\Controllers;

use App\Models\MruExamResultFaculty;
use App\Models\MruStudent;
use App\Models\MruCourse;
use App\Models\MruProgramme;
use App\Models\MruAcademicYear;
use App\Models\MruExamSetting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

/**
 * MruExamResultFacultyController
 * 
 * PRIMARY EXAM MARKS SUBMISSION INTERFACE
 * Table: acad_examresults_faculty (152,122 records)
 * 
 * This is where lecturers enter exam marks and the system combines with coursework.
 */
class MruExamResultFacultyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MRU Exam Results (Faculty)';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruExamResultFaculty());

        // Eager load relationships
        $grid->model()->with(['student', 'course', 'examSetting'])
            ->orderBy('ID', 'desc');
        $grid->paginate(20);

        // Column 1: ID
        $grid->column('ID', __('ID'))->sortable();

        // Column 2: Student
        $grid->column('student_info', __('Student'))->display(function () {
            $student = $this->student;
            $name = $student ? $student->full_name : 'N/A';
            return "<div><strong style='color:#0066cc;'>{$this->regno}</strong><br><small style='font-size:12px;color:#666;'>{$name}</small></div>";
        });

        // Column 3: Course
        $grid->column('course_info', __('Course'))->display(function () {
            $course = $this->course;
            $courseName = $course ? $course->courseName : 'N/A';
            return "<div><strong>{$this->course_id}</strong><br><small style='font-size:11px;color:#666;'>{$courseName}</small></div>";
        });

        // Column 4: Academic Period
        $grid->column('acadyear', __('Year'))->sortable();
        $grid->column('semester', __('Sem'))->sortable();

        // Column 5: Marks
        $grid->column('cw_mark', __('CW'))->sortable();
        $grid->column('ex_mark', __('Exam'))->sortable();

        // Column 6: Total
        $grid->column('total_mark', __('Total'))->sortable();

        // Column 7: Grade
        $grid->column('grade', __('Grade'))->sortable();

        // Column 8: GPA
        $grid->column('gpa', __('GPA'))->sortable();

        // Column 9: Status
        $grid->column('exam_status', __('Status'))->sortable();

        // Advanced Filters (3-column layout)
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            
            // Row 1
            $filter->column(1/3, function ($filter) {
                $filter->like('regno', __('Student RegNo'));
                $filter->like('course_id', __('Course ID'));
                $filter->equal('progid', __('Programme'))->select(MruProgramme::orderBy('progname')->pluck('progname', 'progcode'));
            });
            
            $filter->column(1/3, function ($filter) {
                $filter->equal('acadyear', __('Academic Year'))->select(MruAcademicYear::orderBy('acadyear', 'desc')->pluck('acadyear', 'acadyear'));
                $filter->equal('semester', __('Semester'))->select([
                    1 => 'Semester 1',
                    2 => 'Semester 2',
                    3 => 'Semester 3',
                ]);
                $filter->equal('cyear', __('Study Year'))->select([
                    1 => 'Year 1',
                    2 => 'Year 2',
                    3 => 'Year 3',
                    4 => 'Year 4',
                    5 => 'Year 5',
                ]);
            });
            
            $filter->column(1/3, function ($filter) {
                $filter->equal('grade', __('Grade'))->select([
                    'A' => 'A - Excellent',
                    'B+' => 'B+ - Very Good',
                    'B' => 'B - Good',
                    'C+' => 'C+ - Fairly Good',
                    'C' => 'C - Satisfactory',
                    'D+' => 'D+ - Fair',
                    'D' => 'D - Pass',
                    'E' => 'E - Marginal Fail',
                    'F' => 'F - Fail',
                ]);
                $filter->between('total_mark', __('Total Mark Range'));
                $filter->equal('exam_status', __('Exam Status'))->select([
                    'REGULAR' => 'REGULAR',
                    'RETAKE' => 'RETAKE',
                    'SPECIAL' => 'SPECIAL',
                ]);
            });
        });

        // Quick search
        $grid->quickSearch('regno', 'course_id', 'grade');

        // Export
        $grid->export(function ($export) {
            $export->filename('MRU_Exam_Results_Faculty_' . date('Y-m-d'));
        });

        // Disable batch actions
        $grid->disableBatchActions();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(MruExamResultFaculty::findOrFail($id));

        $show->field('ID', __('ID'));
        
        // Student Information
        $show->divider('Student Information');
        $show->field('regno', __('Registration Number'));
        $show->field('student.sname', __('Surname'));
        $show->field('student.fname', __('First Name'));
        $show->field('student.oname', __('Other Name'));
        
        // Course Information
        $show->divider('Course Information');
        $show->field('course_id', __('Course ID'));
        $show->field('course.CourseName', __('Course Name'));
        $show->field('creditUnits', __('Credit Units'));
        
        // Academic Details
        $show->divider('Academic Details');
        $show->field('acadyear', __('Academic Year'));
        $show->field('semester', __('Semester'));
        $show->field('cyear', __('Year of Study'));
        $show->field('progid', __('Programme ID'));
        $show->field('programme.progname', __('Programme Name'));
        $show->field('stud_session', __('Student Session'));
        
        // Mark Components
        $show->divider('Mark Components');
        $show->field('cw_mark_entered', __('Coursework Entered'))->using([0 => 'No', 1 => 'Yes']);
        $show->field('cw_mark', __('Coursework Mark'));
        $show->field('test_mark_entered', __('Test Entered'))->using([0 => 'No', 1 => 'Yes']);
        $show->field('test_mark', __('Test Mark'));
        $show->field('exam_mark_entered', __('Exam Entered'))->using([0 => 'No', 1 => 'Yes']);
        $show->field('ex_mark', __('Exam Mark'));
        
        // Results
        $show->divider('Final Results');
        $show->field('total_mark', __('Total Mark'))->as(function ($value) {
            return $value . ' / 100';
        });
        $show->field('grade', __('Grade'))->as(function ($value) {
            return $value . ' - ' . $this->grade_description;
        });
        $show->field('gradept', __('Grade Points'));
        $show->field('gpa', __('GPA'));
        $show->field('status_badge', __('Result Status'));
        $show->field('exam_status', __('Exam Status'));
        
        // Approval
        $show->divider('Approval');
        $show->field('approved_by', __('Approved By'));
        $show->field('settingsID', __('Exam Setting ID'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruExamResultFaculty());

        $form->select('regno', __('Student'))
            ->options(function ($value) {
                if ($value) {
                    $student = MruStudent::where('regno', $value)->first();
                    return $student ? [$value => $value . ' - ' . $student->sname . ' ' . $student->fname] : [];
                }
                return [];
            })
            ->ajax('/admin/api/students')
            ->required();

        $form->select('course_id', __('Course'))
            ->options(function ($value) {
                if ($value) {
                    $course = MruCourse::where('CourseID', $value)->first();
                    return $course ? [$value => $value . ' - ' . $course->CourseName] : [];
                }
                return [];
            })
            ->ajax('/admin/api/courses')
            ->required();

        $form->select('acadyear', __('Academic Year'))
            ->options(MruAcademicYear::orderBy('acadyear', 'desc')->pluck('acadyear', 'acadyear'))
            ->required();

        $form->select('semester', __('Semester'))
            ->options([1 => 'Semester 1', 2 => 'Semester 2', 3 => 'Semester 3'])
            ->required();

        $form->select('progid', __('Programme'))
            ->options(MruProgramme::orderBy('progname')->pluck('progname', 'progcode'))
            ->required();

        $form->number('cyear', __('Year of Study'))->min(1)->max(7)->default(1);

        $form->divider('Mark Components');
        
        $form->switch('cw_mark_entered', __('Coursework Entered'))->default(0);
        $form->decimal('cw_mark', __('Coursework Mark'))->default(0);
        
        $form->switch('test_mark_entered', __('Test Entered'))->default(0);
        $form->decimal('test_mark', __('Test Mark'))->default(0);
        
        $form->switch('exam_mark_entered', __('Exam Entered'))->default(0);
        $form->decimal('ex_mark', __('Exam Mark'))->default(0);

        $form->divider('Computed Results');
        
        $form->decimal('total_mark', __('Total Mark'))->required();
        
        $form->select('grade', __('Grade'))
            ->options([
                'A' => 'A - Excellent',
                'B+' => 'B+ - Very Good',
                'B' => 'B - Good',
                'C+' => 'C+ - Fairly Good',
                'C' => 'C - Satisfactory',
                'D+' => 'D+ - Fair',
                'D' => 'D - Pass',
                'E' => 'E - Marginal Fail',
                'F' => 'F - Fail',
            ])
            ->required();

        $form->decimal('gradept', __('Grade Points'))->min(0)->max(5)->default(0);
        $form->decimal('gpa', __('GPA'))->min(0)->max(5)->default(0);
        $form->number('creditUnits', __('Credit Units'))->default(3);

        $form->select('exam_status', __('Exam Status'))
            ->options(['REGULAR' => 'REGULAR', 'RETAKE' => 'RETAKE', 'SPECIAL' => 'SPECIAL'])
            ->default('REGULAR');

        $form->select('stud_session', __('Student Session'))
            ->options(['DAY' => 'DAY', 'WEEKEND' => 'WEEKEND'])
            ->default('DAY');

        $form->text('approved_by', __('Approved By'));

        $form->select('settingsID', __('Exam Setting'))
            ->options(function ($value) {
                if ($value) {
                    $setting = MruExamSetting::find($value);
                    return $setting ? [$value => $setting->courseID . ' - ' . $setting->acad_year . ' Sem ' . $setting->semester] : [];
                }
                return MruExamSetting::orderBy('ID', 'desc')
                    ->limit(100)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->ID => $item->courseID . ' - ' . $item->acad_year . ' Sem ' . $item->semester];
                    });
            });

        // Auto-calculate total mark
        $form->saving(function (Form $form) {
            $total = ($form->cw_mark ?? 0) + ($form->test_mark ?? 0) + ($form->ex_mark ?? 0);
            $form->total_mark = $total;
            
            // Auto-calculate grade based on total
            if ($total >= 80) {
                $form->grade = 'A';
                $form->gradept = 5;
            } elseif ($total >= 75) {
                $form->grade = 'B+';
                $form->gradept = 4.5;
            } elseif ($total >= 70) {
                $form->grade = 'B';
                $form->gradept = 4;
            } elseif ($total >= 65) {
                $form->grade = 'C+';
                $form->gradept = 3.5;
            } elseif ($total >= 60) {
                $form->grade = 'C';
                $form->gradept = 3;
            } elseif ($total >= 55) {
                $form->grade = 'D+';
                $form->gradept = 2.5;
            } elseif ($total >= 50) {
                $form->grade = 'D';
                $form->gradept = 2;
            } elseif ($total >= 45) {
                $form->grade = 'E';
                $form->gradept = 1;
            } else {
                $form->grade = 'F';
                $form->gradept = 0;
            }
            
            $form->gpa = $form->gradept;
        });

        return $form;
    }
}
