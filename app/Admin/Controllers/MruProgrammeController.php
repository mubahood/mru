<?php

namespace App\Admin\Controllers;

use App\Models\MruProgramme;
use App\Models\MruFaculty;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

/**
 * MruProgrammeController
 * 
 * Laravel Admin controller for managing academic programmes in the MRU system.
 * 
 * Features:
 * - Grid view with programme information, faculty, level, and student statistics
 * - Advanced filtering by code, name, faculty, level, study system
 * - Form with validation for creating/editing programmes
 * - Detail view with organized sections and relationship data
 * - Export functionality for programme data
 * - Statistics dashboard showing programme overview
 * 
 * @package App\Admin\Controllers
 * @author MRU Development Team
 * @version 1.0.0
 */
class MruProgrammeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MRU Academic Programmes';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruProgramme());

        // Eager load relationships
        $grid->model()->with(['faculty'])
            ->withCount(['students', 'results'])
            ->orderBy('progname', 'asc');

        // Add statistics header
        $grid->header(function () {
            return $this->renderStatistics();
        });

        // Configure batch actions
        $grid->batchActions(function ($batch) {
            $batch->disableDelete(); // Prevent accidental bulk deletion
        });

        /*
        |--------------------------------------------------------------------------
        | Grid Columns
        |--------------------------------------------------------------------------
        */

        $grid->column('progcode', __('Code'))
            ->width(100)
            ->sortable();

        $grid->column('abbrev', __('Abbrev'))->sortable();

        $grid->column('progname', __('Programme Name'))->sortable();

        $grid->column('levelCode', __('Level'))
            ->display(function ($level) {
                $labels = MruProgramme::LEVEL_LABELS;
                return $labels[$level] ?? 'Unknown';
            })
            ->sortable();

        $grid->column('faculty.abbrev', __('Faculty'));

        $grid->column('couselength', __('Duration'))
            ->display(function ($length) {
                if (!$length) return 'N/A';
                $max = $this->maxduration;
                if ($max && $max != $length) {
                    return "{$length}-{$max} yrs";
                }
                return "{$length} " . ($length == 1 ? 'yr' : 'yrs');
            });

        $grid->column('mincredit', __('Credits'));

        $grid->column('study_system', __('System'))->sortable();

        $grid->column('students_count', __('Students'))
            ->display(function ($count) {
                return "<span class='label label-primary'>" . number_format($count) . "</span>";
            })->sortable();

        $grid->column('results_count', __('Results'))
            ->display(function ($count) {
                return "<span class='label label-success'>" . number_format($count) . "</span>";
            })->sortable();

        $grid->column('status', __('Status'))
            ->display(function () {
                return $this->is_placeholder ? 'Placeholder' : 'Active';
            });

        /*
        |--------------------------------------------------------------------------
        | Grid Filters
        |--------------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            // Remove default ID filter
            $filter->disableIdFilter();

            // Programme code filter
            $filter->like('progcode', 'Programme Code');

            // Programme name search
            $filter->like('progname', 'Programme Name');

            // Abbreviation search
            $filter->like('abbrev', 'Abbreviation');

            // Faculty filter
            $filter->equal('faculty_code', 'Faculty')
                ->select(MruFaculty::active()->orderBy('faculty_code')->pluck('abbrev', 'faculty_code'));

            // Level filter
            $filter->equal('levelCode', 'Level')->select([
                1 => 'Certificate',
                2 => 'Diploma',
                3 => 'Degree',
                4 => 'Masters',
                5 => 'PhD',
            ]);

            // Study system filter
            $filter->equal('study_system', 'Study System')->select([
                'Semester' => 'Semester',
                'Session' => 'Session',
            ]);

            // Duration filter
            $filter->between('couselength', 'Duration (years)');

            // Credit filter
            $filter->between('mincredit', 'Credits');

            // Undergraduate filter
            $filter->where(function ($query) {
                $query->whereIn('levelCode', [1, 2, 3]);
            }, 'Undergraduate Only')->checkbox('1');

            // Postgraduate filter
            $filter->where(function ($query) {
                $query->whereIn('levelCode', [4, 5]);
            }, 'Postgraduate Only')->checkbox('1');

            // Active filter (exclude placeholder)
            $filter->where(function ($query) {
                $query->where('progcode', '!=', MruProgramme::PLACEHOLDER_CODE)
                      ->where('progcode', '!=', 'ALL');
            }, 'Active Only')->checkbox('1');
        });

        /*
        |--------------------------------------------------------------------------
        | Grid Export
        |--------------------------------------------------------------------------
        */

        $grid->export(function ($export) {
            $export->filename('MRU_Programmes_' . date('Y-m-d_His'));
            
            $export->column('progcode', 'Programme Code');
            $export->column('progname', 'Programme Name');
            $export->column('abbrev', 'Abbreviation');
            $export->column('levelCode', 'Level');
            $export->column('faculty_code', 'Faculty Code');
            $export->column('couselength', 'Duration (years)');
            $export->column('maxduration', 'Max Duration');
            $export->column('mincredit', 'Credits');
            $export->column('study_system', 'Study System');
            
            // Add computed columns
            $export->column('student_count', function ($model) {
                return $model->getStudentCount();
            });
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
        $show = new Show(MruProgramme::findOrFail($id));

        // Configure panel
        $show->panel()
            ->tools(function ($tools) {
                // Keep all tools enabled
            });

        /*
        |--------------------------------------------------------------------------
        | Basic Information Section
        |--------------------------------------------------------------------------
        */

        $show->panel()
            ->title('Basic Information')
            ->style('primary');

        $show->field('progcode', __('Programme Code'))
            ->as(function ($code) {
                return $code === MruProgramme::PLACEHOLDER_CODE ? 
                    "{$code} (Placeholder)" : $code;
            })
            ->badge();

        $show->field('progname', __('Programme Name'))
            ->setWidth(12);

        $show->field('abbrev', __('Abbreviation'))
            ->badge('info');

        $show->field('level_label', __('Level'))
            ->badge('success');

        $show->divider();

        /*
        |--------------------------------------------------------------------------
        | Academic Details Section
        |--------------------------------------------------------------------------
        */

        $show->panel()
            ->title('Academic Details')
            ->style('info');

        $show->field('faculty.full_display_name', __('Faculty'))
            ->as(function () {
                return $this->faculty ? $this->faculty->full_display_name : 'Not Assigned';
            });

        $show->field('study_system', __('Study System'))
            ->badge();

        $show->field('duration_display', __('Duration'));

        $show->field('credit_display', __('Credit Requirement'));

        $show->divider();

        /*
        |--------------------------------------------------------------------------
        | Statistics Section
        |--------------------------------------------------------------------------
        */

        $show->panel()
            ->title('Programme Statistics')
            ->style('success');

        $show->field('student_count', __('Enrolled Students'))
            ->as(function () {
                return $this->getStudentCount();
            })
            ->badge('success');

        $show->field('is_undergraduate', __('Category'))
            ->as(function () {
                if ($this->is_undergraduate) return 'Undergraduate';
                if ($this->is_postgraduate) return 'Postgraduate';
                return 'Other';
            })
            ->badge();

        $show->field('status', __('Status'))
            ->as(function () {
                return $this->is_active ? 'Active' : 'Inactive';
            })
            ->badge('success');

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

        // Disable view check
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();

        /*
        |--------------------------------------------------------------------------
        | Form Tabs
        |--------------------------------------------------------------------------
        */

        $form->tab('Basic Information', function ($form) {
            
            $form->text('progcode', __('Programme Code'))
                ->rules('required|string|max:25|unique:acad_programme,progcode,' . ($form->model()->progcode ?? 'NULL') . ',progcode')
                ->help('Unique programme code (e.g., BED, BSCS, MBA)')
                ->required();

            $form->text('progname', __('Programme Name'))
                ->rules('required|string|max:250')
                ->help('Full official name of the programme (will be converted to uppercase)')
                ->required();

            $form->text('abbrev', __('Abbreviation'))
                ->rules('required|string|max:25')
                ->help('Short name/acronym (e.g., BED, MBA)')
                ->required();

        })->tab('Academic Structure', function ($form) {
            
            $form->select('faculty_code', __('Faculty'))
                ->options(MruFaculty::getDropdownOptions())
                ->rules('required')
                ->help('Select the faculty this programme belongs to')
                ->required();

            $form->select('levelCode', __('Programme Level'))
                ->options([
                    1 => 'Certificate',
                    2 => 'Diploma',
                    3 => 'Degree',
                    4 => 'Masters',
                    5 => 'PhD',
                ])
                ->rules('required|in:1,2,3,4,5')
                ->default(3)
                ->required();

            $form->select('study_system', __('Study System'))
                ->options([
                    'Semester' => 'Semester',
                    'Session' => 'Session',
                ])
                ->rules('required|in:Semester,Session')
                ->default('Semester')
                ->required();

            $form->decimal('couselength', __('Programme Duration (years)'))
                ->rules('required|numeric|min:0.5|max:10')
                ->help('Normal duration of the programme in years')
                ->default(3)
                ->required();

            $form->decimal('maxduration', __('Maximum Duration (years)'))
                ->rules('nullable|numeric|min:0.5|max:15')
                ->help('Maximum allowed duration (optional, leave blank if same as normal duration)');

            $form->decimal('mincredit', __('Minimum Credit Hours'))
                ->rules('nullable|numeric|min:0|max:500')
                ->help('Minimum credit hours required to complete the programme');

        })->tab('Additional Information', function ($form) {
            
            $form->html('<div class="alert alert-info">
                <h4><i class="icon fa fa-info"></i> Programme Guidelines</h4>
                <ul>
                    <li><strong>Programme Code:</strong> Use uppercase letters and numbers. Must be unique.</li>
                    <li><strong>Programme Name:</strong> Use official full names as per university records.</li>
                    <li><strong>Level:</strong> 1=Certificate, 2=Diploma, 3=Degree, 4=Masters, 5=PhD</li>
                    <li><strong>Duration:</strong> Typical: Certificate=1yr, Diploma=2yrs, Degree=3-4yrs, Masters=2yrs, PhD=3-5yrs</li>
                    <li><strong>Credits:</strong> Typical: Certificate=60-90, Diploma=120-180, Degree=240-360, Masters=90-120</li>
                </ul>
            </div>');

            // Display statistics for existing records
            if ($form->isEditing()) {
                $programme = $form->model();
                
                $stats = [
                    'Level' => $programme->level_label,
                    'Faculty' => $programme->faculty ? $programme->faculty->abbrev : 'N/A',
                    'Duration' => $programme->duration_display,
                    'Credits' => $programme->credit_display,
                    'Study System' => $programme->study_system,
                    'Enrolled Students' => $programme->getStudentCount(),
                    'Category' => $programme->is_undergraduate ? 'Undergraduate' : ($programme->is_postgraduate ? 'Postgraduate' : 'Other'),
                    'Status' => $programme->is_active ? 'Active' : 'Inactive',
                ];

                $statsHtml = '<div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Programme Statistics</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped">
                            <tbody>';
                
                foreach ($stats as $label => $value) {
                    $statsHtml .= "<tr><th width='200'>{$label}</th><td>{$value}</td></tr>";
                }
                
                $statsHtml .= '</tbody></table></div></div>';
                
                $form->html($statsHtml);
            }

        });

        /*
        |--------------------------------------------------------------------------
        | Form Validation Callbacks
        |--------------------------------------------------------------------------
        */

        $form->saving(function (Form $form) {
            // Validate programme code format
            if (!empty($form->progcode)) {
                $code = strtoupper(trim($form->progcode));
                
                if (strlen($code) > 25) {
                    admin_error('Validation Error', 'Programme code must not exceed 25 characters.');
                    return back()->withInput();
                }
            }

            // Validate duration logic
            if (!empty($form->maxduration) && !empty($form->couselength)) {
                if ($form->maxduration < $form->couselength) {
                    admin_error('Validation Error', 'Maximum duration cannot be less than normal duration.');
                    return back()->withInput();
                }
            }

            // Validate credit hours
            if (!empty($form->mincredit) && $form->mincredit < 0) {
                admin_error('Validation Error', 'Credit hours cannot be negative.');
                return back()->withInput();
            }
        });

        $form->saved(function (Form $form) {
            admin_success('Success', 'Programme saved successfully!');
        });

        return $form;
    }

    /**
     * Render statistics for programme overview
     *
     * @return string
     */
    protected function renderStatistics(): string
    {
        $total = MruProgramme::active()->count();
        $undergraduate = MruProgramme::active()->undergraduate()->count();
        $postgraduate = MruProgramme::active()->postgraduate()->count();
        $semester = MruProgramme::active()->bySemester()->count();
        $session = MruProgramme::active()->bySession()->count();
        
        $totalStudents = DB::table('acad_results')
            ->distinct('regno')
            ->count('regno');

        return '
        <style>
            .programme-stats { margin: 10px 0 15px 0; display: flex; gap: 10px; flex-wrap: wrap; }
            .stat-box { background: #fff; border: 1px solid #d2d6de; border-radius: 3px; padding: 8px 12px; min-width: 140px; flex: 1; }
            .stat-box .stat-label { font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 3px; }
            .stat-box .stat-value { font-size: 18px; font-weight: bold; color: #333; }
            .stat-box.stat-primary { border-left: 3px solid #3c8dbc; }
            .stat-box.stat-success { border-left: 3px solid #00a65a; }
            .stat-box.stat-info { border-left: 3px solid #00c0ef; }
            .stat-box.stat-warning { border-left: 3px solid #f39c12; }
            .stat-box.stat-purple { border-left: 3px solid #605ca8; }
            .stat-box.stat-danger { border-left: 3px solid #dd4b39; }
        </style>
        <div class="programme-stats">
            <div class="stat-box stat-primary">
                <div class="stat-label">Total Programmes</div>
                <div class="stat-value">' . number_format($total) . '</div>
            </div>
            <div class="stat-box stat-success">
                <div class="stat-label">Undergraduate</div>
                <div class="stat-value">' . number_format($undergraduate) . '</div>
            </div>
            <div class="stat-box stat-warning">
                <div class="stat-label">Postgraduate</div>
                <div class="stat-value">' . number_format($postgraduate) . '</div>
            </div>
            <div class="stat-box stat-info">
                <div class="stat-label">Semester System</div>
                <div class="stat-value">' . number_format($semester) . '</div>
            </div>
            <div class="stat-box stat-purple">
                <div class="stat-label">Session System</div>
                <div class="stat-value">' . number_format($session) . '</div>
            </div>
            <div class="stat-box stat-danger">
                <div class="stat-label">Total Students</div>
                <div class="stat-value">' . number_format($totalStudents) . '</div>
            </div>
        </div>';
    }

    /**
     * Get programme details with all statistics
     * 
     * Custom endpoint for fetching comprehensive programme data
     *
     * @param string $code Programme code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgrammeDetails($code)
    {
        try {
            $programme = MruProgramme::findOrFail($code);
            
            return response()->json([
                'success' => true,
                'data' => $programme->getStatistics(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Programme not found',
            ], 404);
        }
    }

    /**
     * Export all programme statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportStatistics()
    {
        $statistics = MruProgramme::getProgrammesWithStudentCounts();
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
            'summary' => MruProgramme::getSummaryData(),
        ]);
    }
}
