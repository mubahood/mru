<?php

namespace App\Admin\Controllers;

use App\Models\MruAcademicResultExport;
use App\Models\MruProgramme;
use App\Models\MruFaculty;
use App\Models\MruAcademicYear;
use App\Exports\MruAcademicResultExcelExport;
use App\Services\MruAcademicResultPdfService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * MRU Academic Result Export Controller
 * 
 * This controller manages academic result exports and summary reports for MRU (Mountains of the Moon University).
 * It handles both detailed exports and summary reports categorized by CGPA ranges according to NCHE 2015 grading system.
 * 
 * Features:
 * - Export academic results to Excel/PDF/HTML formats
 * - Generate summary reports by grade classification
 * - Support for filtering by academic year, semester, programme, study year, and specialisation
 * - CGPA-based student categorization into First Class, Second Class Upper/Lower, Third Class
 * - Identification of halted and retake cases
 * 
 * CGPA Grade Classification (NCHE 2015):
 * - First Class (Honours): 4.40 - 5.00
 * - Second Class Upper Division: 3.60 - 4.39
 * - Second Class Lower Division: 2.80 - 3.59
 * - Third Class (Pass): 2.00 - 2.79
 * 
 * @package App\Admin\Controllers
 * @author MRU Development Team
 * @version 2.0
 */
class MruAcademicResultExportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MRU Academic Result Exports';

    /**
     * CGPA thresholds for grade classification according to NCHE 2015
     * These constants define the grade boundaries used throughout the system
     */
    const GRADE_FIRST_CLASS_MIN = 4.40;
    const GRADE_FIRST_CLASS_MAX = 5.00;
    
    const GRADE_SECOND_UPPER_MIN = 3.60;
    const GRADE_SECOND_UPPER_MAX = 4.39;
    
    const GRADE_SECOND_LOWER_MIN = 2.80;
    const GRADE_SECOND_LOWER_MAX = 3.59;
    
    const GRADE_THIRD_CLASS_MIN = 2.00;
    const GRADE_THIRD_CLASS_MAX = 2.79;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruAcademicResultExport());

        $grid->model()->with(['creator', 'programme'])
            ->orderBy('id', 'desc');

        $grid->column('id', __('ID'))->sortable();
        
        $grid->column('export_name', __('Export Name'))
            ->display(function ($name) {
                return "<strong>{$name}</strong>";
            });

        $grid->column('export_type', __('Type'))
            ->label([
                'excel' => 'success',
                'pdf' => 'danger',
                'html' => 'info',
                'both' => 'primary',
            ])
            ->hide()
            ->sortable();

        $grid->column('academic_year', __('Year'))->sortable();
        $grid->column('semester', __('Sem'))->sortable();

        $grid->column('study_year', __('Yr'))
            ->display(function ($year) {
                $colors = [
                    1 => 'info',
                    2 => 'success',
                    3 => 'warning',
                    4 => 'danger',
                ];
                $color = $colors[$year] ?? 'secondary';
                return "<span class='badge badge-{$color}'>Y{$year}</span>";
            })
            ->hide()
            ->sortable();

        $grid->column('programme_id', __('Programme'))
            ->display(function ($progId) {
                if (!$progId) return '<span class="label label-default">All</span>';
                return $this->programme ? $this->programme->progname : $progId;
            });

        $grid->column('specialisation_id', __('Specialisation'))
            ->display(function ($specId) {
                if (!$specId) return '<span class="label label-default">All</span>';
                $spec = \DB::table('acad_specialisation')->where('spec_id', $specId)->first();
                return $spec ? $spec->abbrev : $specId;
            });

        $grid->column('range', __('Range'))
            ->display(function () {
                $start = $this->start_range ?? 1;
                $end = $this->end_range ?? 100;
                return "<span class='badge badge-info'>{$start}-{$end}</span>";
            });

        $grid->column('total_records', __('Records'))
            ->display(function ($count) {
                return $count > 0 
                    ? "<span class='badge badge-success'>{$count}</span>" 
                    : "<span class='badge badge-secondary'>0</span>";
            })
            ->sortable();

        $grid->column('status', __('Status'))
            ->label([
                'pending' => 'warning',
                'processing' => 'info',
                'completed' => 'success',
                'failed' => 'danger',
            ])
            ->hide()
            ->sortable();

        $grid->column('created_by', __('Created By'))
            ->display(function ($userId) {
                return $this->creator ? $this->creator->name : 'N/A';
            });

        $grid->column('created_at', __('Created At'))
            ->display(function ($date) {
                return date('d M Y H:i', strtotime($date));
            })
            ->sortable();

        $grid->column('generate_excel', __('GEN EXCEL'))
            ->display(function () {
                $url = url("/mru-academic-result-generate?id=$this->id&type=excel");
                return "<a href='$url' target='_blank' class='btn btn-sm btn-success'>
                    <i class='fa fa-file-excel-o'></i>
                </a>";
            });

        $grid->column('generate_pdf', __('GEN PDF'))
            ->display(function () {
                $url = url("/mru-academic-result-generate?id=$this->id&type=pdf");
                return "<a href='$url' target='_blank' class='btn btn-sm btn-danger'>
                    <i class='fa fa-file-pdf-o'></i>
                </a>";
            });

        $grid->column('generate_html', __('GEN HTML'))
            ->display(function () {
                $url = url("/mru-academic-result-generate?id=$this->id&type=html");
                return "<a href='$url' target='_blank' class='btn btn-sm btn-info'>
                    <i class='fa fa-html5'></i>
                </a>";
            });

        $grid->column('view_html', __('VIEW HTML'))
            ->display(function () {
                if ($this->export_type === 'html' || $this->export_type === 'both') {
                    $url = admin_url("mru-academic-result-exports/{$this->id}/view-html");
                    return "<a href='$url' target='_blank' class='btn btn-sm btn-info'>
                        <i class='fa fa-eye'></i>
                    </a>";
                }
                return "<span class='text-muted'>-</span>";
            });

        $grid->column('download_link', __('Download Link'))
            ->display(function () {
                $actions = '';
                
                if ($this->excel_path && file_exists(storage_path('app/' . $this->excel_path))) {
                    $actions .= "<a href='" . admin_url("mru-academic-result-exports/{$this->id}/download-excel") . "' class='btn btn-xs btn-success' style='margin-right: 5px;'>
                        <i class='fa fa-file-excel-o'></i> Excel
                    </a>";
                }
                
                if ($this->pdf_path && file_exists(storage_path('app/' . $this->pdf_path))) {
                    $actions .= "<a href='" . admin_url("mru-academic-result-exports/{$this->id}/download-pdf") . "' class='btn btn-xs btn-danger'>
                        <i class='fa fa-file-pdf-o'></i> PDF
                    </a>";
                }
                
                return $actions ?: '<span class="text-muted">Not generated yet</span>';
            });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            
            $filter->like('export_name', 'Export Name');
            
            $filter->equal('export_type', 'Type')->select([
                'excel' => 'Excel',
                'pdf' => 'PDF',
                'html' => 'HTML',
                'both' => 'Both',
            ]);

            $filter->equal('academic_year', 'Academic Year')
                ->select(MruAcademicYear::pluck('acadyear', 'acadyear')->toArray());

            $filter->equal('semester', 'Semester')->select([
                '1' => 'Semester 1',
                '2' => 'Semester 2',
                '3' => 'Semester 3',
            ]);

            $filter->equal('study_year', 'Year of Study')->select([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
            ]);

            $filter->equal('programme_id', 'Programme')
                ->select(MruProgramme::pluck('progname', 'progcode')->toArray());

            $filter->equal('specialisation_id', 'Specialisation')
                ->select(\DB::table('acad_specialisation')
                    ->orderBy('spec')
                    ->pluck('spec', 'spec_id')
                    ->toArray());

            $filter->between('start_range', 'Range')->integer();

            $filter->equal('status', 'Status')->select([
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'failed' => 'Failed',
            ]);

            $filter->between('created_at', 'Created At')->datetime();
        });

        $grid->column('generate_summary', __('Generate Summary'))
            ->display(function () {
                $url = admin_url("mru-academic-result-exports/{$this->id}/summary-reports");
                return "<a href='$url' target='_blank' class='btn btn-sm btn-warning'>
                    <i class='fa fa-file-pdf-o'></i> Summary
                </a>";
            });

        $grid->actions(function ($actions) {
            $row = $actions->row;
            if ($row->status === 'failed' || $row->status === 'pending') {
                $actions->append("<a href='" . admin_url("mru-academic-result-exports/{$row->id}/regenerate") . "' class='btn btn-xs btn-warning'>
                    <i class='fa fa-refresh'></i> Regenerate
                </a>");
            }
        });

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
        $show = new Show(MruAcademicResultExport::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('export_name', __('Export Name'));
        $show->field('export_type', __('Export Type'))->as(function ($type) {
            return ucfirst($type);
        });
        
        $show->divider();
        
        $show->field('academic_year', __('Academic Year'));
        $show->field('semester', __('Semester'));
        $show->field('study_year', __('Year of Study'))->as(function ($year) {
            return 'Year ' . $year;
        });
        $show->field('programme_id', __('Programme'))->as(function ($progId) {
            return $progId ? ($this->programme ? $this->programme->progname : $progId) : 'All Programmes';
        });
        
        $show->divider();
        
        $show->field('sort_by', __('Sort By'))->as(function ($sort) {
            return ucfirst($sort);
        });
        
        $show->divider();
        
        $show->field('total_records', __('Total Records'));
        $show->field('status', __('Status'))->as(function ($status) {
            return ucfirst($status);
        });
        $show->field('error_message', __('Error Message'));
        
        $show->divider();
        
        $show->field('excel_path', __('Excel File'))->as(function ($path) {
            return $path && file_exists(storage_path('app/' . $path)) 
                ? "<a href='" . admin_url("mru-academic-result-exports/{$this->id}/download-excel") . "' class='btn btn-success'>Download Excel</a>" 
                : 'Not generated';
        })->unescape();
        
        $show->field('pdf_path', __('PDF File'))->as(function ($path) {
            return $path && file_exists(storage_path('app/' . $path)) 
                ? "<a href='" . admin_url("mru-academic-result-exports/{$this->id}/download-pdf") . "' class='btn btn-danger'>Download PDF</a>" 
                : 'Not generated';
        })->unescape();
        
        $show->divider();
        
        $show->field('created_by', __('Created By'))->as(function ($userId) {
            return $this->creator ? $this->creator->name : 'N/A';
        });
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
        $form = new Form(new MruAcademicResultExport());

        $form->text('export_name', __('Export Name'))
            ->required();

        $form->select('export_type', __('Export Type'))
            ->options([
                'excel' => 'Excel Only',
                'pdf' => 'PDF Only',
                'html' => 'HTML Only (Interactive)',
                'both' => 'Both Excel and PDF',
            ])
            ->default('excel')
            ->required();

        $form->divider('Filters');

        $form->select('programme_id', __('Programme'))
            ->options(MruProgramme::all()->mapWithKeys(function ($prog) {
                return [$prog->progcode => $prog->progcode . ' - ' . $prog->progname];
            })->toArray())
            ->required();

        $form->select('academic_year', __('Academic Year'))
            ->options(MruAcademicYear::pluck('acadyear', 'acadyear')->toArray())
            ->required();

        $form->select('semester', __('Semester'))
            ->options([
                '1' => 'Semester 1',
                '2' => 'Semester 2',
                '3' => 'Semester 3',
            ])
            ->required();

        $form->select('study_year', __('Year of Study'))
            ->options([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
            ])
            ->required()
            ->help('Select the year of study for which to export results. Only courses taken in this year will be included.');

        $form->number('minimum_passes_required', __('Minimum Passes Required'))
            ->default(0)
            ->min(0)
            ->required()
            ->help('Number of subjects a student must pass to be considered PASSED (0 = no check). Used to calculate PASS/FAIL status.');

        $form->select('specialisation_id', __('Specialisation (Optional)'))
            ->options(\DB::table('acad_specialisation')
                ->orderBy('spec')
                ->get()
                ->mapWithKeys(function ($spec) {
                    return [$spec->spec_id => $spec->spec_id . ' - ' . $spec->spec . ' (' . $spec->abbrev . ')'];
                })->toArray())
            ->help('Leave empty to export all specialisations');

        $form->divider('Range Settings');

        $form->number('start_range', __('Start Position'))
            ->default(1)
            ->min(1)
            ->required()
            ->help('Starting position in the sorted list (e.g., 1)');

        $form->number('end_range', __('End Position'))
            ->default(100)
            ->min(1)
            ->required()
            ->help('Ending position in the sorted list (e.g., 100)');

        $form->divider('Sorting');

        $form->select('sort_by', __('Sort By'))
            ->options([
                'student' => 'Student Name (Alphabetical)',
                'regno' => 'Registration Number',
            ])
            ->default('student')
            ->required();

        $form->hidden('created_by')->default(Admin::user()->id);
        $form->hidden('status')->default('pending');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });

        return $form;
    }

    /**
     * Download Excel file
     */
    public function downloadExcel($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        
        if (!$export->excel_path || !file_exists(storage_path('app/' . $export->excel_path))) {
            admin_toastr('Excel file not found', 'error');
            return back();
        }

        return Storage::download($export->excel_path, basename($export->excel_path));
    }

    /**
     * Download PDF file
     */
    public function downloadPdf($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        
        if (!$export->pdf_path || !file_exists(storage_path('app/' . $export->pdf_path))) {
            admin_toastr('PDF file not found', 'error');
            return back();
        }

        return Storage::download($export->pdf_path, basename($export->pdf_path));
    }

    /**
     * View HTML export
     */
    public function viewHtml($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        
        if ($export->export_type !== 'html' && $export->export_type !== 'both') {
            admin_toastr('This export is not configured for HTML format', 'error');
            return back();
        }
        
        // Generate HTML on the fly
        $htmlService = new \App\Services\MruAcademicResultHtmlService($export);
        $data = $htmlService->generate();
        
        return view('mru_academic_result_export_html', $data);
    }

    /**
     * Regenerate export
     */
    public function regenerate($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        
        $this->processExport($export);
        
        return redirect(admin_url('mru-academic-result-exports'));
    }

    /**
     * Show summary reports selection page
     * 
     * Displays an interface allowing users to generate various summary reports:
     * - Complete summary (all categories in one PDF)
     * - Individual category reports (First Class, Second Class Upper/Lower, Third Class, Retake Cases)
     * 
     * @param int $id Export record ID
     * @return \Illuminate\View\View
     */
    public function summaryReports($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        
        return view('admin.results.summary-reports-export', compact('export'));
    }

    /**
     * Generate Complete Summary Report (All Lists in One PDF)
     */
    /**
     * Generate complete summary report containing all student categories
     * 
     * This method generates a comprehensive PDF report with the following sections:
     * 1. First Class (Honours) - CGPA 4.40-5.00
     * 2. Second Class Upper Division - CGPA 3.60-4.39
     * 3. Second Class Lower Division - CGPA 2.80-3.59
     * 4. Third Class (Pass) - CGPA 2.00-2.79
     * 5. Halted Cases - Students with >6 retake courses
     * 6. Retake Cases - Students who failed one or more courses
     * 
     * Each category is displayed in a separate table with student details and relevant metrics.
     * The report includes enterprise branding and follows the NCHE 2015 grading system.
     * 
     * @param int $id Export record ID
     * @return \Illuminate\Http\Response PDF stream response
     */
    public function generateCompleteSummary($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        $params = $this->getExportParams($export);
        
        // Get performance lists based on NCHE 2015 CGPA ranges
        $firstClass = $this->getPerformanceList(
            self::GRADE_FIRST_CLASS_MIN, 
            self::GRADE_FIRST_CLASS_MAX, 
            $params
        );
        
        $secondClassUpper = $this->getPerformanceList(
            self::GRADE_SECOND_UPPER_MIN, 
            self::GRADE_SECOND_UPPER_MAX, 
            $params
        );
        
        $secondClassLower = $this->getPerformanceList(
            self::GRADE_SECOND_LOWER_MIN, 
            self::GRADE_SECOND_LOWER_MAX, 
            $params
        );
        
        $thirdClass = $this->getPerformanceList(
            self::GRADE_THIRD_CLASS_MIN, 
            self::GRADE_THIRD_CLASS_MAX, 
            $params
        );
        
        // Get halted and retake cases
        $haltedCases = $this->getHaltedCases($params);
        $retakeCases = $this->getRetakeCases($params);
        
        // Prepare data for PDF view
        $data = [
            'export' => $export,
            'params' => $params,
            'firstClass' => $firstClass,
            'secondClassUpper' => $secondClassUpper,
            'secondClassLower' => $secondClassLower,
            'thirdClass' => $thirdClass,
            'haltedCases' => $haltedCases,
            'retakeCases' => $retakeCases,
        ];
        
        // Generate PDF
        $pdf = Pdf::loadView('admin.results.complete-summary-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'Academic_Results_Summary_' . $export->export_name . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->stream($filename);
    }

    /**
     * Generate First Class (Honours) report
     * 
     * Generates a PDF report containing only students who achieved First Class Honours
     * with CGPA between 4.40 and 5.00 according to NCHE 2015 grading system.
     * 
     * @param int $id Export record ID
     * @return \Illuminate\Http\Response PDF stream response
     */
    public function generateVCList($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        $params = $this->getExportParams($export);
        
        $students = $this->getPerformanceList(
            self::GRADE_FIRST_CLASS_MIN, 
            self::GRADE_FIRST_CLASS_MAX, 
            $params
        );
        
        return $this->generateSummaryPDF('First Class (Honours)', $students, $export);
    }

    /**
     * Generate Second Class Upper Division report
     * 
     * Generates a PDF report containing only students who achieved Second Class Upper Division
     * with CGPA between 3.60 and 4.39 according to NCHE 2015 grading system.
     * 
     * @param int $id Export record ID
     * @return \Illuminate\Http\Response PDF stream response
     */
    public function generateDeansList($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        $params = $this->getExportParams($export);
        
        $students = $this->getPerformanceList(
            self::GRADE_SECOND_UPPER_MIN, 
            self::GRADE_SECOND_UPPER_MAX, 
            $params
        );
        
        return $this->generateSummaryPDF('Second Class Upper Division', $students, $export);
    }

    /**
     * Generate Second Class Lower Division report
     * 
     * Generates a PDF report containing only students who achieved Second Class Lower Division
     * with CGPA between 2.80 and 3.59 according to NCHE 2015 grading system.
     * 
     * @param int $id Export record ID
     * @return \Illuminate\Http\Response PDF stream response
     */
    public function generatePassCases($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        $params = $this->getExportParams($export);
        
        $students = $this->getPerformanceList(
            self::GRADE_SECOND_LOWER_MIN, 
            self::GRADE_SECOND_LOWER_MAX, 
            $params
        );
        
        return $this->generateSummaryPDF('Second Class Lower Division', $students, $export);
    }

    /**
     * Generate Retake Cases (Pass Degree) report
     * 
     * Generates a PDF report containing students who failed one or more courses
     * and need to retake them. Includes the list of failed courses for each student.
     * 
     * @param int $id Export record ID
     * @return \Illuminate\Http\Response PDF stream response
     */
    public function generateRetakeCases($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        $params = $this->getExportParams($export);
        
        $students = $this->getRetakeCases($params);
        
        return $this->generateSummaryPDF('Retake Cases (Pass Degree)', $students, $export);
    }

    /**
     * Extract export parameters from export record
     * 
     * Converts an export record into a standardized array of parameters
     * used for filtering database queries. This ensures consistency across
     * all report generation methods.
     * 
     * @param \App\Models\MruAcademicResultExport $export Export record
     * @return array Associative array of export parameters
     */
    private function getExportParams($export)
    {
        return [
            'acad' => $export->academic_year,           // Academic year (e.g., "2023/2024")
            'semester' => $export->semester,             // Semester number (1 or 2)
            'progid' => $export->programme_id,           // Programme code
            'studyyear' => $export->study_year,          // Year of study (1, 2, 3, or 4)
            'specialisation_id' => $export->specialisation_id, // Optional specialisation ID
            'start_range' => $export->start_range,       // Starting position for range-based exports
            'end_range' => $export->end_range,           // Ending position for range-based exports
        ];
    }

    /**
     * Get students by CGPA performance range
     * 
     * Retrieves students whose CGPA falls within the specified range. This is the core method
     * used for categorizing students into different class divisions (First Class, Second Class Upper/Lower, etc.)
     * 
     * The method:
     * 1. Queries all student results from acad_results table
     * 2. Calculates CGPA using: SUM(CreditUnits * GradePoint) / SUM(CreditUnits)
     * 3. Filters students by CGPA range
     * 4. Sorts by CGPA descending
     * 5. Applies optional range limiting
     * 6. Optionally excludes specified registration numbers
     * 
     * @param float $cgpaMin Minimum CGPA threshold (inclusive)
     * @param float $cgpaMax Maximum CGPA threshold (inclusive)
     * @param array $params Export parameters (acad, semester, progid, studyyear, specialisation_id, start_range, end_range)
     * @param array $excludeRegnos Optional array of registration numbers to exclude
     * @return \Illuminate\Support\Collection Collection of student records with regno, entryno, studname, gender, progid, cgpa
     */
    private function getPerformanceList($cgpaMin, $cgpaMax, $params, $excludeRegnos = [])
    {
        $query = DB::table('acad_results as r')
            ->join('acad_student as s', 's.regno', '=', 'r.regno')
            ->select(
                'r.regno',
                's.entryno',
                DB::raw("CONCAT(s.othername, ' ', s.firstname) as studname"),
                's.gender',
                'r.progid',
                // CGPA Calculation: Weighted average of (Credit Units × Grade Points) / Total Credit Units
                DB::raw('(SELECT SUM(r2.CreditUnits * r2.gradept) / NULLIF(SUM(r2.CreditUnits), 0) 
                         FROM acad_results r2 
                         WHERE r2.regno = r.regno) as cgpa')
            )
            ->whereNotNull('r.regno')
            ->groupBy('r.regno', 's.entryno', 's.othername', 's.firstname', 's.gender', 'r.progid');

        // Exclude specified students if provided (useful for preventing duplication across categories)
        if (!empty($excludeRegnos)) {
            $query->whereNotIn('r.regno', $excludeRegnos);
        }

        // Apply filters from export configuration
        if (!empty($params['acad'])) {
            $query->where('r.acad', $params['acad']);
        }
        if (!empty($params['semester'])) {
            $query->where('r.semester', $params['semester']);
        }
        if (!empty($params['progid'])) {
            $query->where('r.progid', $params['progid']);
        }
        if (!empty($params['studyyear'])) {
            $query->where('r.studyyear', $params['studyyear']);
        }
        if (!empty($params['specialisation_id'])) {
            $query->where('r.spec_id', $params['specialisation_id']);
        }

        $results = $query->get();

        // Filter by CGPA range (done in memory after retrieval for accuracy)
        $filtered = $results->filter(function($student) use ($cgpaMin, $cgpaMax) {
            return $student->cgpa >= $cgpaMin && $student->cgpa <= $cgpaMax;
        })->sortByDesc('cgpa')->values();

        // Apply range limit if specified (e.g., top 100 students)
        if (!empty($params['start_range']) && !empty($params['end_range'])) {
            $start = $params['start_range'] - 1; // Convert to 0-based index
            $end = $params['end_range'];
            $filtered = $filtered->slice($start, $end - $start)->values();
        }

        return $filtered;
    }

    /**
     * Get students who passed all courses (legacy method - now unused in summary reports)
     * 
     * This method was previously used for "Pass Cases" but has been replaced by CGPA-based
     * classification. Kept for backward compatibility with detailed exports.
     * 
     * Identifies students who passed all their courses based on pass thresholds:
     * - Undergraduate programs (level < 4): Pass mark = 50
     * - Postgraduate programs (level >= 4): Pass mark = 60
     * 
     * @param array $params Export parameters
     * @param array $excludeRegnos Registration numbers to exclude
     * @return \Illuminate\Support\Collection Collection of students who passed all courses
     */
    private function getPassCases($params, $excludeRegnos = [])
    {
        // Determine pass threshold based on program level
        $programLevel = null;
        if (!empty($params['progid'])) {
            $prog = MruProgramme::where('progcode', $params['progid'])->first();
            $programLevel = $prog ? $prog->proglev : null;
        }

        $passThreshold = ($programLevel && $programLevel >= 4) ? 60 : 50;

        // Get all students with CGPA
        $allStudentsQuery = DB::table('acad_results as r')
            ->join('acad_student as s', 's.regno', '=', 'r.regno')
            ->select(
                'r.regno', 
                's.entryno', 
                DB::raw("CONCAT(s.othername, ' ', s.firstname) as studname"), 
                's.gender', 
                'r.progid',
                DB::raw('(SELECT SUM(r2.CreditUnits * r2.gradept) / NULLIF(SUM(r2.CreditUnits), 0) 
                         FROM acad_results r2 
                         WHERE r2.regno = r.regno) as cgpa')
            )
            ->whereNotNull('r.regno');

        if (!empty($params['acad'])) {
            $allStudentsQuery->where('r.acad', $params['acad']);
        }
        if (!empty($params['semester'])) {
            $allStudentsQuery->where('r.semester', $params['semester']);
        }
        if (!empty($params['progid'])) {
            $allStudentsQuery->where('r.progid', $params['progid']);
        }
        if (!empty($params['studyyear'])) {
            $allStudentsQuery->where('r.studyyear', $params['studyyear']);
        }
        if (!empty($params['specialisation_id'])) {
            $allStudentsQuery->where('r.spec_id', $params['specialisation_id']);
        }

        // Get students with failing grades
        $failedStudentsQuery = DB::table('acad_results as r')
            ->select('r.regno')
            ->where('r.score', '<', $passThreshold);

        if (!empty($params['acad'])) {
            $failedStudentsQuery->where('r.acad', $params['acad']);
        }
        if (!empty($params['semester'])) {
            $failedStudentsQuery->where('r.semester', $params['semester']);
        }
        if (!empty($params['progid'])) {
            $failedStudentsQuery->where('r.progid', $params['progid']);
        }
        if (!empty($params['studyyear'])) {
            $failedStudentsQuery->where('r.studyyear', $params['studyyear']);
        }
        if (!empty($params['specialisation_id'])) {
            $failedStudentsQuery->where('r.spec_id', $params['specialisation_id']);
        }

        $failedRegnos = $failedStudentsQuery->pluck('regno')->toArray();
        
        // Combine failed and excluded regnos
        $allExcludedRegnos = array_merge($failedRegnos, $excludeRegnos);

        $results = $allStudentsQuery
            ->whereNotIn('r.regno', $allExcludedRegnos)
            ->groupBy('r.regno', 's.entryno', 's.othername', 's.firstname', 's.gender', 'r.progid')
            ->orderBy('studname')
            ->get();

        // Apply range limit
        if (!empty($params['start_range']) && !empty($params['end_range'])) {
            $start = $params['start_range'] - 1;
            $end = $params['end_range'];
            $results = $results->slice($start, $end - $start)->values();
        }

        return $results;
    }

    /**
     * Get incomplete cases - students with fewer course registrations than expected
     * 
     * Identifies students who have registered for fewer courses than the expected total
     * specified in the export configuration (minimum_passes_required field).
     * 
     * This is used in detailed exports (not summary reports) to identify students
     * who haven't completed their full course load for the semester/year.
     * 
     * Algorithm:
     * 1. Get expected course count from export.minimum_passes_required
     * 2. Count each student's course registrations
     * 3. Identify students with count < expected
     * 4. Calculate which courses they're missing
     * 
     * @param array $params Export parameters
     * @param \App\Models\MruAcademicResultExport $export Export record
     * @return \Illuminate\Support\Collection Collection of incomplete students with missing courses
     */
    private function getIncompleteCases($params, $export)
    {
        // Use the expected course count from export configuration
        $expectedCourseCount = $export->minimum_passes_required ?? 0;
        
        // If no expected count is set, return empty collection
        if ($expectedCourseCount == 0) {
            return collect([]);
        }

        // Get all courses for this export configuration (for identifying missing courses)
        $coursesQuery = DB::table('acad_results')
            ->select('courseid')
            ->distinct();

        // Apply same filters as main export
        if (!empty($params['acad'])) {
            $coursesQuery->where('acad', $params['acad']);
        }
        if (!empty($params['semester'])) {
            $coursesQuery->where('semester', $params['semester']);
        }
        if (!empty($params['progid'])) {
            $coursesQuery->where('progid', $params['progid']);
        }
        if (!empty($params['studyyear'])) {
            $coursesQuery->where('studyyear', $params['studyyear']);
        }
        if (!empty($params['specialisation_id'])) {
            $coursesQuery->where('spec_id', $params['specialisation_id']);
        }

        $allCourses = $coursesQuery->pluck('courseid')->toArray();

        // Get all students with their course registration count
        $studentsQuery = DB::table('acad_results as r')
            ->join('acad_student as s', 's.regno', '=', 'r.regno')
            ->select(
                'r.regno',
                's.entryno',
                DB::raw("CONCAT(s.othername, ' ', s.firstname) as studname"),
                's.gender',
                'r.progid',
                DB::raw('COUNT(r.courseid) as courses_count'),
                DB::raw("GROUP_CONCAT(DISTINCT r.courseid ORDER BY r.courseid SEPARATOR ', ') as registered_courses")
            )
            ->whereNotNull('r.regno');

        if (!empty($params['acad'])) {
            $studentsQuery->where('r.acad', $params['acad']);
        }
        if (!empty($params['semester'])) {
            $studentsQuery->where('r.semester', $params['semester']);
        }
        if (!empty($params['progid'])) {
            $studentsQuery->where('r.progid', $params['progid']);
        }
        if (!empty($params['studyyear'])) {
            $studentsQuery->where('r.studyyear', $params['studyyear']);
        }
        if (!empty($params['specialisation_id'])) {
            $studentsQuery->where('r.spec_id', $params['specialisation_id']);
        }

        // Check if total count of registrations < expected count from configuration
        $students = $studentsQuery
            ->groupBy('r.regno', 's.entryno', 's.othername', 's.firstname', 's.gender', 'r.progid')
            ->havingRaw('COUNT(r.courseid) < ?', [$expectedCourseCount])
            ->orderBy('studname')
            ->get();

        // Add incomplete courses info (which courses from expected list they're missing)
        foreach ($students as $student) {
            $registeredCourses = explode(', ', $student->registered_courses);
            $missingCourses = array_diff($allCourses, $registeredCourses);
            $student->incomplete_courses = implode(', ', $missingCourses);
        }

        // Apply range limit
        if (!empty($params['start_range']) && !empty($params['end_range'])) {
            $start = $params['start_range'] - 1;
            $end = $params['end_range'];
            $students = $students->slice($start, $end - $start)->values();
        }

        return $students;
    }

    /**
     * Get halted cases - students whose retake courses exceed maximum semester load
     */
    private function getHaltedCases($params)
    {
        $programLevel = null;
        if (!empty($params['progid'])) {
            $prog = MruProgramme::where('progcode', $params['progid'])->first();
            $programLevel = $prog ? $prog->proglev : null;
        }

        $passThreshold = ($programLevel && $programLevel >= 4) ? 60 : 50;
        $maxSemesterLoad = 6; // Maximum courses per semester

        // Get students with failed courses count
        $query = DB::table('acad_results as r')
            ->join('acad_student as s', 's.regno', '=', 'r.regno')
            ->select(
                'r.regno', 
                's.entryno', 
                DB::raw("CONCAT(s.othername, ' ', s.firstname) as studname"),
                's.gender', 
                'r.progid',
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(r.courseid, ' (', r.grade, ')') SEPARATOR ', ') as failed_courses"),
                DB::raw('COUNT(DISTINCT r.courseid) as failed_count')
            )
            ->where('r.score', '<', $passThreshold)
            ->whereNotNull('r.regno');

        if (!empty($params['acad'])) {
            $query->where('r.acad', $params['acad']);
        }
        if (!empty($params['semester'])) {
            $query->where('r.semester', $params['semester']);
        }
        if (!empty($params['progid'])) {
            $query->where('r.progid', $params['progid']);
        }
        if (!empty($params['studyyear'])) {
            $query->where('r.studyyear', $params['studyyear']);
        }
        if (!empty($params['specialisation_id'])) {
            $query->where('r.spec_id', $params['specialisation_id']);
        }

        $results = $query
            ->groupBy('r.regno', 's.entryno', 's.othername', 's.firstname', 's.gender', 'r.progid')
            ->havingRaw('COUNT(DISTINCT r.courseid) > ?', [$maxSemesterLoad])
            ->orderBy('studname')
            ->get();

        // Apply range limit
        if (!empty($params['start_range']) && !empty($params['end_range'])) {
            $start = $params['start_range'] - 1;
            $end = $params['end_range'];
            $results = $results->slice($start, $end - $start)->values();
        }

        return $results;
    }

    /**
     * Get retake cases - students who failed at least one course
     */
    private function getRetakeCases($params)
    {
        $programLevel = null;
        if (!empty($params['progid'])) {
            $prog = MruProgramme::where('progcode', $params['progid'])->first();
            $programLevel = $prog ? $prog->proglev : null;
        }

        $passThreshold = ($programLevel && $programLevel >= 4) ? 60 : 50;

        $query = DB::table('acad_results as r')
            ->join('acad_student as s', 's.regno', '=', 'r.regno')
            ->select('r.regno', 's.entryno', DB::raw("CONCAT(s.othername, ' ', s.firstname) as studname"),
                    's.gender', 'r.progid',
                    DB::raw("GROUP_CONCAT(DISTINCT CONCAT(r.courseid, ' (', r.grade, ')') SEPARATOR ', ') as failed_courses"))
            ->where('r.score', '<', $passThreshold)
            ->whereNotNull('r.regno');

        if (!empty($params['acad'])) {
            $query->where('r.acad', $params['acad']);
        }
        if (!empty($params['semester'])) {
            $query->where('r.semester', $params['semester']);
        }
        if (!empty($params['progid'])) {
            $query->where('r.progid', $params['progid']);
        }
        if (!empty($params['studyyear'])) {
            $query->where('r.studyyear', $params['studyyear']);
        }
        if (!empty($params['specialisation_id'])) {
            $query->where('r.spec_id', $params['specialisation_id']);
        }

        $results = $query
            ->groupBy('r.regno', 's.entryno', 's.othername', 's.firstname', 's.gender', 'r.progid')
            ->orderBy('studname')
            ->get();

        // Apply range limit
        if (!empty($params['start_range']) && !empty($params['end_range'])) {
            $start = $params['start_range'] - 1;
            $end = $params['end_range'];
            $results = $results->slice($start, $end - $start)->values();
        }

        return $results;
    }

    /**
     * Generate summary PDF
     */
    private function generateSummaryPDF($title, $students, $export)
    {
        $params = $this->getExportParams($export);
        
        $pdf = Pdf::loadView('admin.results.pdf-template', compact('title', 'students', 'params', 'export'));
        
        $pdf->setPaper('A4', 'portrait');
        
        $filename = str_replace(' ', '_', $title) . '_' . $export->export_name . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->stream($filename);
    }
}
