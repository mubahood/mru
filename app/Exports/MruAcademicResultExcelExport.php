<?php

namespace App\Exports;

use App\Models\MruResult;
use App\Models\MruAcademicResultExport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class MruAcademicResultExcelExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    WithTitle, 
    WithCustomStartCell,
    WithEvents
{
    protected $export;
    protected $results;
    protected $summary;
    protected $headerRow = 8; // Start data after headers

    public function __construct(MruAcademicResultExport $export)
    {
        $this->export = $export;
        $this->loadResults();
        $this->calculateSummary();
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

        // Limit to 5000 records for performance
        $this->results = $query->limit(5000)->get();
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
     * @return Collection
     */
    public function collection()
    {
        return $this->results;
    }

    /**
     * Define start cell for data
     */
    public function startCell(): string
    {
        return 'A' . $this->headerRow;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = [
            'Student Reg No',
            'Student Name',
            'Programme',
            'Course Code',
            'Course Name',
            'Academic Year',
            'Semester',
            'Mark',
            'Grade',
            'GPA',
            'Credit Units',
        ];

        if ($this->export->include_coursework) {
            $headings[] = 'Coursework Mark';
        }

        if ($this->export->include_practical) {
            $headings[] = 'Practical Mark';
        }

        $headings[] = 'Status';

        return $headings;
    }

    /**
     * @param mixed $result
     * @return array
     */
    public function map($result): array
    {
        $row = [
            $result->regno,
            $result->regno, // Student name - simplified
            $result->progid ?? 'N/A',
            $result->courseid,
            $result->course ? $result->course->courseName : 'N/A',
            $result->acad,
            $result->semester,
            $result->score ?? 'N/A',
            $result->grade ?? 'N/A',
            $result->gpa ?? 'N/A',
            $result->CreditUnits ?? 'N/A',
        ];

        if ($this->export->include_coursework) {
            $row[] = 'N/A'; // Disabled for performance
        }

        if ($this->export->include_practical) {
            $row[] = 'N/A'; // Disabled for performance
        }

        $status = $result->grade && in_array($result->grade, ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D']) 
            ? 'PASS' 
            : 'FAIL';
        $row[] = $status;

        return $row;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Header row style
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E86AB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        return [
            $this->headerRow => $headerStyle,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Academic Results';
    }

    /**
     * Register events
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add institution header
                $sheet->setCellValue('A1', 'MOUNTAINS OF THE MOON UNIVERSITY');
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '2E86AB'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Add export title
                $sheet->setCellValue('A2', $this->export->export_name);
                $sheet->mergeCells('A2:K2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Add filters information
                $row = 3;
                if ($this->export->academic_year) {
                    $sheet->setCellValue("A{$row}", "Academic Year: " . $this->export->academic_year);
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $row++;
                }
                if ($this->export->semester) {
                    $sheet->setCellValue("A{$row}", "Semester: " . $this->export->semester);
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $row++;
                }
                if ($this->export->programme_id) {
                    $progName = $this->export->programme ? $this->export->programme->progname : $this->export->programme_id;
                    $sheet->setCellValue("A{$row}", "Programme: " . $progName);
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $row++;
                }
                if ($this->export->faculty_code) {
                    $facultyName = $this->export->faculty ? $this->export->faculty->faculty_name : $this->export->faculty_code;
                    $sheet->setCellValue("A{$row}", "Faculty: " . $facultyName);
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $row++;
                }

                // Add generation date
                $sheet->setCellValue("A{$row}", "Generated: " . now()->format('d M Y H:i'));
                $sheet->mergeCells("A{$row}:C{$row}");

                // Add summary section if enabled
                if ($this->export->include_summary) {
                    $summaryStartRow = $this->headerRow + $this->results->count() + 3;
                    
                    $sheet->setCellValue("A{$summaryStartRow}", 'SUMMARY STATISTICS');
                    $sheet->mergeCells("A{$summaryStartRow}:D{$summaryStartRow}");
                    $sheet->getStyle("A{$summaryStartRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '2E86AB'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);

                    $summaryStartRow++;
                    $sheet->setCellValue("A{$summaryStartRow}", 'Total Students:');
                    $sheet->setCellValue("B{$summaryStartRow}", $this->summary['total_students']);
                    
                    $summaryStartRow++;
                    $sheet->setCellValue("A{$summaryStartRow}", 'Total Records:');
                    $sheet->setCellValue("B{$summaryStartRow}", $this->summary['total_records']);
                    
                    $summaryStartRow++;
                    $sheet->setCellValue("A{$summaryStartRow}", 'Total Courses:');
                    $sheet->setCellValue("B{$summaryStartRow}", $this->summary['total_courses']);
                    
                    $summaryStartRow++;
                    $sheet->setCellValue("A{$summaryStartRow}", 'Average Mark:');
                    $sheet->setCellValue("B{$summaryStartRow}", $this->summary['average_mark']);
                    
                    $summaryStartRow++;
                    $sheet->setCellValue("A{$summaryStartRow}", 'Average GPA:');
                    $sheet->setCellValue("B{$summaryStartRow}", $this->summary['average_gpa']);
                    
                    $summaryStartRow++;
                    $sheet->setCellValue("A{$summaryStartRow}", 'Pass Rate:');
                    $sheet->setCellValue("B{$summaryStartRow}", $this->summary['pass_rate'] . '%');

                    // Grade Distribution
                    if (!empty($this->summary['grade_distribution'])) {
                        $summaryStartRow += 2;
                        $sheet->setCellValue("A{$summaryStartRow}", 'GRADE DISTRIBUTION');
                        $sheet->mergeCells("A{$summaryStartRow}:D{$summaryStartRow}");
                        $sheet->getStyle("A{$summaryStartRow}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'E8F4F8'],
                            ],
                        ]);

                        $summaryStartRow++;
                        foreach ($this->summary['grade_distribution'] as $grade => $count) {
                            $sheet->setCellValue("A{$summaryStartRow}", "Grade {$grade}:");
                            $sheet->setCellValue("B{$summaryStartRow}", $count);
                            $summaryStartRow++;
                        }
                    }
                }

                // Auto-size columns
                foreach (range('A', 'O') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Add borders to data range
                $lastRow = $this->headerRow + $this->results->count();
                $lastCol = $this->export->include_practical ? 'N' : ($this->export->include_coursework ? 'M' : 'L');
                $sheet->getStyle("A{$this->headerRow}:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
