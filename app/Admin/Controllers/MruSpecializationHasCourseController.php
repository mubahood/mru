<?php

namespace App\Admin\Controllers;

use App\Models\MruSpecializationHasCourse;
use App\Models\MruSpecialisation;
use App\Models\MruCourse;
use App\Models\MruProgramme;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;

/**
 * MruSpecializationHasCourseController
 * 
 * Laravel Admin controller for managing specialization course assignments in the MRU system.
 * Handles linking courses to specializations with year, semester, lecturer, and approval workflow.
 * 
 * Features:
 * - Grid view with specialization, course, year/semester, lecturer, approval status
 * - Filtering by specialization, programme, year, semester, type, status
 * - Simple form with automatic programme/faculty filling based on specialization
 * - Validation to prevent duplicate assignments
 * - Approval workflow actions
 * 
 * @package App\Admin\Controllers
 * @author MRU Development Team
 * @version 1.0.0
 * @created 2025-12-29
 */
class MruSpecializationHasCourseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Specialization Courses';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruSpecializationHasCourse());

        // Eager load relationships
        $grid->model()->with(['specialization', 'course', 'programme', 'lecturer'])
            ->orderBy('prog_id', 'asc')
            ->orderBy('year', 'asc')
            ->orderBy('semester', 'asc');

        /*
        |--------------------------------------------------------------------------
        | Grid Columns
        |--------------------------------------------------------------------------
        */

        $grid->column('id', __('ID'))->sortable();

        $grid->column('specialization.spec', __('Specialization'))
            ->display(function () {
                return $this->specialization ? $this->specialization->spec : $this->specialization_id;
            });

        $grid->column('course.coursename', __('Course'))
            ->display(function () {
                return $this->course ? $this->course->coursename : $this->course_code;
            });

        $grid->column('prog_id', __('Programme'))->sortable();

        $grid->column('year', __('Year'))->sortable()
            ->display(function ($year) {
                return "Y{$year}";
            });

        $grid->column('semester', __('Semester'))->sortable()
            ->display(function ($semester) {
                return "S{$semester}";
            });

        $grid->column('credits', __('Credits'))->sortable();

        $grid->column('type', __('Type'))->sortable()
            ->display(function ($type) {
                $color = $type === 'mandatory' ? 'primary' : 'info';
                return "<span class='label label-{$color}'>" . ucfirst($type) . "</span>";
            });

        $grid->column('lecturer.name', __('Lecturer'))
            ->display(function () {
                return $this->lecturer ? $this->lecturer->name : '-';
            });

        $grid->column('approval_status', __('Approval'))->sortable()
            ->display(function ($status) {
                $colors = [
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger'
                ];
                $color = $colors[$status] ?? 'default';
                return "<span class='label label-{$color}'>" . ucfirst($status) . "</span>";
            });

        $grid->column('status', __('Status'))->sortable()
            ->display(function ($status) {
                $color = $status === 'active' ? 'success' : 'default';
                return "<span class='label label-{$color}'>" . ucfirst($status) . "</span>";
            });

        /*
        |--------------------------------------------------------------------------
        | Grid Actions
        |--------------------------------------------------------------------------
        */

        $grid->actions(function ($actions) {
            // Add approve/reject actions for pending items
            if ($actions->row->approval_status === 'pending') {
                $actions->append('<a href="' . admin_url('mru-specialization-courses/' . $actions->getKey() . '/approve') . '" class="btn btn-xs btn-success">Approve</a>');
                $actions->append('<a href="' . admin_url('mru-specialization-courses/' . $actions->getKey() . '/reject') . '" class="btn btn-xs btn-danger">Reject</a>');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Grid Filters
        |--------------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            // Specialization filter
            $filter->equal('specialization_id', 'Specialization')
                ->select(MruSpecialisation::orderBy('spec')
                    ->pluck('spec', 'spec_id'));

            // Programme filter
            $filter->equal('prog_id', 'Programme')
                ->select(MruProgramme::orderBy('progname')
                    ->pluck('progname', 'progcode'));

            // Year filter
            $filter->equal('year', 'Year')->select([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4'
            ]);

            // Semester filter
            $filter->equal('semester', 'Semester')->select([
                1 => 'Semester 1',
                2 => 'Semester 2'
            ]);

            // Type filter
            $filter->equal('type', 'Type')->select([
                'mandatory' => 'Mandatory',
                'elective' => 'Elective'
            ]);

            // Approval status filter
            $filter->equal('approval_status', 'Approval Status')->select([
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected'
            ]);

            // Status filter
            $filter->equal('status', 'Status')->select([
                'active' => 'Active',
                'inactive' => 'Inactive'
            ]);
        });

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
        $show = new Show(MruSpecializationHasCourse::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('specialization.spec', __('Specialization'));
        $show->field('course.coursename', __('Course'));
        $show->field('prog_id', __('Programme'));
        $show->field('faculty_code', __('Faculty'));
        $show->field('year', __('Year'));
        $show->field('semester', __('Semester'));
        $show->field('credits', __('Credits'));
        $show->field('type', __('Type'));
        $show->field('lecturer.name', __('Lecturer'));
        $show->field('status', __('Status'));
        $show->field('approval_status', __('Approval Status'));
        $show->field('rejection_reason', __('Rejection Reason'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruSpecializationHasCourse());

        /*
        |--------------------------------------------------------------------------
        | Form Fields
        |--------------------------------------------------------------------------
        */

        $form->select('specialization_id', __('Specialization'))
            ->options(MruSpecialisation::with('programme')
                ->orderBy('spec')
                ->get()
                ->mapWithKeys(function ($spec) {
                    return [$spec->spec_id => "{$spec->spec} ({$spec->prog_id})"];
                }))
            ->rules('required|integer')
            ->required();

        $form->select('course_code', __('Course'))
            ->options(MruCourse::orderBy('courseID')
                ->pluck('courseName', 'courseID'))
            ->rules('required|string|max:15')
            ->required();

        $form->select('year', __('Year'))
            ->options([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4'
            ])
            ->rules('required|integer|between:1,4')
            ->default(1)
            ->required();

        $form->select('semester', __('Semester'))
            ->options([
                1 => 'Semester 1',
                2 => 'Semester 2'
            ])
            ->rules('required|integer|between:1,2')
            ->default(1)
            ->required();

        $form->decimal('credits', __('Credits'))
            ->rules('required|numeric|min:0|max:999')
            ->default(3)
            ->required();

        $form->select('type', __('Type'))
            ->options([
                'mandatory' => 'Mandatory',
                'elective' => 'Elective'
            ])
            ->rules('required|in:mandatory,elective')
            ->default('mandatory')
            ->required();

        $form->select('lecturer_id', __('Lecturer'))
            ->options(User::where('user_type', 'employee')
                ->orderBy('name')
                ->pluck('name', 'id'))
            ->rules('nullable|integer');

        $form->select('status', __('Status'))
            ->options([
                'active' => 'Active',
                'inactive' => 'Inactive'
            ])
            ->rules('required|in:active,inactive')
            ->default('active')
            ->required();

        $form->select('approval_status', __('Approval Status'))
            ->options([
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected'
            ])
            ->rules('required|in:pending,approved,rejected')
            ->default('pending')
            ->required();

        $form->textarea('rejection_reason', __('Rejection Reason'))
            ->rows(3)
            ->rules('nullable|string|max:1000');

        // Auto-fill prog_id and faculty_code from specialization
        $form->saving(function (Form $form) {
            if ($form->specialization_id) {
                $specialization = MruSpecialisation::with('programme.faculty')->find($form->specialization_id);
                if ($specialization) {
                    $form->prog_id = $specialization->prog_id;
                    if ($specialization->programme && $specialization->programme->faculty_code) {
                        $form->faculty_code = $specialization->programme->faculty_code;
                    }
                }
            }
        });

        return $form;
    }

    /**
     * API endpoint to get courses by specialization
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoursesBySpecialization(Request $request)
    {
        $q = $request->get('q');
        $courses = MruCourse::query()
            ->where('coursename', 'like', "%$q%")
            ->orWhere('coursecode', 'like', "%$q%")
            ->limit(20)
            ->get(['coursecode', 'coursename'])
            ->map(function ($course) {
                return [
                    'id' => $course->coursecode,
                    'text' => "{$course->coursecode} - {$course->coursename}"
                ];
            });

        return response()->json($courses);
    }
}
