# MRU Academic Result Export System Documentation

## Overview

The MRU Academic Result Export System is a comprehensive, dynamic solution for exporting academic results data in both Excel and PDF formats. The system provides flexible filtering, beautiful formatting, and detailed summary statistics.

## Features

### ✅ Implemented Features

1. **Dynamic Export Configuration**
   - Export Name: Custom name for each export
   - Export Type: Excel, PDF, or Both
   - Flexible Filters: Academic Year, Semester, Programme, Faculty
   - Sort Options: By Student, Course, Grade, or Programme
   - Optional Inclusions: Coursework Marks, Practical Marks, Summary Statistics

2. **Excel Export**
   - Professional formatting with institution header
   - Color-coded header row (blue background, white text)
   - Filter information display
   - Auto-sized columns
   - Bordered data tables
   - Comprehensive summary statistics section
   - Grade distribution table
   - Generated using Maatwebsite\Excel library

3. **PDF Export**
   - Landscape orientation for better data display
   - Compact 8-9px font for maximum data
   - Institution branding
   - Pass/Fail color coding (green/red)
   - Summary statistics with styled boxes
   - Grade distribution section
   - Generated using DomPDF library

4. **Laravel Admin Interface**
   - Grid view with export history
   - Advanced filtering capabilities
   - Form for creating new exports
   - Status tracking (Pending, Processing, Completed, Failed)
   - Download buttons for completed exports
   - Regenerate option for failed exports
   - Record count display

5. **Database Tracking**
   - Complete export history
   - File path storage
   - Error message logging
   - Creator tracking
   - Configuration preservation via JSON

## System Architecture

### Database Schema

**Table: `mru_academic_result_exports`**

```sql
CREATE TABLE `mru_academic_result_exports` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `export_name` VARCHAR(255) NOT NULL,
    `export_type` ENUM('excel', 'pdf', 'both') DEFAULT 'excel',
    `academic_year` VARCHAR(255) NULL,
    `semester` VARCHAR(255) NULL,
    `programme_id` VARCHAR(255) NULL,
    `faculty_code` VARCHAR(255) NULL,
    `include_coursework` BOOLEAN DEFAULT TRUE,
    `include_practical` BOOLEAN DEFAULT TRUE,
    `include_summary` BOOLEAN DEFAULT TRUE,
    `sort_by` ENUM('student', 'course', 'grade', 'programme') DEFAULT 'student',
    `excel_path` VARCHAR(255) NULL,
    `pdf_path` VARCHAR(255) NULL,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `error_message` TEXT NULL,
    `total_records` INT DEFAULT 0,
    `created_by` BIGINT UNSIGNED NULL,
    `configuration` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    KEY `idx_academic_year` (`academic_year`),
    KEY `idx_semester` (`semester`),
    KEY `idx_programme_id` (`programme_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created_by` (`created_by`)
);
```

### Key Files

1. **Model**
   - `app/Models/MruAcademicResultExport.php`
   - Relationships: creator, programme, faculty, academicYearRelation
   - Scopes: completed(), failed(), pending()
   - Helper methods: markAsProcessing(), markAsCompleted(), markAsFailed()

2. **Migration**
   - `database/migrations/2025_12_24_172452_create_mru_academic_result_exports_table.php`

3. **Excel Export Class**
   - `app/Exports/MruAcademicResultExcelExport.php`
   - Implements: FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithCustomStartCell, WithEvents
   - Features: Dynamic headers, summary calculation, grade distribution, professional styling

4. **PDF Service**
   - `app/Services/MruAcademicResultPdfService.php`
   - Uses: Barryvdh\DomPDF\Facade\Pdf
   - Features: HTML generation, landscape orientation, compact layout, colored status indicators

5. **Controller**
   - `app/Admin/Controllers/MruAcademicResultExportController.php`
   - Methods: grid(), detail(), form(), processExport(), downloadExcel(), downloadPdf(), regenerate()
   - Features: Automatic export processing on form submission, error handling

