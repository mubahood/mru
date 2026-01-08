<?php

namespace App\Services;

use App\Models\MruResult;
use App\Models\MruAcademicResultExport;
use App\Models\MruStudent;
use App\Models\Enterprise;
use App\Helpers\IncompleteMarksTracker;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * MRU Academic Result PDF Export Service
 * Generates matrix-style grade sheets separated by specialization
 */
class MruAcademicResultPdfService
{
    protected $export;
    protected $studentsBySpecialization;
    protected $specializationData = [];
    protected $incompleteTracker;

    public function __construct(MruAcademicResultExport $export)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        
        $this->export = $export;
        $this->incompleteTracker = new IncompleteMarksTracker();
        $this->loadData();
    }

    /**
     * Generate and save PDF
     */
    public function generate()
    {
        $html = $this->generateHtml();
        
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
            'dpi' => 96,
            'enable_php' => false,
            'enable_javascript' => false,
        ]);

        return $pdf;
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

            // Filter out students with no marks at all and track incomplete students
            $studentsWithMarks = $students->filter(function($student) use ($results, $courses, $specName) {
                $studentResults = $results->get($student->regno, collect());
                
                // Skip students with no marks at all
                if ($studentResults->isEmpty()) {
                    return false;
                }
                
                // Use the helper to track incomplete students
                $this->incompleteTracker->trackStudent($student, $courses, $results, $specName);
                
                return true;
            });

            // Only add specialization if there are students with marks
            if ($studentsWithMarks->isNotEmpty()) {
                $this->specializationData[$spec] = [
                    'name' => $specName,
                    'students' => $studentsWithMarks,
                    'courses' => $courses,
                    'results' => $results,
                ];
            }
        }
    }

    /**
     * Get the results count
     */
    public function getResultsCount()
    {
        return collect($this->specializationData)->sum(fn($data) => $data['students']->count());
    }

    /**
     * Generate HTML for matrix-style PDF with separate tables per specialization
     */
    protected function generateHtml()
    {
        // Get dynamic enterprise data
        $ent = Enterprise::first();
        $institutionName = $ent ? strtoupper($ent->name) : 'MUTESA I ROYAL UNIVERSITY';
        $logoPath = $ent && $ent->logo ? public_path('storage/' . $ent->logo) : null;
        
        // Convert logo to base64 data URI (more reliable for DomPDF)
        $logoDataUri = null;
        if ($logoPath && file_exists($logoPath)) {
            $imageType = mime_content_type($logoPath);
            $imageData = base64_encode(file_get_contents($logoPath));
            $logoDataUri = "data:{$imageType};base64,{$imageData}";
        }
        
        $address = $ent ? $ent->address : '';
        $phone = $ent ? $ent->phone : '';
        $email = $ent ? $ent->email : '';
        
        // Programme name
        $progName = $this->export->programme ? $this->export->programme->progname : $this->export->programme_id;
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . e($this->export->export_name) . '</title>
            <style>
                @page {
                    size: A4 landscape;
                    margin: 8mm 6mm;
                }
                body {
                    font-family: "DejaVu Sans", Arial, sans-serif;
                    font-size: 7pt;
                    line-height: 1;
                    margin: 0;
                    padding: 0;
                }
                .header {
                    margin-bottom: 5px;
                    padding-bottom: 3px;
                    border-bottom: 2px solid #333;
                }
                .header-table {
                    width: 100%;
                    margin-bottom: 0;
                }
                .header-table td {
                    vertical-align: top;
                    padding: 0;
                }
                .header-logo-cell {
                    width: 50px;
                    text-align: left;
                }
                .header-logo {
                    max-width: 45px;
                    max-height: 45px;
                    height: auto;
                }
                .header-center {
                    text-align: center;
                    padding: 0 5px;
                }
                .header-spacer {
                    width: 50px;
                }
                .header h1 {
                    color: #1a5490;
                    font-size: 11pt;
                    margin: 0 0 1px 0;
                    line-height: 1;
                    font-weight: bold;
                }
                .header .enterprise-info {
                    font-size: 5pt;
                    color: #666;
                    margin: 1px 0 2px 0;
                }
                .header h2 {
                    font-size: 8pt;
                    margin: 2px 0 0 0;
                    line-height: 1;
                    font-weight: bold;
                    text-decoration: underline;
                }
                .info-section {
                    margin-bottom: 4px;
                    font-size: 6pt;
                    padding: 2px 3px;
                    background-color: #f5f5f5;
                    border: 1px solid #ddd;
                }
                .info-section p {
                    margin: 0;
                    display: inline-block;
                    margin-right: 10px;
                }
                .specialization-section {
                    margin-bottom: 10px;
                    page-break-inside: avoid;
                }
                .spec-title {
                    background-color: #1a5490;
                    color: white;
                    padding: 2px 5px;
                    font-size: 7pt;
                    font-weight: bold;
                    margin-bottom: 2px;
                }
                table.results-matrix {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 5px;
                    table-layout: auto;
                }
                table.results-matrix th {
                    background-color: #e8e8e8;
                    color: #333;
                    padding: 1px 2px;
                    border: 1px solid #ccc;
                    font-weight: bold;
                    font-size: 6pt;
                    vertical-align: top;
                }
                table.results-matrix th.student-info {
                    background-color: #d0d0d0;
                    font-size: 5pt;
                }
                table.results-matrix th.regno-col {
                    min-width: 35px;
                    max-width: 45px;
                    width: 40px;
                }
                table.results-matrix th.name-col {
                    min-width: 50px;
                    max-width: 80px;
                    width: 65px;
                }
                table.results-matrix th.status-col {
                    min-width: 35px;
                    max-width: 45px;
                    width: 40px;
                }
                table.results-matrix th.course-header {
                    background-color: #e8e8e8;
                    min-width: 25px;
                    max-width: 35px;
                    width: 30px;
                    font-size: 4pt;
                    line-height: 1.1;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    hyphens: auto;
                    padding: 1px;
                }
                table.results-matrix td {
                    padding: 1px;
                    border: 1px solid #ddd;
                    text-align: center;
                    font-size: 6pt;
                    max-width: 35px;
                    overflow: hidden;
                }
                table.results-matrix td.student-cell {
                    text-align: left;
                    padding: 1px 2px;
                    font-size: 5pt;
                    max-width: 80px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                table.results-matrix td.status-cell {
                    text-align: center;
                    padding: 1px;
                    font-size: 5pt;
                    font-weight: bold;
                }
                table.results-matrix td.status-pass {
                    background-color: #d4edda;
                    color: #155724;
                }
                table.results-matrix td.status-fail {
                    background-color: #f8d7da;
                    color: #721c24;
                }
                table.results-matrix td.status-incomplete {
                    background-color: #fff3cd;
                    color: #856404;
                }
                table.results-matrix tr:nth-child(even) {
                    background-color: #fafafa;
                }
                .footer {
                    margin-top: 4px;
                    font-size: 5pt;
                    text-align: center;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 2px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <table class="header-table">
                    <tbody>
                        <tr>
                            <td class="header-logo-cell">';
        
        if ($logoDataUri) {
            $html .= '<img src="' . $logoDataUri . '" class="header-logo" alt="Logo" />';
        }
        
        $html .= '</td>
                            <td class="header-center">
                                <h1>' . e($institutionName) . '</h1>';
        
        if ($address || $phone || $email) {
            $html .= '<div class="enterprise-info">';
            $enterpriseInfo = [];
            if ($address) $enterpriseInfo[] = $address;
            if ($phone) $enterpriseInfo[] = 'Tel: ' . $phone;
            if ($email) $enterpriseInfo[] = 'Email: ' . $email;
            $html .= implode(' | ', array_map('e', $enterpriseInfo));
            $html .= '</div>';
        }
        
        $html .= '<h2>' . e($this->export->export_name) . '</h2>
                            </td>
                            <td class="header-spacer"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="info-section">
                <p><strong>Programme:</strong> ' . e($progName) . '</p>
                <p><strong>Academic Year:</strong> ' . e($this->export->academic_year) . '</p>
                <p><strong>Semester:</strong> ' . e($this->export->semester) . '</p>
                <p><strong>Year of Study:</strong> Year ' . e($this->export->study_year) . '</p>';
        
        if (($this->export->minimum_passes_required ?? 0) > 0) {
            $html .= '<p><strong>Minimum Passes:</strong> ' . e($this->export->minimum_passes_required) . ' subjects</p>';
        }
        
        $html .= '<p><strong>Generated:</strong> ' . now()->format('d M Y H:i') . '</p>
            </div>';
            
        // Generate a separate table for each specialization
        foreach ($this->specializationData as $spec => $data) {
            $specName = $data['name'];
            $students = $data['students'];
            $courses = $data['courses'];
            $results = $data['results'];
            
            $html .= '
            <div class="specialization-section">
                <div class="spec-title">' . e($specName) . ' (' . $students->count() . ' Students)</div>
                
                <table class="results-matrix">
                    <thead>
                        <tr>
                            <th class="student-info regno-col" rowspan="2">REG NO</th>
                            <th class="student-info name-col" rowspan="2">NAME</th>
                            <th class="student-info status-col" rowspan="2">STATUS</th>';
            
            // Course code headers for this specialization
            foreach ($courses as $course) {
                $html .= '<th class="course-header">' . e($course->courseID) . '</th>';
            }
            
            $html .= '</tr>
                        <tr>';
            
            // Course name headers (flexible word wrap) for this specialization
            foreach ($courses as $course) {
                $courseName = $course->courseName ?? '';
                $html .= '<th class="course-header">' . e($courseName) . '</th>';
            }
            
            $html .= '</tr>';
            
            $html .= '
                    </thead>
                    <tbody>';;
            
            // Student rows for this specialization
            foreach ($students as $student) {
                $fullName = trim(($student->firstname ?? '') . ' ' . ($student->othername ?? ''));
                $studentName = $fullName ?: $student->regno;
                // Truncate long names to 25 chars
                if (mb_strlen($studentName) > 25) {
                    $studentName = mb_substr($studentName, 0, 25) . '.';
                }
                
                // Get results for this student
                $studentResults = $results->get($student->regno, collect());
                
                // Calculate pass/fail status
                $totalCourses = $courses->count();
                $coursesWithResults = 0;
                $coursesPassed = 0;
                $passingGrades = ['A', 'B', 'C', 'D', 'B+', 'C+', 'D+', 'A+'];
                
                foreach ($courses as $course) {
                    $result = $studentResults->get($course->courseID);
                    if ($result) {
                        $coursesWithResults++;
                        $grade = strtoupper(trim($result->grade ?? ''));
                        // Check if grade indicates pass
                        if (in_array($grade, $passingGrades) || preg_match('/^[A-D][+-]?$/i', $grade)) {
                            $coursesPassed++;
                        }
                    }
                }
                
                // Determine status
                $minRequired = $this->export->minimum_passes_required ?? 0;
                $status = 'N/A';
                $statusClass = '';
                
                if ($minRequired > 0) {
                    if ($coursesWithResults < $totalCourses) {
                        // Missing some results
                        $status = 'INCOMPLETE';
                        $statusClass = 'status-incomplete';
                    } elseif ($coursesPassed >= $minRequired) {
                        // Passed required number
                        $status = 'PASS';
                        $statusClass = 'status-pass';
                    } else {
                        // Failed to meet requirement
                        $status = 'FAIL';
                        $statusClass = 'status-fail';
                    }
                }
                
                $html .= '<tr>
                            <td class="student-cell">' . e($student->regno) . '</td>
                            <td class="student-cell">' . e($studentName) . '</td>
                            <td class="status-cell ' . $statusClass . '">' . e($status) . '</td>';
                
                // Add result for each course
                foreach ($courses as $course) {
                    $result = $studentResults->get($course->courseID);
                    
                    if ($result) {
                        $grade = $result->grade ?? 'N/A';
                        $score = $result->score ? "({$result->score})" : '';
                        $html .= '<td>' . e($grade) . ' ' . e($score) . '</td>';
                    } else {
                        $html .= '<td>-</td>';
                    }
                }
                
                $html .= '</tr>';
            }
            
            $html .= '</tbody>
                </table>
            </div>';
        }
        
        // Add course definitions section for tables with more than 20 courses
        $hasLargeTables = false;
        $allCourses = collect();
        
        foreach ($this->specializationData as $spec => $data) {
            if ($data['courses']->count() > 20) {
                $hasLargeTables = true;
                $allCourses = $allCourses->merge($data['courses']);
            }
        }
        
        if ($hasLargeTables) {
            // Remove duplicates by courseID
            $allCourses = $allCourses->unique('courseID')->sortBy('courseID');
            
            $html .= '
            <div class="course-definitions" style="margin-top: 15px; page-break-before: always;">
                <h3 style="font-size: 9pt; color: #1a5490; margin-bottom: 5px; border-bottom: 2px solid #1a5490; padding-bottom: 2px;">COURSE DEFINITIONS</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 6pt; margin-top: 5px;">
                    <thead>
                        <tr>
                            <th style="background-color: #e8e8e8; padding: 3px; border: 1px solid #ccc; text-align: left; width: 15%;">COURSE CODE</th>
                            <th style="background-color: #e8e8e8; padding: 3px; border: 1px solid #ccc; text-align: left; width: 85%;">COURSE NAME</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($allCourses as $course) {
                $html .= '<tr>
                            <td style="padding: 2px 3px; border: 1px solid #ddd;">' . e($course->courseID) . '</td>
                            <td style="padding: 2px 3px; border: 1px solid #ddd;">' . e($course->courseName ?? '') . '</td>
                        </tr>';
            }
            
            $html .= '</tbody>
                </table>
            </div>';
        }
        
        // Add Students with Incomplete Marks Summary Table
        if ($this->incompleteTracker->hasIncompleteStudents()) {
            $incompleteStudents = $this->incompleteTracker->getIncompleteStudents();
            
            $html .= '
            <div class="incomplete-summary" style="margin-top: 15px; page-break-before: always;">
                <h3 style="font-size: 10pt; color: #d32f2f; margin-bottom: 5px; border-bottom: 3px solid #d32f2f; padding-bottom: 3px; font-weight: bold;">
                    ⚠️ STUDENTS WITH INCOMPLETE MARKS
                </h3>
                <p style="font-size: 7pt; color: #666; margin: 5px 0 8px 0;">
                    The following students have submitted some course results but are missing marks for certain courses. 
                    Total students with incomplete marks: <strong>' . $this->incompleteTracker->getCount() . '</strong>
                </p>
                
                <table style="width: 100%; border-collapse: collapse; font-size: 6.5pt; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #1a5490; color: white;">
                            <th style="padding: 4px 3px; border: 1px solid #ccc; text-align: center; width: 3%;">No.</th>
                            <th style="padding: 4px 3px; border: 1px solid #ccc; text-align: left; width: 10%;">Reg No</th>
                            <th style="padding: 4px 3px; border: 1px solid #ccc; text-align: left; width: 18%;">Student Name</th>
                            <th style="padding: 4px 3px; border: 1px solid #ccc; text-align: left; width: 15%;">Specialization</th>
                            <th style="padding: 4px 3px; border: 1px solid #ccc; text-align: center; width: 6%;">Total Courses</th>
                            <th style="padding: 4px 3px; border: 1px solid #ccc; text-align: center; width: 6%;">Marks Obtained</th>
                            <th style="padding: 4px 3px; border: 1px solid #ccc; text-align: center; width: 6%;">Marks Missing</th>
                            <th style="padding: 4px 3px; border: 1px solid #ccc; text-align: left; width: 36%;">Missing Courses</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($incompleteStudents as $index => $student) {
                $rowColor = ($index % 2 == 0) ? '#ffffff' : '#f8f9fa';
                $missingCoursesStr = $student['missing_courses'];
                
                // Truncate if too long
                if (strlen($missingCoursesStr) > 120) {
                    $missingCoursesStr = substr($missingCoursesStr, 0, 120) . '...';
                }
                
                $html .= '<tr style="background-color: ' . $rowColor . ';">
                            <td style="padding: 3px; border: 1px solid #ddd; text-align: center;">' . ($index + 1) . '</td>
                            <td style="padding: 3px; border: 1px solid #ddd;">' . e($student['regno']) . '</td>
                            <td style="padding: 3px; border: 1px solid #ddd; font-size: 6pt;">' . e($student['name']) . '</td>
                            <td style="padding: 3px; border: 1px solid #ddd; font-size: 6pt;">' . e($student['specialization']) . '</td>
                            <td style="padding: 3px; border: 1px solid #ddd; text-align: center; font-weight: bold;">' . $student['total_courses'] . '</td>
                            <td style="padding: 3px; border: 1px solid #ddd; text-align: center; color: #2e7d32; font-weight: bold;">' . $student['marks_obtained'] . '</td>
                            <td style="padding: 3px; border: 1px solid #ddd; text-align: center; color: #d32f2f; font-weight: bold;">' . $student['marks_missing_count'] . '</td>
                            <td style="padding: 3px; border: 1px solid #ddd; font-size: 5.5pt;">' . e($missingCoursesStr) . '</td>
                        </tr>';
            }
            
            $html .= '</tbody>
                </table>
                
                <div style="margin-top: 8px; padding: 5px; background-color: #fff3cd; border-left: 4px solid #ffc107; font-size: 6.5pt;">
                    <strong>Note:</strong> These students need to submit marks for the missing courses listed above to complete their academic record for this semester.
                </div>
            </div>';
        }
        
        $html .= '
            <div class="footer">
                <p>Generated by MRU Academic Management System | ' . now()->format('d M Y H:i:s') . '</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}
