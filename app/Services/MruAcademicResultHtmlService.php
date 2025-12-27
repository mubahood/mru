<?php

namespace App\Services;

use App\Models\MruResult;
use App\Models\MruAcademicResultExport;
use App\Models\MruStudent;
use App\Models\Enterprise;

/**
 * MRU Academic Result HTML Export Service
 * Generates interactive HTML view with same logic as PDF/Excel
 */
class MruAcademicResultHtmlService
{
    protected $export;
    protected $studentsBySpecialization;
    protected $specializationData = [];
    protected $enterprise;

    public function __construct(MruAcademicResultExport $export)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        
        $this->export = $export;
        $this->enterprise = Enterprise::first();
        $this->loadData();
    }

    /**
     * Generate HTML data for blade view
     */
    public function generate()
    {
        return [
            'export' => $this->export,
            'enterprise' => $this->enterprise,
            'specializationData' => $this->specializationData,
            'logoPath' => $this->getLogoPath(),
        ];
    }

    /**
     * Get logo path for HTML display
     */
    protected function getLogoPath()
    {
        if ($this->enterprise && $this->enterprise->logo) {
            $logoPath = public_path('storage/' . $this->enterprise->logo);
            if (file_exists($logoPath)) {
                return asset('storage/' . $this->enterprise->logo);
            }
        }
        return null;
    }

    /**
     * Load all necessary data grouped by specialization
     */
    protected function loadData()
    {
        // Build base query for students with results
        $studentQuery = MruResult::where('progid', $this->export->programme_id)
            ->where('acad', $this->export->academic_year)
            ->where('semester', $this->export->semester);
        
        // Apply specialisation filter if specified
        if ($this->export->specialisation_id) {
            // Get students with this specialisation who have results
            $specStudentRegnos = MruStudent::where('specialisation', $this->export->specialisation_id)
                ->pluck('regno');
            
            $studentQuery->whereIn('regno', $specStudentRegnos);
        }
        
        $studentRegnos = $studentQuery->distinct()
            ->pluck('regno');

        // Get all student details with specialization, ordered
        $allStudents = MruStudent::select('ID', 'regno', 'firstname', 'othername', 'specialisation')
            ->whereIn('regno', $studentRegnos)
            ->orderBy($this->export->sort_by == 'student' ? 'firstname' : 'regno');
        
        // Apply range limit
        $start = max(1, $this->export->start_range ?? 1);
        $end = max($start, $this->export->end_range ?? 100);
        $limit = $end - $start + 1;
        
        $allStudents = $allStudents->skip($start - 1)
            ->take($limit)
            ->get();

        // Group students by specialization
        $this->studentsBySpecialization = $allStudents->groupBy('specialisation');

        // For each specialization, get only courses that specialization has
        foreach ($this->studentsBySpecialization as $spec => $students) {
            $specRegnos = $students->pluck('regno');
            
            // Get specialization name
            $specInfo = \DB::table('acad_specialisation')
                ->select('spec', 'abbrev')
                ->where('spec_id', $spec)
                ->first();
            
            $specName = $specInfo ? $specInfo->spec : 'Specialization ' . $spec;
            
            // Get courses only for this specialization's students in this study year
            $specCourseIds = MruResult::where('progid', $this->export->programme_id)
                ->where('acad', $this->export->academic_year)
                ->where('semester', $this->export->semester)
                ->where('studyyear', $this->export->study_year)
                ->whereIn('regno', $specRegnos)
                ->distinct()
                ->pluck('courseid');

            // Get course details
            $courses = \DB::table('acad_course')
                ->select('courseID', 'courseName')
                ->whereIn('courseID', $specCourseIds)
                ->orderBy('courseID')
                ->get();

            // Get all results for these students and courses
            $results = MruResult::where('progid', $this->export->programme_id)
                ->where('acad', $this->export->academic_year)
                ->where('semester', $this->export->semester)
                ->where('studyyear', $this->export->study_year)
                ->whereIn('regno', $specRegnos)
                ->whereIn('courseid', $specCourseIds)
                ->select('regno', 'courseid', 'grade', 'score')
                ->get()
                ->groupBy('regno');

            // Calculate status for each student
            $studentsWithStatus = $students->map(function ($student) use ($courses, $results) {
                $studentResults = $results->get($student->regno, collect());
                $status = $this->calculateStatus($student, $courses, $studentResults);
                
                // Add status to student object
                $student->status = $status['status'];
                $student->statusClass = $status['statusClass'];
                $student->coursesPassed = $status['coursesPassed'];
                $student->coursesWithResults = $status['coursesWithResults'];
                $student->totalCourses = $status['totalCourses'];
                
                return $student;
            });

            $this->specializationData[] = [
                'spec_id' => $spec,
                'spec_name' => $specName,
                'students' => $studentsWithStatus,
                'courses' => $courses,
                'results' => $results,
                'student_count' => $students->count(),
            ];
        }
    }

    /**
     * Calculate pass/fail status for a student
     * Uses same logic as PDF and Excel exports
     */
    protected function calculateStatus($student, $courses, $studentResults)
    {
        $totalCourses = $courses->count();
        $coursesWithResults = 0;
        $coursesPassed = 0;
        
        // Passing grades (same as PDF/Excel)
        $passingGrades = ['A', 'B', 'C', 'D', 'B+', 'C+', 'D+', 'A+'];

        foreach ($courses as $course) {
            $result = $studentResults->firstWhere('courseid', $course->courseID);
            
            if ($result) {
                $coursesWithResults++;
                $grade = strtoupper(trim($result->grade ?? ''));
                
                // Check if passing grade
                if (in_array($grade, $passingGrades) || preg_match('/^[A-D][+-]?$/i', $grade)) {
                    $coursesPassed++;
                }
            }
        }

        // Determine status (same logic as PDF/Excel)
        $minRequired = $this->export->minimum_passes_required ?? 0;
        $status = 'N/A';
        $statusClass = '';

        if ($minRequired > 0) {
            // Only mark INCOMPLETE if student has results but not for all courses
            // If student has NO results at all, they might not be registered for these courses
            if ($coursesWithResults > 0 && $coursesWithResults < $totalCourses) {
                $status = 'INCOMPLETE';
                $statusClass = 'status-incomplete';
            } elseif ($coursesPassed >= $minRequired) {
                $status = 'PASS';
                $statusClass = 'status-pass';
            } else {
                $status = 'FAIL';
                $statusClass = 'status-fail';
            }
        }

        return [
            'status' => $status,
            'statusClass' => $statusClass,
            'coursesPassed' => $coursesPassed,
            'coursesWithResults' => $coursesWithResults,
            'totalCourses' => $totalCourses,
        ];
    }

    /**
     * Get result for a specific student and course
     */
    public static function getStudentCourseResult($results, $regno, $courseId)
    {
        $studentResults = $results->get($regno, collect());
        $result = $studentResults->firstWhere('courseid', $courseId);
        
        if ($result) {
            $grade = strtoupper(trim($result->grade ?? ''));
            $score = $result->score ?? '';
            
            // Determine if passing grade
            $passingGrades = ['A', 'B', 'C', 'D', 'B+', 'C+', 'D+', 'A+'];
            $isPassing = in_array($grade, $passingGrades) || preg_match('/^[A-D][+-]?$/i', $grade);
            
            return [
                'grade' => $grade,
                'score' => $score,
                'isPassing' => $isPassing,
                'class' => $isPassing ? 'grade-pass' : 'grade-fail',
            ];
        }
        
        return [
            'grade' => '-',
            'score' => '',
            'isPassing' => false,
            'class' => 'grade-empty',
        ];
    }

    /**
     * Get total results count for marking export as completed
     */
    public function getResultsCount()
    {
        $total = 0;
        foreach ($this->specializationData as $specData) {
            $total += $specData['student_count'];
        }
        return $total;
    }
}
