<?php

namespace App\Services;

use App\Models\MruResult;
use App\Models\MruAcademicResultExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class MruAcademicResultPdfService
{
    protected $export;
    protected $results;
    protected $summary;

    public function __construct(MruAcademicResultExport $export)
    {
        $this->export = $export;
        $this->loadResults();
        $this->calculateSummary();
    }

    /**
     * Generate PDF and return the PDF instance
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
        ]);

        return $pdf;
    }

    /**
     * Load results based on export configuration
     */
    protected function loadResults()
    {
        $query = MruResult::query()
            ->select('acad_results.*')
            ->with(['course']);

        // Apply filters
        if ($this->export->academic_year) {
            $query->where('acad_results.acad', $this->export->academic_year);
        }

        if ($this->export->semester) {
            $query->where('acad_results.semester', $this->export->semester);
        }

        if ($this->export->programme_id) {
            $query->where('acad_results.progid', $this->export->programme_id);
        }

        if ($this->export->faculty_code) {
            $query->join('acad_programme', 'acad_results.progid', '=', 'acad_programme.progcode')
                  ->where('acad_programme.faculty_code', $this->export->faculty_code);
        }

        // Apply sorting
        switch ($this->export->sort_by) {
            case 'student':
                $query->orderBy('acad_results.regno');
                break;
            case 'course':
                $query->orderBy('acad_results.courseid');
                break;
            case 'grade':
                $query->orderBy('acad_results.grade');
                break;
            case 'programme':
                $query->orderBy('acad_results.progid');
                break;
        }

        // Limit to 2000 records for PDF performance
        $this->results = $query->limit(2000)->get();
    }

    /**
     * Calculate summary statistics
     */
    protected function calculateSummary()
    {
        $this->summary = [
            'total_students' => $this->results->pluck('regno')->unique()->count(),
            'total_records' => $this->results->count(),
            'total_courses' => $this->results->pluck('courseID')->unique()->count(),
            'average_mark' => round($this->results->avg('mark'), 2),
            'average_gpa' => round($this->results->avg('gpa'), 2),
            'grade_distribution' => $this->results->groupBy('grade')->map->count()->toArray(),
            'pass_rate' => $this->calculatePassRate(),
        ];
    }

    /**
     * Calculate pass rate
     */
    protected function calculatePassRate()
    {
        $total = $this->results->count();
        if ($total == 0) return 0;

        $passed = $this->results->whereIn('grade', ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D'])->count();
        return round(($passed / $total) * 100, 2);
    }

    /**
     * Get the results count
     */
    public function getResultsCount()
    {
        return $this->results->count();
    }

    /**
     * Generate HTML for PDF
     */
    protected function generateHtml()
    {        // Get company name from database
        $companyName = 'MUTEESA I ROYAL UNIVERSITY';
        try {
            $company = \DB::table('companyinfo')->first();
            if ($company && !empty($company->companyname)) {
                $companyName = $company->companyname;
            }
        } catch (\Exception $e) {
            // Fallback to default if query fails
        }
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . e($this->export->export_name) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 9px;
                    margin: 10px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 15px;
                }
                .header h1 {
                    color: #2E86AB;
                    font-size: 16px;
                    margin: 5px 0;
                }
                .header h2 {
                    font-size: 13px;
                    margin: 5px 0;
                }
                .info-section {
                    margin-bottom: 10px;
                    font-size: 10px;
                }
                .info-section p {
                    margin: 3px 0;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                    font-size: 8px;
                }
                table th {
                    background-color: #2E86AB;
                    color: white;
                    padding: 6px 4px;
                    text-align: left;
                    font-weight: bold;
                    border: 1px solid #000;
                }
                table td {
                    padding: 5px 4px;
                    border: 1px solid #ddd;
                }
                table tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .summary {
                    margin-top: 15px;
                    page-break-inside: avoid;
                }
                .summary h3 {
                    background-color: #2E86AB;
                    color: white;
                    padding: 6px;
                    margin: 10px 0 5px 0;
                    font-size: 11px;
                }
                .summary-grid {
                    display: table;
                    width: 100%;
                    margin-bottom: 10px;
                }
                .summary-row {
                    display: table-row;
                }
                .summary-label {
                    display: table-cell;
                    font-weight: bold;
                    padding: 4px;
                    width: 40%;
                    border: 1px solid #ddd;
                    background-color: #f5f5f5;
                }
                .summary-value {
                    display: table-cell;
                    padding: 4px;
                    border: 1px solid #ddd;
                }
                .grade-dist {
                    display: table;
                    width: 50%;
                    margin-top: 10px;
                }
                .footer {
                    margin-top: 15px;
                    font-size: 8px;
                    text-align: center;
                    color: #666;
                }
                .status-pass {
                    color: green;
                    font-weight: bold;
                }
                .status-fail {
                    color: red;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>' . e($companyName) . '</h1>
                <h2>' . e($this->export->export_name) . '</h2>
            </div>
            
            <div class="info-section">';

        if ($this->export->academic_year) {
            $html .= '<p><strong>Academic Year:</strong> ' . e($this->export->academic_year) . '</p>';
        }
        if ($this->export->semester) {
            $html .= '<p><strong>Semester:</strong> ' . e($this->export->semester) . '</p>';
        }
        if ($this->export->programme_id) {
            $progName = $this->export->programme ? $this->export->programme->progname : $this->export->programme_id;
            $html .= '<p><strong>Programme:</strong> ' . e($progName) . '</p>';
        }
        if ($this->export->faculty_code) {
            $facultyName = $this->export->faculty ? $this->export->faculty->faculty_name : $this->export->faculty_code;
            $html .= '<p><strong>Faculty:</strong> ' . e($facultyName) . '</p>';
        }
        $html .= '<p><strong>Generated:</strong> ' . now()->format('d M Y H:i') . '</p>';
        $html .= '</div>';

        // Data table
        $html .= '<table>
            <thead>
                <tr>
                    <th>Reg No</th>
                    <th>Student Name</th>
                    <th>Programme</th>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Year</th>
                    <th>Sem</th>
                    <th>Mark</th>
                    <th>Grade</th>
                    <th>GPA</th>
                    <th>CU</th>';
        
        if ($this->export->include_coursework) {
            $html .= '<th>CW</th>';
        }
        if ($this->export->include_practical) {
            $html .= '<th>Prac</th>';
        }
        
        $html .= '<th>Status</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($this->results as $result) {
            $studentName = e($result->regno);
            $progName = e($result->progid ?? 'N/A');
            $courseName = $result->course ? e($result->course->courseName) : 'N/A';
            
            $status = $result->grade && in_array($result->grade, ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D']) 
                ? '<span class="status-pass">PASS</span>' 
                : '<span class="status-fail">FAIL</span>';

            $html .= '<tr>
                <td>' . e($result->regno) . '</td>
                <td>' . $studentName . '</td>
                <td>' . $progName . '</td>
                <td>' . e($result->courseid) . '</td>
                <td>' . $courseName . '</td>
                <td>' . e($result->acad) . '</td>
                <td>' . e($result->semester) . '</td>
                <td>' . e($result->score ?? 'N/A') . '</td>
                <td>' . e($result->grade ?? 'N/A') . '</td>
                <td>' . e($result->gpa ?? 'N/A') . '</td>
                <td>' . e($result->CreditUnits ?? 'N/A') . '</td>';

            if ($this->export->include_coursework) {
                $html .= '<td>N/A</td>';
            }

            if ($this->export->include_practical) {
                $html .= '<td>N/A</td>';
            }

            $html .= '<td>' . $status . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';

        // Summary section
        if ($this->export->include_summary) {
            $html .= '<div class="summary">
                <h3>SUMMARY STATISTICS</h3>
                <div class="summary-grid">
                    <div class="summary-row">
                        <div class="summary-label">Total Students:</div>
                        <div class="summary-value">' . $this->summary['total_students'] . '</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Total Records:</div>
                        <div class="summary-value">' . $this->summary['total_records'] . '</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Total Courses:</div>
                        <div class="summary-value">' . $this->summary['total_courses'] . '</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Average Mark:</div>
                        <div class="summary-value">' . $this->summary['average_mark'] . '</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Average GPA:</div>
                        <div class="summary-value">' . $this->summary['average_gpa'] . '</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Pass Rate:</div>
                        <div class="summary-value">' . $this->summary['pass_rate'] . '%</div>
                    </div>
                </div>';

            if (!empty($this->summary['grade_distribution'])) {
                $html .= '<h3 style="margin-top: 10px;">GRADE DISTRIBUTION</h3>
                    <div class="grade-dist">';
                foreach ($this->summary['grade_distribution'] as $grade => $count) {
                    $html .= '<div class="summary-row">
                        <div class="summary-label">Grade ' . e($grade) . ':</div>
                        <div class="summary-value">' . $count . '</div>
                    </div>';
                }
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '<div class="footer">
                <p>Generated by MRU Academic Management System | ' . now()->format('d M Y H:i:s') . '</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}
