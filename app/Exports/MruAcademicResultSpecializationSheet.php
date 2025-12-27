<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

/**
 * Single sheet for one specialization
 */
class MruAcademicResultSpecializationSheet implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles,
    WithTitle
{
    protected $specializationName;
    protected $students;
    protected $courses;
    protected $results;
    protected $minRequired;

    public function __construct($specializationName, $students, $courses, $results, $minRequired = 0)
    {
        $this->specializationName = $specializationName;
        $this->students = $students;
        $this->courses = $courses;
        $this->results = $results;
        $this->minRequired = $minRequired;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->students;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Spec ' . ($this->specializationName ?? 'Unknown');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = ['Reg No', 'Student Name', 'STATUS'];

        // Add each course as a column header
        foreach ($this->courses as $course) {
            $headings[] = $course->courseID;
        }

        return $headings;
    }

    /**
     * @param mixed $student
     * @return array
     */
    public function map($student): array
    {
        $fullName = trim(($student->firstname ?? '') . ' ' . ($student->othername ?? ''));
        $studentName = $fullName ?: $student->regno;

        // Get results for this student
        $studentResults = $this->results->get($student->regno, collect());
        
        // Calculate pass/fail status
        $totalCourses = $this->courses->count();
        $coursesWithResults = 0;
        $coursesPassed = 0;
        $passingGrades = ['A', 'B', 'C', 'D', 'B+', 'C+', 'D+', 'A+'];
        
        foreach ($this->courses as $course) {
            $result = $studentResults->get($course->courseID);
            if ($result) {
                $coursesWithResults++;
                $grade = strtoupper(trim($result->grade ?? ''));
                if (in_array($grade, $passingGrades) || preg_match('/^[A-D][+-]?$/i', $grade)) {
                    $coursesPassed++;
                }
            }
        }
        
        // Determine status
        $status = 'N/A';
        if ($this->minRequired > 0) {
            if ($coursesWithResults < $totalCourses) {
                $status = 'INCOMPLETE';
            } elseif ($coursesPassed >= $this->minRequired) {
                $status = 'PASS';
            } else {
                $status = 'FAIL';
            }
        }

        $row = [
            $student->regno,
            $studentName,
            $status,
        ];

        // Add result for each course
        foreach ($this->courses as $course) {
            $result = $studentResults->get($course->courseID);
            
            if ($result) {
                // Show Grade (Score) format
                $cell = $result->grade ?? 'N/A';
                if ($result->score) {
                    $cell .= " ({$result->score})";
                }
                $row[] = $cell;
            } else {
                $row[] = '-';
            }
        }

        return $row;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Style the first row (headers)
        $sheet->getStyle('1:1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
        ]);
        
        // Apply conditional formatting to STATUS column (column C)
        $highestRow = $sheet->getHighestRow();
        
        for ($row = 2; $row <= $highestRow; $row++) {
            $statusValue = $sheet->getCell('C' . $row)->getValue();
            
            if ($statusValue === 'PASS') {
                // Green background for PASS
                $sheet->getStyle('C' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D4EDDA']
                    ],
                    'font' => ['color' => ['rgb' => '155724'], 'bold' => true],
                ]);
            } elseif ($statusValue === 'FAIL') {
                // Red background for FAIL
                $sheet->getStyle('C' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8D7DA']
                    ],
                    'font' => ['color' => ['rgb' => '721C24'], 'bold' => true],
                ]);
            } elseif ($statusValue === 'INCOMPLETE') {
                // Yellow background for INCOMPLETE
                $sheet->getStyle('C' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF3CD']
                    ],
                    'font' => ['color' => ['rgb' => '856404'], 'bold' => true],
                ]);
            }
        }
        
        return [];
    }
}
