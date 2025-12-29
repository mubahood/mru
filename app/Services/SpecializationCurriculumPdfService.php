<?php

namespace App\Services;

use App\Models\MruSpecialisation;
use App\Models\MruSpecializationHasCourse;
use App\Models\Enterprise;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Specialization Curriculum PDF Service
 * Generates formal curriculum document for a specialization
 * Follows the same design pattern as StudentTranscriptPdfService
 */
class SpecializationCurriculumPdfService
{
    protected $specialization;
    protected $enterprise;
    protected $curriculumData = [];
    protected $summary = [];

    public function __construct($specializationId)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        
        $this->specialization = MruSpecialisation::with(['programme.faculty'])->findOrFail($specializationId);
        $this->enterprise = Enterprise::first();
        $this->loadCurriculumData();
    }

    /**
     * Generate PDF curriculum
     */
    public function generate()
    {
        $html = $this->generateHtml();
        
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'serif',
            'dpi' => 150,
            'enable_php' => false,
            'enable_javascript' => false,
        ]);

        return $pdf;
    }

    /**
     * Load curriculum data
     */
    protected function loadCurriculumData()
    {
        // Get all courses for this specialization
        $courses = MruSpecializationHasCourse::with(['course'])
            ->where('specialization_id', $this->specialization->spec_id)
            ->orderBy('year', 'asc')
            ->orderBy('semester', 'asc')
            ->orderBy('type', 'desc') // mandatory first
            ->orderBy('course_code', 'asc')
            ->get();

        // Group by year first, then by semester
        $groupedByYear = $courses->groupBy('year');

        $this->curriculumData = [];
        foreach ($groupedByYear as $year => $yearCourses) {
            $yearData = ['year' => $year, 'semesters' => []];
            
            $semesterGroups = $yearCourses->groupBy('semester');
            
            foreach ($semesterGroups as $semester => $semesterCourses) {
                $semesterCredits = 0;
                $mandatoryCount = 0;
                $electiveCount = 0;
                $semesterCoursesData = [];
                
                foreach ($semesterCourses as $course) {
                    $courseName = $course->course->courseName ?? 'Course not found';
                    $credits = $course->credits ?? 3;
                    
                    $semesterCoursesData[] = [
                        'code' => $course->course_code,
                        'name' => $courseName,
                        'credits' => $credits,
                        'type' => $course->type,
                        'is_mandatory' => $course->type === 'mandatory'
                    ];
                    
                    $semesterCredits += $credits;
                    if ($course->type === 'mandatory') {
                        $mandatoryCount++;
                    } else {
                        $electiveCount++;
                    }
                }
                
                $yearData['semesters'][$semester] = [
                    'semester' => $semester,
                    'courses' => $semesterCoursesData,
                    'semester_credits' => $semesterCredits,
                    'mandatory_count' => $mandatoryCount,
                    'elective_count' => $electiveCount,
                    'total_courses' => count($semesterCoursesData)
                ];
            }
            
            $this->curriculumData[] = $yearData;
        }

        // Calculate summary
        $this->summary = [
            'total_courses' => $courses->count(),
            'mandatory_courses' => $courses->where('type', 'mandatory')->count(),
            'elective_courses' => $courses->where('type', 'elective')->count(),
            'total_credits' => $courses->sum('credits'),
            'years' => $courses->pluck('year')->unique()->count(),
            'semesters' => $courses->groupBy(function($c) { return $c->year . '-' . $c->semester; })->count()
        ];
    }

    /**
     * Generate HTML content
     */
    protected function generateHtml()
    {
        $logoPath = $this->getLogoPath();
        $generatedDate = now();
        $verificationCode = strtoupper(substr(md5($this->specialization->spec_id . $generatedDate->timestamp), 0, 12));
        
        // Get enterprise colors
        $primaryColor = $this->enterprise->color ?? '#1a5490';
        $secondaryColor = $this->enterprise->sec_color ?? '#e0e0e0';
        
        // Programme details
        $programmeName = $this->specialization->programme->progname ?? 'N/A';
        $programmeCode = $this->specialization->prog_id;
        $facultyName = $this->specialization->programme->faculty->faculty ?? 'N/A';

        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 10mm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 7pt;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .header {
            border-bottom: 2px solid <?php echo $primaryColor; ?>;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .header img {
            max-height: 75px;
            max-width: 140px;
            vertical-align: middle;
        }
        
        .header-text {
            text-align: center;
            vertical-align: middle;
        }
        
        .header h1 {
            font-size: 12pt;
            margin: 2px 0;
            font-weight: bold;
            text-transform: uppercase;
            color: <?php echo $primaryColor; ?>;
        }
        
        .header h2 {
            font-size: 10pt;
            margin: 2px 0;
            font-weight: bold;
            text-decoration: underline;
            color: <?php echo $primaryColor; ?>;
        }
        
        .header p {
            margin: 0;
            font-size: 7pt;
        }
        
        .curriculum-info {
            background: #fff;
            border: 1px solid #000;
            padding: 4px 6px;
            margin-bottom: 6px;
        }
        
        .curriculum-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .curriculum-info td {
            padding: 1px 4px;
            font-size: 7pt;
        }
        
        .curriculum-info td:first-child {
            font-weight: bold;
            width: 120px;
        }
        
        .year-header {
            background: <?php echo $primaryColor; ?>;
            color: #fff;
            padding: 3px 6px;
            font-weight: bold;
            font-size: 8pt;
            margin: 4px 0 2px 0;
        }
        
        .semesters-row {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .semester-col {
            width: 49%;
            vertical-align: top;
            padding: 0 2px;
        }
        
        .semester-title {
            background: #555;
            color: #fff;
            padding: 2px 4px;
            font-weight: bold;
            font-size: 7pt;
            margin-bottom: 2px;
            text-align: center;
        }
        
        .courses-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
            font-size: 6pt;
        }
        
        .courses-table th {
            background: #000;
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: left;
            font-weight: bold;
            color: #fff;
            font-size: 6pt;
        }
        
        .courses-table th.center {
            text-align: center;
        }
        
        .courses-table td {
            border: 1px solid #ccc;
            padding: 1px 1px;
            font-size: 6pt;
        }
        
        .courses-table td.center {
            text-align: center;
        }
        
        .courses-table td.right {
            text-align: right;
        }
        
        .courses-table tr.elective {
            background: #f9f9f9;
        }
        
        .semester-summary {
            background: #fff;
            border: 1px solid #999;
            padding: 1px 3px;
            margin-bottom: 3px;
            font-size: 6pt;
            font-weight: bold;
        }
        
        .semester-summary span {
            margin-right: 8px;
        }
        
        .final-summary {
            border: 1px solid #666;
            padding: 4px;
            margin-top: 6px;
            background: #fff;
        }
        
        .final-summary h3 {
            margin: 0 0 3px 0;
            font-size: 8pt;
            text-align: center;
            text-decoration: underline;
            color: #000;
        }
        
        .summary-grid {
            width: 100%;
            margin-bottom: 4px;
        }
        
        .summary-grid td {
            border: 1px solid #999;
            padding: 3px;
            width: 50%;
        }
        
        .summary-grid strong {
            display: block;
            font-size: 6pt;
            color: #666;
            margin-bottom: 1px;
        }
        
        .summary-grid .value {
            font-size: 9pt;
            font-weight: bold;
            color: #000;
        }
        
        .footer {
            margin-top: 8px;
            border-top: 1px solid #666;
            padding-top: 6px;
        }
        
        .footer-grid {
            width: 100%;
        }
        
        .footer-grid td {
            width: 33.33%;
            text-align: center;
            font-size: 6pt;
        }
        
        .signature-line {
            border-top: 1px solid #666;
            margin-top: 20px;
            padding-top: 2px;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 6px;
            font-size: 6pt;
            border-top: 1px solid #666;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 120px; vertical-align: middle;">
                    <?php if ($logoPath): ?>
                        <img src="<?php echo $logoPath; ?>" alt="Logo">
                    <?php endif; ?>
                </td>
                <td class="header-text">
                    <h1><?php echo htmlspecialchars($this->enterprise->name ?? 'Institution Name'); ?></h1>
                    <p><?php echo htmlspecialchars($this->enterprise->address ?? ''); ?></p>
                    <p>Tel: <?php echo htmlspecialchars($this->enterprise->phone ?? ''); ?> | Email: <?php echo htmlspecialchars($this->enterprise->email ?? ''); ?></p>
                    <h2>OFFICIAL CURRICULUM DOCUMENT</h2>
                </td>
            </tr>
        </table>
    </div>

    <!-- Curriculum Information -->
    <div class="curriculum-info">
        <table>
            <tr>
                <td>Programme:</td>
                <td><strong><?php echo htmlspecialchars($programmeName); ?></strong></td>
                <td>Programme Code:</td>
                <td><strong><?php echo htmlspecialchars($programmeCode); ?></strong></td>
            </tr>
            <tr>
                <td>Specialization:</td>
                <td><?php echo htmlspecialchars($this->specialization->spec); ?></td>
                <td>Abbreviation:</td>
                <td><?php echo htmlspecialchars($this->specialization->abbrev ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td>Faculty:</td>
                <td><?php echo htmlspecialchars($facultyName); ?></td>
                <td>Duration:</td>
                <td><?php echo $this->summary['years']; ?> Year(s)</td>
            </tr>
            <tr>
                <td>Document Generated:</td>
                <td colspan="3"><?php echo $generatedDate->format('d F Y, H:i'); ?></td>
            </tr>
        </table>
    </div>

    <!-- Curriculum by Year and Semester -->
    <?php foreach ($this->curriculumData as $yearData): ?>
        <div class="year-header">
            YEAR <?php echo $yearData['year']; ?>
        </div>

        <table class="semesters-row">
            <tr>
                <?php 
                // Display up to 2 semesters side by side
                $semesters = $yearData['semesters'];
                ksort($semesters); // Sort by semester number
                $semesterCount = 0;
                foreach ($semesters as $semesterData): 
                    $semesterCount++;
                ?>
                    <td class="semester-col">
                        <div class="semester-title">
                            SEMESTER <?php echo $semesterData['semester']; ?>
                        </div>

                        <table class="courses-table">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">CODE</th>
                                    <th style="width: 50%;">COURSE TITLE</th>
                                    <th class="center" style="width: 15%;">CU</th>
                                    <th class="center" style="width: 15%;">TYPE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($semesterData['courses'] as $course): ?>
                                    <tr<?php echo !$course['is_mandatory'] ? ' class="elective"' : ''; ?>>
                                        <td><?php echo htmlspecialchars($course['code']); ?></td>
                                        <td><?php echo htmlspecialchars($course['name']); ?></td>
                                        <td class="center"><strong><?php echo $course['credits']; ?></strong></td>
                                        <td class="center"><?php echo strtoupper(substr($course['type'], 0, 1)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="semester-summary">
                            <span>Courses: <?php echo $semesterData['total_courses']; ?></span>
                            <span>M: <?php echo $semesterData['mandatory_count']; ?></span>
                            <span>E: <?php echo $semesterData['elective_count']; ?></span>
                            <span>Credits: <?php echo $semesterData['semester_credits']; ?> CU</span>
                        </div>
                    </td>
                    
                    <?php if ($semesterCount % 2 == 0 && $semesterCount < count($semesters)): ?>
                        </tr><tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php 
                // Fill empty column if odd number of semesters
                if ($semesterCount % 2 != 0): 
                ?>
                    <td class="semester-col"></td>
                <?php endif; ?>
            </tr>
        </table>
    <?php endforeach; ?>

    <!-- Final Summary -->
    <div class="final-summary">
        <h3>PROGRAMME SUMMARY</h3>

        <table class="summary-grid">
            <tr>
                <td>
                    <strong>TOTAL COURSES</strong>
                    <div class="value"><?php echo $this->summary['total_courses']; ?></div>
                </td>
                <td>
                    <strong>TOTAL CREDIT UNITS</strong>
                    <div class="value"><?php echo $this->summary['total_credits']; ?> CU</div>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>MANDATORY COURSES</strong>
                    <div class="value"><?php echo $this->summary['mandatory_courses']; ?></div>
                </td>
                <td>
                    <strong>ELECTIVE COURSES</strong>
                    <div class="value"><?php echo $this->summary['elective_courses']; ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer with Authentication -->
    <div class="footer">
        <table class="footer-grid">
            <tr>
                <td>
                    <div class="signature-line">
                        <strong>Programme Coordinator</strong>
                    </div>
                </td>
                <td>
                    <div style="padding-top: 20px;">
                        <strong style="color: <?php echo $primaryColor; ?>;">OFFICIAL SEAL</strong>
                        <div style="border: 2px solid <?php echo $primaryColor; ?>; width: 60px; height: 60px; margin: 5px auto; display: inline-block; line-height: 60px; font-size: 7pt;">
                            [SEAL]
                        </div>
                    </div>
                </td>
                <td>
                    <div class="signature-line">
                        <strong>Dean of Faculty</strong>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            <strong>Document Verification Code:</strong> <?php echo $verificationCode; ?><br>
            <strong>Generated:</strong> <?php echo $generatedDate->format('d F Y \a\t H:i:s'); ?><br>
            <em>This is an official curriculum document. Any alteration renders it invalid.</em>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    /**
     * Get logo path as base64 data URI (required for DomPDF)
     */
    protected function getLogoPath()
    {
        if ($this->enterprise && $this->enterprise->logo) {
            $logoPath = public_path('storage/' . $this->enterprise->logo);
            if (file_exists($logoPath)) {
                $imageType = mime_content_type($logoPath);
                $imageData = base64_encode(file_get_contents($logoPath));
                return "data:{$imageType};base64,{$imageData}";
            }
        }
        return null;
    }
}
