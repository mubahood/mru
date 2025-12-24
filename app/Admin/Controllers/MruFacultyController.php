<?php

namespace App\Admin\Controllers;

use App\Models\MruFaculty;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

/**
 * MruFacultyController
 * 
 * Laravel Admin controller for managing academic faculties in the MRU system.
 * 
 * Features:
 * - Grid view with faculty information, dean details, and statistics
 * - Advanced filtering by code, name, dean, contacts
 * - Form with validation for creating/editing faculties
 * - Detail view with organized sections and relationship counts
 * - Export functionality for faculty data
 * - Statistics dashboard showing faculty overview
 * 
 * @package App\Admin\Controllers
 * @author MRU Development Team
 * @version 1.0.0
 */
class MruFacultyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MRU Faculties';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruFaculty());

        // Eager load relationships and counts
        $grid->model()->withCount(['programmes', 'users', 'students'])
            ->orderBy('faculty_code', 'asc');

        // Add statistics header
        $grid->header(function () {
            return $this->renderStatistics();
        });

        // Configure grid actions
        $grid->actions(function ($actions) {
            // Keep all actions enabled
        });

        // Configure batch actions
        $grid->batchActions(function ($batch) {
            $batch->disableDelete(); // Prevent accidental bulk deletion
        });

        // Disable create button for placeholder faculty
        $grid->tools(function ($tools) {
            // Keep create button enabled
        });

        /*
        |--------------------------------------------------------------------------
        | Grid Columns
        |--------------------------------------------------------------------------
        */

        $grid->column('faculty_code', __('Code'))->sortable();

        $grid->column('abbrev', __('Abbreviation'))->sortable();

        $grid->column('faculty_name', __('Faculty Name'))->sortable();

        $grid->column('faculty_dean', __('Dean'));

        $grid->column('faculty_contacts', __('Contacts'));

        $grid->column('programmes_count', __('Programmes'))
            ->display(function ($count) {
                return "<span class='label label-info'>" . number_format($count) . "</span>";
            })->sortable();

        $grid->column('students_count', __('Students'))
            ->display(function ($count) {
                return "<span class='label label-primary'>" . number_format($count) . "</span>";
            })->sortable();

        $grid->column('users_count', __('Users'))
            ->display(function ($count) {
                return "<span class='label label-default'>" . number_format($count) . "</span>";
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

            // Faculty code filter
            $filter->equal('faculty_code', 'Faculty Code')
                ->select(MruFaculty::orderBy('faculty_code')->pluck('faculty_code', 'faculty_code'));

            // Faculty name search
            $filter->like('faculty_name', 'Faculty Name');

            // Abbreviation search
            $filter->like('abbrev', 'Abbreviation');

            // Dean search
            $filter->like('faculty_dean', 'Dean Name');

            // Contacts search
            $filter->like('faculty_contacts', 'Contacts');

            // Has dean filter
            $filter->where(function ($query) {
                $query->where('faculty_dean', '!=', '-')
                      ->where('faculty_dean', '!=', '')
                      ->whereNotNull('faculty_dean');
            }, 'Has Dean')->checkbox('1');

            // Active filter (exclude placeholder)
            $filter->where(function ($query) {
                $query->where('faculty_code', '!=', MruFaculty::PLACEHOLDER_CODE);
            }, 'Active Only')->checkbox('1');
        });

        /*
        |--------------------------------------------------------------------------
        | Grid Export
        |--------------------------------------------------------------------------
        */

        $grid->export(function ($export) {
            $export->filename('MRU_Faculties_' . date('Y-m-d_His'));
            
            $export->column('faculty_code', 'Faculty Code');
            $export->column('faculty_name', 'Faculty Name');
            $export->column('abbrev', 'Abbreviation');
            $export->column('faculty_dean', 'Dean');
            $export->column('faculty_contacts', 'Contacts');
            
            // Add computed columns
            $export->column('programme_count', function ($model) {
                return $model->getProgrammeCount();
            });
            $export->column('user_count', function ($model) {
                return $model->getUserCount();
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
        $show = new Show(MruFaculty::findOrFail($id));

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

        $show->field('faculty_code', __('Faculty Code'))
            ->as(function ($code) {
                return $code === MruFaculty::PLACEHOLDER_CODE ? 
                    "{$code} (Placeholder)" : $code;
            })
            ->badge();

        $show->field('faculty_name', __('Faculty Name'))
            ->setWidth(12);

        $show->field('abbrev', __('Abbreviation'))
            ->badge('info');

        $show->divider();

        /*
        |--------------------------------------------------------------------------
        | Contact Information Section
        |--------------------------------------------------------------------------
        */

        $show->panel()
            ->title('Contact Information')
            ->style('success');

        $show->field('faculty_dean', __('Dean'))
            ->as(function ($dean) {
                if (empty($dean) || $dean === '-' || $dean === 'N/A') {
                    return 'Not Assigned';
                }
                return ucwords(strtolower($dean));
            });

        $show->field('faculty_contacts', __('Contact Numbers'))
            ->as(function ($contacts) {
                if (empty($contacts) || $contacts === '-') {
                    return 'Not Available';
                }
                return $contacts;
            });

        $show->divider();

        /*
        |--------------------------------------------------------------------------
        | Statistics Section
        |--------------------------------------------------------------------------
        */

        $show->panel()
            ->title('Faculty Statistics')
            ->style('info');

        $show->field('programme_count', __('Total Programmes'))
            ->as(function () {
                return $this->getProgrammeCount();
            })
            ->badge('success');

        $show->field('user_count', __('Associated Users'))
            ->as(function () {
                return $this->getUserCount();
            })
            ->badge('info');

        $show->field('status', __('Status'))
            ->as(function () {
                return $this->is_active ? 'Active' : 'Inactive';
            })
            ->badge('success');

        $show->divider();

        /*
        |--------------------------------------------------------------------------
        | Related Programmes Section
        |--------------------------------------------------------------------------
        */

        // Note: Programmes relationship disabled temporarily - uncomment when model is available
        /*
        $show->programmes('Related Programmes', function ($programmes) {
            $programmes->resource('/admin/mru-programmes');
            
            $programmes->column('prog_code', __('Code'));
            $programmes->column('prog_name', __('Programme Name'));
            $programmes->column('study_time', __('Study Time'));
            $programmes->column('min_years', __('Duration (Years)'));
            
            $programmes->disableCreateButton();
            $programmes->disableFilter();
            $programmes->disableExport();
            $programmes->disablePagination();
        });
        */

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruFaculty());

        // Disable view check
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();

        // Configure tools
        $form->tools(function (Form\Tools $tools) {
            // Keep all tools enabled
        });

        /*
        |--------------------------------------------------------------------------
        | Form Tabs
        |--------------------------------------------------------------------------
        */

        $form->tab('Basic Information', function ($form) {
            
            $form->text('faculty_code', __('Faculty Code'))
                ->rules('required|string|max:10|unique:acad_faculty,faculty_code,' . ($form->model()->faculty_code ?? 'NULL') . ',faculty_code')
                ->help('2-digit code (e.g., 01, 02, 03). Will be auto-padded.')
                ->required();

            $form->text('faculty_name', __('Faculty Name'))
                ->rules('required|string|max:150')
                ->help('Full official name of the faculty (will be converted to uppercase)')
                ->required();

            $form->text('abbrev', __('Abbreviation'))
                ->rules('required|string|max:15')
                ->help('Short name/acronym (e.g., FSTEAD, FSSAH, FOE)')
                ->required();

        })->tab('Contact Information', function ($form) {
            
            $form->text('faculty_dean', __('Faculty Dean'))
                ->rules('nullable|string|max:150')
                ->help('Name of the faculty dean (leave blank or use "-" if not assigned)')
                ->default('-');

            $form->text('faculty_contacts', __('Contact Numbers'))
                ->rules('nullable|string|max:45')
                ->help('Phone numbers separated by commas (e.g., 0777123456, 0755987654)')
                ->default('-');

        })->tab('Additional Information', function ($form) {
            
            $form->html('<div class="alert alert-info">
                <h4><i class="icon fa fa-info"></i> Faculty Guidelines</h4>
                <ul>
                    <li><strong>Faculty Code:</strong> Use 2-digit codes (01-99). Code "00" is reserved for placeholder.</li>
                    <li><strong>Faculty Name:</strong> Use official full names as per university records.</li>
                    <li><strong>Abbreviation:</strong> Keep it short and memorable (max 15 characters).</li>
                    <li><strong>Dean:</strong> Enter full name with title (e.g., "Dr. John Doe", "Prof. Jane Smith").</li>
                    <li><strong>Contacts:</strong> Include multiple numbers if available.</li>
                </ul>
            </div>');

            // Display statistics for existing records
            if ($form->isEditing()) {
                $faculty = $form->model();
                
                $stats = [
                    'Total Programmes' => $faculty->getProgrammeCount(),
                    'Associated Users' => $faculty->getUserCount(),
                    'Status' => $faculty->is_active ? 'Active' : 'Inactive',
                ];

                $statsHtml = '<div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Faculty Statistics</h3>
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
            // Validate faculty code format
            if (!empty($form->faculty_code)) {
                $code = str_pad(trim($form->faculty_code), 2, '0', STR_PAD_LEFT);
                
                if (!preg_match('/^\d{2}$/', $code)) {
                    admin_error('Validation Error', 'Faculty code must be a 2-digit number.');
                    return back()->withInput();
                }
            }

            // Validate abbreviation length
            if (!empty($form->abbrev) && strlen($form->abbrev) > 15) {
                admin_error('Validation Error', 'Abbreviation must not exceed 15 characters.');
                return back()->withInput();
            }
        });

        $form->saved(function (Form $form) {
            admin_success('Success', 'Faculty saved successfully!');
        });

        return $form;
    }

    /**
     * Render statistics for faculty overview
     *
     * @return string
     */
    protected function renderStatistics(): string
    {
        $total = MruFaculty::count();
        $active = MruFaculty::active()->count();
        $withDean = MruFaculty::withDean()->count();
        $totalProgrammes = DB::table('acad_programme')->count();
        $totalUsers = DB::table('my_aspnet_user_faculties')->distinct('user_name')->count('user_name');
        
        $avgProgrammes = $active > 0 ? round($totalProgrammes / $active, 1) : 0;

        return '
        <style>
            .faculty-stats { margin: 10px 0 15px 0; display: flex; gap: 10px; flex-wrap: wrap; }
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
        <div class="faculty-stats">
            <div class="stat-box stat-primary">
                <div class="stat-label">Total Faculties</div>
                <div class="stat-value">' . number_format($total) . '</div>
            </div>
            <div class="stat-box stat-success">
                <div class="stat-label">Active Faculties</div>
                <div class="stat-value">' . number_format($active) . '</div>
            </div>
            <div class="stat-box stat-info">
                <div class="stat-label">With Dean</div>
                <div class="stat-value">' . number_format($withDean) . '</div>
            </div>
            <div class="stat-box stat-warning">
                <div class="stat-label">Total Programmes</div>
                <div class="stat-value">' . number_format($totalProgrammes) . '</div>
            </div>
            <div class="stat-box stat-purple">
                <div class="stat-label">Avg Programmes</div>
                <div class="stat-value">' . $avgProgrammes . '</div>
            </div>
            <div class="stat-box stat-danger">
                <div class="stat-label">Associated Users</div>
                <div class="stat-value">' . number_format($totalUsers) . '</div>
            </div>
        </div>';
    }

    /**
     * Get faculty details with all statistics
     * 
     * Custom endpoint for fetching comprehensive faculty data
     *
     * @param string $code Faculty code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFacultyDetails($code)
    {
        try {
            $faculty = MruFaculty::findOrFail($code);
            
            return response()->json([
                'success' => true,
                'data' => $faculty->getStatistics(),
                'programmes' => $faculty->programmes()->select('prog_code', 'prog_name')->get(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }
    }

    /**
     * Export all faculty statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportStatistics()
    {
        $statistics = MruFaculty::getAllStatistics();
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
            'summary' => MruFaculty::getSummaryData(),
        ]);
    }
}