6. **Routes**
   - `app/Admin/routes.php`
   - Resource route + 3 custom routes (download-excel, download-pdf, regenerate)

7. **Menu**
   - Added to `admin_menu` table under MRU section
   - Title: "Academic Exports"
   - Icon: fa-download
   - Order: 13

## Usage Guide

### Creating a New Export

1. Navigate to **MRU → Academic Exports** in the admin menu
2. Click **Create**
3. Fill in the form:
   - **Export Name**: Enter descriptive name (e.g., "2023/2024 Semester 1 Results")
   - **Export Type**: Choose Excel, PDF, or Both
   - **Filters**: Optionally filter by Academic Year, Semester, Programme, Faculty
   - **Options**: Toggle Coursework Marks, Practical Marks, Summary Statistics
   - **Sort By**: Choose sorting preference
4. Click **Submit**
5. Export is processed automatically and status updates to "Completed"

### Downloading Exports

1. Navigate to the exports grid
2. Locate your export in the list
3. Click the **Excel** or **PDF** button in the Actions column
4. File downloads automatically

### Regenerating Failed Exports

1. Find the failed export in the grid (red "Failed" badge)
2. Click the **Regenerate** button
3. System attempts to process the export again

### Filtering Export History

Use the grid filters to find specific exports:
- Export Name (text search)
- Export Type (dropdown)
- Academic Year (dropdown)
- Semester (dropdown)
- Programme (dropdown)
- Faculty (dropdown)
- Status (dropdown)
- Created At (date range)

## Export Content

### Excel Export Structure

1. **Header Section** (Rows 1-7)
   - Row 1: Institution name (MOUNTAINS OF THE MOON UNIVERSITY)
   - Row 2: Export name
   - Rows 3-7: Filter information (Academic Year, Semester, Programme, Faculty, Generated date)

2. **Data Table** (Starting Row 8)
   - Columns: Reg No, Student Name, Programme, Course Code, Course Name, Year, Semester, Mark, Grade, GPA, Credit Units, [Coursework], [Practical], Status
   - Professional blue header with white text
   - Bordered cells
   - Even-row shading (handled by Excel default)

3. **Summary Section** (After data)
   - Total Students
   - Total Records
   - Total Courses
   - Average Mark
   - Average GPA
   - Pass Rate
   - **Grade Distribution** subsection

### PDF Export Structure

1. **Header**
   - Institution name (centered, blue)
   - Export name (centered, bold)
   - Filter information

2. **Data Table**
   - Compact 8px font
   - Blue header row with white text
   - Pass status in green, Fail in red
   - Bordered cells with alternating row colors

3. **Summary Section**
   - Styled summary boxes
   - Statistics grid
   - Grade distribution table

## Technical Details

### Dynamic Query Building

The export classes dynamically build queries based on configuration:

```php
// Apply filters
if ($this->export->academic_year) {
    $query->where('acad', $this->export->academic_year);
}

if ($this->export->semester) {
    $query->where('sem', $this->export->semester);
}

// Apply sorting
switch ($this->export->sort_by) {
    case 'student':
        $query->join('acad_student', ...)
              ->orderBy('acad_student.firstname');
        break;
    case 'grade':
        $query->orderByRaw('FIELD(grade, "A+", "A", ...)');
        break;
}
```

### Summary Calculations

```php
$this->summary = [
    'total_students' => $this->results->pluck('regno')->unique()->count(),
    'total_records' => $this->results->count(),
    'total_courses' => $this->results->pluck('courseID')->unique()->count(),
    'average_mark' => round($this->results->avg('mark'), 2),
    'average_gpa' => round($this->results->avg('gpa'), 2),
    'grade_distribution' => $this->results->groupBy('grade')->map->count(),
    'pass_rate' => $this->calculatePassRate(),
];
```

### Pass Rate Calculation

Grades A+ through D are considered passing:

