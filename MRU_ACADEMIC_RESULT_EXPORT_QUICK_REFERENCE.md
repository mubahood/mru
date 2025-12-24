# MRU Academic Result Export System - Quick Reference

## 🚀 Quick Start

### Access the System
```
Admin Panel → MRU → Academic Exports
```

### Create New Export
1. Click **Create**
2. Enter export name
3. Select format (Excel/PDF/Both)
4. Apply filters (optional)
5. Click **Submit**
6. Download when status = "Completed"

---

## 📋 System Components

| Component | Location | Purpose |
|-----------|----------|---------|
| **Model** | `app/Models/MruAcademicResultExport.php` | Data structure & relationships |
| **Migration** | `database/migrations/2025_12_24_172452_*` | Database table creation |
| **Excel Export** | `app/Exports/MruAcademicResultExcelExport.php` | Excel generation logic |
| **PDF Service** | `app/Services/MruAcademicResultPdfService.php` | PDF generation logic |
| **Controller** | `app/Admin/Controllers/MruAcademicResultExportController.php` | Admin interface & logic |
| **Routes** | `app/Admin/routes.php` | URL routing |
| **Menu** | `admin_menu` table | Navigation item |

---

## ⚙️ Configuration Options

### Export Type
- **Excel** - .xlsx file only
- **PDF** - .pdf file only
- **Both** - Both Excel and PDF files

### Filters
- **Academic Year** - Filter by year (e.g., 2023/2024)
- **Semester** - Filter by semester (1, 2, or 3)
- **Programme** - Filter by specific programme
- **Faculty** - Filter by faculty

### Options
- **Include Coursework** - Add coursework marks column
- **Include Practical** - Add practical marks column
- **Include Summary** - Add summary statistics section

### Sorting
- **Student** - Sort by student name (alphabetically)
- **Course** - Sort by course code
- **Grade** - Sort by grade (A+ to F)
- **Programme** - Sort by programme code

---

## 📊 Export Content

### Data Columns

**Always Included:**
- Student Reg No
- Student Name
- Programme
- Course Code
- Course Name
- Academic Year
- Semester
- Mark
- Grade
- GPA
- Credit Units
- Status (PASS/FAIL)

**Optional:**
- Coursework Mark (if enabled)
- Practical Mark (if enabled)

### Summary Statistics

**When Enabled:**
- Total Students
- Total Records
- Total Courses
- Average Mark
- Average GPA
- Pass Rate (%)
- Grade Distribution

---

## 💾 File Storage

**Location:** `storage/app/exports/`

**Naming:**
```
mru_academic_results_{export_id}_{timestamp}.xlsx
mru_academic_results_{export_id}_{timestamp}.pdf
```

**Example:**
```
mru_academic_results_5_2025-12-24_153045.xlsx
mru_academic_results_5_2025-12-24_153045.pdf
```

---

## 🎨 Output Formats

