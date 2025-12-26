<?php

namespace App\Admin\Controllers;

use App\Models\MruProgramme;
use App\Models\MruFaculty;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * MruProgrammeConfigurationController
 * 
 * Controller for configuring programme semester structures.
 * Allows editing programme semester configurations inline.
 * 
 * Purpose:
 * - Configure total semesters for each programme
 * - Set number of courses per semester (semester 1-12)
 * - Support quick inline editing for efficient configuration
 * - Manage programme structure planning
 * 
 * @package App\Admin\Controllers
 * @author MRU Development Team
 * @version 1.0.0
 */
class MruProgrammeConfigurationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Programme Configurations';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruProgramme());

        // Load relationships and order by programme code
        $grid->model()
            ->with(['faculty'])
            ->where('progcode', '!=', 'PLACEHOLDER')
            ->where('progcode', '!=', 'ALL')
            ->orderBy('progcode');

        // Disable batch actions
        $grid->disableBatchActions();
        
        // Enable row actions
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });

        // Add quick search
        $grid->quickSearch('progcode', 'abbrev', 'progname')->placeholder('Search by code, abbreviation or name...');

        // Add tools for auto-filling
  /*       $grid->tools(function ($tools) {
            $tools->append('
                <div class="btn-group pull-right" style="margin-right: 5px">
                    <a href="'.url('admin/mru-programmes-configurations/auto-fill').'" class="btn btn-sm btn-success">
                        <i class="fa fa-magic"></i> Auto-Fill Semester Config
                    </a>
                </div>
            ');
        }); */

        
        /*
        |--------------------------------------------------------------------------
        | Grid Columns with Inline Editing
        |--------------------------------------------------------------------------
        */

        $grid->column('progcode', 'Code')
            ->sortable();

        $grid->column('abbrev', 'Abbr')
            ->editable()
            ->sortable();

        $grid->column('progname', 'Programme Name')
            ->editable()
            ->display(function ($progname) {
                return $progname ?: '-';
            });

        $grid->column('faculty.abbrev', 'Faculty')
            ->display(function () {
                return $this->faculty ? $this->faculty->abbrev : '-';
            });

        $grid->column('total_semesters', 'Semesters')
            ->editable('select', [
                0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6',
                7 => '7', 8 => '8', 9 => '9', 10 => '10', 11 => '11', 12 => '12'
            ])
            ->display(function ($value) {
                return $value ?? 0;
            })
            ->sortable();

        // Semester course count columns (inline editable)
        $grid->column('number_of_semester_1_courses', 'S1')
            ->editable()
            ->display(function ($value) {
                return $value ?? '-';
            });

        $grid->column('number_of_semester_2_courses', 'S2')
            ->editable()
            ->display(function ($value) {
                return $value ?? '-';
            });

        $grid->column('number_of_semester_3_courses', 'S3')
            ->editable()
            ->display(function ($value) {
                return $value ?? '-';
            });

        $grid->column('number_of_semester_4_courses', 'S4')
            ->editable()
            ->display(function ($value) {
                return $value ?? '-';
            });

        $grid->column('number_of_semester_5_courses', 'S5')
            ->editable()
            ->display(function ($value) {
                return $value ?? '-';
            });

        $grid->column('number_of_semester_6_courses', 'S6')
            ->editable()
            ->display(function ($value) {
                return $value ?? '-';
            });

        $grid->column('number_of_semester_7_courses', 'S7')
            ->editable()
            ->display(function ($value) {
                return $value ?? '-';
            });

        $grid->column('number_of_semester_8_courses', 'S8')
            ->editable()
            ->display(function ($value) {
                return $value ?? '-';
            });

        // Verification and Processing Status
        $grid->column('is_verified', 'Verified')
            ->editable('select', ['Yes' => 'Yes', 'No' => 'No'])
            ->sortable();

        $grid->column('is_processed', 'Processed')
            ->sortable();

        $grid->column('process_passed', 'Pass')
            ->sortable();
        
        $grid->column('error_mess', 'Error')
            ->display(function ($value) {
                if ($value) {
                    return '<span style="color: #e74c3c; font-size: 11px;">' . htmlspecialchars(substr($value, 0, 100)) . '</span>';
                }
                return '-';
            })
            ->sortable();
        
        // Actions column with Process Enrollments button
        $grid->column('actions', __('Actions'))
            ->display(function () {
                $url = url('fix-student-semester-enrollment?programme_id=' . urlencode($this->progcode));
                return "<a href='{$url}' target='_blank' class='btn btn-sm btn-primary' style='margin: 2px;'>
                    <i class='fa fa-users'></i> Process Enrollments
                </a>";
            });

        /*
        |--------------------------------------------------------------------------
        | Grid Filters
        |--------------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            
            // Basic search
            $filter->like('progcode', 'Code');
            $filter->like('progname', 'Programme Name');
            $filter->like('abbrev', 'Abbreviation');
            
            // Faculty filter
            $filter->equal('faculty_code', 'Faculty')
                ->select(MruFaculty::active()->pluck('abbrev', 'faculty_code'));
            
            // Level filter
            $filter->equal('levelCode', 'Level')->select([
                1 => 'Certificate',
                2 => 'Diploma',
                3 => 'Degree',
                4 => 'Masters',
                5 => 'PhD',
            ]);
            
            // Semester configuration
            $filter->equal('total_semesters', 'Total Semesters')->select([
                0 => '0', 2 => '2', 4 => '4', 6 => '6', 8 => '8', 10 => '10', 12 => '12'
            ]);
            
            // Verification status
            $filter->equal('is_verified', 'Verified')
                ->select(['Yes' => 'Yes', 'No' => 'No']);
            
            // Processing status
            $filter->equal('is_processed', 'Processed')
                ->select(['Yes' => 'Yes', 'No' => 'No']);
            
            // Processing result
            $filter->equal('process_passed', 'Passed')
                ->select(['Yes' => 'Yes', 'No' => 'No']);
            
            // Special filters
            $filter->where(function ($query) {
                $query->whereNotNull('error_mess')->where('error_mess', '!=', '');
            }, 'Has Errors')->checkbox('1');
            
            $filter->where(function ($query) {
                $query->where('total_semesters', '>', 0);
            }, 'Configured')->checkbox('1');
            
            $filter->where(function ($query) {
                $query->where('is_verified', 'Yes')
                      ->where('is_processed', 'No');
            }, 'Ready to Process')->checkbox('1');
            
            $filter->where(function ($query) {
                $query->where('is_processed', 'Yes')
                      ->where('process_passed', 'No');
            }, 'Failed Processing')->checkbox('1');
        });

        // Disable features
        $grid->disableExport();
        $grid->disableCreateButton();

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
        $show = new Show(MruProgramme::findOrFail($id));

        $show->field('progcode', __('Programme Code'));
        $show->field('progname', __('Programme Name'));
        $show->field('abbrev', __('Abbreviation'));
        $show->field('faculty.full_display_name', __('Faculty'));
        $show->field('total_semesters', __('Total Semesters'));
        
        $show->divider();
        
        for ($i = 1; $i <= 12; $i++) {
            $field = "number_of_semester_{$i}_courses";
            $show->field($field, __("Semester {$i} Courses"));
        }

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruProgramme());

        // Disable unnecessary features
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();

        /*
        |--------------------------------------------------------------------------
        | Form Fields - Simple and Straightforward
        |--------------------------------------------------------------------------
        */

        $form->html('<div class="alert alert-info">
            <h4><i class="icon fa fa-calendar"></i> Programme Semester Configuration</h4>
            <p>Configure the semester structure for this programme.</p>
            <ul>
                <li><strong>Total Semesters:</strong> Number of semesters in the programme (e.g., 6 for 3-year degree)</li>
                <li><strong>Courses per Semester:</strong> How many courses in each semester</li>
            </ul>
        </div>');

        $form->text('progcode', __('Programme Code'))
            ->rules('required')
            ->readonly(function ($form) {
                return $form->isEditing();
            })
            ->required();

        $form->text('progname', __('Programme Name'))
            ->rules('required')
            ->required();

        $form->text('abbrev', __('Abbreviation'))
            ->rules('required')
            ->required();

        $form->select('faculty_code', __('Faculty'))
            ->options(\App\Models\MruFaculty::active()->pluck('abbrev', 'faculty_code'))
            ->rules('required')
            ->required();

        $form->divider('Semester Structure');

        $form->number('total_semesters', __('Total Semesters'))
            ->rules('required|integer|min:0|max:12')
            ->default(0)
            ->required();

        $form->divider('Courses Per Semester');

        for ($i = 1; $i <= 12; $i++) {
            $field = "number_of_semester_{$i}_courses";
            $form->number($field, __("Semester {$i} Courses"))
                ->rules('nullable|integer|min:0|max:20');
        }

        $form->divider('Verification & Processing Status');

        $form->select('is_verified', __('Is Verified'))
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->default('No')
            ->help('Has this programme been verified?');

        $form->select('is_processed', __('Is Processed'))
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->default('No')
            ->help('Has this programme been processed?');

        $form->select('process_passed', __('Process Passed'))
            ->options(['Yes' => 'Yes', 'No' => 'No'])
            ->default('No')
            ->help('Did the processing pass successfully?');

        $form->textarea('error_mess', __('Error Message'))
            ->rows(3)
            ->help('Any error messages or notes during processing');

        return $form;
    }

    /**
     * Auto-fill semester configuration for all programmes
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function autoFill()
    {
        try {
            \Artisan::call('mru:autofill-programme-semesters', ['--force' => true]);
            $output = \Artisan::output();
            
            admin_success('Success', 'Semester configurations have been automatically filled based on course data!');
            
            return redirect()->back();
        } catch (\Exception $e) {
            admin_error('Error', 'Failed to auto-fill: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}

