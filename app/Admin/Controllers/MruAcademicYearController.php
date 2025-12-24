<?php

namespace App\Admin\Controllers;

use App\Models\MruAcademicYear;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * MruAcademicYearController
 * 
 * Laravel Admin controller for managing academic years in the MRU system.
 * Handles CRUD operations for academic year records.
 * 
 * @package App\Admin\Controllers
 */
class MruAcademicYearController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Academic Years';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruAcademicYear());

        // Eager load relationship counts
        $grid->model()->withCount(['results', 'registrations', 'courseworkSettings'])
            ->orderBy('acadyear', 'desc');

        /*
        |----------------------------------------------------------------------
        | QUICK SEARCH
        |----------------------------------------------------------------------
        */

        $grid->quickSearch('acadyear')->placeholder('Search year...');

        /*
        |----------------------------------------------------------------------
        | GRID COLUMNS
        |----------------------------------------------------------------------
        */

        // ID
        $grid->column('ID', __('ID'))
            ->sortable();

        // Academic Year
        $grid->column('acadyear', __('Academic Year'))->sortable();

        // Start Year
        $grid->column('start_year', __('Start Year'))
            ->display(function () {
                return $this->start_year;
            })
            ->sortable();

        // End Year
        $grid->column('end_year', __('End Year'))
            ->display(function () {
                return $this->end_year;
            })
            ->sortable();

        // Results Count
        $grid->column('results_count', __('Results'))
            ->display(function ($count) {
                if ($count > 0) {
                    return '<span class="label label-success">' . number_format($count) . '</span>';
                }
                return '<span class="label label-default">0</span>';
            })->sortable();

        // Registrations Count
        $grid->column('registrations_count', __('Registrations'))
            ->display(function ($count) {
                if ($count > 0) {
                    return '<span class="label label-primary">' . number_format($count) . '</span>';
                }
                return '<span class="label label-default">0</span>';
            })->sortable();

        // Coursework Settings Count
        $grid->column('coursework_settings_count', __('Coursework'))
            ->display(function ($count) {
                if ($count > 0) {
                    return '<span class="label label-info">' . number_format($count) . '</span>';
                }
                return '<span class="label label-default">0</span>';
            })->sortable();

        // Registrations Count
        $grid->column('registrations_count', __('Registrations'))
            ->display(function () {
                $count = $this->getRegistrationsCount();
                if ($count > 0) {
                    return '<span class="label label-success">' . number_format($count) . '</span>';
                }
                return '<span class="text-muted">-</span>';
            });

        // Students Count
        $grid->column('students_count', __('Students'))
            ->display(function () {
                $count = $this->getStudentsCount();
                if ($count > 0) {
                    return '<span class="label label-warning">' . number_format($count) . '</span>';
                }
                return '<span class="text-muted">-</span>';
            });

        // Status
        $grid->column('status', __('Status'))
            ->display(function () {
                if ($this->isActive()) {
                    return '<span class="label label-success">Active</span>';
                }
                return '<span class="label label-default">Inactive</span>';
            });

        /*
        |----------------------------------------------------------------------
        | GRID FILTERS
        |----------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            // Remove default ID filter
            $filter->disableIdFilter();

            // Academic year search
            $filter->like('acadyear', __('Academic Year'));

            // Status filter
            $filter->where(function ($query) {
                if ($this->input === 'current') {
                    $query->where('acadyear', MruAcademicYear::getCurrentAcademicYear());
                } elseif ($this->input === 'future') {
                    $query->where('acadyear', '>', MruAcademicYear::getCurrentAcademicYear());
                } elseif ($this->input === 'past') {
                    $query->where('acadyear', '<', MruAcademicYear::getCurrentAcademicYear());
                }
            }, 'Status')->select([
                'current' => 'Current',
                'future' => 'Future',
                'past' => 'Past',
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
            $export->filename('Academic_Years_' . date('Y-m-d_His'));
            
            $export->column('ID', 'ID');
            $export->column('acadyear', 'Academic Year');
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
        $show = new Show(MruAcademicYear::findOrFail($id));

        /*
        |----------------------------------------------------------------------
        | BASIC INFORMATION
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Academic Year Information')
            ->style('primary');

        $show->field('ID', __('ID'));
        $show->field('acadyear', __('Academic Year'));
        $show->field('start_year', __('Start Year'));
        $show->field('end_year', __('End Year'));
        $show->field('label', __('Label'));

        $show->divider();

        /*
        |----------------------------------------------------------------------
        | STATUS
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Status')
            ->style('info');

        $show->field('is_current', __('Is Current'))->as(function ($value) {
            return $value ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>';
        })->unescape();

        $show->field('is_future', __('Is Future'))->as(function ($value) {
            return $value ? '<span class="label label-info">Yes</span>' : '<span class="label label-default">No</span>';
        })->unescape();

        $show->field('is_past', __('Is Past'))->as(function ($value) {
            return $value ? '<span class="label label-warning">Yes</span>' : '<span class="label label-default">No</span>';
        })->unescape();

        $show->divider();

        /*
        |----------------------------------------------------------------------
        | STATISTICS
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Statistics')
            ->style('success');

        $show->field('results_count', __('Total Results'))->as(function () {
            return number_format($this->getResultsCount());
        });

        $show->field('registrations_count', __('Total Registrations'))->as(function () {
            return number_format($this->getRegistrationsCount());
        });

        $show->field('students_count', __('Unique Students'))->as(function () {
            return number_format($this->getStudentsCount());
        });

        $show->field('is_active', __('Has Data'))->as(function () {
            return $this->isActive() ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>';
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
        $form = new Form(new MruAcademicYear());

        /*
        |----------------------------------------------------------------------
        | FORM FIELDS
        |----------------------------------------------------------------------
        */

        $form->text('acadyear', __('Academic Year'))
            ->rules('required|regex:/^\d{4}\/\d{4}$/|unique:acad_acadyears,acadyear,' . request()->route('academic_year'))
            ->placeholder('e.g., 2024/2025')
            ->help('Format: YYYY/YYYY (e.g., 2024/2025)')
            ->required();

        /*
        |----------------------------------------------------------------------
        | FORM CALLBACKS
        |----------------------------------------------------------------------
        */

        // Before saving
        $form->saving(function (Form $form) {
            // Validate format
            if (!MruAcademicYear::isValidFormat($form->acadyear)) {
                admin_error('Error', 'Invalid academic year format. Use YYYY/YYYY format (e.g., 2024/2025)');
                return back()->withInput();
            }

            // Validate year sequence
            $parts = explode('/', $form->acadyear);
            if (count($parts) === 2) {
                $startYear = (int) $parts[0];
                $endYear = (int) $parts[1];
                
                if ($endYear !== $startYear + 1) {
                    admin_error('Error', 'End year must be exactly one year after start year');
                    return back()->withInput();
                }
            }
        });

        // After saving
        $form->saved(function (Form $form) {
            \Log::info('Academic year saved', [
                'year' => $form->model()->acadyear,
                'id' => $form->model()->ID,
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
