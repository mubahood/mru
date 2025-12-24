<?php

namespace App\Admin\Controllers;

use App\Models\MruCourseRegistration;
use App\Models\MruCourse;
use App\Models\MruProgramme;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * MruCourseRegistrationController
 * 
 * Laravel Admin controller for managing course registrations in the MRU system.
 * Handles CRUD operations for student course enrollments.
 * 
 * @package App\Admin\Controllers
 */
class MruCourseRegistrationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Course Registrations';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruCourseRegistration());

        // Eager load relationships
        $grid->model()->with(['course', 'student', 'programme'])
            ->orderBy('ID', 'desc');

        /*
        |----------------------------------------------------------------------
        | GRID COLUMNS
        |----------------------------------------------------------------------
        */

        // ID
        $grid->column('ID', __('ID'))
            ->sortable();

        // Student Information
        $grid->column('student', __('Student'))
            ->display(function () {
                if ($this->student) {
                    $name = $this->student->full_name;
                    return "<div><strong>{$this->regno}</strong><br><small style='color:#666;'>{$name}</small></div>";
                }
                return $this->regno;
            })
            ->sortable('regno');

        // Course Information
        $grid->column('course_info', __('Course'))
            ->display(function () {
                if ($this->course) {
                    $name = $this->course->courseName;
                    $credits = $this->course->CreditUnit;
                    return "<div><strong>{$this->courseID}</strong><br><small style='color:#666;'>{$name}</small><br><small style='color:#999;'>{$credits} Credits</small></div>";
                }
                return $this->courseID;
            })
            ->sortable('courseID');

        // Academic Year
        $grid->column('acad_year', __('Academic Year'))
            ->sortable();

        // Semester
        $grid->column('semester', __('Semester'))
            ->sortable()
            ->display(function ($semester) {
                return match($semester) {
                    1 => 'Sem 1',
                    2 => 'Sem 2',
                    3 => 'Sem 3',
                    default => 'Sem ' . $semester
                };
            });

        // Programme
        $grid->column('prog_id', __('Programme'))
            ->sortable();

        // Course Status
        $grid->column('course_status', __('Status'))->sortable();

        // Study Session
        $grid->column('stud_session', __('Session'))->sortable();

        /*
        |----------------------------------------------------------------------
        | GRID FILTERS
        |----------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            // Remove default ID filter
            $filter->disableIdFilter();

            // Student registration number
            $filter->like('regno', __('Student Reg No'));

            // Course code
            $filter->like('courseID', __('Course Code'));

            // Academic year
            $filter->equal('acad_year', __('Academic Year'))
                ->select(MruCourseRegistration::getAcademicYears());

            // Semester
            $filter->equal('semester', __('Semester'))
                ->select([
                    1 => 'Semester 1',
                    2 => 'Semester 2',
                    3 => 'Semester 3',
                ]);

            // Programme
            $filter->like('prog_id', __('Programme'));

            // Course status
            $filter->equal('course_status', __('Status'))
                ->select([
                    MruCourseRegistration::STATUS_REGULAR => 'Regular',
                    MruCourseRegistration::STATUS_NORMAL => 'Normal',
                    MruCourseRegistration::STATUS_RETAKE => 'Retake',
                ]);

            // Study session
            $filter->equal('stud_session', __('Session'))
                ->select([
                    MruCourseRegistration::SESSION_DAY => 'Day',
                    MruCourseRegistration::SESSION_WEEKEND => 'Weekend',
                    MruCourseRegistration::SESSION_EVENING => 'Evening',
                    MruCourseRegistration::SESSION_INSERVICE => 'In-Service',
                    MruCourseRegistration::SESSION_FULL_TIME => 'Full Time',
                    MruCourseRegistration::SESSION_PART_TIME => 'Part Time',
                ]);
        });

        /*
        |----------------------------------------------------------------------
        | GRID ACTIONS
        |----------------------------------------------------------------------
        */

        $grid->actions(function ($actions) {
            // Keep all actions enabled
        });

        /*
        |----------------------------------------------------------------------
        | GRID EXPORT
        |----------------------------------------------------------------------
        */

        $grid->export(function ($export) {
            $export->filename('Course_Registrations_' . date('Y-m-d_His'));
            
            $export->column('ID', 'ID');
            $export->column('regno', 'Student Reg No');
            $export->column('courseID', 'Course Code');
            $export->column('acad_year', 'Academic Year');
            $export->column('semester', 'Semester');
            $export->column('prog_id', 'Programme');
            $export->column('course_status', 'Status');
            $export->column('stud_session', 'Session');
        });

        /*
        |----------------------------------------------------------------------
        | GRID SETTINGS
        |----------------------------------------------------------------------
        */

        // Set rows per page
        $grid->paginate(50);

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
        $show = new Show(MruCourseRegistration::findOrFail($id));

        /*
        |----------------------------------------------------------------------
        | REGISTRATION INFORMATION
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Registration Information')
            ->style('primary');

        $show->field('ID', __('Registration ID'));
        $show->field('regno', __('Student Reg No'));
        $show->field('courseID', __('Course Code'));
        
        $show->divider();

        /*
        |----------------------------------------------------------------------
        | ACADEMIC DETAILS
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Academic Details')
            ->style('info');

        $show->field('acad_year', __('Academic Year'));
        $show->field('semester', __('Semester'))->as(function ($value) {
            return $this->semester_label;
        });
        $show->field('prog_id', __('Programme Code'));

        $show->divider();

        /*
        |----------------------------------------------------------------------
        | STATUS & SESSION
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Status & Session')
            ->style('success');

        $show->field('course_status', __('Registration Status'))->as(function ($value) {
            return $this->status_label;
        })->unescape();

        $show->field('stud_session', __('Study Session'))->as(function ($value) {
            return $this->session_label;
        })->unescape();

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruCourseRegistration());

        /*
        |----------------------------------------------------------------------
        | FORM FIELDS
        |----------------------------------------------------------------------
        */

        $form->text('regno', __('Student Registration Number'))
            ->rules('required|max:25')
            ->required();

        $form->select('courseID', __('Course Code'))
            ->options(MruCourse::pluck('courseID', 'courseID'))
            ->rules('required|max:25')
            ->required();

        $form->text('acad_year', __('Academic Year'))
            ->rules('required|max:25')
            ->placeholder('e.g., 2023/2024')
            ->required();

        $form->select('semester', __('Semester'))
            ->options([
                1 => 'Semester 1',
                2 => 'Semester 2',
                3 => 'Semester 3',
            ])
            ->rules('required|in:1,2,3')
            ->default(1)
            ->required();

        $form->select('course_status', __('Registration Status'))
            ->options([
                MruCourseRegistration::STATUS_REGULAR => 'Regular',
                MruCourseRegistration::STATUS_NORMAL => 'Normal',
                MruCourseRegistration::STATUS_RETAKE => 'Retake',
            ])
            ->rules('required|in:' . implode(',', MruCourseRegistration::STATUSES))
            ->default(MruCourseRegistration::STATUS_REGULAR)
            ->required();

        $form->select('prog_id', __('Programme Code'))
            ->options(MruProgramme::pluck('progcode', 'progcode'))
            ->rules('required|max:25')
            ->required();

        $form->select('stud_session', __('Study Session'))
            ->options([
                MruCourseRegistration::SESSION_DAY => 'Day',
                MruCourseRegistration::SESSION_WEEKEND => 'Weekend',
                MruCourseRegistration::SESSION_EVENING => 'Evening',
                MruCourseRegistration::SESSION_INSERVICE => 'In-Service',
                MruCourseRegistration::SESSION_FULL_TIME => 'Full Time',
                MruCourseRegistration::SESSION_PART_TIME => 'Part Time',
            ])
            ->rules('required|in:' . implode(',', MruCourseRegistration::STUDY_SESSIONS))
            ->default(MruCourseRegistration::SESSION_DAY)
            ->required();

        /*
        |----------------------------------------------------------------------
        | FORM CALLBACKS
        |----------------------------------------------------------------------
        */

        // Before saving
        $form->saving(function (Form $form) {
            // Normalize fields
            if ($form->regno) {
                $form->regno = strtoupper(trim($form->regno));
            }
            if ($form->courseID) {
                $form->courseID = strtoupper(trim($form->courseID));
            }
            if ($form->prog_id) {
                $form->prog_id = strtoupper(trim($form->prog_id));
            }

            // Validate semester
            if (!in_array($form->semester, MruCourseRegistration::SEMESTERS)) {
                admin_error('Error', 'Invalid semester selected');
                return back()->withInput();
            }
        });

        // After saving
        $form->saved(function (Form $form) {
            \Log::info('Course registration saved', [
                'registration_id' => $form->model()->ID,
                'regno' => $form->model()->regno,
                'course' => $form->model()->courseID,
                'user' => \Admin::user()->username ?? 'unknown',
            ]);
        });

        /*
        |----------------------------------------------------------------------
        | FORM SETTINGS
        |----------------------------------------------------------------------
        */

        $form->tools(function (Form\Tools $tools) {
            // Keep all tools enabled
        });

        return $form;
    }
}
