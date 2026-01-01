<?php

namespace App\Admin\Controllers;

use App\Models\MruResult;
use App\Models\MruCourse;
use App\Models\MruStudent;
use App\Models\MruProgramme;
use App\Models\MruAcademicYear;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * MruResultController
 * 
 * Comprehensive Laravel Admin controller for managing student academic results.
 * Features: Advanced filtering, statistics, grade analysis, multi-level search.
 * 
 * @package App\Admin\Controllers
 * @version 2.0.0
 */
class MruResultController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Academic Results (Marks)';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruResult());

        // Eager load relationships for better performance
        $grid->model()->with(['course', 'student', 'programme'])
            ->orderBy('ID', 'desc');

        /*
        |----------------------------------------------------------------------
        | QUICK SEARCH
        |----------------------------------------------------------------------
        */

        $grid->quickSearch(function ($model, $query) {
            $model->where(function ($model) use ($query) {
                $model->where('regno', 'like', "%{$query}%")
                      ->orWhere('courseid', 'like', "%{$query}%")
                      ->orWhere('acad', 'like', "%{$query}%")
                      ->orWhere('progid', 'like', "%{$query}%")
                      ->orWhere('grade', 'like', "%{$query}%");
            });
        })->placeholder('Search: Regno, Course ID, Year, Programme, Grade');

        /*
        |----------------------------------------------------------------------
        | GRID COLUMNS
        |----------------------------------------------------------------------
        */

        // ID
        $grid->column('ID', __('ID'))
            ->sortable()
            ->width(70);

        // Student Information
        $grid->column('student_info', __('Student'))
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
                    return "<div><strong>{$this->courseid}</strong><br><small style='color:#666;'>{$name}</small><br><small style='color:#999;'>{$credits} CU</small></div>";
                }
                return $this->courseid;
            })
            ->sortable('courseid')
            ->width(150);

        // Academic Year & Semester
        $grid->column('acad', __('Year'))->sortable();
        $grid->column('semester', __('Sem'))
            ->display(function ($sem) {
                return 'Sem ' . $sem;
            })->sortable();

        // Programme
        $grid->column('progid', __('Programme'))->sortable();

        // Score
        $grid->column('score', __('Score'))->sortable();

        // Grade
        $grid->column('grade', __('Grade'))->sortable();

        // Grade Points & GPA
        $grid->column('gradept', __('Points'))->sortable();
        $grid->column('gpa', __('GPA'))->sortable();

        // Status
        $grid->column('status', __('Status'))->sortable();
        /*
        |----------------------------------------------------------------------
        | CUSTOM TOOLS - SUMMARY REPORTS
        |----------------------------------------------------------------------
        */

        $grid->tools(function ($tools) {
            $tools->append('
                <div class="btn-group pull-right" style="margin-right: 10px">
                    <button type="button" class="btn btn-success btn-sm" id="generate-summary-btn">
                        <i class="fa fa-file-pdf-o"></i> Generate Summary Reports
                    </button>
                </div>
                
                <script>
                    $(document).ready(function() {
                        $("#generate-summary-btn").click(function() {
                            // Get current filter values
                            var params = new URLSearchParams(window.location.search);
                            var acad = params.get("acad") || "";
                            var semester = params.get("semester") || "";
                            var progid = params.get("progid") || "";
                            var studyyear = params.get("studyyear") || "";
                            
                            // Build URL with parameters
                            var url = "' . admin_url('mru-results/summary-reports') . '";
                            url += "?acad=" + acad + "&semester=" + semester + "&progid=" + progid + "&studyyear=" + studyyear;
                            
                            // Open in new tab
                            window.open(url, "_blank");
                        });
                    });
                </script>
            ');
        });

        /*
        |----------------------------------------------------------------------
        | GRID FILTERS
        |----------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            // Remove default ID filter
            $filter->disableIdFilter();

            // Expand filters by default
            $filter->expand();

            /*
            | Column 1: Student & Course Filters
            */
            $filter->column(1/3, function ($filter) {
                // Student registration number
                $filter->like('regno', 'Student Regno')->placeholder('Search by registration number');

                // Course ID
                $filter->like('courseid', 'Course ID')->placeholder('Search by course code');

                // Programme
                $filter->equal('progid', 'Programme')->select(MruProgramme::orderBy('progname')->pluck('progname', 'progcode')->prepend('All Programmes', ''));
            });

            /*
            | Column 2: Academic Filters
            */
            $filter->column(1/3, function ($filter) {
                // Academic year
                $filter->equal('acad', 'Academic Year')->select(MruAcademicYear::orderBy('acadyear', 'desc')->pluck('acadyear', 'acadyear')->prepend('All Years', ''));

                // Semester
                $filter->equal('semester', 'Semester')->select([
                    '' => 'All Semesters',
                    1 => 'Semester 1',
                    2 => 'Semester 2',
                    3 => 'Semester 3',
                ]);

                // Study year
                $filter->equal('studyyear', 'Study Year')->select([
                    '' => 'All Years',
                    1 => 'Year 1',
                    2 => 'Year 2',
                    3 => 'Year 3',
                    4 => 'Year 4',
                    5 => 'Year 5',
                    6 => 'Year 6',
                    7 => 'Year 7',
                ]);
            });

            /*
            | Column 3: Grade & Score Filters
            */
            $filter->column(1/3, function ($filter) {
                // Grade
                $filter->equal('grade', 'Grade')->select([
                    '' => 'All Grades',
                    'A' => 'A',
                    'B+' => 'B+',
                    'B' => 'B',
                    'C+' => 'C+',
                    'C' => 'C',
                    'D+' => 'D+',
                    'D' => 'D',
                    'E' => 'E',
                    'F' => 'F',
                ]);

                // Score range
                $filter->between('score', 'Score Range')->integer();

                // Pass/Fail status
                $filter->where(function ($query) {
                    if ($this->input === 'pass') {
                        $query->whereIn('grade', MruResult::PASSING_GRADES);
                    } elseif ($this->input === 'fail') {
                        $query->whereIn('grade', MruResult::FAILING_GRADES);
                    }
                }, 'Result Status')->select([
                    '' => 'All Results',
                    'pass' => 'Pass Only',
                    'fail' => 'Fail Only',
                ]);

                // GPA range
                $filter->between('gpa', 'GPA Range')->decimal();
            });
        });

        /*
        |----------------------------------------------------------------------
        | STATISTICS HEADER
        |----------------------------------------------------------------------
        */

        $grid->header(function () {
            return $this->renderStatistics();
        });

        /*
        |----------------------------------------------------------------------
        | GRID ACTIONS & EXPORT
        |----------------------------------------------------------------------
        */

        // Disable batch actions for safety
        $grid->disableBatchActions();

        // Configure row actions
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        // Export functionality
        $grid->export(function ($export) {
            $export->filename('Academic_Results_' . date('Y-m-d_His'));
            
            $export->column('ID', 'ID');
            $export->column('regno', 'Registration No');
            $export->column('courseid', 'Course ID');
            $export->column('acad', 'Academic Year');
            $export->column('semester', 'Semester');
            $export->column('studyyear', 'Study Year');
            $export->column('progid', 'Programme');
            $export->column('score', 'Score');
            $export->column('grade', 'Grade');
            $export->column('gradept', 'Grade Points');
            $export->column('gpa', 'GPA');
            $export->column('CreditUnits', 'Credit Units');
            $export->column('result_comment', 'Comment');
        });

        // Rows per page
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
        $show = new Show(MruResult::findOrFail($id));

        /*
        |----------------------------------------------------------------------
        | STUDENT INFORMATION
        |----------------------------------------------------------------------
        */

        $show->panel()
            ->title('Student Information')
            ->style('primary')
            ->tools(function ($tools) {
                $tools->disableDelete();
            });

        $show->field('regno', __('Registration Number'));
        
        $show->field('student_info', __('Student Details'))->as(function () {
            $student = MruStudent::where('regno', $this->regno)->first();
            if ($student) {
                return $student->firstname . ' ' . $student->othername . 
                       ' (' . $student->gender . ')';
            }
            return 'N/A';
        });

        /*
        |----------------------------------------------------------------------
        | COURSE INFORMATION
        |----------------------------------------------------------------------
        */

        $show->divider();
        $show->panel()
            ->title('Course Information')
            ->style('info');

        $show->field('courseid', __('Course ID'));
        
        $show->field('course_name', __('Course Name'))->as(function () {
            return $this->course ? $this->course->coursename : 'N/A';
        });
        
        $show->field('CreditUnits', __('Credit Units'));

        /*
        |----------------------------------------------------------------------
        | ACADEMIC INFORMATION
        |----------------------------------------------------------------------
        */

        $show->divider();
        $show->panel()
            ->title('Academic Information')
            ->style('success');

        $show->field('acad', __('Academic Year'));
        
        $show->field('semester', __('Semester'))->using([
            1 => 'Semester 1',
            2 => 'Semester 2',
            3 => 'Semester 3',
        ])->badge([
            1 => 'warning',
            2 => 'info',
            3 => 'primary',
        ]);
        
        $show->field('studyyear', __('Study Year'))->as(function ($year) {
            return 'Year ' . $year;
        });
        
        $show->field('progid', __('Programme ID'));
        
        $show->field('programme_name', __('Programme Name'))->as(function () {
            $programme = MruProgramme::where('progcode', $this->progid)->first();
            return $programme ? $programme->progname : 'N/A';
        });

        /*
        |----------------------------------------------------------------------
        | RESULTS SECTION
        |----------------------------------------------------------------------
        */

        $show->divider();
        $show->panel()
            ->title('Results & Performance')
            ->style('warning');

        $show->field('score', __('Score'))->as(function ($score) {
            return $score . '%';
        })->badge();
        
        $show->field('grade', __('Grade'))->badge([
            'A' => 'success',
            'B+' => 'info',
            'B' => 'info',
            'C+' => 'warning',
            'C' => 'warning',
            'D+' => 'default',
            'D' => 'default',
            'E' => 'danger',
            'F' => 'danger',
        ]);
        
        $show->field('gradept', __('Grade Points'))->as(function ($pts) {
            return number_format($pts, 2);
        });
        
        $show->field('gpa', __('GPA'))->as(function ($gpa) {
            return number_format($gpa, 2);
        });
        
        $show->field('result_comment', __('Comment'));

        /*
        |----------------------------------------------------------------------
        | STATUS
        |----------------------------------------------------------------------
        */

        $show->divider();
        
        $show->field('status', __('Result Status'))->as(function () {
            return $this->is_passing ? 'PASS' : 'FAIL';
        })->badge([
            'PASS' => 'success',
            'FAIL' => 'danger',
        ]);

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruResult());

        // Disable timestamps
        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();

        /*
        |----------------------------------------------------------------------
        | STUDENT & COURSE INFO
        |----------------------------------------------------------------------
        */

        $form->text('regno', __('Student Registration Number'))
            ->rules('required|max:85')
            ->placeholder('e.g., 22/U/BSAF/0269/K/WKD')
            ->help('Enter the student registration number');

        $form->text('courseid', __('Course ID'))
            ->rules('required|max:25')
            ->placeholder('e.g., BBM 2201')
            ->help('Enter the course code');

        $form->select('progid', __('Programme'))
            ->options(function () {
                return MruProgramme::orderBy('progname')
                    ->pluck('progname', 'progcode');
            })
            ->rules('required|max:25')
            ->help('Select the programme');

        /*
        |----------------------------------------------------------------------
        | ACADEMIC DETAILS
        |----------------------------------------------------------------------
        */

        $form->divider();

        $form->select('acad', __('Academic Year'))
            ->options(function () {
                return MruAcademicYear::orderBy('acadyear', 'desc')
                    ->pluck('acadyear', 'acadyear');
            })
            ->rules('required|max:25')
            ->default(MruAcademicYear::getCurrentAcademicYear())
            ->help('Select academic year');

        $form->select('semester', __('Semester'))
            ->options([
                1 => 'Semester 1',
                2 => 'Semester 2',
                3 => 'Semester 3',
            ])
            ->rules('required|in:1,2,3')
            ->default(1);

        $form->number('studyyear', __('Study Year'))
            ->min(1)
            ->max(7)
            ->rules('required|integer|min:1|max:7')
            ->default(1)
            ->help('Enter the year of study (1-7)');

        $form->decimal('CreditUnits', __('Credit Units'))
            ->rules('required|numeric|min:0|max:10')
            ->default(3)
            ->help('Enter the credit units for this course');

        /*
        |----------------------------------------------------------------------
        | RESULTS
        |----------------------------------------------------------------------
        */

        $form->divider();

        $form->number('score', __('Score'))
            ->min(0)
            ->max(100)
            ->rules('required|integer|min:0|max:100')
            ->help('Enter score (0-100)');

        $form->select('grade', __('Grade'))
            ->options([
                'A' => 'A (Excellent)',
                'B+' => 'B+ (Very Good)',
                'B' => 'B (Good)',
                'C+' => 'C+ (Satisfactory)',
                'C' => 'C (Fair)',
                'D+' => 'D+ (Pass)',
                'D' => 'D (Pass)',
                'E' => 'E (Fail)',
                'F' => 'F (Fail)',
            ])
            ->rules('required|in:A,B+,B,C+,C,D+,D,E,F')
            ->help('Select the letter grade');

        $form->decimal('gradept', __('Grade Points'))
            ->rules('required|numeric|min:0|max:5')
            ->help('Enter grade points (0-5)');

        $form->decimal('gpa', __('GPA'))
            ->rules('nullable|numeric|min:0|max:5')
            ->help('GPA value (optional)');

        $form->text('result_comment', __('Comment'))
            ->rules('nullable|max:25')
            ->help('Optional comment on the result');

        /*
        |----------------------------------------------------------------------
        | FORM CALLBACKS
        |----------------------------------------------------------------------
        */

        $form->saving(function (Form $form) {
            // Auto-calculate GPA if not provided
            if (empty($form->gpa) && !empty($form->gradept) && !empty($form->CreditUnits)) {
                $form->gpa = $form->gradept;
            }

            // Validate student exists
            $studentExists = MruStudent::where('regno', $form->regno)->exists();
            if (!$studentExists) {
                admin_error('Error', 'Student with registration number ' . $form->regno . ' does not exist.');
                return back()->withInput();
            }

            // Validate course exists
            $courseExists = MruCourse::where('courseID', $form->courseid)->exists();
            if (!$courseExists) {
                admin_warning('Warning', 'Course ' . $form->courseid . ' not found in courses database.');
            }
        });

        // Tools
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        return $form;
    }

    /**
     * Display statistics in the grid header
     *
     * @return string
     */
    protected function renderStatistics()
    {
        // Get current filtered query
        $query = request()->all();
        
        // Build base query with filters
        $resultsQuery = MruResult::query();
        
        // Apply filters
        if (isset($query['regno'])) {
            $resultsQuery->where('regno', 'like', '%' . $query['regno'] . '%');
        }
        if (isset($query['courseid'])) {
            $resultsQuery->where('courseid', 'like', '%' . $query['courseid'] . '%');
        }
        if (isset($query['acad'])) {
            $resultsQuery->where('acad', $query['acad']);
        }
        if (isset($query['semester'])) {
            $resultsQuery->where('semester', $query['semester']);
        }
        if (isset($query['studyyear'])) {
            $resultsQuery->where('studyyear', $query['studyyear']);
        }
        if (isset($query['grade'])) {
            $resultsQuery->where('grade', $query['grade']);
        }
        if (isset($query['progid'])) {
            $resultsQuery->where('progid', $query['progid']);
        }

        // Calculate statistics
        $totalResults = $resultsQuery->count();
        $passedResults = (clone $resultsQuery)->passing()->count();
        $failedResults = (clone $resultsQuery)->failing()->count();
        $averageScore = round((clone $resultsQuery)->avg('score') ?? 0, 2);
        $averageGPA = round((clone $resultsQuery)->avg('gpa') ?? 0, 2);
        $uniqueStudents = (clone $resultsQuery)->distinct('regno')->count('regno');

        $passRate = $totalResults > 0 ? round(($passedResults / $totalResults) * 100, 2) : 0;
        $failRate = $totalResults > 0 ? round(($failedResults / $totalResults) * 100, 2) : 0;

        // Grade distribution
        $gradeDistribution = (clone $resultsQuery)
            ->select('grade', DB::raw('count(*) as count'))
            ->groupBy('grade')
            ->pluck('count', 'grade')
            ->toArray();

        // Render statistics
        return '
        <style>
            .results-stats { margin: 10px 0 15px 0; display: flex; gap: 10px; flex-wrap: wrap; }
            .stat-box { background: #fff; border: 1px solid #d2d6de; border-radius: 3px; padding: 10px 15px; min-width: 140px; flex: 1; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }
            .stat-box .stat-label { font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 4px; font-weight: 600; }
            .stat-box .stat-value { font-size: 20px; font-weight: bold; color: #333; }
            .stat-box .stat-sub { font-size: 11px; color: #999; margin-top: 2px; }
            .stat-box.stat-primary { border-left: 4px solid #3c8dbc; }
            .stat-box.stat-success { border-left: 4px solid #00a65a; }
            .stat-box.stat-danger { border-left: 4px solid #dd4b39; }
            .stat-box.stat-warning { border-left: 4px solid #f39c12; }
            .stat-box.stat-info { border-left: 4px solid #00c0ef; }
            .stat-box.stat-purple { border-left: 4px solid #605ca8; }
            .grade-dist { margin-top: 10px; padding: 8px; background: #f5f5f5; border-radius: 3px; font-size: 11px; }
            .grade-dist span { margin-right: 12px; }
        </style>
        <div class="results-stats">
            <div class="stat-box stat-primary">
                <div class="stat-label"><i class="fa fa-database"></i> Total Results</div>
                <div class="stat-value">' . number_format($totalResults) . '</div>
                <div class="stat-sub">' . number_format($uniqueStudents) . ' students</div>
            </div>
            <div class="stat-box stat-success">
                <div class="stat-label"><i class="fa fa-check-circle"></i> Passed</div>
                <div class="stat-value">' . number_format($passedResults) . '</div>
                <div class="stat-sub">' . $passRate . '% pass rate</div>
            </div>
            <div class="stat-box stat-danger">
                <div class="stat-label"><i class="fa fa-times-circle"></i> Failed</div>
                <div class="stat-value">' . number_format($failedResults) . '</div>
                <div class="stat-sub">' . $failRate . '% fail rate</div>
            </div>
            <div class="stat-box stat-info">
                <div class="stat-label"><i class="fa fa-bar-chart"></i> Avg Score</div>
                <div class="stat-value">' . $averageScore . '%</div>
                <div class="stat-sub">Mean performance</div>
            </div>
            <div class="stat-box stat-purple">
                <div class="stat-label"><i class="fa fa-graduation-cap"></i> Avg GPA</div>
                <div class="stat-value">' . number_format($averageGPA, 2) . '</div>
                <div class="stat-sub">Out of 5.0</div>
            </div>
        </div>
        ' . (count($gradeDistribution) > 0 ? '
        <div class="grade-dist">
            <strong>Grade Distribution:</strong> ' . 
            implode(' ', array_map(function($grade, $count) {
                $colors = [
                    'A' => '#00a65a', 'B+' => '#00c0ef', 'B' => '#00c0ef',
                    'C+' => '#f39c12', 'C' => '#f39c12',
                    'D+' => '#999', 'D' => '#999',
                    'E' => '#dd4b39', 'F' => '#dd4b39',
                ];
                $color = $colors[$grade] ?? '#666';
                return '<span style="color: ' . $color . '; font-weight: bold;">' . 
                       $grade . ': ' . number_format($count) . '</span>';
            }, array_keys($gradeDistribution), $gradeDistribution)) . '
        </div>' : '');
    }

    /**
     * Generate Summary Reports Page
     * Shows interface to generate VC List, Dean List, Pass Cases, etc.
     */
    public function summaryReports()
    {
        $acad = request('acad');
        $semester = request('semester');
        $progid = request('progid');
        $studyyear = request('studyyear');

        return view('admin.results.summary-reports', compact('acad', 'semester', 'progid', 'studyyear'));
    }

    /**
     * Generate VC's List PDF
     * Students with CGPA 4.40 - 5.00
     */
    public function generateVCList()
    {
        $params = $this->getSummaryParams();
        
        $students = $this->getPerformanceList(4.40, 5.00, $params);
        
        return $this->generatePDF('VC\'s List', $students, $params);
    }

    /**
     * Generate Dean's List PDF
     * Students with CGPA 4.00 - 4.39
     */
    public function generateDeansList()
    {
        $params = $this->getSummaryParams();
        
        $students = $this->getPerformanceList(4.00, 4.39, $params);
        
        return $this->generatePDF('Dean\'s List', $students, $params);
    }

    /**
     * Generate Pass Cases PDF
     * Students who passed all courses
     */
    public function generatePassCases()
    {
        $params = $this->getSummaryParams();
        
        $students = $this->getPassCases($params);
        
        return $this->generatePDF('Pass Cases - Normal Progress', $students, $params);
    }

    /**
     * Generate Retake Cases PDF
     * Students who failed at least one course
     */
    public function generateRetakeCases()
    {
        $params = $this->getSummaryParams();
        
        $students = $this->getRetakeCases($params);
        
        return $this->generatePDF('Retake Cases', $students, $params);
    }

    /**
     * Get summary report parameters from request
     */
    private function getSummaryParams()
    {
        return [
            'acad' => request('acad'),
            'semester' => request('semester'),
            'progid' => request('progid'),
            'studyyear' => request('studyyear'),
        ];
    }

    /**
     * Get performance list (VC/Dean)
     * Calculates CGPA and filters by range
     */
    private function getPerformanceList($cgpaMin, $cgpaMax, $params)
    {
        $query = DB::table('acad_results as r')
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
            ->whereNotNull('r.regno')
            ->groupBy('r.regno', 's.entryno', 's.othername', 's.firstname', 's.gender', 'r.progid');

        // Apply filters
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

        $results = $query->get();

        // Filter by CGPA range and sort
        return $results->filter(function($student) use ($cgpaMin, $cgpaMax) {
            return $student->cgpa >= $cgpaMin && $student->cgpa <= $cgpaMax;
        })->sortByDesc('cgpa')->values();
    }

    /**
     * Get pass cases - students who passed all courses
     */
    private function getPassCases($params)
    {
        // Get program level to determine pass threshold
        $programLevel = null;
        if (!empty($params['progid'])) {
            $prog = MruProgramme::where('progcode', $params['progid'])->first();
            $programLevel = $prog ? $prog->proglev : null;
        }

        // Pass threshold: 50 for undergrad, 60 for postgrad
        $passThreshold = ($programLevel && $programLevel >= 4) ? 60 : 50;

        // Get all students in the filters
        $allStudentsQuery = DB::table('acad_results as r')
            ->join('acad_student as s', 's.regno', '=', 'r.regno')
            ->select('r.regno', 's.entryno', DB::raw("CONCAT(s.othername, ' ', s.firstname) as studname"), 
                    's.gender', 'r.progid')
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

        // Get students with any failing grade
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

        $failedRegnos = $failedStudentsQuery->pluck('regno')->toArray();

        // Return students NOT in failed list
        return $allStudentsQuery
            ->whereNotIn('r.regno', $failedRegnos)
            ->groupBy('r.regno', 's.entryno', 's.othername', 's.firstname', 's.gender', 'r.progid')
            ->orderBy('studname')
            ->get();
    }

    /**
     * Get retake cases - students who failed at least one course
     */
    private function getRetakeCases($params)
    {
        // Get program level to determine pass threshold
        $programLevel = null;
        if (!empty($params['progid'])) {
            $prog = MruProgramme::where('progcode', $params['progid'])->first();
            $programLevel = $prog ? $prog->proglev : null;
        }

        // Pass threshold: 50 for undergrad, 60 for postgrad
        $passThreshold = ($programLevel && $programLevel >= 4) ? 60 : 50;

        $query = DB::table('acad_results as r')
            ->join('acad_student as s', 's.regno', '=', 'r.regno')
            ->select('r.regno', 's.entryno', DB::raw("CONCAT(s.othername, ' ', s.firstname) as studname"),
                    's.gender', 'r.progid',
                    DB::raw("GROUP_CONCAT(DISTINCT CONCAT(r.courseid, ' (', r.grade, ')') SEPARATOR ', ') as failed_courses"))
            ->where('r.score', '<', $passThreshold)
            ->whereNotNull('r.regno');

        // Apply filters
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

        return $query
            ->groupBy('r.regno', 's.entryno', 's.othername', 's.firstname', 's.gender', 'r.progid')
            ->orderBy('studname')
            ->get();
    }

    /**
     * Generate PDF from student data
     */
    private function generatePDF($title, $students, $params)
    {
        $pdf = Pdf::loadView('admin.results.pdf-template', compact('title', 'students', 'params'));
        
        $pdf->setPaper('A4', 'portrait');
        
        $filename = str_replace(' ', '_', $title) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->stream($filename);
    }
}
