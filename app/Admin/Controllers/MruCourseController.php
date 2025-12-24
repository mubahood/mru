<?php

namespace App\Admin\Controllers;

use App\Models\MruCourse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * MruCourseController
 * 
 * Laravel Admin controller for managing academic courses in the MRU system.
 * Handles CRUD operations, filtering, and statistics display for courses.
 * 
 * @package App\Admin\Controllers
 */
class MruCourseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MRU Courses';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruCourse());

        // Eager load relationships and add statistics
        $grid->model()->withCount(['results', 'registrations'])
            ->orderBy('courseID', 'asc');

        /*
        |----------------------------------------------------------------------
        | GRID COLUMNS
        |----------------------------------------------------------------------
        */

        // Course Code
        $grid->column('courseID', __('Course Code'))
            ->sortable();

        // Course Name
        $grid->column('courseName', __('Course Name'))
            ->sortable();

        // Credit Units
        $grid->column('CreditUnit', __('Credits'))
            ->sortable()
            ->display(function ($credits) {
                return $credits ? number_format($credits, 1) : '-';
            });

        // Lecture Hours
        $grid->column('LectureHr', __('Lecture Hrs'))
            ->sortable()
            ->display(function ($hours) {
                return $hours ? number_format($hours, 1) : '-';
            });

        // Practical Hours
        $grid->column('PracticalHr', __('Practical Hrs'))
            ->sortable()
            ->display(function ($hours) {
                return $hours ? number_format($hours, 1) : '-';
            });

        // Core Status
        $grid->column('CoreStatus', __('Type'))->sortable();

        // Status
        $grid->column('stat', __('Status'))->sortable();

        // Enrollments
        $grid->column('registrations_count', __('Enrollments'))
            ->display(function ($count) {
                return "<span class='label label-primary'>" . number_format($count) . "</span>";
            })->sortable();

        // Results
        $grid->column('results_count', __('Results'))
            ->display(function ($count) {
                return "<span class='label label-success'>" . number_format($count) . "</span>";
            })->sortable();

        /*
        |----------------------------------------------------------------------
        | GRID FILTERS
        |----------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            // Remove default ID filter
            $filter->disableIdFilter();

            // Course Code filter
            $filter->like('courseID', __('Course Code'));

            // Course Name filter
            $filter->like('courseName', __('Course Name'));

            // Status filter
            $filter->equal('stat', __('Status'))->select([
                MruCourse::STATUS_ACTIVE => 'Active',
                MruCourse::STATUS_INACTIVE => 'Inactive',
            ]);

            // Core Status filter
            $filter->equal('CoreStatus', __('Type'))->select([
                MruCourse::CORE_STATUS_CORE => 'Core',
                MruCourse::CORE_STATUS_OPTIONAL => 'Optional',
            ]);
        });

        /*
        |----------------------------------------------------------------------
        | GRID ACTIONS
        |----------------------------------------------------------------------
        */

        $grid->actions(function ($actions) {
            // Keep all actions enabled by default
        });

        /*
        |----------------------------------------------------------------------
        | GRID EXPORT
        |----------------------------------------------------------------------
        */

        $grid->export(function ($export) {
            $export->filename('MRU_Courses_' . date('Y-m-d_His'));
            
            $export->column('courseID', 'Course Code');
            $export->column('courseName', 'Course Name');
            $export->column('CreditUnit', 'Credit Units');
            $export->column('LectureHr', 'Lecture Hours');
            $export->column('PracticalHr', 'Practical Hours');
            $export->column('ContactHr', 'Contact Hours');
            $export->column('CoreStatus', 'Type');
            $export->column('stat', 'Status');
        });

        /*
        |----------------------------------------------------------------------
        | GRID SETTINGS
        |----------------------------------------------------------------------
        */

        // Enable batch actions
        $grid->batchActions(function ($batch) {
            // Add custom batch actions if needed
        });

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
        $show = new Show(MruCourse::findOrFail($id));

        /*
        |----------------------------------------------------------------------
        | BASIC INFORMATION PANEL
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Basic Information')
            ->style('primary');

        $show->field('courseID', __('Course Code'));
        $show->field('courseName', __('Course Name'));
        $show->field('CreditUnit', __('Credit Units'))->as(function ($value) {
            return $value ? number_format($value, 1) : 'Not Set';
        });

        $show->divider();

        /*
        |----------------------------------------------------------------------
        | HOURS DETAILS PANEL
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Hours Breakdown')
            ->style('info');

        $show->field('LectureHr', __('Lecture Hours'))->as(function ($value) {
            return $value ? number_format($value, 1) : 'Not Set';
        });

        $show->field('PracticalHr', __('Practical Hours'))->as(function ($value) {
            return $value ? number_format($value, 1) : 'Not Set';
        });

        $show->field('ContactHr', __('Contact Hours'))->as(function ($value) {
            return $value ? number_format($value, 1) : 'Not Set';
        });

        $show->field('total_hours', __('Total Hours'))->as(function () {
            return number_format($this->total_hours, 1);
        });

        $show->divider();

        /*
        |----------------------------------------------------------------------
        | CLASSIFICATION PANEL
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Classification')
            ->style('success');

        $show->field('CoreStatus', __('Course Type'))->as(function ($value) {
            return $this->core_status_label;
        })->badge();

        $show->field('stat', __('Status'))->as(function ($value) {
            return $this->status_label;
        })->badge();

        $show->divider();

        /*
        |----------------------------------------------------------------------
        | DESCRIPTION PANEL
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Description')
            ->style('warning');

        $show->field('courseDescription', __('Course Description'))->as(function ($value) {
            return $value ?: 'No description available';
        });

        $show->divider();

        /*
        |----------------------------------------------------------------------
        | STATISTICS PANEL
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Statistics')
            ->style('danger');

        $show->field('student_count', __('Total Students'))->as(function () {
            return number_format($this->getStudentCount());
        });

        $show->field('result_count', __('Total Results'))->as(function () {
            return number_format($this->getResultCount());
        });

        $show->field('pass_rate', __('Pass Rate'))->as(function () {
            return number_format($this->getPassRate(), 2) . '%';
        });

        // Disable actions if needed
        $show->panel()
            ->tools(function ($tools) {
                // $tools->disableDelete();
            });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruCourse());

        /*
        |----------------------------------------------------------------------
        | TAB 1: BASIC INFORMATION
        |----------------------------------------------------------------------
        */

        $form->text('courseID', __('Course Code'))
            ->rules('required|max:25|unique:acad_course,courseID,' . request()->route('course'))
            ->required();

        $form->text('courseName', __('Course Name'))
            ->rules('required|max:250')
            ->required();

        $form->decimal('CreditUnit', __('Credit Units'))
            ->rules('nullable|numeric|min:0|max:450')
            ->default(0);

        $form->decimal('LectureHr', __('Lecture Hours'))
            ->rules('nullable|numeric|min:0')
            ->default(0);

        $form->decimal('PracticalHr', __('Practical Hours'))
            ->rules('nullable|numeric|min:0')
            ->default(0);

        $form->decimal('ContactHr', __('Contact Hours'))
            ->rules('nullable|numeric|min:0')
            ->default(0);

        $form->select('stat', __('Status'))
            ->options([
                MruCourse::STATUS_ACTIVE => 'Active',
                MruCourse::STATUS_INACTIVE => 'Inactive',
            ])
            ->rules('nullable|in:' . implode(',', MruCourse::STATUSES))
            ->default(MruCourse::STATUS_ACTIVE);

        $form->select('CoreStatus', __('Course Type'))
            ->options([
                MruCourse::CORE_STATUS_CORE => 'Core',
                MruCourse::CORE_STATUS_OPTIONAL => 'Optional',
            ])
            ->rules('nullable|in:' . implode(',', MruCourse::CORE_STATUSES));

        $form->textarea('courseDescription', __('Course Description'))
            ->rules('nullable')
            ->rows(5);

        // Before saving
        $form->saving(function (Form $form) {
            // Normalize course code
            if ($form->courseID) {
                $form->courseID = strtoupper(trim($form->courseID));
            }

            // Validate credit units
            if ($form->CreditUnit < 0) {
                admin_error('Error', 'Credit units cannot be negative');
                return back()->withInput();
            }
        });

        // After saving
        $form->saved(function (Form $form) {
            // Log the action
            \Log::info('Course saved', [
                'course_id' => $form->model()->courseID,
                'course_name' => $form->model()->courseName,
                'user' => \Admin::user()->username ?? 'unknown',
            ]);
        });

        /*
        |----------------------------------------------------------------------
        | FORM SETTINGS
        |----------------------------------------------------------------------
        */

        // Configure form tools
        $form->tools(function (Form\Tools $tools) {
            // $tools->disableDelete();
        });

        return $form;
    }
}