# MRU System Documentation

**Version:** 1.0  
**Date:** December 27, 2025  
**Project:** Mutesa I Royal University Academic Management System

---

## Table of Contents
1. [System Overview](#system-overview)
2. [Database Architecture](#database-architecture)
3. [Models & Relationships](#models--relationships)
4. [Controllers & Routes](#controllers--routes)
5. [PDF Generation](#pdf-generation)
6. [Critical Issues & Solutions](#critical-issues--solutions)
7. [Best Practices](#best-practices)

---

## System Overview

### Technology Stack
- **Framework:** Laravel 8.54
- **Admin Panel:** Laravel-Admin 1.x
- **Database:** MySQL 5.7.44 (mru_main)
- **PDF Library:** barryvdh/laravel-dompdf v2.2.0
- **Institution:** Mutesa I Royal University

### Enterprise Configuration
```php
Enterprise ID: 1
Name: Mutesa I Royal University
Email: info@mru.ac.ug
Phone: +256 414 271 068, +256 414 271 069
Address: Mengo, Kampala, Uganda
Logo: storage/images/13a8517fdada1bb3307abbdf6abfe616.jpeg
```

---

## Database Architecture

### Core Academic Tables

#### 1. **acad_student** (30,916 records)
Primary student records table for MRU system.

**Key Fields:**
- `ID` (Primary Key)
- `firstname`, `middlename`, `lastname`
- `Reg_no` (Registration number)
- `progid` (Programme code)
- `email`, `telephone`
- `is_processed`, `is_processed_successful`, `processing_reason` (Added 2025-12-26)

**Relationships:**
- Links to `acad_programme` via `progid`
- Referenced by `student_has_semeters` via `student_id`

**Critical Notes:**
- ⚠️ MRU uses `acad_student` NOT `admin_users` for student records
- ⚠️ StudentHasSemeter model validation expects `admin_users` - causes conflicts

---

#### 2. **acad_programme**
Academic programme definitions.

**Key Fields:**
- `progcode` (Primary Key, VARCHAR)
- `progname` (Full programme name)
- `abbrev` (Abbreviation)
- `faculty_code` (FK to acad_faculties)
- `levelCode` (1=Cert, 2=Dip, 3=Degree, 4=Masters, 5=PhD)
- `couselength`, `maxduration` (Duration in years)
- `study_system` (Semester/Session)
- `total_semesters`
- `is_processed`, `process_passed`, `error_mess` (Processing status)
- Semester course counts: `number_of_semester_1_courses` through `number_of_semester_12_courses`

**Statistics:**
- Total: 127 programmes
- Undergraduate: 119
- Postgraduate: 8
- Semester System: 120
- Session System: 7

---

#### 3. **acad_curriculum** (142 records)
Curriculum version control system tracking NCHE-approved curricula.

**Purpose:**
- Version control for programme curricula
- NCHE compliance tracking
- Intake-based differentiation
- Historical curriculum records

**Key Fields:**
- `ID` (Primary Key)
- `Tittle` (e.g., "ACAD 2018 AUGUST")
- `Description` (Approval details)
- `Progcode` (FK to acad_programme)
- `StartYear` (Effective year)
- `intake` (AUGUST, JANUARY, JULY, JUNE, FEBRUARY)

**Statistics:**
- 126 programmes covered
- Date range: 2007-2025
- 73 curriculum versions actively in use

**Relationship:**
```
acad_curriculum (ID) → acad_programmecourses (CurriculumID)
```

---

#### 4. **acad_programmecourses** (3,834 records)
Maps courses to programmes with year/semester placement.

**Key Fields:**
- `ID` (Primary Key)
- `progcode` (FK to acad_programme)
- `course_code` (FK to acad_course.courseID)
- `study_year` (1-5)
- `semester` (1-2)
- `CurriculumID` (FK to acad_curriculum)

**Purpose:**
- Defines programme curriculum structure
- Specifies when courses should be taken
- Links to curriculum versions

**ALL 3,834 records reference a CurriculumID** - no orphan entries.

---

#### 5. **acad_course**
Course master data.

**Key Fields:**
- `courseID` (Primary Key, VARCHAR(25)) ⚠️ NOT `coursecode`
- `courseName` (VARCHAR(250)) ⚠️ Capital N
- `CreditUnit` (DOUBLE)
- `ContactHr`, `LectureHr`, `PracticalHr`
- `courseDescription` (TEXT)
- `stat`, `CoreStatus`

**Critical Notes:**
- Primary key is `courseID` not `coursecode`
- Course name is `courseName` with capital N
- Used in relationships via `courseID`

---

#### 6. **student_has_semeters**
Student semester enrollment tracking.

**Key Fields:**
- `id` ⚠️ **NOT AUTO-INCREMENT** - must generate manually
- `student_id` (References acad_student.ID)
- `term_id` (Semester reference)
- `enterprise_id`

**Critical Issue:**
```php
// StudentHasSemeter model has boot() validation
protected static function boot() {
    static::creating(function ($model) {
        // Validates student exists in User (admin_users) table
        $user = User::find($model->student_id);
        if (!$user || $user->user_type != 'student') {
            throw new Exception("Student account not found");
        }
    });
}
```

**Problem:** MRU students are in `acad_student`, NOT `admin_users`

**Solution:**
```php
// Bypass Eloquent model - use direct DB insertion
$nextId = DB::table('student_has_semeters')->max('id') + 1;
DB::table('student_has_semeters')->insert([
    'id' => $nextId,  // Manual ID generation
    'student_id' => $student->ID,  // acad_student.ID
    'term_id' => $semesterForReg->id,
    'enterprise_id' => $student->enterprise_id ?? 1,
    // ... other fields
]);
```

---

## Models & Relationships

### Naming Convention
All MRU models MUST be prefixed with `Mru`:
- ✅ `MruProgramme`, `MruStudent`, `MruCourse`
- ❌ `Programme`, `Student`, `Course`

### Model Template Structure

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * MruXxx Model
 * 
 * Brief description of model purpose.
 * 
 * Database Structure:
 * - Table: actual_table_name
 * - Primary Key: field_name
 * - Total Records: ~X,XXX records
 * 
 * @property type $field Description
 * 
 * @method static Builder scopeName($param) Description
 */
class MruXxx extends Model
{
    protected $table = 'actual_table_name';
    protected $primaryKey = 'field_name';
    public $timestamps = false; // If no created_at/updated_at
    
    protected $fillable = [
        'field1',
        'field2',
    ];
    
    protected $casts = [
        'numeric_field' => 'integer',
    ];
    
    // RELATIONSHIPS
    public function relatedModel(): BelongsTo {
        return $this->belongsTo(RelatedModel::class, 'foreign_key', 'owner_key');
    }
    
    // SCOPES
    public function scopeActive(Builder $query): Builder {
        return $query->where('status', 'active');
    }
    
    // ACCESSORS
    public function getFullNameAttribute(): string {
        return "{$this->field1} {$this->field2}";
    }
}
```

### Key Relationships

```php
// Programme → Faculty
MruProgramme::belongsTo(MruFaculty, 'faculty_code', 'faculty_code');

// Programme → Students
MruProgramme::hasMany(MruStudent, 'progid', 'progcode');

// Programme Courses → Programme
MruProgrammeCourse::belongsTo(MruProgramme, 'progcode', 'progcode');

// Programme Courses → Course
MruProgrammeCourse::belongsTo(MruCourse, 'course_code', 'courseID');

// Programme Courses → Curriculum
MruProgrammeCourse::belongsTo(MruCurriculum, 'CurriculumID', 'ID');

// Curriculum → Programme
MruCurriculum::belongsTo(MruProgramme, 'Progcode', 'progcode');

// Curriculum → Programme Courses
MruCurriculum::hasMany(MruProgrammeCourse, 'CurriculumID', 'ID');
```

---

## Controllers & Routes

### Controller Naming Convention
- Format: `Mru{EntityName}Controller`
- Examples: `MruProgrammeController`, `MruCurriculumController`

### Route Naming Convention
- Format: `mru-{entity-name}` (kebab-case)
- Examples: `mru-programmes`, `mru-curriculums`

### Grid Best Practices

```php
protected function grid()
{
    $grid = new Grid(new MruModel());
    
    // Eager load relationships for performance
    $grid->model()->with(['relation1', 'relation2']);
    
    // Disable resource-intensive features
    $grid->disableBatchActions(); // Or customize
    $grid->disableExport(); // Unless needed
    
    // Compact display
    $grid->column('field', 'Label')
        ->display(function ($value) {
            // Minimal HTML, avoid heavy computations
            return $value;
        })
        ->sortable();
    
    // Relationship display
    $grid->column('relation.field', 'Label')
        ->display(function () {
            return $this->relation ? $this->relation->field : '-';
        });
    
    // Filters - simple and fast
    $grid->filter(function ($filter) {
        $filter->disableIdFilter();
        $filter->like('field', 'Label');
        $filter->equal('status', 'Status')->select([...]);
    });
    
    $grid->paginate(50); // Reasonable page size
    
    return $grid;
}
```

### Form Best Practices

```php
protected function form()
{
    $form = new Form(new MruModel());
    
    // Simple, straight layout - NO TABS
    $form->text('field', 'Label')
        ->required()
        ->rules('required|max:255');
    
    $form->select('foreign_key', 'Relation')
        ->options(RelatedModel::pluck('name', 'id'))
        ->required();
    
    // Minimal validation, no help text
    $form->number('numeric', 'Number')
        ->min(0)
        ->required();
    
    $form->disableCreatingCheck();
    $form->disableEditingCheck();
    $form->disableViewCheck();
    
    return $form;
}
```

### Route Registration

```php
// app/Admin/routes.php
$router->resource('mru-entity-name', MruEntityNameController::class);
```

### Menu Registration

```sql
-- Parent: 195 (MRU submenu)
INSERT INTO admin_menu (parent_id, order, title, icon, uri, created_at, updated_at)
VALUES (195, [next_order], 'Menu Title', 'fa-icon', 'mru-entity-name', NOW(), NOW());
```

---

### 5. PDF Generation

All PDF generation in the MRU system follows a consistent pattern for dynamic branding and space optimization.

#### Dynamic Header Pattern

**❌ NEVER Hardcode University Information:**
```php
// WRONG - Hardcoded
$html = '<h1>MBARARA UNIVERSITY OF SCIENCE & TECHNOLOGY</h1>';
$html = '<h1>MOUNTAINS OF THE MOON UNIVERSITY</h1>';
$html = '<h1>MUTEESA I ROYAL UNIVERSITY</h1>';

// WRONG - Wrong table
$company = DB::table('companyinfo')->first();
```

**✅ ALWAYS Use Enterprise Model:**
```php
// Controller
use App\Models\Enterprise;

$ent = Enterprise::first();
$logoPath = $ent && $ent->logo ? public_path('storage/' . $ent->logo) : null;

$pdf = PDF::loadView('pdf.template', [
    'data' => $data,
    'ent' => $ent,
    'logoPath' => $logoPath,
]);
```

**✅ View Template (Blade):**
```blade
<table class="header-table">
    <tbody>
        <tr>
            <td class="header-logo">
                @if($logoPath && file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="{{ $ent->name }}">
                @endif
            </td>
            <td class="header-center">
                <h1>{{ strtoupper($ent->name) }}</h1>
                @if($ent->address)
                    <p>{{ $ent->address }}</p>
                @endif
                @if($ent->phone_number)
                    <p>Tel: {{ $ent->phone_number }}
                        @if($ent->phone_number_2), {{ $ent->phone_number_2 }}@endif
                    </p>
                @endif
                @if($ent->email)
                    <p>Email: {{ $ent->email }}</p>
                @endif
            </td>
            <td class="header-spacer"></td>
        </tr>
    </tbody>
</table>

<hr class="divider">
```

**✅ HTML String (Service/Export):**
```php
// Get dynamic enterprise data
$ent = Enterprise::first();
$institutionName = $ent ? strtoupper($ent->name) : 'MUTESA I ROYAL UNIVERSITY';

$html = '<!DOCTYPE html>
<html>
<head>
    <title>' . e($export->name) . '</title>
    <style>...</style>
</head>
<body>
    <div class="header">
        <h1>' . e($institutionName) . '</h1>
    </div>
    ...
</body>
</html>';
```

### Space-Optimized PDF Styling

All MRU PDFs use consistent space-optimized styling for professional, compact documents:

```css
@page {
    margin: 12mm 10mm;  /* Minimal page margins */
}

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 8pt;     /* Compact font size */
    line-height: 1.2;   /* Tight line spacing */
    margin: 0;
    padding: 0;
}

table {
    border-collapse: collapse;
    width: 100%;
    font-size: 7pt;     /* Even smaller for tables */
}

table th, table td {
    padding: 3px 4px;   /* Minimal padding */
    border: 1px solid #ddd;
}

.compact-section {
    padding: 4px 6px;
    margin-bottom: 8px;
}
```

**Key Principles:**
- ❌ Never hardcode university name/contact
- ✅ Always use Enterprise model
- ✅ Dynamic logo loading with file existence check
- ✅ Compact spacing (7-8pt fonts for body/tables)
- ✅ Minimal margins (10-12mm page margins)
- ✅ Tight line-height (1.1-1.2)
- ✅ No page breaks unless necessary
- ✅ Consistent color scheme (#2E86AB for headers)

---

## Academic Result Exports

The MRU system includes a comprehensive academic result export system with Excel and PDF generation.

### Components

**Controller:** `app/Admin/Controllers/MruAcademicResultExportController.php`
- Manages export configurations
- Grid with filters (year, semester, programme, faculty, status)
- Generate and download buttons
- No help text in forms (following MRU guidelines)

**Model:** `app/Models/MruAcademicResultExport.php`
- Table: `mru_academic_result_exports`
- Relationships: creator (User), programme (MruProgramme), faculty (MruFaculty)
- Scopes: completed(), failed(), pending()
- Status tracking: pending → processing → completed/failed

**Excel Export:** `app/Exports/MruAcademicResultExcelExport.php`
- Uses `Enterprise` model for dynamic institution name
- Eager loads: course, student, programme relationships
- Shows actual student names (firstname + middlename + lastname)
- Shows programme abbreviations
- Summary statistics with grade distribution
- Professional styling with auto-sized columns

**PDF Service:** `app/Services/MruAcademicResultPdfService.php`
- Uses `Enterprise` model for dynamic institution name
- Space-optimized layout (7-8pt fonts, 12mm margins)
- Eager loads: course, student, programme relationships
- Shows actual student names from relationships
- Landscape orientation for better table display
- Consistent styling with curriculum PDFs

### Key Improvements (2025-12-27)

**Before:**
- ❌ Hardcoded "MOUNTAINS OF THE MOON UNIVERSITY" in Excel
- ❌ Hardcoded "MUTEESA I ROYAL UNIVERSITY" in PDF
- ❌ Used wrong `companyinfo` table
- ❌ Only loaded `course` relationship
- ❌ Showed `regno` as student name
- ❌ Showed `progid` as programme
- ❌ Larger fonts and margins

**After:**
- ✅ Dynamic `Enterprise::first()` for institution name
- ✅ Correct `enterprises` table
- ✅ Eager loads: course, student, programme
- ✅ Shows full student names: `firstname middlename lastname`
- ✅ Shows programme abbreviations
- ✅ Space-optimized: 7-8pt fonts, 12mm/10mm margins
- ✅ Consistent with curriculum PDF styling
- ✅ No help text in forms

### Usage

1. Navigate to: `/admin/mru-academic-result-exports`
2. Click "New" to create export configuration
3. Select filters: Academic Year, Semester, Programme, Faculty
4. Choose export type: Excel, PDF, or Both
5. Configure options: Include Coursework, Practical, Summary
6. Click "GENERATE" button to process
7. Download Excel/PDF from grid once completed

### Performance Notes

- Excel exports limited to 5,000 records
- PDF exports limited to 2,000 records (performance)
- Results cached after first load
- Relationships eager loaded to prevent N+1 queries

---

## Critical Issues & Solutions

### Issue 1: Student Enrollment Validation Failure

**Problem:**
```
Student account not found for ID: 12345
13,892 enrollment failures
```

**Root Cause:**
- `StudentHasSemeter` model validates against `admin_users` table
- MRU stores students in `acad_student` table
- Architectural mismatch

**Solution:**
```php
// ❌ DON'T use Eloquent model
$enrollment = new StudentHasSemeter();
$enrollment->student_id = $student->ID;
$enrollment->save(); // FAILS

// ✅ DO use direct DB insertion
$nextId = DB::table('student_has_semeters')->max('id') + 1;
DB::table('student_has_semeters')->insert([
    'id' => $nextId,
    'student_id' => $student->ID,
    'term_id' => $term->id,
    'enterprise_id' => $student->enterprise_id ?? 1,
    // ... other fields
]);
```

---

### Issue 2: Non-Auto-Increment Primary Key

**Problem:**
```
student_has_semeters.id is NOT auto-increment
insertGetId() fails
```

**Solution:**
```php
// Manual ID generation required
$nextId = DB::table('student_has_semeters')->max('id') + 1;
DB::table('student_has_semeters')->insert([
    'id' => $nextId,  // Manual assignment
    // ... other fields
]);
```

---

### Issue 3: Course Name Column Casing

**Problem:**
```php
$course->coursename // Returns NULL
```

**Root Cause:**
```sql
-- Table structure uses capital N
courseName VARCHAR(250)
```

**Solution:**
```php
$course->courseName // ✅ Correct
```

---

### Issue 4: Course Foreign Key

**Problem:**
```php
// Wrong assumption
acad_programmecourses.course_code → acad_course.coursecode
```

**Actual Structure:**
```php
acad_programmecourses.course_code → acad_course.courseID
```

**Solution:**
```php
public function course(): BelongsTo {
    return $this->belongsTo(MruCourse::class, 'course_code', 'courseID');
}
```

---

## Best Practices

### 1. Database Column Naming
- Always verify actual column names
- Don't assume Laravel conventions
- Check for camelCase vs snake_case

### 2. Relationship Definitions
- Explicitly specify foreign and owner keys
- Test relationships with sample data
- Document non-standard relationships

### 3. Performance Optimization
- Use eager loading: `with(['relation1', 'relation2'])`
- Limit grid computations
- Avoid N+1 queries
- Use appropriate pagination

### 4. Form Design
- Keep forms simple and straight
- No tabs unless absolutely necessary
- Minimal validation rules
- No help text clutter

### 5. PDF Generation
- Always use Enterprise model for header
- Compact spacing for space optimization
- Test with actual data
- Consider page breaks for long content

### 6. Code Documentation
- Document model purposes
- Explain non-obvious relationships
- Note critical issues and solutions
- Include statistics and context

---

## Quick Reference

### Common Commands
```bash
# Check syntax
php -l path/to/file.php

# Test model relationships
php artisan tinker --execute "\$model = App\Models\MruModel::with('relation')->first();"

# Check database structure
php artisan tinker --execute "\$columns = DB::select('DESCRIBE table_name');"

# Count records
php artisan tinker --execute "echo App\Models\MruModel::count();"
```

### Database Shortcuts
```sql
-- Find menu parent ID
SELECT id, parent_id, title, uri FROM admin_menu WHERE title LIKE '%MRU%';

-- Get next menu order
SELECT MAX(order) + 1 FROM admin_menu WHERE parent_id = 195;

-- Check table structure
DESCRIBE table_name;

-- Count records
SELECT COUNT(*) FROM table_name;
```

### Important Table Statistics
```
acad_student:         30,916 records
acad_programme:          127 records
acad_curriculum:         142 records
acad_programmecourses: 3,834 records
acad_course:         [unknown] records
admin_menu (MRU):         ~20 items
```

### Menu Structure
```
195 - MRU (Parent)
├── 196 - Results
├── 197 - Faculties
├── 198 - Programmes
├── 199 - Courses
├── 200 - Course Registrations
├── 201 - Students
├── 202 - Academic Years
├── 203 - Exam Results (Faculty)
├── 204 - Coursework Marks
├── 205 - Practical Exam Marks
├── 206 - Exam Settings
├── 207 - Coursework Settings
├── 208 - Academic Exports
├── 209 - Dashboard
├── 210 - Semesters
├── 212 - Programmes Configurations
├── 215 - Student Enrollments
├── 216 - Programme Courses
└── [Next] - New items
```

---

## Change Log

### 2025-12-27 (Latest)
- **Academic Result Exports - Complete Overhaul**:
  * Removed hardcoded university names ("MOUNTAINS OF THE MOON", "MUTEESA I ROYAL UNIVERSITY")
  * Implemented dynamic Enterprise model for institution branding
  * Added proper relationship eager loading (student, programme, course)
  * Improved student name display using actual names from `acad_student` table
  * Improved programme display using abbreviations
  * Space-optimized PDF styling (7-8pt fonts, 12mm/10mm margins)
  * Removed all help text from forms (following MRU guidelines)
  * Changed from `companyinfo` table to `enterprises` table
  * Consistent styling with curriculum PDF system

- **Curriculum Management System**:
  * Created `MruCurriculum` model and controller
  * Created `MruProgrammeCourse` model with curriculum relationship
  * Updated `MruProgrammeController` with curriculum PDF button
  * Created `ProgrammeCurriculumPdfController` with dynamic enterprise data
  * Optimized PDF template for space efficiency

### 2025-12-26
- Added `is_processed`, `is_processed_successful`, `processing_reason` to `acad_student`
- Created migration: `2025_12_26_232037_add_processing_fields_to_acad_student_table`
- Updated `MruStudent` model fillable array
- Fixed enrollment processing to use direct DB insertion

---

**Document maintained by:** MRU Development Team  
**Last updated:** 27 December 2025  
**System version:** Laravel 8.54 / Laravel-Admin 1.x
