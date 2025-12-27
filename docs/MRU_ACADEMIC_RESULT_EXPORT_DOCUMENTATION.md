# MRU Academic Result Export System - Technical Documentation

**Last Updated:** December 27, 2025  
**System Version:** Laravel 8.54 + Laravel-Admin  
**Purpose:** Export academic results in PDF and Excel formats with pass/fail status tracking

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Database Schema](#database-schema)
3. [Key Features](#key-features)
4. [Logo Display Solution](#logo-display-solution)
5. [Column Width Optimization](#column-width-optimization)
6. [Pass/Fail Status System](#passfail-status-system)
7. [Grade Validation Logic](#grade-validation-logic)
8. [Color Coding](#color-coding)
9. [Configuration Options](#configuration-options)
10. [Troubleshooting](#troubleshooting)
11. [Code Reference](#code-reference)

---

## System Overview

The MRU Academic Result Export system generates formatted academic transcripts with:
- **Specialization Separation**: Students grouped by their specialization
- **Matrix Format**: Courses as columns, students as rows
- **Status Tracking**: PASS/FAIL/INCOMPLETE indicators with color coding
- **Multiple Filters**: Academic year, semester, study year, programme, specialization, regno range
- **Dual Format**: PDF (landscape A4) and Excel (multi-sheet) exports

### Technology Stack

- **Framework**: Laravel 8.54
- **Admin Panel**: Laravel-Admin
- **PDF Generation**: barryvdh/laravel-dompdf
- **Excel Export**: maatwebsite/excel 3.x
- **Database**: MySQL 5.7.44
- **Environment**: MAMP (macOS)

---

## Database Schema

### Main Table: `mru_academic_result_exports`

```sql
CREATE TABLE `mru_academic_result_exports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `export_name` varchar(255) NOT NULL,
  `export_type` enum('pdf','excel','both') NOT NULL DEFAULT 'both',
  `academic_year` varchar(20) NOT NULL,
  `semester` enum('1','2') NOT NULL,
  `study_year` int(11) NOT NULL,                          -- Year of study (1-4)
  `programme_id` bigint(20) unsigned DEFAULT NULL,
  `specialisation_id` bigint(20) unsigned DEFAULT NULL,
  `minimum_passes_required` int(11) NOT NULL DEFAULT 0,   -- Pass threshold
  `start_range` varchar(20) DEFAULT NULL,
  `end_range` varchar(20) DEFAULT NULL,
  `sort_by` enum('regno','name') NOT NULL DEFAULT 'regno',
  `excel_path` varchar(500) DEFAULT NULL,
  `pdf_path` varchar(500) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `total_records` int(11) DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `configuration` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Related Tables

- **acad_student**: Student records (regno, firstname, othername, specialisation)
- **acad_results**: Grade records (regno, courseid, grade, score)
- **acad_course**: Course definitions (courseID, courseName)
- **acad_specialisation**: Specialization metadata (spec_id, spec, abbrev)
- **enterprise**: Institution branding (name, logo, contact info)

---

## Key Features

### 1. Study Year Filtering

Added to enable filtering by year of study (1st year, 2nd year, etc.)

**Database Field:**
```sql
ALTER TABLE mru_academic_result_exports 
ADD COLUMN study_year INT NOT NULL AFTER semester;
```

**Form Configuration:**
```php
$form->select('study_year', __('Year of Study'))
    ->options([
        1 => 'Year 1',
        2 => 'Year 2',
        3 => 'Year 3',
        4 => 'Year 4',
    ])
    ->required();
```

### 2. Minimum Passes Required

Defines the number of subjects a student must pass to be considered "PASSED"

**Database Field:**
```sql
ALTER TABLE mru_academic_result_exports 
ADD COLUMN minimum_passes_required INT NOT NULL DEFAULT 0 AFTER study_year;
```

**Form Configuration:**
```php
$form->number('minimum_passes_required', __('Minimum Passes Required'))
    ->default(0)
    ->min(0)
    ->required()
    ->help('Number of subjects a student must pass to be considered PASSED (0 = no check)');
```

**Usage:**
- Set to `0` to disable status checking
- Set to a positive number (e.g., `5`) to require that many passes

### 3. STATUS Column

Displays student's overall performance status with visual indicators

**Three Status Types:**
1. **PASS** (Green): Student passed >= minimum_passes_required courses
2. **FAIL** (Red): Student passed < minimum_passes_required courses
3. **INCOMPLETE** (Yellow): Student has missing results for some courses

---

## Logo Display Solution

### Problem

Logo images were not displaying in PDF exports when using direct file paths.

### Root Cause

DomPDF has limitations with local file path resolution, especially with symlinked directories (like Laravel's `storage/app/public` → `public/storage` symlink).

### Solution: Base64 Data URI Encoding

Convert logo images to base64-encoded data URIs for embedding directly in HTML.

**Implementation:**

```php
// In MruAcademicResultPdfService.php

// 1. Get logo path from enterprise record
$logoPath = $ent && $ent->logo ? public_path('storage/' . $ent->logo) : null;
$logoDataUri = null;

// 2. Encode to base64 data URI
if ($logoPath && file_exists($logoPath)) {
    $imageType = mime_content_type($logoPath);
    $imageData = base64_encode(file_get_contents($logoPath));
    $logoDataUri = "data:{$imageType};base64,{$imageData}";
}

// 3. Use in HTML
if ($logoDataUri) {
    $html .= '<img src="' . $logoDataUri . '" class="header-logo" alt="Logo" />';
}
```

**Benefits:**
- ✅ Works reliably across different server configurations
- ✅ No dependency on file system paths
- ✅ Embeds image directly in PDF document
- ✅ Compatible with all image formats (JPEG, PNG, GIF)

**Logo Storage Path:**
```
Database: images/13a8517fdada1bb3307abbdf6abfe616.jpeg
Full Path: /Applications/MAMP/htdocs/mru/public/storage/images/...
```

---

## Column Width Optimization

### Problem

Student name and registration number columns were taking excessive horizontal space, making the table difficult to read.

### Solution: Precise CSS Width Constraints + Text Truncation

**CSS Implementation:**

```css
/* Registration Number Column */
.regno-col {
    min-width: 35px;
    max-width: 45px;
    width: 40px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Student Name Column */
.name-col {
    min-width: 50px;
    max-width: 80px;
    width: 65px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* STATUS Column */
.status-col {
    min-width: 35px;
    max-width: 45px;
    width: 40px;
    text-align: center;
    font-weight: bold;
}
```

**PHP Name Truncation:**

```php
// Truncate long names to 25 characters
$studentName = trim(($student->firstname ?? '') . ' ' . ($student->othername ?? ''));
if (mb_strlen($studentName) > 25) {
    $studentName = mb_substr($studentName, 0, 25) . '.';
}
```

**Results:**
- Regno column: 60-100px → 40px (37.5% reduction)
- Name column: 60-100px → 65px (35% reduction)
- STATUS column: 40px (new)
- More courses visible per page
- Improved readability

---

## Pass/Fail Status System

### Overview

Calculates and displays each student's overall performance based on:
1. **Total courses** in their specialization
2. **Courses with results** (student has grade recorded)
3. **Courses passed** (grade is A, B, C, or D with optional +/-)

### Status Determination Logic

```php
$minRequired = $this->export->minimum_passes_required ?? 0;

if ($minRequired > 0) {
    // Check if student has results for all courses
    if ($coursesWithResults < $totalCourses) {
        $status = 'INCOMPLETE';
        $statusClass = 'status-incomplete';
    }
    // Check if student passed enough courses
    elseif ($coursesPassed >= $minRequired) {
        $status = 'PASS';
        $statusClass = 'status-pass';
    }
    // Student didn't pass enough courses
    else {
        $status = 'FAIL';
        $statusClass = 'status-fail';
    }
} else {
    // No minimum requirement set
    $status = 'N/A';
    $statusClass = '';
}
```

### Calculation Example

**Scenario:**
- Specialization has 8 courses
- Student has results for 7 courses
- Student passed 6 courses (A, B, C grades)
- Minimum passes required: 5

**Status:** `INCOMPLETE` (Yellow)  
**Reason:** Student missing results for 1 course

**Alternative Scenario:**
- Student has results for all 8 courses
- Student passed 6 courses

**Status:** `PASS` (Green)  
**Reason:** 6 passes >= 5 required

---

## Grade Validation Logic

### Passing Grades

The system recognizes the following grades as "passing":

```php
$passingGrades = ['A', 'B', 'C', 'D', 'B+', 'C+', 'D+', 'A+'];
```

### Validation Implementation

Two-layer approach for robustness:

```php
// 1. Array check (fast, exact match)
if (in_array($grade, $passingGrades)) {
    $coursesPassed++;
}
// 2. Regex fallback (flexible, catches variations)
elseif (preg_match('/^[A-D][+-]?$/i', $grade)) {
    $coursesPassed++;
}
```

**Grade Processing:**
```php
$grade = strtoupper(trim($result->grade ?? ''));
```

### Supported Grade Formats

✅ **Valid Passing Grades:**
- `A`, `A+`
- `B`, `B+`
- `C`, `C+`
- `D`, `D+`
- Case-insensitive (e.g., `a+`, `B`, `c+`)

❌ **Failing Grades:**
- `E`, `F`
- `FAIL`, `INCOMPLETE`
- Empty or NULL grades
- Any grade below D

---

## Color Coding

### PDF Implementation (CSS)

```css
/* PASS Status - Green */
.status-pass {
    background-color: #d4edda;
    color: #155724;
    font-weight: bold;
}

/* FAIL Status - Red */
.status-fail {
    background-color: #f8d7da;
    color: #721c24;
    font-weight: bold;
}

/* INCOMPLETE Status - Yellow */
.status-incomplete {
    background-color: #fff3cd;
    color: #856404;
    font-weight: bold;
}
```

### Excel Implementation (PhpSpreadsheet)

```php
use PhpOffice\PhpSpreadsheet\Style\Fill;

public function styles(Worksheet $sheet)
{
    // Get highest row with data
    $highestRow = $sheet->getHighestRow();
    
    // Loop through data rows (skip header row 1)
    for ($row = 2; $row <= $highestRow; $row++) {
        $statusValue = $sheet->getCell('C' . $row)->getValue();
        
        if ($statusValue === 'PASS') {
            $sheet->getStyle('C' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D4EDDA'],
                ],
                'font' => [
                    'color' => ['rgb' => '155724'],
                    'bold' => true,
                ],
            ]);
        }
        elseif ($statusValue === 'FAIL') {
            $sheet->getStyle('C' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8D7DA'],
                ],
                'font' => [
                    'color' => ['rgb' => '721C24'],
                    'bold' => true,
                ],
            ]);
        }
        elseif ($statusValue === 'INCOMPLETE') {
            $sheet->getStyle('C' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF3CD'],
                ],
                'font' => [
                    'color' => ['rgb' => '856404'],
                    'bold' => true,
                ],
            ]);
        }
    }
    
    return [];
}
```

### Color Scheme Reference

| Status | Background | Text | RGB (BG) | RGB (Text) |
|--------|-----------|------|----------|------------|
| **PASS** | Light Green | Dark Green | #D4EDDA | #155724 |
| **FAIL** | Light Red | Dark Red | #F8D7DA | #721C24 |
| **INCOMPLETE** | Light Yellow | Dark Yellow | #FFF3CD | #856404 |

---

## Configuration Options

### Export Configuration Form

**Basic Settings:**
```php
$form->text('export_name', __('Export Name'))->required();

$form->select('export_type', __('Export Type'))
    ->options([
        'pdf' => 'PDF Only',
        'excel' => 'Excel Only',
        'both' => 'Both PDF & Excel',
    ])
    ->default('both')
    ->required();
```

**Academic Period:**
```php
$form->text('academic_year', __('Academic Year'))
    ->placeholder('e.g., 2023/2024')
    ->required();

$form->select('semester', __('Semester'))
    ->options([
        '1' => 'Semester 1',
        '2' => 'Semester 2',
    ])
    ->required();

$form->select('study_year', __('Year of Study'))
    ->options([
        1 => 'Year 1',
        2 => 'Year 2',
        3 => 'Year 3',
        4 => 'Year 4',
    ])
    ->required();
```

**Filtering:**
```php
$form->select('programme_id', __('Programme'))
    ->options(MruProgramme::pluck('programme', 'id'))
    ->required();

$form->select('specialisation_id', __('Specialisation'))
    ->options(function ($id) {
        return AcadSpecialisation::pluck('spec', 'spec_id');
    })
    ->help('Leave empty to include all specialisations');

$form->text('start_range', __('Start Regno Range'))
    ->placeholder('e.g., 2023/001');

$form->text('end_range', __('End Regno Range'))
    ->placeholder('e.g., 2023/999');
```

**Pass/Fail Settings:**
```php
$form->number('minimum_passes_required', __('Minimum Passes Required'))
    ->default(0)
    ->min(0)
    ->required()
    ->help('Number of subjects a student must pass to be considered PASSED (0 = no check)');
```

**Sorting:**
```php
$form->select('sort_by', __('Sort By'))
    ->options([
        'regno' => 'Registration Number',
        'name' => 'Student Name',
    ])
    ->default('regno')
    ->required();
```

---

## Troubleshooting

### Issue 1: Logo Not Displaying

**Symptoms:**
- PDF generates successfully but logo image is missing
- Blank space where logo should appear

**Solutions:**

1. **Verify logo file exists:**
```bash
php artisan tinker
$ent = App\Models\Enterprise::first();
echo $ent->logo; // Should show: images/filename.jpeg
echo file_exists(public_path('storage/' . $ent->logo)); // Should be TRUE
```

2. **Check storage link:**
```bash
ls -la public/storage
# Should show: storage -> ../storage/app/public
php artisan storage:link
```

3. **Verify base64 encoding in code:**
```php
// In MruAcademicResultPdfService.php
// Make sure you're using $logoDataUri, not $logoPath
if ($logoDataUri) {
    $html .= '<img src="' . $logoDataUri . '" ...>';
}
```

### Issue 2: STATUS Column Shows N/A

**Cause:** `minimum_passes_required` is set to 0

**Solution:** Set a positive number in the export form (e.g., 5)

### Issue 3: Migration Conflicts

**Symptoms:**
```
SQLSTATE[42000]: Syntax error or access violation...
Migration table not found
```

**Solution:** Use manual ALTER TABLE instead of `php artisan migrate`

```bash
php artisan tinker

# Add column
DB::statement('
    ALTER TABLE mru_academic_result_exports 
    ADD COLUMN study_year INT NOT NULL AFTER semester
');

# Mark migration as run
DB::table('migrations')->insert([
    'migration' => '2025_12_27_add_study_year_to_mru_academic_result_exports_table',
    'batch' => DB::table('migrations')->max('batch') + 1
]);
```

### Issue 4: Excel Cells Not Color-Coded

**Cause:** `styles()` method not implemented or STATUS column position changed

**Solution:** Verify STATUS column is in position C (column 3) in Excel:

```php
public function headings(): array
{
    return ['Reg No', 'Student Name', 'STATUS', ...courses];
    //      Column A   Column B      Column C   (starts here)
}
```

### Issue 5: Students Showing INCOMPLETE Despite Having All Results

**Debug Steps:**

```php
// In MruAcademicResultPdfService.php, add debugging:
$totalCourses = $courses->count();
Log::info("Student {$student->regno}: Total courses = {$totalCourses}");

$coursesWithResults = 0;
foreach ($courses as $course) {
    $result = $studentResults->get($course->courseID);
    if ($result) {
        $coursesWithResults++;
        Log::info("Has result for course {$course->courseID}: {$result->grade}");
    } else {
        Log::info("NO result for course {$course->courseID}");
    }
}

Log::info("Courses with results: {$coursesWithResults}");
```

**Common Causes:**
- Course added to programme but student hasn't taken exam yet
- Result not recorded in `acad_results` table
- CourseID mismatch between `acad_course` and `acad_results`

---

## Code Reference

### File Structure

```
app/
├── Admin/
│   └── Controllers/
│       └── MruAcademicResultExportController.php
├── Models/
│   └── MruAcademicResultExport.php
└── Services/
    ├── MruAcademicResultExcelExport.php
    ├── MruAcademicResultPdfService.php
    └── MruAcademicResultSpecializationSheet.php

database/
└── migrations/
    ├── 2025_12_27_add_study_year_to_mru_academic_result_exports_table.php
    └── 2025_12_27_222901_add_minimum_passes_required_to_mru_academic_result_exports_table.php
```

### Key Service Methods

**PDF Generation:**
```php
// app/Services/MruAcademicResultPdfService.php

public function generatePdf(): string
{
    // 1. Fetch data and group by specialization
    $this->fetchData();
    
    // 2. Generate HTML with embedded logo
    $html = $this->generateHtmlContent();
    
    // 3. Convert to PDF
    $pdf = Pdf::loadHTML($html)
        ->setPaper('a4', 'landscape')
        ->setOptions([...]);
    
    // 4. Save and return path
    return $this->savePdf($pdf);
}

private function generateHtmlContent(): string
{
    // Encode logo to base64
    $logoDataUri = $this->getLogoDataUri();
    
    // Build HTML sections:
    // - Header with logo
    // - Info section
    // - Per-specialization tables with STATUS column
    
    return $html;
}
```

**Excel Generation:**
```php
// app/Services/MruAcademicResultExcelExport.php

public function sheets(): array
{
    $sheets = [];
    $minRequired = $this->export->minimum_passes_required ?? 0;
    
    foreach ($this->specializationData as $spec => $data) {
        $sheets[] = new MruAcademicResultSpecializationSheet(
            $data['spec_name'],
            $data['students'],
            $data['courses'],
            $data['results'],
            $minRequired  // Pass minimum requirement to sheet
        );
    }
    
    return $sheets;
}
```

**Excel Sheet with Conditional Formatting:**
```php
// app/Services/MruAcademicResultSpecializationSheet.php

public function map($student): array
{
    // Calculate STATUS for this student
    $status = $this->calculateStatus($student);
    
    // Build row: [Regno, Name, STATUS, ...course results]
    return [
        $student->regno,
        $studentName,
        $status,
        ...$courseResults
    ];
}

public function styles(Worksheet $sheet)
{
    // Apply color formatting to STATUS column (C)
    for ($row = 2; $row <= $highestRow; $row++) {
        $statusValue = $sheet->getCell('C' . $row)->getValue();
        
        // Apply appropriate color based on status
        if ($statusValue === 'PASS') { /* green */ }
        elseif ($statusValue === 'FAIL') { /* red */ }
        elseif ($statusValue === 'INCOMPLETE') { /* yellow */ }
    }
}
```

### Model Configuration

```php
// app/Models/MruAcademicResultExport.php

protected $fillable = [
    'export_name',
    'export_type',
    'academic_year',
    'semester',
    'study_year',              // Added for year filtering
    'programme_id',
    'specialisation_id',
    'minimum_passes_required', // Added for pass/fail logic
    'start_range',
    'end_range',
    'sort_by',
    'excel_path',
    'pdf_path',
    'status',
    'error_message',
    'total_records',
    'created_by',
    'configuration',
];

protected $casts = [
    'configuration' => 'array',
];
```

---

## Migration Files

### Study Year Migration

```php
// database/migrations/2025_12_27_add_study_year_to_mru_academic_result_exports_table.php

public function up()
{
    Schema::table('mru_academic_result_exports', function (Blueprint $table) {
        $table->integer('study_year')->after('semester');
    });
}

public function down()
{
    Schema::table('mru_academic_result_exports', function (Blueprint $table) {
        $table->dropColumn('study_year');
    });
}
```

### Minimum Passes Migration

```php
// database/migrations/2025_12_27_222901_add_minimum_passes_required_to_mru_academic_result_exports_table.php

public function up()
{
    Schema::table('mru_academic_result_exports', function (Blueprint $table) {
        $table->integer('minimum_passes_required')
            ->default(0)
            ->after('study_year');
    });
}

public function down()
{
    Schema::table('mru_academic_result_exports', function (Blueprint $table) {
        $table->dropColumn('minimum_passes_required');
    });
}
```

---

## Performance Considerations

### Large Datasets

When exporting results for entire programmes with 500+ students:

1. **Memory Limit:** Increase PHP memory limit
```php
ini_set('memory_limit', '512M');
```

2. **Chunk Processing:** Consider chunking student queries
```php
Student::where('specialisation', $spec)
    ->chunk(100, function ($students) {
        // Process batch
    });
```

3. **PDF Page Breaks:** DomPDF automatically handles page breaks, but test with large datasets

4. **Excel Performance:** PhpSpreadsheet is memory-intensive. For 1000+ rows, consider:
   - Using `\Maatwebsite\Excel\Concerns\ShouldQueue`
   - Implementing background job processing

### Optimization Tips

- Cache specialization and course data during generation
- Use eager loading for relationships: `with(['programme', 'specialisation'])`
- Generate Excel and PDF separately if both are large
- Store exports in storage and serve download links instead of inline generation

---

## Future Enhancement Ideas

1. **Email Notifications:** Send email when export completes
2. **Bulk Export:** Generate exports for multiple programmes at once
3. **Custom Grade Schemes:** Allow configurable passing grades per programme
4. **Analytics Dashboard:** Show pass/fail statistics
5. **Result Comparison:** Compare performance across semesters
6. **Grade Point Average:** Calculate and display GPA/CGPA
7. **Export Templates:** Save filter configurations as templates
8. **Audit Trail:** Track who generated which exports and when

---

## Support & Maintenance

### Testing Checklist

Before deploying changes:

- [ ] Test logo display in PDF
- [ ] Verify column widths are appropriate
- [ ] Test STATUS calculation with:
  - [ ] All passing grades
  - [ ] All failing grades
  - [ ] Mixed grades
  - [ ] Missing results
- [ ] Verify color coding in both PDF and Excel
- [ ] Test with minimum_passes_required = 0
- [ ] Test with various programme sizes (small & large)
- [ ] Test specialization filtering
- [ ] Test regno range filtering
- [ ] Verify Excel conditional formatting applies correctly
- [ ] Check PDF page breaks with large datasets

### Common Maintenance Tasks

**Update Passing Grades:**
```php
// In MruAcademicResultPdfService.php and MruAcademicResultSpecializationSheet.php
$passingGrades = ['A', 'B', 'C', 'D', 'B+', 'C+', 'D+', 'A+'];
```

**Change Color Scheme:**
```php
// Update hex colors in both CSS (PDF) and PhpSpreadsheet styling (Excel)
```

**Adjust Column Widths:**
```php
// In MruAcademicResultPdfService.php CSS section
.regno-col { width: 40px; }
.name-col { width: 65px; }
```

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | Dec 27, 2025 | Initial release with specialization separation |
| 1.1.0 | Dec 27, 2025 | Added logo base64 encoding fix |
| 1.2.0 | Dec 27, 2025 | Optimized column widths and name truncation |
| 1.3.0 | Dec 27, 2025 | Added study_year field |
| 1.4.0 | Dec 27, 2025 | Added pass/fail status system with color coding |

---

## License & Credits

**Developed for:** Mbarara University of Science and Technology  
**System:** MRU Academic Management System  
**Framework:** Laravel 8.54 + Laravel-Admin  
**PDF Library:** barryvdh/laravel-dompdf  
**Excel Library:** maatwebsite/excel 3.x  

---

**End of Documentation**
