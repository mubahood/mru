<?php

namespace App\Admin\Controllers;

use App\Models\TempMruSpecializationHasCourse;
use App\Models\MruSpecialisation;
use App\Models\MruCourse;
use App\Models\MruProgramme;
use App\Models\MruFaculty;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * TempMruSpecializationHasCourseController
 * 
 * Laravel Admin controller for managing temporary curriculum records.
 * Used during automatic curriculum generation workflow before copying to permanent table.
 * 
 * @package App\Admin\Controllers
 * @author MRU Development Team
 * @version 1.0.0
 * @created 2025-12-29
 */
class TempMruSpecializationHasCourseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Temp Curriculum (Auto-Generated)';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TempMruSpecializationHasCourse());

        /*
        |--------------------------------------------------------------------------
        | Grid Configuration
        |--------------------------------------------------------------------------
        */
        $grid->model()->with(['specialization', 'course', 'programme'])->orderBy('id', 'desc');
        $grid->disableExport();
        $grid->disableColumnSelector();
        
        /*
        |--------------------------------------------------------------------------
        | Batch Actions
        |--------------------------------------------------------------------------
        */
        $grid->batchActions(function ($batch) {
            $batch->add(new \App\Admin\Actions\Batch\CopyToPermanentAction());
        });

        $grid->actions(function ($actions) {
            $actions->add(new \App\Admin\Actions\Row\CopyToPermanentRowAction());
        });

        /*
        |--------------------------------------------------------------------------
        | Grid Filters
        |--------------------------------------------------------------------------
        */
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            
            $filter->equal('specialization_id', 'Specialization')
                ->select(MruSpecialisation::orderBy('prog_id')->orderBy('spec')->get()->mapWithKeys(function ($item) {
                    return [$item->spec_id => $item->spec . ' (' . $item->prog_id . ')'];
                }));
            
            $filter->equal('prog_id', 'Programme')
                ->select(MruProgramme::orderBy('progname')->pluck('progname', 'progcode'));
            
            $filter->equal('year', 'Year')->select([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
            ]);
            
            $filter->equal('semester', 'Semester')->select([
                1 => 'Semester 1',
                2 => 'Semester 2',
            ]);
            
            $filter->equal('type', 'Type')->select([
                'mandatory' => 'Mandatory',
                'elective' => 'Elective',
            ]);
            
            $filter->equal('approval_status', 'Approval Status')->select([
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ]);
            
            $filter->equal('is_created', 'Created Status')->select([
                1 => 'Created',
                0 => 'Not Created',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Grid Columns
        |--------------------------------------------------------------------------
        */
        $grid->column('id', __('ID'))->sortable();
        
        $grid->column('specialization.spec', __('Specialization'))->limit(30);
        
        $grid->column('course_code', __('Course Code'));
        $grid->column('course.courseName', __('Course Name'))->limit(40);
        
        $grid->column('year', __('Year'))->display(function ($year) {
            return 'Year ' . $year;
        });
        
        $grid->column('semester', __('Sem'))->display(function () {
            return 'Sem ' . $this->semester;
        });
        
        $grid->column('credits', __('Credits'))->display(function ($credits) {
            return $credits . ' CU';
        });
        
        $grid->column('type', __('Type'))->display(function ($type) {
            return ucfirst($type);
        });
        
        $grid->column('status', __('Status'))->display(function ($status) {
            return ucfirst($status);
        });
        
        $grid->column('approval_status', __('Approval'))->display(function ($status) {
            return ucfirst($status);
        });        
        $grid->column('is_created', __('Created'))->display(function () {
            if ($this->is_created) {
                return "<span class='label label-success'><i class='fa fa-check'></i> Yes</span>";
            }
            return "<span class='label label-default'><i class='fa fa-times'></i> No</span>";
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
        $show = new Show(TempMruSpecializationHasCourse::findOrFail($id));

        $show->field('id', __('ID'));
        
        $show->field('specialization.spec', __('Specialization'));
        $show->field('course_code', __('Course Code'));
        $show->field('course.courseName', __('Course Name'));
        
        $show->field('prog_id', __('Programme ID'));
        $show->field('programme.progname', __('Programme Name'));
        
        $show->field('faculty_code', __('Faculty Code'));
        $show->field('faculty.faculty', __('Faculty Name'));
        
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
        $form = new Form(new TempMruSpecializationHasCourse());

        /*
        |--------------------------------------------------------------------------
        | Form Fields
        |--------------------------------------------------------------------------
        */

        $form->select('specialization_id', __('Specialization'))
            ->options(MruSpecialisation::with('programme')->get()->pluck('spec', 'spec_id'))
            ->rules('required|integer')
            ->required();

        $form->select('course_code', __('Course'))
            ->options(MruCourse::orderBy('courseName')->get()->pluck('courseName', 'courseID'))
            ->rules('required|string|max:15')
            ->required();

        $form->select('year', __('Year'))
            ->options([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
            ])
            ->rules('required|integer|between:1,4')
            ->required();

        $form->select('semester', __('Semester'))
            ->options([
                1 => 'Semester 1',
                2 => 'Semester 2',
            ])
            ->rules('required|integer|between:1,2')
            ->required();

        $form->decimal('credits', __('Credits'))
            ->default(3.00)
            ->rules('required|numeric|min:0|max:999.99')
            ->required();

        $form->select('type', __('Type'))
            ->options([
                'mandatory' => 'Mandatory',
                'elective' => 'Elective',
            ])
            ->default('mandatory')
            ->rules('required|in:mandatory,elective')
            ->required();

        $form->select('lecturer_id', __('Lecturer'))
            ->options(User::where('status', 'active')->orderBy('name')->pluck('name', 'id'))
            ->rules('nullable|integer');

        $form->select('status', __('Status'))
            ->options([
                'active' => 'Active',
                'inactive' => 'Inactive',
            ])
            ->default('active')
            ->rules('required|in:active,inactive')
            ->required();

        $form->select('approval_status', __('Approval Status'))
            ->options([
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ])
            ->default('pending')
            ->rules('required|in:pending,approved,rejected')
            ->required();

        $form->textarea('rejection_reason', __('Rejection Reason'))
            ->rules('nullable|string');

        return $form;
    }
}
