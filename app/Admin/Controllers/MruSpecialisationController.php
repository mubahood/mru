<?php

namespace App\Admin\Controllers;

use App\Models\MruSpecialisation;
use App\Models\MruProgramme;
use App\Models\TempMruSpecializationHasCourse;
use App\Services\SpecializationCurriculumPdfService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

/**
 * MruSpecialisationController
 * 
 * Laravel Admin controller for managing academic specializations/majors in the MRU system.
 * Specializations represent specific areas of focus within programmes (e.g., History & Luganda for BAED).
 * 
 * Features:
 * - Grid view with specialization details and programme information
 * - Filtering by programme, name, abbreviation
 * - Simple form for creating/editing specializations
 * - Detail view with programme relationship
 * 
 * @package App\Admin\Controllers
 * @author MRU Development Team
 * @version 1.0.0
 * @created 2025-12-29
 */
class MruSpecialisationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MRU Specializations';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruSpecialisation());

        // Eager load programme relationship
        $grid->model()->with('programme')
            ->where('prog_id', '!=', '-') // Exclude placeholder
            ->orderBy('prog_id', 'asc')
            ->orderBy('spec', 'asc');

        // Configure grid
        $grid->disableBatchActions();

        /*
        |--------------------------------------------------------------------------
        | Grid Actions
        |--------------------------------------------------------------------------
        */

        // Add custom action to generate curriculum
        $grid->actions(function ($actions) {
            $specId = $actions->getKey();
            $url = admin_url('mru-specialisations/' . $specId . '/generate-curriculum');
            $actions->append('<a href="' . $url . '" class="btn btn-xs btn-info" title="Generate Curriculum"><i class="fa fa-magic"></i> Generate Curriculum</a>');
        });

        /*
        |--------------------------------------------------------------------------
        | Grid Columns
        |--------------------------------------------------------------------------
        */

        $grid->column('spec_id', __('ID'))->sortable();

        $grid->column('prog_id', __('Programme Code'))->sortable();

        $grid->column('programme.progname', __('Programme Name'))
            ->display(function () {
                return $this->programme ? $this->programme->progname : $this->prog_id;
            });

        $grid->column('spec', __('Specialization Name'))->sortable();

        $grid->column('abbrev', __('Abbreviation'))->sortable();

        $grid->column('courses_count', __('Total'))
            ->display(function () {
                $count = \App\Models\MruSpecializationHasCourse::where('specialization_id', $this->spec_id)->count();
                return $count > 0 
                    ? "<span class='badge badge-success'>{$count}</span>" 
                    : "<span class='badge badge-secondary'>0</span>";
            })
            ->sortable();

        $grid->column('temp_courses_count', __('Temp'))
            ->display(function () {
                $count = \App\Models\TempMruSpecializationHasCourse::where('specialization_id', $this->spec_id)->count();
                return $count > 0 
                    ? "<span class='badge badge-warning'>{$count}</span>" 
                    : "<span class='badge badge-secondary'>0</span>";
            });

        $grid->column('sem1_count', __('Sem 1'))
            ->display(function () {
                $count = \App\Models\MruSpecializationHasCourse::where('specialization_id', $this->spec_id)
                    ->where('semester', 1)->count();
                return $count > 0 ? "<span class='badge badge-info'>{$count}</span>" : "-";
            });

        $grid->column('sem2_count', __('Sem 2'))
            ->display(function () {
                $count = \App\Models\MruSpecializationHasCourse::where('specialization_id', $this->spec_id)
                    ->where('semester', 2)->count();
                return $count > 0 ? "<span class='badge badge-info'>{$count}</span>" : "-";
            });

        $grid->column('sem3_count', __('Sem 3'))
            ->display(function () {
                $count = \App\Models\MruSpecializationHasCourse::where('specialization_id', $this->spec_id)
                    ->where('semester', 3)->count();
                return $count > 0 ? "<span class='badge badge-info'>{$count}</span>" : "-";
            });

        $grid->column('sem4_count', __('Sem 4'))
            ->display(function () {
                $count = \App\Models\MruSpecializationHasCourse::where('specialization_id', $this->spec_id)
                    ->where('semester', 4)->count();
                return $count > 0 ? "<span class='badge badge-info'>{$count}</span>" : "-";
            });

        $grid->column('sem5_count', __('Sem 5'))
            ->display(function () {
                $count = \App\Models\MruSpecializationHasCourse::where('specialization_id', $this->spec_id)
                    ->where('semester', 5)->count();
                return $count > 0 ? "<span class='badge badge-info'>{$count}</span>" : "-";
            });

        $grid->column('sem6_count', __('Sem 6'))
            ->display(function () {
                $count = \App\Models\MruSpecializationHasCourse::where('specialization_id', $this->spec_id)
                    ->where('semester', 6)->count();
                return $count > 0 ? "<span class='badge badge-info'>{$count}</span>" : "-";
            });

        $grid->column('generate_curriculum', __('Generate'))
            ->display(function () {
                $url = admin_url('mru-specialisations/' . $this->spec_id . '/generate-curriculum');
                return "<a href='{$url}' target='_blank' class='btn btn-xs btn-info' title='Generate Curriculum'>
                    <i class='fa fa-magic'></i> Generate Curriculum
                </a>";
            });

        $grid->column('curriculum_pdf', __('Curriculum PDF'))
            ->display(function () {
                $url = admin_url('mru-specialisations/' . $this->spec_id . '/curriculum-pdf');
                return "<a href='{$url}' target='_blank' class='btn btn-xs btn-danger' title='Download Curriculum PDF'>
                    <i class='fa fa-file-pdf-o'></i> PDF
                </a>";
            });

        /*
        |--------------------------------------------------------------------------
        | Grid Filters
        |--------------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            // Programme filter
            $filter->equal('prog_id', 'Programme')
                ->select(MruProgramme::orderBy('progname')->pluck('progname', 'progcode'));

            // Specialization name search
            $filter->like('spec', 'Specialization Name');

            // Abbreviation search
            $filter->like('abbrev', 'Abbreviation');
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
        $show = new Show(MruSpecialisation::findOrFail($id));

        $show->field('spec_id', __('ID'));
        $show->field('prog_id', __('Programme Code'));
        $show->field('programme.progname', __('Programme Name'));
        $show->field('spec', __('Specialization Name'));
        $show->field('abbrev', __('Abbreviation'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruSpecialisation());

        /*
        |--------------------------------------------------------------------------
        | Form Fields
        |--------------------------------------------------------------------------
        */

        $form->select('prog_id', __('Programme'))
            ->options(MruProgramme::orderBy('progname')->pluck('progname', 'progcode'))
            ->rules('required|string|max:10')
            ->required();

        $form->text('spec', __('Specialization Name'))
            ->rules('required|string|max:250')
            ->required();

        $form->text('abbrev', __('Abbreviation'))
            ->rules('required|string|max:20')
            ->required();

        return $form;
    }

    /**
     * Display curriculum generation page for a specialization
     *
     * @param int $id Specialization ID
     * @return \Encore\Admin\Layout\Content
     */
    public function generateCurriculum($id)
    {
        $specialization = MruSpecialisation::with('programme')->findOrFail($id);
        
        // Analyze courses from acad_results for this programme
        $coursesFromResults = \Illuminate\Support\Facades\DB::table('acad_results as r')
            ->select(
                'r.courseid',
                'r.semester',
                'r.studyyear',
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT r.regno) as student_count'),
                \Illuminate\Support\Facades\DB::raw('MIN(r.acad) as first_year'),
                \Illuminate\Support\Facades\DB::raw('MAX(r.acad) as last_year')
            )
            ->where('r.progid', $specialization->prog_id)
            ->whereNotNull('r.courseid')
            ->whereNotNull('r.semester')
            ->whereNotNull('r.studyyear')
            ->where('r.courseid', '!=', '')
            ->groupBy('r.courseid', 'r.semester', 'r.studyyear')
            ->orderBy('r.studyyear', 'asc')
            ->orderBy('r.semester', 'asc')
            ->orderBy('r.courseid', 'asc')
            ->get();

        // Analyze each course
        $analysis = [];
        $summary = [
            'total_courses' => 0,
            'will_create' => 0,
            'already_exists' => 0,
            'invalid_courses' => 0,
        ];

        /**
         * DUPLICATE DETECTION AND FILTERING LOGIC
         * 
         * A course is marked as DUPLICATE (*) if:
         * 1. The same course CODE appears multiple times across different years/semesters
         * 2. The same course NAME appears multiple times (even with different codes)
         * 
         * IMPORTANT: Duplicates are EXCLUDED from display and will NOT be imported.
         * Only the first occurrence of each course (by code and name) will be processed.
         */
        
        // Track first occurrence of each course code and course name
        $seenCodes = [];
        $seenNames = [];
        $uniqueCoursesData = [];
        
        foreach ($coursesFromResults as $courseData) {
            $course = \App\Models\MruCourse::where('courseID', $courseData->courseid)->first();
            
            $isDuplicate = false;
            
            // Check if this course code has been seen before
            if (isset($seenCodes[$courseData->courseid])) {
                $isDuplicate = true;
            }
            
            // Check if this course name has been seen before
            if ($course && $course->courseName) {
                $normalizedName = strtoupper(trim($course->courseName));
                if (isset($seenNames[$normalizedName])) {
                    $isDuplicate = true;
                }
            }
            
            // Skip duplicates - only process first occurrence
            if ($isDuplicate) {
                continue;
            }
            
            // Mark this course code and name as seen
            $seenCodes[$courseData->courseid] = true;
            if ($course && $course->courseName) {
                $normalizedName = strtoupper(trim($course->courseName));
                $seenNames[$normalizedName] = true;
            }
            
            $uniqueCoursesData[] = $courseData;
        }

        foreach ($uniqueCoursesData as $courseData) {
            $course = \App\Models\MruCourse::where('courseID', $courseData->courseid)->first();
            
            /**
             * Check if course already exists in curriculum for this specialization
             * IMPORTANT: We check across ALL semesters and years to prevent duplicates
             * A course should only appear ONCE in the entire curriculum, regardless of semester/year
             */
            $existing = null;
            if ($course) {
                $existing = \App\Models\MruSpecializationHasCourse::where('specialization_id', $specialization->spec_id)
                    ->where(function($query) use ($courseData, $course) {
                        // Check by course code OR course name across ALL semesters
                        $query->where('course_code', $courseData->courseid);
                        if ($course->courseName) {
                            // Also check if any course with same name exists
                            $query->orWhereHas('course', function($q) use ($course) {
                                $q->where('courseName', $course->courseName);
                            });
                        }
                    })
                    ->first();
            }

            $status = !$course ? 'invalid' : ($existing ? 'exists' : 'will_create');
            
            $analysis[] = [
                'course_code' => $courseData->courseid,
                'course_name' => $course ? $course->courseName : 'NOT FOUND',
                'year' => $courseData->studyyear,
                'semester' => $courseData->semester,
                'credits' => $course ? $course->CreditUnit : 0,
                'student_count' => $courseData->student_count,
                'first_year' => $courseData->first_year,
                'last_year' => $courseData->last_year,
                'status' => $status,
                'existing_status' => $existing ? $existing->status : null,
                'existing_approval' => $existing ? $existing->approval_status : null,
            ];

            $summary['total_courses']++;
            if ($status === 'will_create') {
                $summary['will_create']++;
            } elseif ($status === 'exists') {
                $summary['already_exists']++;
            } else {
                $summary['invalid_courses']++;
            }
        }

        // Group by year first, then by semester for hierarchical display
        $groupedAnalysis = collect($analysis)
            ->groupBy('year')
            ->map(function ($yearCourses) {
                return $yearCourses->groupBy('semester')->sortKeys();
            })
            ->sortKeys();

        return Admin::content(function ($content) use ($specialization, $groupedAnalysis, $summary) {
            $content->header('Generate Curriculum');
            $content->description('Specialization: ' . $specialization->spec);
            $content->body(view('admin.mru.specializations.generate-curriculum', [
                'specialization' => $specialization,
                'groupedAnalysis' => $groupedAnalysis,
                'summary' => $summary,
            ]));
        });
    }

    /**
     * Process curriculum generation for a specialization
     *
     * @param int $id Specialization ID
     * @return \Encore\Admin\Layout\Content
     */
    public function processGenerateCurriculum($id)
    {
        $specialization = MruSpecialisation::with('programme')->findOrFail($id);
        
        // Initialize report data
        $report = [
            'specialization' => $specialization,
            'created' => [],
            'existing' => [],
            'skipped' => [],
            'errors' => [],
            'total_processed' => 0,
            'total_created' => 0,
            'total_existing' => 0,
            'total_skipped' => 0,
            'total_errors' => 0,
        ];

        try {
            /**
             * OPTIMIZED SQL QUERY - Join with acad_course to get all data in one query
             * This eliminates N+1 query problems and validates courses in SQL
             */
            $coursesFromResults = \Illuminate\Support\Facades\DB::table('acad_results as r')
                ->join('acad_course as c', 'r.courseid', '=', 'c.courseID')
                ->select(
                    'r.courseid',
                    'r.semester',
                    'r.studyyear',
                    'c.courseName',
                    'c.CreditUnit',
                    \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT r.regno) as student_count')
                )
                ->where('r.progid', $specialization->prog_id)
                ->whereNotNull('r.courseid')
                ->whereNotNull('r.semester')
                ->whereNotNull('r.studyyear')
                ->whereNotNull('c.courseName')
                ->where('r.courseid', '!=', '')
                ->where('c.courseName', '!=', '')
                ->where('r.semester', '>', 0)
                ->where('r.studyyear', '>', 0)
                ->where('r.studyyear', '<=', 6)
                ->whereIn('r.semester', [1, 2])
                ->groupBy('r.courseid', 'r.semester', 'r.studyyear', 'c.courseName', 'c.CreditUnit')
                ->orderBy('r.studyyear', 'asc')
                ->orderBy('r.semester', 'asc')
                ->orderBy('r.courseid', 'asc')
                ->get();

            if ($coursesFromResults->isEmpty()) {
                $report['errors'][] = [
                    'course_code' => 'SYSTEM',
                    'course_name' => 'NO DATA',
                    'year' => 'N/A',
                    'semester' => 'N/A',
                    'error' => 'No course data found in results for this programme.',
                ];
                $report['total_errors']++;
                
                return Admin::content(function ($content) use ($report) {
                    $content->header('Curriculum Generation Report');
                    $content->description('Import Failed');
                    $content->body(view('admin.mru.specializations.generate-curriculum-report', $report));
                });
            }
            
            $report['total_processed'] = count($coursesFromResults);

            /**
             * OPTIMIZED EXISTING RECORDS CHECK
             * Pre-load ALL existing temp records for this specialization in ONE query
             * Only skip what's already been created in temp table
             */
            $existingTempRecords = \App\Models\TempMruSpecializationHasCourse::where('specialization_id', $specialization->spec_id)
                ->get()
                ->keyBy(function($item) {
                    return $item->course_code . '_' . $item->year . '_' . $item->semester;
                });

            // Use database transaction for data integrity
            \Illuminate\Support\Facades\DB::beginTransaction();
            
            try {
                $recordsToInsert = [];
                
                foreach ($coursesFromResults as $courseData) {
                    try {
                        // VALIDATION 1: Verify semester and year values (already filtered in SQL but double-check)
                        if (!in_array($courseData->semester, [1, 2]) || $courseData->studyyear < 1 || $courseData->studyyear > 6) {
                            $report['skipped'][] = [
                                'course_code' => $courseData->courseid,
                                'course_name' => $courseData->courseName,
                                'year' => $courseData->studyyear,
                                'semester' => $courseData->semester,
                                'reason' => 'Invalid semester or year value',
                            ];
                            $report['total_skipped']++;
                            continue;
                        }

                        /**
                         * VALIDATION 2: Check if already exists in TEMP table
                         * Only skip if this exact combination exists in temp
                         */
                        $tempKey = $courseData->courseid . '_' . $courseData->studyyear . '_' . $courseData->semester;
                        $existingTemp = $existingTempRecords->get($tempKey);

                        if ($existingTemp) {
                            $report['existing'][] = [
                                'course_code' => $courseData->courseid,
                                'course_name' => $courseData->courseName,
                                'year' => $courseData->studyyear,
                                'semester' => $courseData->semester,
                                'existing_year' => $existingTemp->year,
                                'existing_semester' => $existingTemp->semester,
                                'status' => $existingTemp->status,
                                'approval_status' => $existingTemp->approval_status,
                            ];
                            $report['total_existing']++;
                            continue;
                        }

                        /**
                         * VALIDATION 3: Verify course exists in acad_course table
                         * Skip if course not found (already joined in query, but double-check)
                         */
                        if (empty($courseData->courseName)) {
                            $report['skipped'][] = [
                                'course_code' => $courseData->courseid,
                                'course_name' => 'MISSING',
                                'year' => $courseData->studyyear,
                                'semester' => $courseData->semester,
                                'reason' => 'Course not found in courses table',
                            ];
                            $report['total_skipped']++;
                            continue;
                        }

                        /**
                         * PREPARE RECORD FOR BULK INSERT INTO TEMP TABLE
                         * Generate everything regardless of duplicates in name or code
                         */
                        $credits = $courseData->CreditUnit ?? 3;
                        $credits = is_numeric($credits) && $credits > 0 ? (int)$credits : 3;
                        
                        $recordsToInsert[] = [
                            'specialization_id' => $specialization->spec_id,
                            'course_code' => $courseData->courseid,
                            'year' => (int)$courseData->studyyear,
                            'semester' => (int)$courseData->semester,
                            'credits' => $credits,
                            'type' => 'mandatory',
                            'lecturer_id' => null,
                            'status' => 'active',
                            'approval_status' => 'pending',
                            'rejection_reason' => null,
                            'prog_id' => $specialization->prog_id,
                            'faculty_code' => $specialization->programme->faculty_code ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        // Add to created report
                        $report['created'][] = [
                            'course_code' => $courseData->courseid,
                            'course_name' => $courseData->courseName,
                            'year' => $courseData->studyyear,
                            'semester' => $courseData->semester,
                            'credits' => $credits,
                            'type' => 'mandatory',
                            'status' => 'active',
                            'approval_status' => 'pending',
                            'student_count' => $courseData->student_count,
                        ];

                    } catch (\Exception $e) {
                        $report['errors'][] = [
                            'course_code' => $courseData->courseid ?? 'N/A',
                            'course_name' => $courseData->courseName ?? 'N/A',
                            'year' => $courseData->studyyear ?? 'N/A',
                            'semester' => $courseData->semester ?? 'N/A',
                            'error' => $e->getMessage(),
                        ];
                        $report['total_errors']++;
                    }
                }
                
                /**
                 * OPTIMIZED BULK INSERT INTO TEMP TABLE
                 * Insert all records at once instead of individual saves
                 */
                if (!empty($recordsToInsert)) {
                    try {
                        \Illuminate\Support\Facades\DB::table('temp_mru_specialization_courses')->insert($recordsToInsert);
                        $report['total_created'] = count($recordsToInsert);
                    } catch (\Exception $e) {
                        $report['errors'][] = [
                            'course_code' => 'BULK_INSERT',
                            'course_name' => 'SYSTEM',
                            'year' => 'N/A',
                            'semester' => 'N/A',
                            'error' => 'Bulk insert failed: ' . $e->getMessage(),
                        ];
                        $report['total_errors']++;
                        throw $e; // Re-throw to trigger rollback
                    }
                }
                
                // Commit transaction if no errors occurred
                if ($report['total_errors'] === 0) {
                    \Illuminate\Support\Facades\DB::commit();
                } else {
                    // Rollback if there were any errors
                    \Illuminate\Support\Facades\DB::rollBack();
                    $report['created'] = []; // Clear created since rollback happened
                    $report['total_created'] = 0;
                    $report['errors'][] = [
                        'course_code' => 'SYSTEM',
                        'course_name' => 'TRANSACTION',
                        'year' => 'N/A',
                        'semester' => 'N/A',
                        'error' => 'Transaction rolled back due to errors. No records were created.',
                    ];
                }
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                $report['created'] = []; // Clear created since rollback happened
                $report['total_created'] = 0;
                $report['errors'][] = [
                    'course_code' => 'SYSTEM',
                    'course_name' => 'DATABASE',
                    'year' => 'N/A',
                    'semester' => 'N/A',
                    'error' => 'Database transaction error: ' . $e->getMessage(),
                ];
                $report['total_errors']++;
            }

        } catch (\Exception $e) {
            $report['errors'][] = [
                'course_code' => 'N/A',
                'year' => 'N/A',
                'semester' => 'N/A',
                'error' => 'General error: ' . $e->getMessage(),
            ];
            $report['total_errors']++;
        }

        return Admin::content(function ($content) use ($report) {
            $content->header('Curriculum Generation Report');
            $content->description('Process completed');
            $content->body(view('admin.mru.specializations.generate-curriculum-report', $report));
        });
    }

    /**
     * Generate and download curriculum PDF for a specialization
     *
     * @param int $id Specialization ID
     * @return \Illuminate\Http\Response
     */
    public function curriculumPdf($id)
    {
        try {
            $specialization = MruSpecialisation::findOrFail($id);
            $curriculumService = new SpecializationCurriculumPdfService($id);
            $pdf = $curriculumService->generate();
            
            $filename = 'Curriculum_' . str_replace(' ', '_', $specialization->spec) . '_' . date('Ymd') . '.pdf';
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            admin_error('Error', 'Failed to generate curriculum PDF: ' . $e->getMessage());
            return back();
        }
    }
}
