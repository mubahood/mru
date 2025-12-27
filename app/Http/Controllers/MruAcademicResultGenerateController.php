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
}