### Excel Format
- **Paper**: Default (auto-sized columns)
- **Header**: Blue (#2E86AB) with white text
- **Font**: 11px for headers, 10px for data
- **Layout**: Institution header + filters + data + summary

### PDF Format
- **Paper**: A4 Landscape
- **Header**: Blue with institution name
- **Font**: 8-9px (compact)
- **Colors**: Green (PASS), Red (FAIL)
- **Layout**: Header + filters + data table + summary

---

## 🔍 Grid Filters

| Filter | Type | Options |
|--------|------|---------|
| Export Name | Text | Search by name |
| Export Type | Select | Excel, PDF, Both |
| Academic Year | Select | All years from database |
| Semester | Select | 1, 2, 3 |
| Programme | Select | All programmes |
| Faculty | Select | All faculties |
| Status | Select | Pending, Processing, Completed, Failed |
| Created At | Date Range | From - To |

---

## 🏷️ Status Badges

| Status | Color | Meaning |
|--------|-------|---------|
| **Pending** | Yellow | Export queued, not started |
| **Processing** | Blue | Export currently generating |
| **Completed** | Green | Export successful, ready for download |
| **Failed** | Red | Export failed, see error message |

---

## 🔧 Common Actions

### Download Excel
```
Grid: Click "Excel" button in Actions column
Detail: Click "Download Excel" button
```

### Download PDF
```
Grid: Click "PDF" button in Actions column
Detail: Click "Download PDF" button
```

### Regenerate Failed Export
```
Grid: Click "Regenerate" button (for failed exports)
URL: /admin/mru-academic-result-exports/{id}/regenerate
```

### View Export Details
```
Grid: Click on any row
URL: /admin/mru-academic-result-exports/{id}
```

---

## 📈 Summary Calculations

### Pass Rate Formula
```
Pass Rate = (Passed / Total) × 100

Where Passed = Count of grades in: A+, A, B+, B, C+, C, D+, D
```

### Average Calculations
```
Average Mark = Sum of all marks / Total records
Average GPA = Sum of all GPAs / Total records
```

### Grade Distribution
```
Count of records grouped by grade
Example: A+ = 15, A = 23, B+ = 45, ...
```

---

## 🚨 Troubleshooting

### Export Shows "Failed"
**Solution:** Click "Regenerate" button or check error message in detail view

### Download Button Missing
**Check:** Status must be "Completed" and file must exist in storage

### Empty Export
**Check:** Filters might be too restrictive, verify database has matching records

### Summary Shows 0
**Check:** No records matched the filters, adjust filter criteria

### File Not Found Error
**Check:** File might have been manually deleted from storage/app/exports/

---

## 📞 Database Queries

### View All Exports
```sql
SELECT * FROM mru_academic_result_exports 
ORDER BY created_at DESC;
```

### Completed Exports Only
```sql
SELECT * FROM mru_academic_result_exports 
WHERE status = 'completed';
```

### Failed Exports
```sql
SELECT id, export_name, error_message 
FROM mru_academic_result_exports 
WHERE status = 'failed';
```

### Exports by User
```sql
SELECT e.*, u.name as creator_name 
FROM mru_academic_result_exports e
LEFT JOIN users u ON e.created_by = u.id
WHERE e.created_by = ?;
```

---

## 🎯 Best Practices

### Naming Conventions
✅ **Good:** "2023/2024 Sem 1 Faculty of Science Results"  
✅ **Good:** "BAED Programme Final Year Results"  
❌ **Bad:** "Export 1"  
❌ **Bad:** "Test"

### Filter Usage
- Use specific filters for focused reports
- Leave filters empty for comprehensive reports
- Combine filters for targeted analysis

### File Management
- Download important exports immediately
- Keep storage/app/exports/ directory clean
- Archive old exports if storage is limited

### Performance Tips
- Avoid exporting entire database (use filters)
- Generate during off-peak hours for large exports
- Use "Both" format only when necessary

---

## 🔐 Security Notes

- Only admin users can access export system
- Creator ID is automatically logged
- File paths are validated before download
- Direct URL access is protected by middleware
- Downloaded files contain institution branding

---

## 📚 Related Documentation

- **Full Documentation:** `MRU_ACADEMIC_RESULT_EXPORT_DOCUMENTATION.md`
- **Implementation Summary:** `MRU_ACADEMIC_RESULT_EXPORT_IMPLEMENTATION_SUMMARY.md`
- **Database Standards:** `MRU_DATABASE_RELATIONSHIPS_STANDARD.md`
- **Student Detail Page:** `STUDENT_DETAIL_PAGE_DOCUMENTATION.md`

---

## 🎓 Example Use Cases

### 1. Semester Results Report
```
Export Name: 2024/2025 Semester 1 Results
Type: Both
Academic Year: 2024/2025
Semester: 1
Include All Options: Yes
Sort By: Programme
```

### 2. Faculty Performance Analysis
```
Export Name: Faculty of Science 2024 Performance
Type: Excel
Faculty: Faculty of Science
Academic Year: 2024/2025
Include Summary: Yes
Sort By: Student
```

### 3. Programme Completion Report
```
Export Name: BAED Final Year 2024
Type: PDF
Programme: BAED
Academic Year: 2024/2025
Semester: 2
Sort By: Grade
```

### 4. Individual Course Results
```
Export Name: Mathematics Course Results Sem 1
Type: Excel
Academic Year: 2024/2025
Semester: 1
Include All: Yes
Sort By: Student
```

---

**Last Updated:** December 24, 2025  
**Version:** 1.0  
**Status:** Production Ready
