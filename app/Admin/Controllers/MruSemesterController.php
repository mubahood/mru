<?php

namespace App\Admin\Controllers;

use App\Models\MruSemester;
use App\Models\AcademicYear;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

/**
 * MruSemesterController
 * 
 * Laravel Admin controller for managing academic semesters.
 * 
 * @package App\Admin\Controllers
 */
class MruSemesterController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MRU Semesters';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruSemester());

        // Configure grid
        $grid->model()
            ->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'desc');

        // Quick search
        $grid->quickSearch('name', 'term_name', 'details')
            ->placeholder('Search by semester name or details');

        // Define columns
        $grid->column('id', 'ID')->sortable();
        $grid->column('name', 'Semester')->sortable()->display(function ($name) {
            return "Semester {$name}";
        });
        $grid->column('academic_year.name', 'Academic Year')->sortable();
        $grid->column('starts', 'Start Date')->sortable()->display(function () {
            return $this->starts ? $this->starts->format('M d, Y') : 'N/A';
        });
        $grid->column('ends', 'End Date')->sortable()->display(function () {
            return $this->ends ? $this->ends->format('M d, Y') : 'N/A';
        });
        $grid->column('is_active', 'Is Current')->sortable()
            ->display(function ($isActive) {
                return $isActive == 1 ? 
                    "<span class='label label-success'>Yes</span>" : 
                    "<span class='label label-default'>No</span>";
            });
        $grid->column('details', 'Details')->limit(50);
        $grid->column('created_at', 'Created')->sortable()->display(function () {
            return $this->created_at ? $this->created_at->format('Y-m-d H:i') : 'N/A';
        })->hide();

        // Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            
            $filter->like('name', 'Semester Name');
            $filter->equal('academic_year_id', 'Academic Year')
                ->select(AcademicYear::where('enterprise_id', Admin::user()->enterprise_id)
                    ->orderBy('name', 'desc')
                    ->pluck('name', 'id'));
            $filter->equal('is_active', 'Is Current')->select([
                1 => 'Yes',
                0 => 'No',
            ]);
            $filter->between('starts', 'Start Date')->date();
            $filter->between('ends', 'End Date')->date();
        });

        // Configure actions
        $grid->actions(function ($actions) {
            // Keep all actions enabled
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
        $show = new Show(MruSemester::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('name', 'Semester')->as(function ($name) {
            return "Semester {$name}";
        });
        $show->field('academic_year.name', 'Academic Year');
        $show->field('starts', 'Start Date')->as(function ($date) {
            return $date ? $date->format('M d, Y') : 'N/A';
        });
        $show->field('ends', 'End Date')->as(function ($date) {
            return $date ? $date->format('M d, Y') : 'N/A';
        });
        $show->field('is_active', 'Is Current')->as(function ($isActive) {
            return $isActive == 1 ? 'Yes' : 'No';
        });
        $show->field('details', 'Details');
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruSemester());

        // Hidden enterprise_id
        $form->hidden('enterprise_id')->default(Admin::user()->enterprise_id);

        // Academic Year
        $form->select('academic_year_id', 'Academic Year')
            ->options(AcademicYear::where('enterprise_id', Admin::user()->enterprise_id)
                ->orderBy('name', 'desc')
                ->pluck('name', 'id'))
            ->rules('required');

        // Semester Name
        $form->text('name', 'Semester Number')
            ->rules('required')
            ->placeholder('e.g., 1, 2, 3')
            ->help('Enter semester number (1, 2, 3, etc.)');

        // Start Date
        $form->date('starts', 'Start Date')
            ->rules('required')
            ->format('YYYY-MM-DD');

        // End Date
        $form->date('ends', 'End Date')
            ->rules('required')
            ->format('YYYY-MM-DD');

        // Is Current (Active)
        $form->radio('is_active', 'Is Current Semester')
            ->options([
                1 => 'Yes',
                0 => 'No',
            ])
            ->default(0)
            ->help('Set to Yes to make this the current active semester');

        // Details
        $form->textarea('details', 'Details')
            ->rows(3)
            ->placeholder('Optional additional details about this semester');

        // Form callbacks
        $form->saving(function (Form $form) {
            // Auto-set term_name from name
            $form->term_name = $form->name;

            // If setting this semester as current, deactivate all others
            if ($form->is_active == 1) {
                MruSemester::where('enterprise_id', $form->enterprise_id)
                    ->where('id', '!=', $form->model()->id ?? 0)
                    ->update(['is_active' => 0]);
            }
        });

        return $form;
    }
}
