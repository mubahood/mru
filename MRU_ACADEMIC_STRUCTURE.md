# MRU Academic Structure Documentation

**Version:** 1.0  
**Date:** December 26, 2025  
**Purpose:** Complete documentation of MRU's academic year and semester structure

---

## Table of Contents
1. [Overview](#overview)
2. [Academic Year Structure](#academic-year-structure)
3. [Semester Structure](#semester-structure)
4. [Table Relationships](#table-relationships)
5. [Important Distinctions](#important-distinctions)
6. [Usage Examples](#usage-examples)

---

## Overview

The MRU system uses a hierarchical structure to manage academic time periods:

```
Enterprise (University)
└── Academic Year (2024/2025)
    ├── Semester 1 (Aug-Dec)
    └── Semester 2 (Jan-Jul)
        └── Student Semester Enrollments
            └── Course Registrations
```

---

## Academic Year Structure

### AcademicYear Model
**Table:** `academic_years`  
**Model:** `App\Models\AcademicYear`  
**Used By:** MRU Main System

#### Purpose
- Main academic year table used throughout the system
- Links to semesters, student enrollments, and classes
- Manages the active academic year

#### Fields
```php
id                  bigint      Primary key
enterprise_id       bigint      Enterprise ID
name                text        Academic year name (e.g., "2024/2025")
starts              date        Start date (e.g., 2024-08-01)
ends                date        End date (e.g., 2025-07-31)
details             text        Additional details
is_active           tinyint     Active flag (1=Yes, 0=No)
process_data        varchar     Processing status
created_at          timestamp
updated_at          timestamp
```

#### Auto-Creation of Semesters
When a new `AcademicYear` is created:
- **For Universities:** Automatically creates 2 semesters (Semester 1 and 2)
- **For Other Enterprises:** Automatically creates 3 terms (Term 1, 2, and 3)

This is handled in `AcademicYear::boot()` method:
```php
self::created(function ($m) {
    $ent = Enterprise::find($m->enterprise_id);
    $terms = ($ent->type == 'University') ? [1, 2] : [1, 2, 3];
    
    foreach ($terms as $t) {
        $term = new Term();
        $term->enterprise_id = $m->enterprise_id;
        $term->academic_year_id = $m->id;
        $term->name = $t;
        $term->is_active = ($t == 1) ? 1 : 0; // First semester is active
        $term->save();
    }
});
```

#### Current Data
```
ID: 1
Name: 2024/2025
Starts: 2024-08-01
Ends: 2025-07-31
Is Active: Yes
Semesters: 2 (Semester 1 and 2)
```

---

## Semester Structure

### MruSemester Model
**Table:** `terms`  
**Model:** `App\Models\MruSemester`  
**Also Known As:** Term (for non-University enterprises)

#### Purpose
- Represents individual semesters within an academic year
- Tracks the current active semester
- Links student enrollments to specific time periods

#### Fields
```php
id                  bigint      Primary key
enterprise_id       bigint      Enterprise ID
academic_year_id    bigint      FK to academic_years.id
name                text        Semester number ("1", "2")
term_name           varchar     Semester term name (same as name)
starts              date        Start date
ends                date        End date
details             text        Additional details
is_active           tinyint     Current active flag (1=Yes, 0=No)
created_at          timestamp
updated_at          timestamp
```

#### Important Rules
1. **Only ONE semester can be active** (is_active=1) at a time per enterprise
2. When activating a semester, all others are automatically deactivated
3. Semester names are simple numbers: "1", "2", "3"
4. For Universities: Only Semesters 1 and 2 are created

#### Current Data
```
Semester 1:
  ID: 1
  Name: 1
  Academic Year: 2024/2025
  Dates: 2024-08-01 to 2024-12-31
  Is Active: Yes ✓

Semester 2:
  ID: 2
  Name: 2
  Academic Year: 2024/2025
  Dates: 2025-01-01 to 2025-07-31
  Is Active: No
```

---

## Table Relationships

### Database Schema

```sql
┌─────────────────────────┐
│   academic_years        │
├─────────────────────────┤
│ id (PK)                 │
│ enterprise_id           │
│ name                    │
│ starts                  │
│ ends                    │
│ is_active               │
└────────────┬────────────┘
             │ 1
             │
             │ academic_year_id
             │
             │ *
┌────────────┴────────────┐
│   terms                 │
│   (MruSemester)         │
├─────────────────────────┤
│ id (PK)                 │
│ enterprise_id           │
│ academic_year_id (FK)   │
│ name                    │
│ starts                  │
│ ends                    │
│ is_active               │
└────────────┬────────────┘
             │ 1
             │
             │ term_id
             │
             │ *
┌────────────┴────────────┐
│ student_has_semeters    │
├─────────────────────────┤
│ id (PK)                 │
│ student_id (FK)         │
│ term_id (FK)            │
│ academic_year_id (FK)   │
│ year_name               │
│ semester_name           │
│ registration_number     │
└─────────────────────────┘
```

### Laravel Relationships

#### AcademicYear
```php
// One-to-Many
public function terms() {
    return $this->hasMany(Term::class);
}
```

#### MruSemester (Term)
```php
// Belongs-To
public function academic_year() {
    return $this->belongsTo(AcademicYear::class, 'academic_year_id');
}

// Has-Many
public function student_enrollments() {
    return $this->hasMany(StudentHasSemeter::class, 'term_id');
}
```

---

## Important Distinctions

### ⚠️ DO NOT CONFUSE THESE TABLES

| Model | Table | Purpose | Used For |
|-------|-------|---------|----------|
| **AcademicYear** | `academic_years` | ✅ Main system table | Semesters, Student enrollments, Classes |
| **MruAcademicYear** | `acad_acadyears` | ❌ Legacy table | Historical results ONLY |

### Key Points
1. **MruSemester links to AcademicYear**, NOT MruAcademicYear
2. The `academic_years` table is the source of truth for current academic operations
3. The `acad_acadyears` table is only used for legacy result data
4. When creating semesters, ALWAYS link to `academic_years.id`

---

## Usage Examples

### Create a New Academic Year

```php
// Creating an academic year automatically creates semesters
$academicYear = new AcademicYear();
$academicYear->enterprise_id = 1;
$academicYear->name = '2025/2026';
$academicYear->starts = '2025-08-01';
$academicYear->ends = '2026-07-31';
$academicYear->is_active = 0;
$academicYear->save();

// 2 semesters are automatically created:
// - Semester 1 (name: "1", is_active: 1)
// - Semester 2 (name: "2", is_active: 0)
```

### Get Current Active Semester

```php
// Using MruSemester model
$currentSemester = MruSemester::where('enterprise_id', 1)
    ->where('is_active', 1)
    ->first();

// Or using scope
$currentSemester = MruSemester::current()
    ->forEnterprise(1)
    ->first();

echo "Current: " . $currentSemester->name_text;
// Output: "Current: Semester 1 - 2024/2025"
```

### Activate a Semester

```php
$semester = MruSemester::find(2); // Semester 2
$semester->activate(); // Deactivates all others, activates this one

// Equivalent to:
$semester->is_active = 1;
$semester->save(); // The controller handles deactivating others
```

### Get All Semesters for an Academic Year

```php
$academicYear = AcademicYear::find(1);
$semesters = $academicYear->terms; // Returns all semesters

foreach ($semesters as $semester) {
    echo "Semester {$semester->name}: ";
    echo $semester->starts->format('M d, Y') . " - ";
    echo $semester->ends->format('M d, Y');
    echo ($semester->is_active ? " (Active)" : "");
    echo "\n";
}
```

### Enroll Student in Current Semester

```php
$currentSemester = MruSemester::current()->forEnterprise(1)->first();
$student = User::where('user_type', 'student')->first();

$enrollment = new StudentHasSemeter();
$enrollment->student_id = $student->id;
$enrollment->term_id = $currentSemester->id;
$enrollment->academic_year_id = $currentSemester->academic_year_id;
$enrollment->year_name = 1; // Year of study
$enrollment->semester_name = $currentSemester->name; // Semester number
$enrollment->registration_number = $student->user_number;
$enrollment->save();
```

### Query Students Enrolled in Current Semester

```php
$currentSemester = MruSemester::current()->first();

$enrolledStudents = StudentHasSemeter::where('term_id', $currentSemester->id)
    ->with('student')
    ->get();

echo "Students enrolled in " . $currentSemester->name_text . ":\n";
foreach ($enrolledStudents as $enrollment) {
    echo "- {$enrollment->student->name} (Year {$enrollment->year_name})\n";
}
```

---

## Admin Panel Access

### Academic Years
- **URL:** `/admin/academic-years`
- **Controller:** `AcademicYearController`
- **Features:**
  - Create/Edit academic years
  - View associated semesters
  - Set active academic year

### Semesters
- **URL:** `/admin/mru-semesters`
- **Controller:** `MruSemesterController`
- **Features:**
  - View all semesters
  - Filter by academic year
  - Set current active semester
  - Edit semester dates

### Student Semester Enrollments
- **URL:** `/admin/student-has-semeters`
- **Controller:** `StudentHasSemeterController`
- **Features:**
  - Enroll students in semesters
  - Track semester progress
  - Manage fees and services

---

## Summary

✅ **AcademicYear** (academic_years) → Main system table  
✅ **MruSemester** (terms) → Semesters within academic years  
✅ **StudentHasSemeter** (student_has_semeters) → Student enrollments  
✅ Automatic creation of 2 semesters per academic year for Universities  
✅ Only one active semester at a time  
✅ Complete relationship chain: AcademicYear → Semester → Student Enrollment → Course Registration

---

**Last Updated:** December 26, 2025  
**Maintained By:** MRU Development Team
