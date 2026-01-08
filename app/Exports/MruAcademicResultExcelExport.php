<?php

namespace App\Exports;

use App\Models\MruResult;
use App\Models\MruAcademicResultExport;
use App\Models\MruStudent;
use App\Exports\MruAcademicResultSpecializationSheet;
use App\Helpers\IncompleteMarksTracker;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * MRU Academic Result Matrix Export
 * 
 * Generates separate Excel sheets per specialization:
 * - Each sheet = one specialization group
 * - X-axis (columns): Courses for that specialization
 * - Y-axis (rows): Students in that specialization
 * - Cells: Results (Grade/Score)
 */
class MruAcademicResultExcelExport implements WithMultipleSheets
{
    protected $export;
    protected $specializationData = [];
    protected $incompleteTracker;

    public function __construct(MruAcademicResultExport $export)
    {
        $this->export = $export;
        $this->incompleteTracker = new IncompleteMarksTracker();
        $this->loadData();
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $minRequired = $this->export->minimum_passes_required ?? 0;
        
        foreach ($this->specializationData as $spec => $data) {
            $specName = $spec ?: 'No Spec';
            $sheets[] = new MruAcademicResultSpecializationSheet(
                $specName,
                $data['students'],
                $data['courses'],
                $data['results'],
                $minRequired
            );
        }
        
        // Add incomplete students sheet if any exist
        if ($this->incompleteTracker->hasIncompleteStudents()) {
            $sheets[] = new \App\Exports\MruIncompleteStudentsSheet(
                $this->incompleteTracker->getIncompleteStudents()
            );
        }
        
        return $sheets;
    }

    /**
     * Load all necessary data grouped by specialization
     */
    protected function loadData()
    {
        // Build base query for students with results
        $studentQuery = MruResult::where('progid', $this->export->programme_id)
            ->where('acad', $this->export->academic_year)
            ->where('semester', $this->export->semester)
            ->where('studyyear', $this->export->study_year);
        
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
        $studentsBySpec = $allStudents->groupBy('specialisation');

        // For each specialization, get only courses that specialization has
        foreach ($studentsBySpec as $spec => $students) {
            $specRegnos = $students->pluck('regno');
            
            // Get courses only for this specialization's students
            $courseIds = MruResult::select('courseid')
                ->where('progid', $this->export->programme_id)
                ->where('acad', $this->export->academic_year)
                ->where('semester', $this->export->semester)
                ->where('studyyear', $this->export->study_year)
                ->whereIn('regno', $specRegnos)
                ->distinct()
                ->pluck('courseid');

            $courses = \DB::table('acad_course')
                ->select('courseID', 'courseName')
                ->whereIn('courseID', $courseIds)
                ->orderBy('courseID')
                ->get();

            // Get results for these students
            $results = MruResult::select('regno', 'courseid', 'grade', 'score')
                ->where('progid', $this->export->programme_id)
                ->where('acad', $this->export->academic_year)
                ->where('semester', $this->export->semester)
                ->where('studyyear', $this->export->study_year)
                ->whereIn('regno', $specRegnos)
                ->get()
                ->groupBy('regno')
                ->map(function ($studentResults) {
                    return $studentResults->keyBy('courseid');
                });

            // Filter out students with no marks and track incomplete students
            $studentsWithMarks = $students->filter(function($student) use ($results, $courses, $spec) {
                $studentResults = $results->get($student->regno, collect());
                
                // Skip students with no marks at all
                if ($studentResults->isEmpty()) {
                    return false;
                }
                
                // Get specialization name
                $specInfo = \DB::table('acad_specialisation')
                    ->select('spec')
                    ->where('spec_id', $spec)
                    ->first();
                $specName = $specInfo ? $specInfo->spec : 'Specialization ' . $spec;
                
                // Use the helper to track incomplete students
                $this->incompleteTracker->trackStudent($student, $courses, $results, $specName);
                
                return true;
            });

            // Only add specialization if there are students with marks
            if ($studentsWithMarks->isNotEmpty()) {
                $this->specializationData[$spec] = [
                    'students' => $studentsWithMarks,
                    'courses' => $courses,
                    'results' => $results,
                ];
            }
        }
    }

    /**
     * Get total student count across all specializations
     */
    public function getTotalStudentCount()
    {
        return collect($this->specializationData)->sum(fn($data) => $data['students']->count());
    }
}
