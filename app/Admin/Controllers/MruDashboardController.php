<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MruStudent;
use App\Models\MruCourse;
use App\Models\MruProgramme;
use App\Models\MruFaculty;
use App\Models\MruResult;
use App\Models\MruCourseRegistration;
use App\Models\MruAcademicYear;
use Illuminate\Http\Request;

class MruDashboardController extends Controller
{
    /**
     * Display dashboard with statistics
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get selected academic year or default to current year
        $selectedYear = $request->get('academic_year');
        
        if (!$selectedYear) {
            // Calculate current academic year based on current date
            $currentMonth = date('n'); // 1-12
            $currentYear = date('Y');
            
            // If we're in Jan-Aug, academic year is previous/current (e.g., 2025/2026)
            // If we're in Sep-Dec, academic year is current/next (e.g., 2025/2026)
            if ($currentMonth >= 9) {
                $selectedYear = $currentYear . '/' . ($currentYear + 1);
            } else {
                $selectedYear = ($currentYear - 1) . '/' . $currentYear;
            }
            
            // Verify this year exists in database, otherwise use most recent
            $yearExists = MruAcademicYear::where('acadyear', $selectedYear)->exists();
            if (!$yearExists) {
                $latestYear = MruAcademicYear::orderBy('acadyear', 'desc')->first();
                $selectedYear = $latestYear ? $latestYear->acadyear : $selectedYear;
            }
        }

        // Get selected semester (null means all semesters)
        $selectedSemester = $request->get('semester');

        // Get all academic years for dropdown
        $academicYears = MruAcademicYear::orderBy('acadyear', 'desc')->get();

        // Get available semesters
        $semesters = [
            '' => 'All Semesters',
            '1' => 'Semester 1',
            '2' => 'Semester 2',
            '3' => 'Semester 3 (Recess)'
        ];

        // Get statistics
        $stats = $this->getStatistics($selectedYear, $selectedSemester);

        // Get programme enrollments
        $programmeEnrollments = $this->getProgrammeEnrollments($selectedYear, $selectedSemester);

        // Get logged-in user's enterprise color
        $user = \Encore\Admin\Facades\Admin::user();
        $primaryColor = '#3c8dbc'; // Default
        $secondaryColor = '#00a65a'; // Default
        
        if ($user && $user->enterprise_id) {
            $enterprise = \App\Models\Enterprise::find($user->enterprise_id);
            if ($enterprise && $enterprise->color) {
                $primaryColor = $enterprise->color;
            }
            if ($enterprise && $enterprise->sec_color) {
                $secondaryColor = $enterprise->sec_color;
            }
        }

        return view('admin.mru-dashboard', [
            '_user_' => $user,
            '_menu_' => \Encore\Admin\Facades\Admin::menu(),
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'stats' => $stats,
            'programmeEnrollments' => $programmeEnrollments,
            'primaryColor' => $primaryColor,
            'secondaryColor' => $secondaryColor,
        ]);
    }

    /**
     * Get statistics for the selected academic year and semester
     *
     * @param string $academicYear
     * @param string|null $semester
     * @return array
     */
    protected function getStatistics($academicYear, $semester = null)
    {
        // Build base queries
        $resultsQuery = MruResult::where('acad', $academicYear);
        $registrationsQuery = MruCourseRegistration::where('acad_year', $academicYear);

        // Apply semester filter if provided
        if ($semester !== null && $semester !== '') {
            $resultsQuery->where('semester', $semester);
            $registrationsQuery->where('semester', $semester);
        }

        // Total results for the academic year/semester
        $totalResults = (clone $resultsQuery)->count();
        
        // Passing results (grade not F, W, I, or null)
        $passingResults = (clone $resultsQuery)
            ->whereNotIn('grade', ['F', 'W', 'I'])
            ->whereNotNull('grade')
            ->count();
        
        // Failing results
        $failingResults = (clone $resultsQuery)
            ->where('grade', 'F')
            ->count();
        
        // Calculate pass rate
        $passRate = $totalResults > 0 ? number_format(($passingResults / $totalResults) * 100, 2) : 0;
        
        // Calculate average GPA
        $averageGpa = (clone $resultsQuery)
            ->whereNotNull('gpa')
            ->where('gpa', '>', 0)
            ->avg('gpa');
        $averageGpa = $averageGpa ? number_format($averageGpa, 2) : 0;

        // Unique students who registered at least one course in the selected year/semester
        $studentsRegistered = (clone $registrationsQuery)
            ->distinct('regno')
            ->count('regno');
        
        // Course registrations count
        $courseRegistrations = (clone $registrationsQuery)->count();
        
        return [
            'total_students' => MruStudent::count(),
            'total_courses' => MruCourse::count(),
            'total_programmes' => MruProgramme::count(),
            'total_faculties' => MruFaculty::count(),
            'students_registered' => $studentsRegistered,
            'course_registrations' => $courseRegistrations,
            'total_results' => $totalResults,
            'passing_results' => $passingResults,
            'failing_results' => $failingResults,
            'pass_rate' => $passRate,
            'average_gpa' => $averageGpa,
        ];
    }

    /**
     * Get programme enrollments for the selected academic year and semester
     *
     * @param string $academicYear
     * @param string|null $semester
     * @return \Illuminate\Support\Collection
     */
    protected function getProgrammeEnrollments($academicYear, $semester = null)
    {
        $query = \Illuminate\Support\Facades\DB::table('acad_course_registration as acr')
            ->leftJoin('acad_programme as p', 'acr.prog_id', '=', 'p.progcode')
            ->select(
                'acr.prog_id',
                'p.progname',
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT acr.regno) as student_count')
            )
            ->where('acr.acad_year', $academicYear);

        // Apply semester filter if provided
        if ($semester !== null && $semester !== '') {
            $query->where('acr.semester', $semester);
        }

        return $query->groupBy('acr.prog_id', 'p.progname')
            ->orderByRaw('COUNT(DISTINCT acr.regno) DESC')
            ->get();
    }
}
