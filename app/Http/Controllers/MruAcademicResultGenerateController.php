<?php

namespace App\Http\Controllers;

use App\Models\MruAcademicResultExport;
use App\Exports\MruAcademicResultExcelExport;
use App\Services\MruAcademicResultPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MruAcademicResultGenerateController extends Controller
{
    public function index(Request $req)
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        $export = MruAcademicResultExport::find($req->id);
        
        if ($export == null) {
            die("Export configuration not found.");
        }

        try {
            $export->markAsProcessing();

            // Generate Excel
            if (in_array($export->export_type, ['excel', 'both'])) {
                $excelExport = new MruAcademicResultExcelExport($export);
                $fileName = 'mru_academic_results_' . $export->id . '_' . date('Y-m-d_His') . '.xlsx';
                $excelPath = 'exports/' . $fileName;
                
                Excel::store($excelExport, $excelPath);
                
                // Get total records
                $reflection = new \ReflectionClass($excelExport);
                $property = $reflection->getProperty('results');
                $property->setAccessible(true);
                $results = $property->getValue($excelExport);
                $totalRecords = $results ? $results->count() : 0;
                
                $export->markAsCompleted($totalRecords, $excelPath, null);
                
                // Download the Excel file
                return response()->download(
                    storage_path('app/' . $excelPath),
                    $fileName
                );
            }

            // Generate PDF
            if (in_array($export->export_type, ['pdf', 'both'])) {
                $pdfService = new MruAcademicResultPdfService($export);
                $pdf = $pdfService->generate();
                
                $fileName = 'mru_academic_results_' . $export->id . '_' . date('Y-m-d_His') . '.pdf';
                $pdfPath = 'exports/' . $fileName;
                
                // Save PDF
                Storage::put($pdfPath, $pdf->output());
                
                $totalRecords = $pdfService->getResultsCount();
                $export->markAsCompleted($totalRecords, null, $pdfPath);
                
                // Stream the PDF
                return $pdf->stream($fileName);
            }

        } catch (\Exception $e) {
            $export->markAsFailed($e->getMessage());
            \Log::error('Export failed: ' . $e->getMessage(), [
                'export_id' => $export->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            die('Export failed: ' . $e->getMessage());
        }
    }
}
