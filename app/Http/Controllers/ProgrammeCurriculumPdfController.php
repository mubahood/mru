<?php

namespace App\Http\Controllers;

use App\Models\MruProgramme;
use App\Models\MruCurriculum;
use App\Models\MruProgrammeCourse;
use App\Models\Enterprise;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

/**
 * ProgrammeCurriculumPdfController
 * 
 * Generates PDF curriculum documents for academic programmes.
 * Shows all courses organized by year and semester.
 */
class ProgrammeCurriculumPdfController extends Controller
{
    /**
     * Generate curriculum PDF for a programme
     *
     * @param Request $request
     * @param string $identifier Programme code or ID
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request, $identifier)
    {
        // Find programme by code or ID
        $programme = MruProgramme::where('progcode', $identifier)
            ->orWhere('id', $identifier)
            ->with('faculty')
            ->firstOrFail();

        // Get the latest curriculum for this programme
        $curriculum = MruCurriculum::where('Progcode', $programme->progcode)
            ->orderBy('StartYear', 'desc')
            ->orderBy('intake', 'desc')
            ->first();

        // Get all programme courses organized by year and semester
        $courses = MruProgrammeCourse::where('progcode', $programme->progcode)
            ->with('course')
            ->orderBy('study_year')
            ->orderBy('semester')
            ->orderBy('course_code')
            ->get();

        // Organize courses by year and semester
        $coursesByYearSem = [];
        foreach ($courses as $course) {
            $year = $course->study_year;
            $sem = $course->semester;
            
            if (!isset($coursesByYearSem[$year])) {
                $coursesByYearSem[$year] = [];
            }
            if (!isset($coursesByYearSem[$year][$sem])) {
                $coursesByYearSem[$year][$sem] = [];
            }
            
            $coursesByYearSem[$year][$sem][] = $course;
        }

        // Get enterprise data
        $ent = Enterprise::first();
        $logoPath = $ent && $ent->logo ? public_path('storage/' . $ent->logo) : null;

        // Calculate statistics
        $totalCourses = $courses->count();
        $totalCredits = $courses->sum(function ($c) {
            return $c->course ? $c->course->CreditUnit : 0;
        });

        // Generate PDF
        $pdf = PDF::loadView('pdf.programme-curriculum', [
            'programme' => $programme,
            'curriculum' => $curriculum,
            'coursesByYearSem' => $coursesByYearSem,
            'totalCourses' => $totalCourses,
            'totalCredits' => $totalCredits,
            'generatedDate' => now()->format('d M Y H:i'),
            'ent' => $ent,
            'logoPath' => $logoPath,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Curriculum_' . $programme->progcode . '_' . date('Ymd') . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download curriculum PDF for a programme
     *
     * @param Request $request
     * @param string $identifier Programme code or ID
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request, $identifier)
    {
        // Find programme by code or ID
        $programme = MruProgramme::where('progcode', $identifier)
            ->orWhere('id', $identifier)
            ->with('faculty')
            ->firstOrFail();

        // Get the latest curriculum for this programme
        $curriculum = MruCurriculum::where('Progcode', $programme->progcode)
            ->orderBy('StartYear', 'desc')
            ->orderBy('intake', 'desc')
            ->first();

        // Get all programme courses organized by year and semester
        $courses = MruProgrammeCourse::where('progcode', $programme->progcode)
            ->with('course')
            ->orderBy('study_year')
            ->orderBy('semester')
            ->orderBy('course_code')
            ->get();

        // Organize courses by year and semester
        $coursesByYearSem = [];
        foreach ($courses as $course) {
            $year = $course->study_year;
            $sem = $course->semester;
            
            if (!isset($coursesByYearSem[$year])) {
                $coursesByYearSem[$year] = [];
            }
            if (!isset($coursesByYearSem[$year][$sem])) {
                $coursesByYearSem[$year][$sem] = [];
            }
            
            $coursesByYearSem[$year][$sem][] = $course;
        }

        // Get enterprise data
        $ent = Enterprise::first();
        $logoPath = $ent && $ent->logo ? public_path('storage/' . $ent->logo) : null;

        // Calculate statistics
        $totalCourses = $courses->count();
        $totalCredits = $courses->sum(function ($c) {
            return $c->course ? $c->course->CreditUnit : 0;
        });

        // Generate PDF
        $pdf = PDF::loadView('pdf.programme-curriculum', [
            'programme' => $programme,
            'curriculum' => $curriculum,
            'coursesByYearSem' => $coursesByYearSem,
            'totalCourses' => $totalCourses,
            'totalCredits' => $totalCredits,
            'generatedDate' => now()->format('d M Y H:i'),
            'ent' => $ent,
            'logoPath' => $logoPath,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Curriculum_' . $programme->progcode . '_' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