```php
protected function calculatePassRate()
{
    $total = $this->results->count();
    if ($total == 0) return 0;

    $passed = $this->results->whereIn('grade', 
        ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D']
    )->count();
    
    return round(($passed / $total) * 100, 2);
}
```

## File Storage

Exports are stored in:
```
storage/app/exports/
├── mru_academic_results_{id}_{timestamp}.xlsx
└── mru_academic_results_{id}_{timestamp}.pdf
```

Example:
```
storage/app/exports/mru_academic_results_1_2025-12-24_173045.xlsx
storage/app/exports/mru_academic_results_1_2025-12-24_173045.pdf
```

## Error Handling

The system includes comprehensive error handling:

1. **Try-Catch Blocks**: All export processing wrapped in try-catch
2. **Status Tracking**: Exports marked as "failed" with error messages
3. **Error Display**: Error messages shown in grid and detail views
4. **Regeneration**: Failed exports can be regenerated
5. **File Validation**: Checks file existence before download

## Performance Considerations

1. **Eager Loading**: Relationships loaded efficiently
   ```php
   MruResult::with(['student', 'course', 'student.programme', 'student.faculty'])
   ```

2. **Query Optimization**: Indexed columns used for filtering
3. **Chunking**: Not needed for typical datasets (< 10,000 records)
4. **Memory Management**: Collections used efficiently
5. **File Storage**: Stored locally, not in database

## Security

1. **Authentication**: Laravel-Admin middleware required
2. **Authorization**: Admin::user()->id tracked as creator
3. **File Path Validation**: Checks before serving downloads
4. **Input Sanitization**: Laravel validation applied
5. **XSS Prevention**: HTML escaped in outputs

## Customization

### Adding New Filter Options

Edit the form in MruAcademicResultExportController:

```php
$form->select('new_filter', __('New Filter'))
    ->options([...])
    ->help('Help text');
```

Update the model migration and query building logic accordingly.

### Modifying Export Columns

**Excel**: Update headings() and map() methods in MruAcademicResultExcelExport.php

**PDF**: Modify generateHtml() method in MruAcademicResultPdfService.php

### Changing Styling

**Excel**: Modify styles() and registerEvents() in MruAcademicResultExcelExport.php

**PDF**: Update CSS in generateHtml() method

## Testing Recommendations

1. **Small Dataset**: Test with single student/course
2. **Large Dataset**: Test with 1000+ records
3. **All Filters**: Test each filter combination
4. **Both Formats**: Test Excel and PDF separately and together
5. **Edge Cases**: Empty results, missing relationships
6. **Download**: Verify file integrity
7. **Summary**: Verify calculation accuracy

## Troubleshooting

### Export Fails with "Class not found"

**Solution**: Run `composer dump-autoload`

### PDF Generation Error

**Solution**: Ensure DomPDF is installed: `composer require barryvdh/laravel-dompdf`

### Excel Download Issues

**Solution**: Verify Maatwebsite/Excel installation and storage permissions

### Missing Relationships

**Solution**: Check model relationships are defined and eager loading is applied

### Empty Exports

**Solution**: Verify filters aren't too restrictive, check database records exist

## Future Enhancements

Potential additions:

1. **Email Delivery**: Send exports via email
2. **Scheduled Exports**: Cron-based automatic generation
3. **Templates**: Pre-defined export configurations
4. **Batch Processing**: Queue large exports
5. **CSV Format**: Add CSV export option
6. **Custom Columns**: Allow users to select which columns to include
7. **Charts**: Add visual grade distribution charts
8. **Watermarks**: Add institutional watermarks to PDFs
9. **Encryption**: Secure sensitive exports

## Conclusion

The MRU Academic Result Export System provides a robust, flexible, and user-friendly solution for generating professional academic result reports. With dynamic filtering, multiple format support, and comprehensive summary statistics, it meets all requirements for modern academic record management.

---

**Created**: December 24, 2025  
**Version**: 1.0  
**Author**: GitHub Copilot  
**Status**: Production Ready
