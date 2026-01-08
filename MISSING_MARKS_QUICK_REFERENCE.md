# Missing Marks Feature - Quick Reference

## One-Line Summary
Track and report students with incomplete course marks submissions via grid button or direct URL, with HTML/Excel/PDF export options.

## Access Points

### 1. Grid Button
Navigate to: **Admin Panel → MRU Academic Result Exports**  
Click: **Red "Missing" button** (in the Actions column)

### 2. Direct URL
```
/admin/mru-academic-result-exports/{id}/generate-missing-marks
```

## URL Parameters

| Parameter | Values | Default | Description |
|-----------|--------|---------|-------------|
| `type` | `html`, `excel`, `pdf` | `html` | Export format |

## Examples

**View in browser**:
```
/admin/mru-academic-result-exports/123/generate-missing-marks
```

**Download Excel**:
```
/admin/mru-academic-result-exports/123/generate-missing-marks?type=excel
```

**Download PDF**:
```
/admin/mru-academic-result-exports/123/generate-missing-marks?type=pdf
```

## For Developers

### Basic Usage

```php
use App\Helpers\IncompleteMarksTracker;

// Initialize tracker
$tracker = new IncompleteMarksTracker();

// Track students
foreach ($students as $student) {
    $tracker->trackStudent($student, $courses, $results, $specName);
}

// Get results
$incomplete = $tracker->getIncompleteStudents();
$count = $tracker->getCount();
```

### Available Methods

```php
$tracker->trackStudent($student, $courses, $results, $specName);  // Track one student
$tracker->getIncompleteStudents();                                 // Get all incomplete
$tracker->getCount();                                              // Get count
$tracker->hasIncompleteStudents();                                 // Check if any exist
$tracker->getSortedIncompleteStudents('regno', 'asc');           // Get sorted list
$tracker->getIncompleteStudentsBySpecialization('BCS');          // Filter by spec
$tracker->getStatistics();                                         // Get stats
$tracker->clear();                                                 // Reset data
```

### Data Structure

```php
[
    'regno' => 'S21B13/001',
    'name' => 'John Doe',
    'specialization' => 'Computer Science',
    'total_courses' => 10,
    'marks_obtained' => 7,
    'marks_missing_count' => 3,
    'missing_courses' => 'CSC201, CSC202, CSC203'
]
```

## File Locations

| Component | Path |
|-----------|------|
| Helper Class | `app/Helpers/IncompleteMarksTracker.php` |
| Controller | `app/Http/Controllers/MruAcademicResultGenerateController.php` |
| HTML View | `resources/views/mru_missing_marks_report.blade.php` |
| Routes | `app/Admin/routes.php` |
| Grid Integration | `app/Admin/Controllers/MruAcademicResultExportController.php` |

## Testing

```bash
php /Applications/MAMP/htdocs/mru/test_incomplete_tracker.php
```

Expected: "All tests passed successfully!"

## Design Specs

### HTML
- Minimal CSS (no Bootstrap)
- Enterprise primary color only
- Font sizes: 9-18px
- Print support

### PDF
- A4 Landscape
- 7pt DejaVu Sans font
- Institutional logo + branding
- Matches standard PDF exports

### Excel
- Single sheet: "Incomplete Marks"
- 8 columns
- One row per student

## Key Features

✅ Grid button integration  
✅ Multiple export formats (HTML/Excel/PDF)  
✅ Enterprise branding  
✅ Professional styling  
✅ Print support  
✅ Sorting and filtering  
✅ Statistics generation  
✅ Comprehensive documentation  

## Status

**Version**: 1.0.0  
**Status**: ✅ Production Ready  
**Testing**: ✅ All tests passing  
**Documentation**: Complete

## Documentation

- **Full Docs**: `INCOMPLETE_MARKS_TRACKING_DOCUMENTATION.md`
- **Release Notes**: `MISSING_MARKS_FEATURE_RELEASE_NOTES.md`
- **Inline Docs**: Available in all PHP files

---

*For detailed information, see INCOMPLETE_MARKS_TRACKING_DOCUMENTATION.md*
