<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

/**
 * Excel sheet showing students with incomplete marks
 */
class MruIncompleteStudentsSheet implements 
    FromCollection, 
    WithHeadings, 
    WithStyles,
    WithTitle,
    WithColumnWidths
{
    protected $incompleteStudents;

    public function __construct($incompleteStudents)
    {
        $this->incompleteStudents = $incompleteStudents;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect($this->incompleteStudents)->map(function($student, $index) {
            return [
                'no' => $index + 1,
                'regno' => $student['regno'],
                'name' => $student['name'],
                'specialization' => $student['specialization'],
                'total_courses' => $student['total_courses'],
                'marks_obtained' => $student['marks_obtained'],
                'marks_missing_count' => $student['marks_missing_count'],
                'missing_courses' => $student['missing_courses'],
            ];
        });
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Incomplete Marks';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No.',
            'Reg No',
            'Student Name',
            'Specialization',
            'Total Courses',
            'Marks Obtained',
            'Marks Missing',
            'Missing Courses',
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No.
            'B' => 15,  // Reg No
            'C' => 30,  // Student Name
            'D' => 25,  // Specialization
            'E' => 14,  // Total Courses
            'F' => 15,  // Marks Obtained
            'G' => 15,  // Marks Missing
            'H' => 50,  // Missing Courses
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('1:1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D32F2F'], // Red for warning
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Get highest row
        $highestRow = $sheet->getHighestRow();
        
        // Apply alternating row colors for better readability
        for ($row = 2; $row <= $highestRow; $row++) {
            $fillColor = ($row % 2 == 0) ? 'FFFFFF' : 'F8F9FA';
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor],
                ],
            ]);
            
            // Center align numeric columns
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Bold and color the marks obtained (green)
            $sheet->getStyle("F{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '2E7D32'], // Green
                ],
            ]);
            
            // Bold and color the marks missing (red)
            $sheet->getStyle("G{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'D32F2F'], // Red
                ],
            ]);
        }
        
        // Add borders to all cells
        $sheet->getStyle("A1:H{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);
        
        // Wrap text in Missing Courses column
        $sheet->getStyle("H2:H{$highestRow}")
            ->getAlignment()
            ->setWrapText(true);
        
        // Set row height for better visibility
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(25);
        }
        
        return [];
    }
}
