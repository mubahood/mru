<?php

namespace App\Http\Controllers;

use App\Models\MruAcademicResultExport;
use App\Exports\MruAcademicResultExcelExport;
use App\Services\MruAcademicResultPdfService;
use App\Services\MruAcademicResultHtmlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MruAcademicResultGenerateController extends Controller
{
    public function index(Request $req)
    {
        // Set generous limits for export generation
        ini_set('max_execution_time', '600'); // 10 minutes
        ini_set('memory_limit', '1024M'); // 1GB
        set_time_limit(600);

        $export = MruAcademicResultExport::find($req->id);
        
        if ($export == null) {
            return back()->with('error', 'Export configuration not found.');
        }

        try {
            $export->markAsProcessing();

            // Get type from request or default to export_type
            $type = $req->get('type', $export->export_type);

            // Generate Excel
            if (in_array($type, ['excel', 'both'])) {
                $excelExport = new MruAcademicResultExcelExport($export);
                $fileName = 'mru_academic_results_' . $export->id . '_' . date('Y-m-d_His') . '.xlsx';
                
                // Get student count
                $totalRecords = $excelExport->getTotalStudentCount();
                
                $export->markAsCompleted($totalRecords);
                
                // Download directly without storing
                return Excel::download($excelExport, $fileName);
            }

            // Generate PDF
            if (in_array($type, ['pdf', 'both'])) {
                $pdfService = new MruAcademicResultPdfService($export);
                $pdf = $pdfService->generate();
                
                $fileName = 'mru_academic_results_' . $export->id . '_' . date('Y-m-d_His') . '.pdf';
                
                $totalRecords = $pdfService->getResultsCount();
                $export->markAsCompleted($totalRecords);
                
                // Stream the PDF directly
                return $pdf->stream($fileName);
            }

            // Generate HTML (View directly in browser)
            if ($type === 'html') {
                $htmlService = new MruAcademicResultHtmlService($export);
                $data = $htmlService->generate();
                
                $totalRecords = $htmlService->getResultsCount();
                $export->markAsCompleted($totalRecords);
                
                // Return HTML view
                return view('mru_academic_result_export_html', $data);
            }

        } catch (\Exception $e) {
            $export->markAsFailed($e->getMessage());
            \Log::error('Export Generation Error', [
                'export_id' => $export->id,
                'programme' => $export->programme_id,
                'year' => $export->academic_year,
                'semester' => $export->semester,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate Missing Marks Report
     * 
     * Generates a specialized report showing ONLY students with incomplete marks.
     * This report uses the same IncompleteMarksTracker logic as the full export
     * but displays only the incomplete students table without full grade matrices.
     * 
     * Features:
     * - HTML (default): Interactive browser view with print support
     * - Excel: Single sheet with incomplete students only
     * - PDF: Professional report matching standard PDF exports
     * 
     * URL Parameters:
     * - type: html|excel|pdf (default: html)
     * 
     * Example URLs:
     * - /admin/mru-academic-result-exports/{id}/generate-missing-marks
     * - /admin/mru-academic-result-exports/{id}/generate-missing-marks?type=excel
     * - /admin/mru-academic-result-exports/{id}/generate-missing-marks?type=pdf
     * 
     * @param Request $req Request object with export ID and optional type parameter
     * @return \Illuminate\Http\Response PDF stream, Excel download, or Blade view
     */
    public function generateMissingMarks(Request $req)
    {
        // Set generous limits for export generation
        ini_set('max_execution_time', '600'); // 10 minutes
        ini_set('memory_limit', '1024M'); // 1GB
        set_time_limit(600);

        $export = MruAcademicResultExport::find($req->id);
        
        if ($export == null) {
            return back()->with('error', 'Export configuration not found.');
        }

        try {
            // Get type from request (default to HTML for quick viewing)
            $type = $req->get('type', 'html');

            // Generate Excel - Only Incomplete Students Sheet
            if ($type === 'excel') {
                $excelExport = new MruAcademicResultExcelExport($export);
                
                // Get incomplete students from the tracker
                $incompleteStudents = $excelExport->sheets();
                $incompleteSheet = null;
                
                // Find the incomplete students sheet
                foreach ($incompleteStudents as $sheet) {
                    if ($sheet instanceof \App\Exports\MruIncompleteStudentsSheet) {
                        $incompleteSheet = $sheet;
                        break;
                    }
                }
                
                if (!$incompleteSheet) {
                    return back()->with('warning', 'No students with incomplete marks found for this export.');
                }
                
                $fileName = 'mru_missing_marks_' . $export->id . '_' . date('Y-m-d_His') . '.xlsx';
                
                // Export only the incomplete students sheet
                return Excel::download(new class($incompleteSheet) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                    protected $sheet;
                    
                    public function __construct($sheet) {
                        $this->sheet = $sheet;
                    }
                    
                    public function sheets(): array {
                        return [$this->sheet];
                    }
                }, $fileName);
            }

            // Generate PDF - Only Incomplete Students Table
            if ($type === 'pdf') {
                $pdfService = new MruAcademicResultPdfService($export);
                $pdfService->generate(); // Load data first
                
                // Check if there are incomplete students
                $incompleteTracker = new \ReflectionProperty($pdfService, 'incompleteTracker');
                $incompleteTracker->setAccessible(true);
                $tracker = $incompleteTracker->getValue($pdfService);
                
                if (!$tracker->hasIncompleteStudents()) {
                    return back()->with('warning', 'No students with incomplete marks found for this export.');
                }
                
                // Generate HTML for missing marks only
                $html = $this->generateMissingMarksHtml($export, $tracker->getIncompleteStudents());
                
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdf->setPaper('A4', 'landscape');
                
                $fileName = 'mru_missing_marks_' . $export->id . '_' . date('Y-m-d_His') . '.pdf';
                
                return $pdf->stream($fileName);
            }

            // Generate HTML (View directly in browser) - Default
            if ($type === 'html') {
                $htmlService = new MruAcademicResultHtmlService($export);
                $data = $htmlService->generate();
                
                // Check if there are incomplete students
                if (empty($data['incompleteStudents'])) {
                    return back()->with('warning', 'No students with incomplete marks found for this export.');
                }
                
                // Pass data to specialized missing marks view
                return view('mru_missing_marks_report', [
                    'export' => $data['export'],
                    'enterprise' => $data['enterprise'],
                    'incompleteStudents' => $data['incompleteStudents'],
                    'logoPath' => $data['logoPath'],
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Missing Marks Export Generation Error', [
                'export_id' => $export->id,
                'programme' => $export->programme_id,
                'year' => $export->academic_year,
                'semester' => $export->semester,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Missing marks export failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate HTML for Missing Marks PDF
     * 
     * Creates professionally styled HTML matching standard MRU PDF exports.
     * Includes institution branding, logo, and consistent formatting.
     * 
     * Styling Features:
     * - A4 Landscape format with proper margins
     * - Institution logo and branding colors
     * - Compact 7pt font for data density
     * - Table with enterprise primary color header
     * - Professional footer with generation timestamp
     * 
     * @param MruAcademicResultExport $export Export configuration
     * @param array $incompleteStudents Array of incomplete student records
     * @return string Complete HTML document ready for PDF rendering
     */
    protected function generateMissingMarksHtml($export, $incompleteStudents)
    {
        $ent = \App\Models\Enterprise::first();
        
        // Get logo path
        $logoPath = '';
        if ($ent && $ent->logo) {
            $logoFullPath = public_path('storage/' . $ent->logo);
            if (file_exists($logoFullPath)) {
                $logoPath = $logoFullPath;
            }
        }
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Students with Missing Marks</title>
            <style>
                @page {
                    size: A4 landscape;
                    margin: 8mm 6mm;
                }
                body {
                    font-family: "DejaVu Sans", Arial, sans-serif;
                    font-size: 7pt;
                    line-height: 1.2;
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
                    padding: 3px 4px;
                    background-color: #f5f5f5;
                    border: 1px solid #ddd;
                }
                .info-section strong {
                    color: #333;
                }
                .summary-box {
                    background-color: #fff3cd;
                    border-left: 3px solid #856404;
                    padding: 3px 4px;
                    margin-bottom: 5px;
                    font-size: 6pt;
                }
                .summary-box strong {
                    color: #856404;
                    font-size: 8pt;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 5px;
                    font-size: 6pt;
                }
                thead th {
                    background-color: #1a5490;
                    color: white;
                    padding: 2px 3px;
                    border: 1px solid #ccc;
                    font-weight: bold;
                    text-align: left;
                    font-size: 6pt;
                }
                tbody td {
                    padding: 2px 3px;
                    border: 1px solid #ddd;
                    vertical-align: top;
                    font-size: 5.5pt;
                }
                tbody tr:nth-child(even) {
                    background-color: #fafafa;
                }
                .num-col {
                    text-align: center;
                    font-weight: bold;
                }
                .missing-courses {
                    font-size: 5pt;
                    line-height: 1.3;
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
                    <tr>
                        <td class="header-logo-cell">';
        
        if ($logoPath) {
            $html .= '<img src="' . $logoPath . '" class="header-logo" alt="Logo">';
        }
        
        $html .= '</td>
                        <td class="header-center">
                            <h1>' . e($ent ? $ent->name : 'ACADEMIC INSTITUTION') . '</h1>';
        
        if ($ent && ($ent->address || $ent->phone1 || $ent->email)) {
            $html .= '<div class="enterprise-info">';
            $enterpriseDetails = [];
            if ($ent->address) $enterpriseDetails[] = e($ent->address);
            if ($ent->phone1) $enterpriseDetails[] = 'Tel: ' . e($ent->phone1);
            if ($ent->email) $enterpriseDetails[] = 'Email: ' . e($ent->email);
            $html .= implode(' | ', $enterpriseDetails);
            $html .= '</div>';
        }
        
        $html .= '              <h2>STUDENTS WITH INCOMPLETE MARKS REPORT</h2>
                        </td>
                        <td class="header-spacer"></td>
                    </tr>
                </table>
            </div>
            
            <div class="info-section">
                <strong>Academic Year:</strong> ' . e($export->academic_year) . ' | 
                <strong>Semester:</strong> ' . e($export->semester) . ' | 
                <strong>Year of Study:</strong> ' . e($export->study_year) . ' | 
                <strong>Programme:</strong> ' . e($export->programme ? $export->programme->progname : 'All Programmes') . '
            </div>
            
            <div class="summary-box">
                Total Students with Incomplete Marks: <strong>' . count($incompleteStudents) . '</strong>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 3%;">No.</th>
                        <th style="width: 10%;">Reg No</th>
                        <th style="width: 18%;">Student Name</th>
                        <th style="width: 15%;">Specialization</th>
                        <th style="width: 6%;">Total</th>
                        <th style="width: 7%;">Obtained</th>
                        <th style="width: 7%;">Missing</th>
                        <th style="width: 34%;">Missing Courses</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($incompleteStudents as $index => $student) {
            $html .= '<tr>
                        <td class="num-col">' . ($index + 1) . '</td>
                        <td>' . e($student['regno']) . '</td>
                        <td>' . e($student['name']) . '</td>
                        <td>' . e($student['specialization']) . '</td>
                        <td class="num-col">' . $student['total_courses'] . '</td>
                        <td class="num-col">' . $student['marks_obtained'] . '</td>
                        <td class="num-col">' . $student['marks_missing_count'] . '</td>
                        <td class="missing-courses">' . e($student['missing_courses']) . '</td>
                    </tr>';
        }
        
        $html .= '</tbody>
            </table>
            
            <div class="footer">
                Generated by MRU Academic Management System | ' . now()->format('d M Y H:i:s') . '
            </div>
        </body>
        </html>';
        
        return $html;
    }}