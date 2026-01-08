# Academic Results Summary Reports Documentation

## Overview

The Summary Reports system generates comprehensive PDF reports categorizing students by their academic performance according to the NCHE 2015 grading system. Reports include **professional bar and pie charts** generated using PHP's GD library for visual data representation. This documentation provides guidance for developers maintaining or extending this feature.

---

## Table of Contents

1. [System Architecture](#system-architecture)
2. [CGPA Grade Classification](#cgpa-grade-classification)
3. [Visual Analytics & Charts](#visual-analytics--charts)
4. [Key Components](#key-components)
5. [Database Schema](#database-schema)
6. [Usage Guide](#usage-guide)
7. [Extending the System](#extending-the-system)
8. [Troubleshooting](#troubleshooting)

---

## System Architecture

### Components Overview

```
MruAcademicResultExportController
├── Summary Report Generation Methods
│   ├── generateCompleteSummary() - All categories in one PDF + Charts
│   ├── generateVCList() - First Class only
│   ├── generateDeansList() - Second Class Upper only
│   ├── generatePassCases() - Second Class Lower only
│   └── generateRetakeCases() - Failed courses
│
├── Core Helper Methods
│   ├── getPerformanceList() - CGPA-based student categorization
│   ├── getHaltedCases() - Students with >6 retake courses
│   ├── getRetakeCases() - Students with failed courses
│   ├── getIncompleteCases() - Students with insufficient courses
│   └── getExportParams() - Parameter extraction
│
├── Chart Generation
│   └── ChartHelper (app/Helpers/ChartHelper.php)
│       ├── generateBarChart() - Horizontal bar charts
│       └── generatePieChart() - Pie charts with legend
│
└── PDF Generation
    └── generateSummaryPDF() - Generic PDF generator
```

### Data Flow

```
User Request
    ↓
Controller Method (e.g., generateCompleteSummary)
    ↓
Retrieve & Categorize Students (getPerformanceList, etc.)
    ↓
Generate Chart Images (ChartHelper)
    ↓
Pass Data + Charts to Blade View
    ↓
DomPDF Renders PDF with Embedded Charts
    ↓
getExportParams() - Extract filters
    ↓
Query Methods - Fetch categorized students
    ↓
Blade Template - Format as PDF
    ↓
PDF Response - Stream to browser
```

---

## CGPA Grade Classification

### NCHE 2015 Standards

The system uses the following CGPA ranges defined as constants in the controller:

| Grade Classification | CGPA Range | Constant Name |
|---------------------|------------|---------------|
| First Class (Honours) | 4.40 - 5.00 | `GRADE_FIRST_CLASS_MIN/MAX` |
| Second Class Upper Division | 3.60 - 4.39 | `GRADE_SECOND_UPPER_MIN/MAX` |
| Second Class Lower Division | 2.80 - 3.59 | `GRADE_SECOND_LOWER_MIN/MAX` |
| Third Class (Pass) | 2.00 - 2.79 | `GRADE_THIRD_CLASS_MIN/MAX` |

### CGPA Calculation Formula

```sql
CGPA = SUM(CreditUnits × GradePoint) / SUM(CreditUnits)
```

This weighted average ensures courses with more credit units have greater impact on overall performance.

---

## Visual Analytics & Charts

### Overview

The summary reports include professional **bar charts** and **pie charts** to visualize student grade distribution. Charts are generated server-side using PHP's GD library and embedded as base64-encoded PNG images in the PDF.

### Chart Types

#### 1. Horizontal Bar Chart
- Shows student count for each category
- Color-coded bars matching category colors
- Sorted by count (descending)
- Displays values both inside and outside bars
- Grid lines for easy reading

#### 2. Pie Chart with Legend
- Visual percentage distribution
- Professional color scheme
- Detailed legend with counts and percentages
- Anti-aliased rendering for smooth edges

### ChartHelper Class

Located in: `app/Helpers/ChartHelper.php`

```php
use App\Helpers\ChartHelper;

// Generate bar chart
$barChart = ChartHelper::generateBarChart($data, [
    'width' => 800,
    'height' => 500,
    'title' => 'Student Distribution by Category',
    'bgColor' => [248, 249, 250],
]);

// Generate pie chart
$pieChart = ChartHelper::generatePieChart($data, [
    'size' => 400,
    'title' => 'Grade Distribution Percentage',
    'bgColor' => [255, 255, 255],
]);
```

**Data Format:**
```php
$data = [
    ['label' => 'Category Name', 'value' => 100, 'color' => '#1a5490'],
    // ... more categories
];
```

**Return Value:** Base64-encoded PNG image data URL ready for HTML/PDF embedding

### Color Scheme

| Category | Color Code | Visual |
|----------|-----------|--------|
| First Class | #1a5490 | Enterprise Blue |
| Second Class Upper | #2e7d32 | Success Green |
| Second Class Lower | #f57c00 | Warning Orange |
| Third Class | #c62828 | Red |
| Halted Cases | #6a1b9a | Purple |
| Retake Cases | #455a64 | Gray |

### Key Insights Section

The report automatically generates data-driven insights:
- **Highest Category**: Most populous grade category
- **Honours Degree Performance**: Combined First + Second Upper percentage
- **Overall Pass Rate**: All passing grades percentage
- **Attention Required**: Alert for intervention-needed cases

---

## Key Components

### 1. Controller Constants

Located in: `app/Admin/Controllers/MruAcademicResultExportController.php`

```php
class MruAcademicResultExportController extends AdminController
{
    // CGPA thresholds for grade classification
    const GRADE_FIRST_CLASS_MIN = 4.40;
    const GRADE_FIRST_CLASS_MAX = 5.00;
    
    const GRADE_SECOND_UPPER_MIN = 3.60;
    const GRADE_SECOND_UPPER_MAX = 4.39;
    
    const GRADE_SECOND_LOWER_MIN = 2.80;
    const GRADE_SECOND_LOWER_MAX = 3.59;
    
    const GRADE_THIRD_CLASS_MIN = 2.00;
    const GRADE_THIRD_CLASS_MAX = 2.79;
}
```

**Why use constants?**
- Centralized grade boundaries
- Easy to update if grading system changes
- Prevents hardcoding throughout the application
- Self-documenting code

---

### 2. Core Method: getPerformanceList()

**Purpose:** Retrieve students within a specific CGPA range

**Signature:**
```php
private function getPerformanceList($cgpaMin, $cgpaMax, $params, $excludeRegnos = [])
```

**Parameters:**
- `$cgpaMin` (float): Minimum CGPA threshold (inclusive)
- `$cgpaMax` (float): Maximum CGPA threshold (inclusive)
- `$params` (array): Export filters (acad, semester, progid, studyyear, etc.)
- `$excludeRegnos` (array): Optional registration numbers to exclude

**Returns:** `\Illuminate\Support\Collection`

**Algorithm:**
1. Join `acad_results` and `acad_student` tables
2. Calculate CGPA using weighted average subquery
3. Apply export configuration filters
4. Filter by CGPA range in memory
5. Sort by CGPA descending
6. Apply range limiting if specified
7. Exclude specified registration numbers

**Example Usage:**
```php
// Get First Class students
$firstClass = $this->getPerformanceList(
    self::GRADE_FIRST_CLASS_MIN,
    self::GRADE_FIRST_CLASS_MAX,
    $params
);

// Get Second Class Upper excluding First Class students
$secondClassUpper = $this->getPerformanceList(
    self::GRADE_SECOND_UPPER_MIN,
    self::GRADE_SECOND_UPPER_MAX,
    $params,
    $firstClass->pluck('regno')->toArray()
);
```

---

### 3. Export Parameters

**Structure:**
```php
[
    'acad' => '2023/2024',           // Academic year
    'semester' => 1,                  // Semester number (1 or 2)
    'progid' => 'BIT',                // Programme code
    'studyyear' => 3,                 // Year of study (1-4)
    'specialisation_id' => 'CS',      // Optional specialisation
    'start_range' => 1,               // Starting position
    'end_range' => 100,               // Ending position
]
```

**Extraction Method:**
```php
private function getExportParams($export)
{
    return [
        'acad' => $export->academic_year,
        'semester' => $export->semester,
        'progid' => $export->programme_id,
        'studyyear' => $export->study_year,
        'specialisation_id' => $export->specialisation_id,
        'start_range' => $export->start_range,
        'end_range' => $export->end_range,
    ];
}
```

---

### 4. Complete Summary Generation

**Method:** `generateCompleteSummary($id)`

**Process:**
1. Load export record from database
2. Extract export parameters
3. Fetch all student categories:
   - First Class (4.40-5.00)
   - Second Class Upper (3.60-4.39)
   - Second Class Lower (2.80-3.59)
   - Third Class (2.00-2.79)
   - Halted Cases (>6 retake courses)
   - Retake Cases (failed courses)
4. Pass data to Blade template
5. Generate PDF using DomPDF
6. Stream PDF to browser

**Example Code:**
```php
public function generateCompleteSummary($id)
{
    $export = MruAcademicResultExport::findOrFail($id);
    $params = $this->getExportParams($export);
    
    $firstClass = $this->getPerformanceList(
        self::GRADE_FIRST_CLASS_MIN, 
        self::GRADE_FIRST_CLASS_MAX, 
        $params
    );
    
    // ... fetch other categories ...
    
    $data = [
        'export' => $export,
        'params' => $params,
        'firstClass' => $firstClass,
        // ... other categories ...
    ];
    
    $pdf = Pdf::loadView('admin.results.complete-summary-pdf', $data);
    $pdf->setPaper('A4', 'portrait');
    
    return $pdf->stream('Summary_' . date('Y-m-d') . '.pdf');
}
```

---

## Database Schema

### Primary Tables

#### acad_results
```sql
CREATE TABLE acad_results (
    regno VARCHAR(50),          -- Student registration number
    acad VARCHAR(20),           -- Academic year (e.g., "2023/2024")
    semester INT,               -- Semester (1 or 2)
    progid VARCHAR(20),         -- Programme code
    studyyear INT,              -- Year of study (1-4)
    spec_id VARCHAR(20),        -- Specialisation ID (optional)
    courseid VARCHAR(20),       -- Course code
    CreditUnits INT,            -- Course credit units
    gradept DECIMAL(3,2),       -- Grade point (0.00-5.00)
    score DECIMAL(5,2),         -- Percentage score
    -- ... other fields ...
);
```

#### acad_student
```sql
CREATE TABLE acad_student (
    regno VARCHAR(50) PRIMARY KEY,  -- Student registration number
    entryno VARCHAR(50),             -- Entry number
    firstname VARCHAR(100),          -- First name
    othername VARCHAR(100),          -- Other names
    gender ENUM('M', 'F'),           -- Gender
    -- ... other fields ...
);
```

#### mru_academic_result_exports
```sql
CREATE TABLE mru_academic_result_exports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    export_name VARCHAR(255),
    academic_year VARCHAR(20),
    semester INT,
    study_year INT,
    programme_id VARCHAR(20),
    specialisation_id VARCHAR(20),
    start_range INT,
    end_range INT,
    minimum_passes_required INT,    -- Expected course count
    -- ... other fields ...
);
```

---

## Usage Guide

### For Administrators

#### Generating a Complete Summary

1. Navigate to **MRU Academic Result Exports**
2. Locate your export record
3. Click **"Generate Summary Reports"** button
4. Click **"Generate Complete Summary Report (All Lists)"**
5. PDF will open in new tab

#### Generating Individual Category Reports

1. Follow steps 1-3 above
2. Click on specific category card:
   - First Class
   - Second Class Upper
   - Second Class Lower
   - Retake Cases
3. PDF will open in new tab

### For Developers

#### Creating a New Export Configuration

```php
$export = MruAcademicResultExport::create([
    'export_name' => 'BIT Year 3 Semester 1 2023/2024',
    'academic_year' => '2023/2024',
    'semester' => 1,
    'study_year' => 3,
    'programme_id' => 'BIT',
    'specialisation_id' => null,
    'start_range' => 1,
    'end_range' => 150,
    'minimum_passes_required' => 9,
]);
```

#### Programmatically Generating Report

```php
$controller = new MruAcademicResultExportController();
$pdf = $controller->generateCompleteSummary($exportId);
```

---

## Extending the System

### Adding a New Grade Category

**Step 1:** Add constants to controller
```php
const GRADE_NEW_CATEGORY_MIN = 1.50;
const GRADE_NEW_CATEGORY_MAX = 1.99;
```

**Step 2:** Update `generateCompleteSummary()` method
```php
$newCategory = $this->getPerformanceList(
    self::GRADE_NEW_CATEGORY_MIN,
    self::GRADE_NEW_CATEGORY_MAX,
    $params
);

$data = [
    // ... existing categories ...
    'newCategory' => $newCategory,
];
```

**Step 3:** Update Blade template
```blade
{{-- New Category Section --}}
<div class="section-header">
    NEW CATEGORY
    <span class="count">{{ count($newCategory) }} Students</span>
</div>

@if(count($newCategory) > 0)
<table class="data-table">
    {{-- table structure --}}
</table>
@endif
```

**Step 4:** Update summary footer
```blade
New Category: <strong>{{ count($newCategory) }}</strong> |
```

---

### Modifying CGPA Ranges

**Option 1:** Update constants (recommended)
```php
const GRADE_FIRST_CLASS_MIN = 4.50; // Changed from 4.40
```

**Option 2:** Create configuration file
```php
// config/grading.php
return [
    'first_class' => ['min' => 4.40, 'max' => 5.00],
    'second_upper' => ['min' => 3.60, 'max' => 4.39],
    // ... etc
];

// Controller usage
$grades = config('grading.first_class');
$firstClass = $this->getPerformanceList($grades['min'], $grades['max'], $params);
```

---

### Adding Custom Filters

**Example:** Filter by faculty

```php
// Add to getPerformanceList() method
if (!empty($params['faculty_id'])) {
    $query->join('mru_programmes as p', 'p.progcode', '=', 'r.progid')
          ->where('p.faculty_id', $params['faculty_id']);
}
```

---

## Troubleshooting

### Issue: Missing Students in Report

**Possible Causes:**
1. Student CGPA calculation returned NULL
2. Student filtered out by export parameters
3. Student excluded by range limiting

**Solution:**
```php
// Add debug logging
\Log::info('Total students before filtering:', ['count' => $results->count()]);
\Log::info('Students after CGPA filter:', ['count' => $filtered->count()]);
```

---

### Issue: Incorrect CGPA Calculation

**Verification Query:**
```sql
SELECT 
    regno,
    SUM(CreditUnits * gradept) as weighted_sum,
    SUM(CreditUnits) as total_credits,
    SUM(CreditUnits * gradept) / NULLIF(SUM(CreditUnits), 0) as cgpa
FROM acad_results
WHERE regno = 'STUDENT_REGNO'
GROUP BY regno;
```

**Check:**
- Are `CreditUnits` values correct?
- Are `gradept` values in range 0.00-5.00?
- Is NULLIF preventing division by zero?

---

### Issue: PDF Generation Timeout

**Causes:**
- Too many students (>1000)
- Complex queries
- Insufficient memory

**Solutions:**

1. **Increase PHP limits:**
```php
// In controller method
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');
```

2. **Paginate results:**
```php
$filtered = $filtered->chunk(500)->map(function($chunk) {
    // Process chunk
    return $chunk;
})->flatten();
```

3. **Queue the generation:**
```php
// Create a job
dispatch(new GenerateSummaryReportJob($exportId));
```

---

### Issue: Layout Breaks in PDF

**Common Fixes:**

1. **Avoid page breaks within tables:**
```css
table {
    page-break-inside: avoid;
}
```

2. **Force page breaks before sections:**
```css
.section-header {
    page-break-before: always;
}
```

3. **Adjust margins:**
```css
@page {
    margin: 15mm 10mm; /* Increase if content cut off */
}
```

---

## Code Style Guidelines

### Naming Conventions

- **Methods:** camelCase (`getPerformanceList`)
- **Variables:** camelCase (`$firstClass`)
- **Constants:** UPPER_SNAKE_CASE (`GRADE_FIRST_CLASS_MIN`)
- **Database columns:** snake_case (`academic_year`)

### Documentation Standards

**Method Documentation:**
```php
/**
 * Short description (one line)
 * 
 * Longer description explaining the method's purpose,
 * algorithm, and any important notes.
 * 
 * @param type $param Description
 * @return type Description
 */
```

**Blade Comments:**
```blade
{{-- 
    Component Name
    Description of what this section does
--}}
```

---

## Testing Checklist

Before deploying changes:

- [ ] Verify all CGPA ranges match NCHE 2015 standards
- [ ] Test with small dataset (10-20 students)
- [ ] Test with large dataset (500+ students)
- [ ] Verify PDF renders correctly on different browsers
- [ ] Check all categories show correct student counts
- [ ] Verify no duplicate students across categories
- [ ] Test with edge cases (CGPA exactly on boundary)
- [ ] Validate export parameter filtering
- [ ] Check PDF file naming convention
- [ ] Review generated SQL queries for performance

---

## Performance Optimization

### Query Optimization

1. **Index key columns:**
```sql
CREATE INDEX idx_regno ON acad_results(regno);
CREATE INDEX idx_filters ON acad_results(acad, semester, progid, studyyear);
```

2. **Use query caching:**
```php
$results = Cache::remember("export_{$exportId}", 3600, function() {
    return $query->get();
});
```

### Memory Management

```php
// Process in chunks for large datasets
DB::table('acad_results')
    ->where(/* filters */)
    ->chunk(1000, function($students) {
        // Process each chunk
    });
```

---

## Future Enhancements

### Planned Features

1. **Export to Excel** - Generate summary in spreadsheet format
2. **Email Reports** - Schedule automatic report generation
3. **Historical Comparison** - Compare performance across semesters
4. **Analytics Dashboard** - Visual charts of grade distribution
5. **Batch Generation** - Generate reports for multiple programmes

### Configuration Options

Consider adding to `config/academic.php`:

```php
return [
    'grading_system' => [
        'first_class' => ['min' => 4.40, 'max' => 5.00, 'label' => 'First Class (Honours)'],
        // ... other grades
    ],
    
    'pdf_settings' => [
        'orientation' => 'portrait',
        'paper_size' => 'A4',
        'font_size' => '8.5pt',
    ],
    
    'performance_limits' => [
        'max_students_per_pdf' => 1000,
        'query_timeout' => 60,
    ],
];
```

---

## Support & Contact

For technical support or questions:
- **Email:** muhindo@schooldynamics.ug
- **Documentation:** This file
- **Code Location:** `app/Admin/Controllers/MruAcademicResultExportController.php`

---

**Last Updated:** January 2026  
**Version:** 2.0  
**Maintained by:** MRU Development Team
