<?php

namespace App\Services;

use App\Models\MruResult;
use App\Models\MruStudent;
use App\Models\Enterprise;
use Illuminate\Support\Facades\DB;

/**
 * Student Academic Transcript Service
 * Generates comprehensive transcript with GPA, CGPA, credits, honors, warnings
 */
class StudentTranscriptService
{
    protected $student;
    protected $enterprise;
    protected $transcriptData = [];
    protected $gradePoints = [
        'A+' => 5.0, 'A' => 5.0,
        'B+' => 4.5, 'B' => 4.0,
        'C+' => 3.5, 'C' => 3.0,
        'D+' => 2.5, 'D' => 2.0,
        'E' => 1.0,
        'F' => 0.0,
    ];

    public function __construct($studentId)
    {
        $this->student = MruStudent::findOrFail($studentId);
        $this->enterprise = Enterprise::first();
        $this->loadTranscriptData();
    }

    /**
     * Load all transcript data
     */
    protected function loadTranscriptData()
    {
        // Get all results for this student
        $allResults = MruResult::where('regno', $this->student->regno)
            ->orderBy('acad')
            ->orderBy('semester')
            ->orderBy('studyyear')
            ->get();

        // Group by academic year, semester, and study year
        $grouped = $allResults->groupBy(function ($result) {
            return $result->acad . '|' . $result->semester . '|' . $result->studyyear;
        });

        $cumulativeCredits = 0;
        $cumulativePoints = 0;
        $allDistinctions = [];
        $allWarnings = [];

        foreach ($grouped as $key => $results) {
            list($academicYear, $semester, $studyYear) = explode('|', $key);

            // Get course details
            $courseIds = $results->pluck('courseid')->unique();
            $courses = DB::table('acad_course')
                ->whereIn('courseID', $courseIds)
                ->get()
                ->keyBy('courseID');

            $semesterCredits = 0;
            $semesterPoints = 0;
            $semesterCourses = [];
            $passedCount = 0;
            $failedCourses = [];

            foreach ($results as $result) {
                $course = $courses->get($result->courseid);
                $grade = strtoupper(trim($result->grade ?? ''));
                $credits = $course->credit ?? 3; // Default 3 credits if not specified
                
                // Calculate grade points
                $gradePoint = $this->gradePoints[$grade] ?? 0;
                $points = $gradePoint * $credits;

                $semesterCredits += $credits;
                $semesterPoints += $points;

                // Check if passed
                $isPassed = in_array($grade, ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D']);
                if ($isPassed) {
                    $passedCount++;
                } else {
                    $failedCourses[] = $course->courseName ?? $result->courseid;
                }

                // Check for distinction
                if (in_array($grade, ['A+', 'A'])) {
                    $allDistinctions[] = [
                        'course' => $course->courseName ?? $result->courseid,
                        'grade' => $grade,
                        'semester' => $academicYear . ' Sem ' . $semester,
                    ];
                }

                $semesterCourses[] = [
                    'code' => $result->courseid,
                    'name' => $course->courseName ?? 'N/A',
                    'credits' => $credits,
                    'grade' => $grade,
                    'gradePoint' => $gradePoint,
                    'points' => $points,
                    'isPassed' => $isPassed,
                ];
            }

            // Calculate semester GPA
            $semesterGPA = $semesterCredits > 0 ? round($semesterPoints / $semesterCredits, 2) : 0;

            // Update cumulative
            $cumulativeCredits += $semesterCredits;
            $cumulativePoints += $semesterPoints;
            $cgpa = $cumulativeCredits > 0 ? round($cumulativePoints / $cumulativeCredits, 2) : 0;

            // Check for academic warnings
            if ($semesterGPA < 2.0) {
                $allWarnings[] = [
                    'semester' => $academicYear . ' Sem ' . $semester,
                    'type' => 'Academic Probation',
                    'reason' => 'GPA below 2.0',
                ];
            }

            if (count($failedCourses) > 0) {
                $allWarnings[] = [
                    'semester' => $academicYear . ' Sem ' . $semester,
                    'type' => 'Failed Courses',
                    'reason' => count($failedCourses) . ' course(s) failed',
                ];
            }

            $this->transcriptData[] = [
                'academic_year' => $academicYear,
                'semester' => $semester,
                'study_year' => $studyYear,
                'courses' => $semesterCourses,
                'credits_earned' => $semesterCredits,
                'semester_gpa' => $semesterGPA,
                'cumulative_credits' => $cumulativeCredits,
                'cgpa' => $cgpa,
                'passed_count' => $passedCount,
                'failed_courses' => $failedCourses,
            ];
        }

        // Calculate honors
        $finalCGPA = count($this->transcriptData) > 0 
            ? $this->transcriptData[count($this->transcriptData) - 1]['cgpa'] 
            : 0;

        $honors = $this->determineHonors($finalCGPA);

        // Store summary data
        $this->summary = [
            'total_credits' => $cumulativeCredits,
            'final_cgpa' => $finalCGPA,
            'distinctions' => $allDistinctions,
            'honors' => $honors,
            'warnings' => $allWarnings,
        ];
    }

    /**
     * Determine graduation honors based on CGPA
     */
    protected function determineHonors($cgpa)
    {
        if ($cgpa >= 4.5) {
            return 'First Class Honours (Distinction)';
        } elseif ($cgpa >= 4.0) {
            return 'First Class Honours';
        } elseif ($cgpa >= 3.5) {
            return 'Second Class Honours (Upper Division)';
        } elseif ($cgpa >= 3.0) {
            return 'Second Class Honours (Lower Division)';
        } elseif ($cgpa >= 2.5) {
            return 'Pass';
        } else {
            return 'No Honours';
        }
    }

    /**
     * Generate transcript data for view
     */
    public function generate()
    {
        // Get programme details - use progid field which contains the progcode
        $programme = DB::table('acad_programme')
            ->where('progcode', $this->student->progid)
            ->first();

        // Get specialization details
        $specialization = DB::table('acad_specialisation')
            ->where('spec_id', $this->student->specialisation)
            ->first();

        return [
            'student' => $this->student,
            'enterprise' => $this->enterprise,
            'programme' => $programme,
            'specialization' => $specialization,
            'transcript_data' => $this->transcriptData,
            'summary' => $this->summary,
            'generated_date' => now(),
            'logo_path' => $this->getLogoPath(),
        ];
    }

    /**
     * Get logo path
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
}
