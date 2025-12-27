<?php

namespace App\Services;

use App\Models\MruStudent;
use App\Models\Enterprise;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Student Academic Transcript PDF Service
 * Generates formal, professional transcript with GPA, CGPA, credits, honors, warnings
 */
class StudentTranscriptPdfService
{
    protected $student;
    protected $enterprise;
    protected $transcriptData = [];
    protected $summary = [];
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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        
        $this->student = MruStudent::findOrFail($studentId);
        $this->enterprise = Enterprise::first();
        $this->loadTranscriptData();
    }

    /**
     * Generate PDF transcript
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
     * Load all transcript data
     */
    protected function loadTranscriptData()
    {
        // Get all results for this student
        $allResults = DB::table('acad_results')
            ->where('regno', $this->student->regno)
            ->orderBy('acad')
            ->orderBy('semester')
            ->orderBy('studyyear')
            ->get();

        if ($allResults->isEmpty()) {
            $this->summary = [
                'total_credits' => 0,
                'final_cgpa' => 0.00,
                'distinctions' => [],
                'honors' => 'No Results',
                'warnings' => []
            ];
            return;
        }

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
                if (!$course) continue;

                $grade = strtoupper(trim($result->grade ?? ''));
                $credits = $course->credit ?? 3;
                
                // Calculate grade points
                $gradePoint = $this->gradePoints[$grade] ?? 0;
                $points = $gradePoint * $credits;

                $isPassed = in_array($grade, ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D']);

                $semesterCourses[] = [
                    'code' => $result->courseid,
                    'name' => $course->courseName,
                    'credits' => $credits,
                    'grade' => $grade,
                    'gradePoint' => $gradePoint,
                    'points' => $points,
                    'isPassed' => $isPassed
                ];

                $semesterCredits += $credits;
                $semesterPoints += $points;

                if ($isPassed) {
                    $passedCount++;
                } else {
                    $failedCourses[] = $result->courseid;
                }

                // Track distinctions (A+ or A grades)
                if (in_array($grade, ['A+', 'A'])) {
                    $allDistinctions[] = [
                        'course' => $course->courseName,
                        'grade' => $grade,
                        'semester' => "$academicYear - Sem $semester"
                    ];
                }
            }

            // Calculate semester GPA
            $semesterGpa = $semesterCredits > 0 ? $semesterPoints / $semesterCredits : 0;

            // Update cumulative
            $cumulativeCredits += $semesterCredits;
            $cumulativePoints += $semesterPoints;
            $cgpa = $cumulativeCredits > 0 ? $cumulativePoints / $cumulativeCredits : 0;

            // Check for warnings
            if ($semesterGpa < 2.0 && $semesterGpa > 0) {
                $allWarnings[] = [
                    'semester' => "$academicYear - Sem $semester",
                    'type' => 'Academic Probation',
                    'reason' => 'Semester GPA below 2.0 (' . number_format($semesterGpa, 2) . ')'
                ];
            }

            if (!empty($failedCourses)) {
                $allWarnings[] = [
                    'semester' => "$academicYear - Sem $semester",
                    'type' => 'Failed Courses',
                    'reason' => count($failedCourses) . ' course(s) failed: ' . implode(', ', $failedCourses)
                ];
            }

            $this->transcriptData[] = [
                'academic_year' => $academicYear,
                'semester' => $semester,
                'study_year' => $studyYear,
                'courses' => $semesterCourses,
                'credits_earned' => $semesterCredits,
                'semester_gpa' => $semesterGpa,
                'cumulative_credits' => $cumulativeCredits,
                'cgpa' => $cgpa,
                'passed_count' => $passedCount,
                'failed_courses' => $failedCourses
            ];
        }

        $finalCgpa = $cumulativeCredits > 0 ? $cumulativePoints / $cumulativeCredits : 0;

        $this->summary = [
            'total_credits' => $cumulativeCredits,
            'final_cgpa' => $finalCgpa,
            'distinctions' => $allDistinctions,
            'honors' => $this->determineHonors($finalCgpa),
            'warnings' => $allWarnings
        ];
    }

    /**
     * Determine honors classification
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
     * Generate HTML for PDF
     */
    protected function generateHtml()
    {
        // Get programme and specialization details
        $programme = DB::table('acad_programme')
            ->where('progcode', $this->student->progid)
            ->first();

        $specialization = DB::table('acad_specialisation')
            ->where('spec_id', $this->student->specialisation)
            ->first();

        $logoPath = $this->getLogoPath();
        $generatedDate = now();
        $verificationCode = strtoupper(substr(md5($this->student->regno . $generatedDate->timestamp), 0, 12));
        
        // Get enterprise colors
        $primaryColor = $this->enterprise->color ?? '#1a5490';
        $secondaryColor = $this->enterprise->sec_color ?? '#e0e0e0';

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
            font-size: 9pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .header {
            border-bottom: 3px double <?php echo $primaryColor; ?>;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .header img {
            max-height: 60px;
            max-width: 120px;
            vertical-align: middle;
        }
        
        .header-text {
            text-align: center;
            vertical-align: middle;
        }
        
        .header h1 {
            font-size: 16pt;
            margin: 3px 0;
            font-weight: bold;
            text-transform: uppercase;
            color: <?php echo $primaryColor; ?>;
        }
        
        .header h2 {
            font-size: 13pt;
            margin: 3px 0;
            font-weight: bold;
            text-decoration: underline;
            color: <?php echo $primaryColor; ?>;
        }
        
        .header p {
            margin: 1px 0;
            font-size: 8pt;
        }
        
        .student-info {
            background: #fff;
            border: 1px solid <?php echo $primaryColor; ?>;
            padding: 6px 8px;
            margin-bottom: 10px;
        }
        
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .student-info td {
            padding: 2px 5px;
            font-size: 9pt;
        }
        
        .student-info td:first-child {
            font-weight: bold;
            width: 120px;
        }
        
        .semester-header {
            background: <?php echo $primaryColor; ?>;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10pt;
            margin: 8px 0 3px 0;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 8pt;
        }
        
        .results-table th {
            background: #fff;
            border: 1px solid <?php echo $primaryColor; ?>;
            padding: 3px 2px;
            text-align: left;
            font-weight: bold;
            color: <?php echo $primaryColor; ?>;
        }
        
        .results-table th.center {
            text-align: center;
        }
        
        .results-table td {
            border: 1px solid <?php echo $primaryColor; ?>;
            padding: 2px 2px;
        }
        
        .results-table td.center {
            text-align: center;
        }
        
        .results-table td.right {
            text-align: right;
        }
        
        .results-table tr.failed {
            background: #ffe6e6;
        }
        
        .semester-summary {
            background: #fff;
            border: 1px solid <?php echo $primaryColor; ?>;
            padding: 4px 6px;
            margin-bottom: 6px;
            font-size: 8pt;
            font-weight: bold;
        }
        
        .semester-summary span {
            margin-right: 15px;
        }
        
        .final-summary {
            border: 2px solid <?php echo $primaryColor; ?>;
            padding: 8px;
            margin-top: 10px;
            background: #fff;
        }
        
        .final-summary h3 {
            margin: 0 0 6px 0;
            font-size: 11pt;
            text-align: center;
            text-decoration: underline;
            color: <?php echo $primaryColor; ?>;
        }
        
        .summary-grid {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .summary-grid td {
            border: 1px solid <?php echo $primaryColor; ?>;
            padding: 5px;
            width: 50%;
        }
        
        .summary-grid strong {
            display: block;
            font-size: 7pt;
            color: #666;
            margin-bottom: 2px;
        }
        
        .summary-grid .value {
            font-size: 13pt;
            font-weight: bold;
            color: <?php echo $primaryColor; ?>;
        }
        
        .distinctions, .warnings {
            margin-top: 6px;
        }
        
        .distinctions h4, .warnings h4 {
            font-size: 9pt;
            margin: 0 0 3px 0;
            text-decoration: underline;
            color: <?php echo $primaryColor; ?>;
        }
        
        .distinctions ul, .warnings ul {
            margin: 0;
            padding-left: 15px;
            font-size: 8pt;
        }
        
        .distinctions li {
            color: #155724;
            margin-bottom: 2px;
        }
        
        .warnings li {
            color: #721c24;
            margin-bottom: 2px;
        }
        
        .footer {
            margin-top: 12px;
            border-top: 2px solid <?php echo $primaryColor; ?>;
            padding-top: 10px;
        }
        
        .footer-grid {
            width: 100%;
        }
        
        .footer-grid td {
            width: 33.33%;
            text-align: center;
            font-size: 8pt;
        }
        
        .signature-line {
            border-top: 1px solid <?php echo $primaryColor; ?>;
            margin-top: 30px;
            padding-top: 3px;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 10px;
            font-size: 7pt;
            border-top: 1px solid <?php echo $primaryColor; ?>;
            padding-top: 6px;
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
                    <h2>OFFICIAL ACADEMIC TRANSCRIPT</h2>
                </td>
            </tr>
        </table>
    </div>

    <!-- Student Information -->
    <div class="student-info">
        <table>
            <tr>
                <td>Student Name:</td>
                <td><strong><?php echo strtoupper($this->student->firstname . ' ' . $this->student->othername); ?></strong></td>
                <td>Registration Number:</td>
                <td><strong><?php echo $this->student->regno; ?></strong></td>
            </tr>
            <tr>
                <td>Programme:</td>
                <td><?php echo htmlspecialchars($programme->progname ?? 'N/A'); ?></td>
                <td>Specialization:</td>
                <td><?php echo htmlspecialchars($specialization->spec ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td>Date of Birth:</td>
                <td><?php echo $this->student->dob ?? 'N/A'; ?></td>
                <td>Gender:</td>
                <td><?php echo $this->student->sex ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td>Transcript Generated:</td>
                <td colspan="3"><?php echo $generatedDate->format('d F Y, H:i'); ?></td>
            </tr>
        </table>
    </div>

    <!-- Academic Results by Semester -->
    <?php foreach ($this->transcriptData as $semester): ?>
        <div class="semester-header">
            <?php echo $semester['academic_year']; ?> - SEMESTER <?php echo $semester['semester']; ?> (Year <?php echo $semester['study_year']; ?>)
        </div>

        <table class="results-table">
            <thead>
                <tr>
                    <th style="width: 15%;">COURSE CODE</th>
                    <th style="width: 45%;">COURSE TITLE</th>
                    <th class="center" style="width: 10%;">CREDITS</th>
                    <th class="center" style="width: 10%;">GRADE</th>
                    <th class="center" style="width: 10%;">GP</th>
                    <th class="right" style="width: 10%;">POINTS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($semester['courses'] as $course): ?>
                    <tr<?php echo !$course['isPassed'] ? ' class="failed"' : ''; ?>>
                        <td><?php echo htmlspecialchars($course['code']); ?></td>
                        <td><?php echo htmlspecialchars($course['name']); ?></td>
                        <td class="center"><?php echo $course['credits']; ?></td>
                        <td class="center"><strong><?php echo $course['grade']; ?></strong></td>
                        <td class="center"><?php echo number_format($course['gradePoint'], 2); ?></td>
                        <td class="right"><?php echo number_format($course['points'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="semester-summary">
            <span>Credits Earned: <?php echo $semester['credits_earned']; ?></span>
            <span>Semester GPA: <?php echo number_format($semester['semester_gpa'], 2); ?></span>
            <span>Cumulative Credits: <?php echo $semester['cumulative_credits']; ?></span>
            <span>CGPA: <?php echo number_format($semester['cgpa'], 2); ?></span>
        </div>

        <?php if (!empty($semester['failed_courses'])): ?>
            <div style="background: #fff3cd; border: 1px solid #856404; padding: 3px 5px; margin-bottom: 6px; font-size: 8pt;">
                <strong>Failed Courses:</strong> <?php echo implode(', ', $semester['failed_courses']); ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Final Summary -->
    <div class="final-summary">
        <h3>ACADEMIC SUMMARY</h3>

        <table class="summary-grid">
            <tr>
                <td>
                    <strong>TOTAL CREDITS EARNED</strong>
                    <div class="value"><?php echo $this->summary['total_credits']; ?></div>
                </td>
                <td>
                    <strong>CUMULATIVE GPA (CGPA)</strong>
                    <div class="value"><?php echo number_format($this->summary['final_cgpa'], 2); ?></div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>CLASSIFICATION</strong>
                    <div class="value" style="font-size: 11pt;"><?php echo $this->summary['honors']; ?></div>
                </td>
            </tr>
        </table>

        <?php if (!empty($this->summary['distinctions'])): ?>
            <div class="distinctions">
                <h4>DISTINCTIONS</h4>
                <ul>
                    <?php foreach ($this->summary['distinctions'] as $distinction): ?>
                        <li><?php echo htmlspecialchars($distinction['course']); ?> - Grade <?php echo $distinction['grade']; ?> (<?php echo $distinction['semester']; ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($this->summary['warnings'])): ?>
            <div class="warnings">
                <h4>ACADEMIC WARNINGS</h4>
                <ul>
                    <?php foreach ($this->summary['warnings'] as $warning): ?>
                        <li><strong><?php echo $warning['semester']; ?>:</strong> <?php echo $warning['type']; ?> - <?php echo $warning['reason']; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer with Authentication -->
    <div class="footer">
        <table class="footer-grid">
            <tr>
                <td>
                    <div class="signature-line">
                        <strong>Registrar</strong>
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
                        <strong>Dean of Students</strong>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            <strong>Document Verification Code:</strong> <?php echo $verificationCode; ?><br>
            <strong>Generated:</strong> <?php echo $generatedDate->format('d F Y \a\t H:i:s'); ?><br>
            <em>This is an official document. Any alteration renders it invalid.</em>
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
