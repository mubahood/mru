# MRU Database Relationships & Standards Documentation

**Document Purpose:** Complete reference guide for MRU database structure, relationships, and naming conventions.  
**Exemplary Student:** ID 24821 - SWABULAH NAMATOVU (23/U/24821/PS)  
**Programme:** BAED (Bachelor of Arts with Education) - Luganda & History  
**Created:** December 24, 2025

---

## Table of Contents

1. [Core Entity Relationships](#core-entity-relationships)
2. [Database Naming Conventions](#database-naming-conventions)
3. [Primary Tables & Their Keys](#primary-tables--their-keys)
4. [Relationship Chains](#relationship-chains)
5. [Laravel Model Standards](#laravel-model-standards)
6. [Column Name Mappings](#column-name-mappings)
7. [Example Data Flow](#example-data-flow)

---

## Core Entity Relationships

### 1. STUDENT → PROGRAMME → FACULTY

```
acad_student
    └─ progid (FK) ──────→ acad_programme.progcode (PK)
                               └─ faculty_code (FK) ──────→ acad_faculty.faculty_code (PK)
```

**Real Example:**
```sql
Student ID 24821 (SWABULAH NAMATOVU)
    └─ progid: 'BAED'
        └─ Programme: 'BACHELOR OF ARTS WITH EDUCATION'
            └─ faculty_code: '04'
                └─ Faculty: 'FACULTY OF EDUCATION'
                    └─ Dean: '-DR. KIGUNDU STEPHEN'
```

**Laravel Implementation:**
```php
// MruStudent.php
public function programme(): BelongsTo
{
    return $this->belongsTo(MruProgramme::class, 'progid', 'progcode');
}

// MruProgramme.php
public function faculty(): BelongsTo
{
    return $this->belongsTo(MruFaculty::class, 'faculty_code', 'faculty_code');
}

// Usage with nested eager loading
$student = MruStudent::with('programme.faculty')->find(24821);
echo $student->programme->faculty->faculty_name; // "FACULTY OF EDUCATION"
```

---

### 2. STUDENT → SPECIALISATION (Education Programmes)

```
acad_student
    └─ specialisation (FK) ──────→ acad_specialisation.spec_id (PK)
                                        └─ prog_id ──────→ acad_programme.progcode
```

**Real Example:**
```sql
Student ID 24821
    └─ specialisation: '76'  (stored as varchar but contains numeric ID)
        └─ Specialisation Record:
            ├─ spec_id: 76
            ├─ prog_id: 'BAED'
            ├─ spec: 'Luganda & History'  (full teaching subjects)
            └─ abbrev: 'L & H'
```

**Critical Notes:**
- ⚠️ Column `acad_student.specialisation` stores spec_id as VARCHAR
- ⚠️ Naming conflict: Cannot use relationship name `specialisation()` because column exists
- ✅ **Standard:** Use relationship name `specialisationDetails()` to avoid conflict

**Laravel Implementation:**
```php
// MruStudent.php
public function specialisationDetails(): BelongsTo
{
    return $this->belongsTo(MruSpecialisation::class, 'specialisation', 'spec_id');
}

// MruSpecialisation.php
protected $table = 'acad_specialisation';
protected $primaryKey = 'spec_id';

public function programme(): BelongsTo
{
    return $this->belongsTo(MruProgramme::class, 'prog_id', 'progcode');
}

// Usage
$student = MruStudent::with('specialisationDetails')->find(24821);
echo $student->specialisationDetails->spec; // "Luganda & History"
echo $student->specialisationDetails->abbrev; // "L & H"
```

---

### 3. STUDENT → COURSE REGISTRATION → COURSE

```
acad_student
    └─ regno (FK) ──────→ acad_course_registration.regno
                              ├─ courseID (FK) ──────→ acad_course.courseID (PK)
                              ├─ acad_year
                              └─ semester
```

**Real Example:**
```sql
Student: '23/U/24821/PS'
    └─ Course Registration:
        ├─ courseID: 'EDU1101'
        ├─ acad_year: '2023/2024'
        ├─ semester: 1
        ├─ course_status: 'NORMAL'
        └─ prog_id: 'BAED'
```

**Key Relationships:**
- One student can have MANY course registrations
- Each registration links to ONE course
- Registration includes academic year and semester context

**Laravel Implementation:**
```php
// MruStudent.php
public function courseRegistrations(): HasMany
{
    return $this->hasMany(MruCourseRegistration::class, 'regno', 'regno');
}

// MruCourseRegistration.php
public function course(): BelongsTo
{
    return $this->belongsTo(MruCourse::class, 'courseID', 'courseID');
}

// Usage with nested eager loading
$student = MruStudent::with('courseRegistrations.course')->find(24821);
foreach ($student->courseRegistrations as $registration) {
    echo $registration->course->courseName; // Course name via relationship
}
```

---

### 4. STUDENT → RESULTS

```
acad_student
    └─ regno (FK) ──────→ acad_results.regno
                              ├─ courseid (FK) ──────→ acad_course.courseID
                              ├─ acad (academic year)
                              └─ semester
```

**Real Example:**
```sql
Student: '23/U/24821/PS'
    └─ Result Record:
        ├─ courseid: 'EDU1101'
        ├─ semester: 1
        ├─ acad: '2023/2024'
        ├─ score: 75
        ├─ grade: 'B'
        ├─ gpa: 4.00
        ├─ gradept: 3.0
        └─ CreditUnits: 4.0
```

**Critical Column Names:**
- ⚠️ `courseid` (lowercase) not `courseID`
- ⚠️ `acad` not `acad_year`
- ⚠️ `gpa` (lowercase) not `GPA`
- ⚠️ `CreditUnits` (CamelCase) not `credit_units`

**Laravel Implementation:**
```php
// MruStudent.php
public function results(): HasMany
{
    return $this->hasMany(MruResult::class, 'regno', 'regno');
}

// MruResult.php
protected $table = 'acad_results';
protected $primaryKey = 'ID';

// Column name mappings in model
protected $casts = [
    'score' => 'integer',
    'gpa' => 'decimal:2',
    'gradept' => 'decimal:2',
    'CreditUnits' => 'decimal:2',
];

// Access results
$results = $student->results;
$semesterGPA = $results->where('semester', 1)->avg('gpa');
```

---

### 5. COURSEWORK MARKS → COURSEWORK SETTINGS

```
acad_coursework_marks
    └─ CSID (FK) ──────→ acad_coursework_settings.ID (PK)
                             ├─ courseID ──────→ acad_course.courseID
                             ├─ acadyear
                             └─ semester
```

**Real Example:**
```sql
Coursework Mark:
    ├─ reg_no: '23/U/24821/PS'
    ├─ ass_1_mark: 18.0
    ├─ test_1_mark: 15.0
    ├─ final_score: 33.0
    └─ CSID: 12345 (links to settings)
        └─ Coursework Settings:
            ├─ courseID: 'EDU1101'
            ├─ max_assn_1: 20.0
            ├─ max_test_1: 15.0
            ├─ total_mark: 40.0
            └─ lecturerID: 'LEC001'
```

**Critical Column Names:**
- ⚠️ `reg_no` not `regno` in coursework_marks
- ⚠️ `acadyear` not `acad_year` in settings
- ⚠️ `CSID` links marks to settings

**Laravel Implementation:**
```php
// MruCourseworkMark.php
protected $table = 'acad_coursework_marks';
protected $primaryKey = 'ID';

public function settings(): BelongsTo
{
    return $this->belongsTo(MruCourseworkSetting::class, 'CSID', 'ID');
}

// MruCourseworkSetting.php
protected $table = 'acad_coursework_settings';
protected $primaryKey = 'ID';

public function course(): BelongsTo
{
    return $this->belongsTo(MruCourse::class, 'courseID', 'courseID');
}

// MruStudent.php
public function courseworkMarks(): HasMany
{
    return $this->hasMany(MruCourseworkMark::class, 'reg_no', 'regno');
}
```

---

### 6. PRACTICAL EXAM MARKS → PRACTICAL EXAM SETTINGS

```
acad_practicalexam_marks
    └─ CSID (FK) ──────→ acad_practicalexam_settings.ID (PK)
                             ├─ courseID ──────→ acad_course.courseID
                             ├─ acadyear
                             └─ semester
```

**Structure (Same as Coursework):**
- `reg_no` in marks table
- `CSID` links to settings
- Settings contain max marks configuration
- Parallel structure to coursework system

**Laravel Implementation:**
```php
// MruPracticalExamMark.php
protected $table = 'acad_practicalexam_marks';
protected $primaryKey = 'ID';

public function settings(): BelongsTo
{
    return $this->belongsTo(MruPracticalExamSetting::class, 'CSID', 'ID');
}

// MruStudent.php
public function practicalExamMarks(): HasMany
{
    return $this->hasMany(MruPracticalExamMark::class, 'reg_no', 'regno');
}
```

---

## Database Naming Conventions

### Standard Patterns Discovered:

| Convention | Example | Usage |
|------------|---------|-------|
| **Table Names** | `acad_student` | Prefix `acad_` + singular noun (lowercase) |
| **Primary Keys** | `ID` (uppercase) | Auto-increment, bigint unsigned |
| **Foreign Keys** | `progid`, `faculty_code` | Lowercase or varies by table |
| **Registration Numbers** | `regno`, `reg_no` | **INCONSISTENT** - both forms used |
| **Course IDs** | `courseID` (camelCase) | Consistent in most tables |
| **Academic Year** | `acad`, `acadyear`, `acad_year` | **INCONSISTENT** - three forms |
| **Credit Units** | `CreditUnits` (PascalCase) | Used in results table |
| **GPA** | `gpa` (lowercase) | Used in results table |

### ⚠️ Critical Inconsistencies:

1. **Registration Number:**
   - `acad_student.regno` (primary identifier)
   - `acad_course_registration.regno`
   - `acad_coursework_marks.reg_no` ⚠️ Different!
   - `acad_practicalexam_marks.reg_no` ⚠️ Different!

2. **Academic Year:**
   - `acad_course_registration.acad_year`
   - `acad_results.acad` ⚠️ Different!
   - `acad_coursework_settings.acadyear` ⚠️ Different!

3. **Course ID:**
   - Mostly `courseID` (camelCase)
   - Sometimes `courseid` (lowercase) in results

---

## Primary Tables & Their Keys

### Complete Reference:

| Table | Primary Key | Key Type | Common FK Usage |
|-------|-------------|----------|-----------------|
| `acad_student` | `ID` | Auto-increment | New standard (2025) |
| `acad_student` | `regno` | Unique string | Legacy identifier (23/U/24821/PS) |
| `acad_programme` | `progcode` | Char(25) | Referenced as `progid` in student |
| `acad_faculty` | `faculty_code` | Char(10) | Referenced by programme |
| `acad_specialisation` | `spec_id` | Auto-increment | Referenced by student.specialisation |
| `acad_course` | `courseID` | Char(25) | Referenced everywhere |
| `acad_course_registration` | `ID` | Auto-increment | Bridge table |
| `acad_results` | `ID` | Auto-increment | One per course per student |
| `acad_coursework_marks` | `ID` | Auto-increment | Multiple per student |
| `acad_coursework_settings` | `ID` | Auto-increment | One per course/semester |
| `acad_practicalexam_marks` | `ID` | Auto-increment | Multiple per student |
| `acad_practicalexam_settings` | `ID` | Auto-increment | One per course/semester |

### Important Notes:

1. **Student Identity Evolution:**
   - Old system: Used `regno` as primary key
   - New system: Added `ID` (auto-increment) as primary key
   - Migration: `2025_12_24_130343_add_id_primary_key_to_acad_student_table`
   - ✅ **Standard:** Always use `ID` for routing, keep `regno` for display

2. **Registration Number Format:**
   - Pattern: `YY/U/ENTRYNO/PS`
   - Example: `23/U/24821/PS`
   - Components:
     - `23` = Entry year (2023)
     - `U` = University level
     - `24821` = Student ID number
     - `PS` = Programme suffix

---

## Relationship Chains

### Complete Student Data Access Pattern:

```php
$student = MruStudent::with([
    // Identity & Programme (Level 1)
    'programme.faculty',              // Programme with nested faculty
    'specialisationDetails',          // Teaching subjects (education)
    
    // Academic Records (Level 2)
    'results',                        // Final grades & GPA
    'courseRegistrations.course',     // Enrolled courses with details
    
    // Assessment Records (Level 3)
    'courseworkMarks.settings.course',      // Coursework with max marks
    'practicalExamMarks.settings.course',   // Practical with max marks
])->findOrFail(24821);
```

### Accessing Related Data:

```php
// Basic Information
$name = $student->firstname . ' ' . $student->othername;
$regNo = $student->regno; // "23/U/24821/PS" for display
$id = $student->ID; // 24821 for routing

// Programme & Faculty
$programme = $student->programme->progname;  // "BACHELOR OF ARTS WITH EDUCATION"
$faculty = $student->programme->faculty->faculty_name; // "FACULTY OF EDUCATION"
$dean = $student->programme->faculty->faculty_dean; // "-DR. KIGUNDU STEPHEN"

// Specialisation (Education Students)
if ($student->specialisationDetails) {
    $subjects = $student->specialisationDetails->spec; // "Luganda & History"
    $abbrev = $student->specialisationDetails->abbrev; // "L & H"
}

// Academic Performance
$cgpa = $student->results->avg('gpa'); // Overall GPA
$totalCredits = $student->results->sum('CreditUnits'); // Total credits earned

// Current Registrations
foreach ($student->courseRegistrations as $registration) {
    $courseName = $registration->course->courseName;
    $semester = $registration->semester;
    $academicYear = $registration->acad_year;
}

// Coursework Performance
foreach ($student->courseworkMarks as $mark) {
    $courseName = $mark->settings->course->courseName;
    $score = $mark->final_score;
    $maxScore = $mark->settings->total_mark;
    $percentage = ($score / $maxScore) * 100;
}
```

---

## Laravel Model Standards

### Standard Model Template:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MruStudent extends Model
{
    // Table Configuration
    protected $table = 'acad_student';
    protected $primaryKey = 'ID'; // New standard (auto-increment)
    public $timestamps = false; // MRU tables don't use timestamps
    
    // Mass Assignment Protection
    protected $guarded = ['ID'];
    
    // Type Casting
    protected $casts = [
        'dob' => 'date',
        'entryyear' => 'integer',
        'duration' => 'integer',
        'gradSystemID' => 'integer',
    ];
    
    // Relationships (BelongsTo = Foreign Key on this table)
    public function programme(): BelongsTo
    {
        return $this->belongsTo(MruProgramme::class, 'progid', 'progcode');
    }
    
    public function specialisationDetails(): BelongsTo
    {
        // Note: Named "specialisationDetails" not "specialisation" 
        // to avoid conflict with column name
        return $this->belongsTo(MruSpecialisation::class, 'specialisation', 'spec_id');
    }
    
    // Relationships (HasMany = Foreign Key on other table)
    public function results(): HasMany
    {
        return $this->hasMany(MruResult::class, 'regno', 'regno');
    }
    
    public function courseRegistrations(): HasMany
    {
        return $this->hasMany(MruCourseRegistration::class, 'regno', 'regno');
    }
    
    // Use regno as foreign key since coursework_marks uses reg_no
    public function courseworkMarks(): HasMany
    {
        return $this->hasMany(MruCourseworkMark::class, 'reg_no', 'regno');
    }
    
    public function practicalExamMarks(): HasMany
    {
        return $this->hasMany(MruPracticalExamMark::class, 'reg_no', 'regno');
    }
}
```

### Relationship Naming Standards:

| Relationship Type | Method Name | Return Type | Foreign Key Location |
|-------------------|-------------|-------------|----------------------|
| **BelongsTo** | Singular noun | `BelongsTo` | On current model's table |
| **HasMany** | Plural noun | `HasMany` | On related model's table |
| **HasOne** | Singular noun | `HasOne` | On related model's table |

**Examples:**
- `programme()` → BelongsTo (student has FK `progid`)
- `courseRegistrations()` → HasMany (registrations have FK `regno`)
- `faculty()` → BelongsTo (programme has FK `faculty_code`)

### Avoiding Naming Conflicts:

⚠️ **Problem:** When a column name matches a relationship method name, Laravel prioritizes the column.

```php
// ❌ WRONG - Creates conflict
public function specialisation(): BelongsTo
{
    return $this->belongsTo(MruSpecialisation::class, 'specialisation', 'spec_id');
}
// Accessing $student->specialisation returns "76" (column value) not the relationship

// ✅ CORRECT - Use different name
public function specialisationDetails(): BelongsTo
{
    return $this->belongsTo(MruSpecialisation::class, 'specialisation', 'spec_id');
}
// Accessing $student->specialisationDetails returns the relationship object
```

---

## Column Name Mappings

### Critical Mappings for Queries:

```php
// When joining tables, use correct column names:

// STUDENT → RESULTS
// Student uses: regno
// Results uses: regno
// Match on: $student->regno = $result->regno

// STUDENT → COURSEWORK MARKS
// Student uses: regno
// Marks uses: reg_no ⚠️ Different!
// Match on: $student->regno = $mark->reg_no

// COURSE REGISTRATION → RESULTS
// Registration uses: acad_year, courseID
// Results uses: acad, courseid ⚠️ Different!
// Match on: $registration->acad_year = $result->acad
//          $registration->courseID = $result->courseid

// COURSEWORK MARKS → SETTINGS
// Marks uses: CSID
// Settings uses: ID
// Match on: $mark->CSID = $setting->ID

// PROGRAMME → FACULTY
// Programme uses: faculty_code
// Faculty uses: faculty_code
// Match on: $programme->faculty_code = $faculty->faculty_code

// STUDENT → SPECIALISATION
// Student uses: specialisation (varchar containing numeric ID)
// Specialisation uses: spec_id (int)
// Match on: $student->specialisation = $specialisation->spec_id
```

### SQL Join Examples:

```sql
-- Student with Results (matching column names)
SELECT s.*, r.*
FROM acad_student s
LEFT JOIN acad_results r ON s.regno = r.regno;

-- Student with Coursework Marks (different column names)
SELECT s.*, cm.*
FROM acad_student s
LEFT JOIN acad_coursework_marks cm ON s.regno = cm.reg_no;

-- Registration with Results (different column names)
SELECT cr.*, r.*
FROM acad_course_registration cr
LEFT JOIN acad_results r 
    ON cr.regno = r.regno 
    AND cr.courseID = r.courseid 
    AND cr.acad_year = r.acad;

-- Coursework Marks with Settings
SELECT cm.*, cs.*
FROM acad_coursework_marks cm
LEFT JOIN acad_coursework_settings cs ON cm.CSID = cs.ID;

-- Student with Complete Chain
SELECT 
    s.ID, s.regno, s.firstname,
    spec.spec as teaching_subjects,
    p.progname,
    f.faculty_name
FROM acad_student s
LEFT JOIN acad_specialisation spec ON s.specialisation = spec.spec_id
LEFT JOIN acad_programme p ON s.progid = p.progcode
LEFT JOIN acad_faculty f ON p.faculty_code = f.faculty_code;
```

---

## Example Data Flow

### Real Student 24821 - Complete Data Flow:

```
┌─────────────────────────────────────────────────────────────────────┐
│                         STUDENT RECORD                               │
│  ID: 24821                                                           │
│  Regno: 23/U/24821/PS                                                │
│  Name: SWABULAH NAMATOVU                                             │
│  Gender: FEMALE                                                      │
│  DOB: 2004-06-25                                                     │
│  Entry Year: 2022                                                    │
│  Session: DAY                                                        │
│  Email: swabulahnamatovu72@gmail.com                                 │
└───────────────┬─────────────────────────────────────────────────────┘
                │
                ├─── progid: 'BAED'
                │    └──→ ┌────────────────────────────────────────┐
                │         │      PROGRAMME RECORD                  │
                │         │  Code: BAED                            │
                │         │  Name: BACHELOR OF ARTS WITH EDUCATION │
                │         │  Duration: 3 years                     │
                │         │  Max Duration: 6 years                 │
                │         │  Min Credits: 153                      │
                │         │  Level: Undergraduate                  │
                │         │  Study System: Semester                │
                │         └──────────┬─────────────────────────────┘
                │                    │
                │                    └─── faculty_code: '04'
                │                         └──→ ┌─────────────────────────┐
                │                              │   FACULTY RECORD        │
                │                              │  Code: 04               │
                │                              │  Name: FACULTY OF ED... │
                │                              │  Dean: DR. KIGUNDU...   │
                │                              │  Abbrev: FOE            │
                │                              └─────────────────────────┘
                │
                └─── specialisation: '76'
                     └──→ ┌──────────────────────────────────────────┐
                          │    SPECIALISATION RECORD                 │
                          │  Spec ID: 76                             │
                          │  Programme: BAED                         │
                          │  Subjects: Luganda & History             │
                          │  Abbreviation: L & H                     │
                          └──────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    ACADEMIC REGISTRATIONS                            │
└─────────────────────────────────────────────────────────────────────┘
  
  regno: '23/U/24821/PS' ───→ [ Multiple Course Registrations ]
                                │
                                ├─── Registration 1:
                                │    ├─ courseID: 'EDU1101'
                                │    ├─ acad_year: '2023/2024'
                                │    ├─ semester: 1
                                │    ├─ course_status: 'NORMAL'
                                │    └──→ [ Course Details ]
                                │         ├─ courseName: "Introduction to Education"
                                │         ├─ CreditUnit: 4
                                │         └─ CoreStatus: "CORE"
                                │
                                ├─── Registration 2:
                                │    ├─ courseID: 'LUG1101'
                                │    └─ [...similar structure]
                                │
                                └─── Registration N...

┌─────────────────────────────────────────────────────────────────────┐
│                       ACADEMIC RESULTS                               │
└─────────────────────────────────────────────────────────────────────┘

  regno: '23/U/24821/PS' ───→ [ Multiple Results ]
                                │
                                ├─── Result 1:
                                │    ├─ courseid: 'EDU1101'
                                │    ├─ acad: '2023/2024'
                                │    ├─ semester: 1
                                │    ├─ score: 75
                                │    ├─ grade: 'B'
                                │    ├─ gpa: 4.00
                                │    ├─ gradept: 3.0
                                │    └─ CreditUnits: 4.0
                                │
                                └─── Result N...

┌─────────────────────────────────────────────────────────────────────┐
│                      COURSEWORK MARKS                                │
└─────────────────────────────────────────────────────────────────────┘

  reg_no: '23/U/24821/PS' ───→ [ Multiple Coursework Records ]
                                  │
                                  ├─── Coursework 1:
                                  │    ├─ ass_1_mark: 18.0
                                  │    ├─ test_1_mark: 15.0
                                  │    ├─ final_score: 33.0
                                  │    └─ CSID: 12345
                                  │        └──→ [ Coursework Settings ]
                                  │             ├─ courseID: 'EDU1101'
                                  │             ├─ max_assn_1: 20.0
                                  │             ├─ max_test_1: 15.0
                                  │             ├─ total_mark: 40.0
                                  │             └─ lecturerID: 'LEC001'
                                  │
                                  └─── Coursework N...
```

### Data Access Pattern in Controller:

```php
public function detail($id)
{
    // Load student with all relationships
    $student = MruStudent::with([
        'programme.faculty',
        'specialisationDetails',
        'results',
        'courseRegistrations.course',
        'courseworkMarks.settings.course',
        'practicalExamMarks.settings.course'
    ])->findOrFail($id);
    
    return view('admin.mru.students.show', compact('student'));
}
```

### Data Display in View:

```blade
{{-- Basic Info --}}
<h1>{{ $student->firstname }} {{ $student->othername }}</h1>
<p>Registration: {{ $student->regno }}</p>

{{-- Programme & Faculty --}}
<h3>{{ $student->programme->progname }}</h3>
<p>Faculty: {{ $student->programme->faculty->faculty_name }}</p>
<p>Dean: {{ $student->programme->faculty->faculty_dean }}</p>

{{-- Specialisation (Education Students) --}}
@if($student->specialisationDetails)
    <div class="alert alert-info">
        Teaching Subjects: {{ $student->specialisationDetails->spec }}
        <span class="badge">{{ $student->specialisationDetails->abbrev }}</span>
    </div>
@endif

{{-- Academic Performance --}}
<table>
    @foreach($student->results as $result)
        <tr>
            <td>{{ $result->courseid }}</td>
            <td>{{ $result->grade }}</td>
            <td>{{ $result->gpa }}</td>
            <td>{{ $result->CreditUnits }}</td>
        </tr>
    @endforeach
</table>

{{-- Coursework Progress --}}
@foreach($student->courseworkMarks as $mark)
    <div>
        Course: {{ $mark->settings->course->courseName }}
        Score: {{ $mark->final_score }} / {{ $mark->settings->total_mark }}
        ({{ round(($mark->final_score / $mark->settings->total_mark) * 100, 1) }}%)
    </div>
@endforeach
```

---

## Standards Summary

### ✅ Always Follow These Standards:

1. **Primary Keys:**
   - Use `ID` (auto-increment) for routing
   - Keep `regno` for display and relationships with old tables
   
2. **Relationship Names:**
   - Avoid conflicts with column names
   - Use descriptive suffixes (e.g., `specialisationDetails` not `specialisation`)
   
3. **Eager Loading:**
   - Load all needed relationships in controller
   - Use nested eager loading: `'programme.faculty'`
   
4. **Column Names:**
   - Always check actual column names before writing queries
   - Use mappings documented above for joins
   
5. **Foreign Keys:**
   - Registration: `regno` in most tables, `reg_no` in coursework/practical
   - Academic Year: `acad_year`, `acad`, or `acadyear` depending on table
   - Course ID: Usually `courseID`, sometimes `courseid`

### ⚠️ Common Pitfalls to Avoid:

1. Don't assume consistent column naming across tables
2. Don't name relationships same as columns
3. Don't forget `with()` for eager loading (N+1 problem)
4. Don't use `progid` to join programme (use `progcode`)
5. Don't use `faccode` to join faculty (use `faculty_code`)

---

**End of Documentation**  
**Reference Student:** ID 24821 - SWABULAH NAMATOVU  
**Last Updated:** December 24, 2025
