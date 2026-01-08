# Summary Reports Quick Reference Guide

## CGPA Grade Ranges (NCHE 2015)

| Classification | CGPA Min | CGPA Max | Controller Constant |
|---------------|----------|----------|---------------------|
| First Class | 4.40 | 5.00 | `GRADE_FIRST_CLASS_MIN/MAX` |
| Second Upper | 3.60 | 4.39 | `GRADE_SECOND_UPPER_MIN/MAX` |
| Second Lower | 2.80 | 3.59 | `GRADE_SECOND_LOWER_MIN/MAX` |
| Third Class | 2.00 | 2.79 | `GRADE_THIRD_CLASS_MIN/MAX` |

## Key Methods

### Generate Complete Summary
```php
public function generateCompleteSummary($id)
```
- **Purpose:** Generate PDF with all categories
- **Returns:** PDF stream
- **Route:** `mru-academic-result-exports/{id}/generate-complete-summary`

### Get Performance List
```php
private function getPerformanceList($cgpaMin, $cgpaMax, $params, $excludeRegnos = [])
```
- **Purpose:** Fetch students by CGPA range
- **Parameters:**
  - `$cgpaMin`: Minimum CGPA (float)
  - `$cgpaMax`: Maximum CGPA (float)
  - `$params`: Export filters (array)
  - `$excludeRegnos`: Students to exclude (array)
- **Returns:** Collection

## Common Tasks

### Add New Grade Category
1. Add constants to controller
2. Update `generateCompleteSummary()` to fetch category
3. Add section to Blade template
4. Update summary footer

### Modify CGPA Range
1. Update constants in controller:
   ```php
   const GRADE_FIRST_CLASS_MIN = 4.50; // Example
   ```
2. Clear cache: `php artisan view:clear && php artisan config:clear`

### Debug Missing Students
```php
// Add to getPerformanceList()
\Log::info('Query results:', ['count' => $results->count()]);
\Log::info('After CGPA filter:', ['count' => $filtered->count()]);
```

## File Locations

- **Controller:** `app/Admin/Controllers/MruAcademicResultExportController.php`
- **PDF Template:** `resources/views/admin/results/complete-summary-pdf.blade.php`
- **UI Page:** `resources/views/admin/results/summary-reports-export.blade.php`
- **Routes:** Defined in controller via Laravel Admin
- **Full Documentation:** `SUMMARY_REPORTS_DOCUMENTATION.md`

## Database Tables

- `acad_results` - Student course results
- `acad_student` - Student personal information
- `mru_academic_result_exports` - Export configurations

## CGPA Calculation
```sql
CGPA = SUM(CreditUnits × GradePoint) / SUM(CreditUnits)
```

## Testing Commands

```bash
# Clear caches
php artisan view:clear && php artisan config:clear && php artisan route:clear

# Check syntax
php -l app/Admin/Controllers/MruAcademicResultExportController.php

# Verify routes
php artisan route:list | grep summary

# Compile views
php artisan view:cache
```

## Performance Tips

1. Add database indexes on `regno`, `acad`, `semester`, `progid`
2. Use query caching for large datasets
3. Increase PHP memory limit for >1000 students
4. Process in chunks if needed

## Common Issues

| Issue | Solution |
|-------|----------|
| Missing students | Check CGPA calculation, verify filters |
| PDF timeout | Increase `max_execution_time` |
| Layout breaks | Adjust CSS `page-break` properties |
| Wrong CGPA | Verify `CreditUnits` and `gradept` data |

---

**Quick Start:**
1. Navigate to MRU Academic Result Exports
2. Click "Generate Summary Reports" on any export
3. Choose complete summary or individual category
4. PDF opens in new tab

**Need More Help?**
See `SUMMARY_REPORTS_DOCUMENTATION.md` for comprehensive guide.
