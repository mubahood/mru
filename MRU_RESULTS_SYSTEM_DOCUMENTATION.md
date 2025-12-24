# MRU Results System - Models and Controller Documentation

## Overview
This document provides comprehensive documentation for the MRU Results system, including the `MruResult` and `MruCourse` models, and the `MruResultController` Laravel Admin controller.

**Date Created:** December 20, 2025

---

## 1. MruResult Model

### Purpose
The `MruResult` model represents student academic results in the MRU system. It maps to the `acad_results` table and contains student grades, scores, GPA information, and academic performance data.

### Database Table
- **Table Name:** `acad_results`
- **Primary Key:** `ID` (integer, auto-increment)
- **Timestamps:** Not used (no `created_at`, `updated_at`)
- **Total Records:** 605,764 results

### Schema Structure

| Column | Type | Description |
|--------|------|-------------|
| ID | int(11) | Primary key |
| regno | char(85) | Student registration number |
| courseid | char(25) | Course identifier (references `acad_course.courseID`) |
| semester | int(11) | Semester number (1 or 2) |
| acad | char(25) | Academic year (e.g., "2007/2008") |
| studyyear | int(11) | Study year (1-7) |
| score | int(10) unsigned | Raw score (0-100) |
| grade | char(5) | Letter grade (A, B+, B, C+, C, D, F) |
| gradept | double | Grade point value (0-5) |
| gpa | double(5,2) | Grade Point Average |
| result_comment | char(25) | Optional comments |
| CreditUnits | double | Course credit units |
| progid | char(25) | Program identifier |

### Fillable Attributes
```php
[
    'regno', 'courseid', 'semester', 'acad', 'studyyear',
    'score', 'grade', 'gradept', 'gpa', 'result_comment',
    'CreditUnits', 'progid'
]
```

### Relationships

#### 1. `student()` - BelongsTo User
Returns the student who owns this result.
```php
$result->student; // Returns User model
```

#### 2. `course()` - BelongsTo MruCourse
Returns the course associated with this result.
```php
$result->course; // Returns MruCourse model
$result->course->courseName; // "AGRICULTURE EDUCATION I"
```

#### 3. `program()` - BelongsTo AcademicProgram
Returns the academic program for this result.
```php
$result->program; // Returns AcademicProgram model
```

### Constants

#### Grade Constants
```php
const GRADE_A = 'A';
const GRADE_B_PLUS = 'B+';
const GRADE_B = 'B';
const GRADE_C_PLUS = 'C+';
const GRADE_C = 'C';
const GRADE_D = 'D';
const GRADE_F = 'F';

const PASSING_GRADES = ['A', 'B+', 'B', 'C+', 'C', 'D'];
const FAILING_GRADES = ['F'];
```

#### Semester Constants
```php
const SEMESTER_ONE = 1;
const SEMESTER_TWO = 2;
```

### Query Scopes

#### 1. `forStudent($regno)`
Filter results by student registration number.
```php
MruResult::forStudent('MRU INS/2/07/BEPE/06')->get();
```

#### 2. `forAcademicYear($year)`
Filter results by academic year.
```php
MruResult::forAcademicYear('2023/2024')->get();
```

#### 3. `forSemester($semester)`
Filter results by semester (1 or 2).
```php
MruResult::forSemester(1)->get();
```

#### 4. `passing()`
Get only passing results (grades A through D).
```php
MruResult::passing()->count(); // 545,346 passing results
```

#### 5. `failing()`
Get only failing results (grade F).
```php
MruResult::failing()->count(); // 11,896 failing results
```

#### 6. `forCourse($courseId)`
Filter results by course ID.
```php
MruResult::forCourse('BEFI1101')->get();
```

#### 7. `forStudyYear($year)`
Filter results by study year.
```php
MruResult::forStudyYear(1)->get();
```

#### 8. `excellent()`
Get results with A grades only.
```php
MruResult::excellent()->get();
```

### Accessors (Read-Only Computed Properties)

#### 1. `academic_year`
Returns formatted academic year.
```php
$result->academic_year; // "2023/2024"
```

#### 2. `is_passing`
Boolean indicating if result is passing.
```php
$result->is_passing; // true or false
```

#### 3. `is_failing`
Boolean indicating if result is failing.
```php
$result->is_failing; // true or false
```

#### 4. `score_percentage`
Returns the score as a percentage.
```php
$result->score_percentage; // 85.0
```

#### 5. `grade_display`
Returns formatted grade with points.
```php
$result->grade_display; // "A (5 pts)"
```

#### 6. `semester_name`
Returns human-readable semester name.
```php
$result->semester_name; // "Semester 1"
```

### Mutators (Setters with Auto-formatting)

#### 1. `semester` Mutator
Ensures semester is 1 or 2.
```php
$result->semester = 3; // Automatically converts to 1
```

#### 2. `grade` Mutator
Normalizes grade to uppercase.
```php
$result->grade = 'a'; // Stored as 'A'
```

#### 3. `score` Mutator
Ensures score is within valid range (0-100).
```php
$result->score = 150; // Automatically capped at 100
$result->score = -10; // Automatically set to 0
```

### Public Methods

#### 1. `getWeightedPoints()`
Calculate weighted grade points (grade point × credit units).
```php
$weightedPoints = $result->getWeightedPoints(); // float
```

#### 2. `getGradeStatus()`
Get the grade status as "Pass", "Fail", or "Retake".
```php
$status = $result->getGradeStatus(); // "Pass"
```

### Static Methods

#### 1. `calculateGPA($results)`
Calculate GPA for a collection of results.
```php
$gpa = MruResult::calculateGPA($results); // 3.45
```

#### 2. `getSemesterGPA($regno, $academicYear, $semester)`
Get semester GPA for a specific student.
```php
$gpa = MruResult::getSemesterGPA('MRU INS/2/07/BEPE/06', '2023/2024', 1);
```

#### 3. `getCumulativeGPA($regno)`
Get cumulative GPA for a student across all semesters.
```php
$cumulativeGPA = MruResult::getCumulativeGPA('MRU INS/2/07/BEPE/06');
```

#### 4. `getTranscriptData($regno)`
Get complete transcript data for a student.
```php
$transcript = MruResult::getTranscriptData('MRU INS/2/07/BEPE/06');
// Returns:
[
    'results' => [...], // Grouped by year and semester
    'cumulative_gpa' => 3.45,
    'total_credit_units' => 120,
    'total_courses' => 40,
    'passed_courses' => 38,
    'failed_courses' => 2
]
```

#### 5. `getGradeDistribution($courseId, $academicYear = null)`
Get grade distribution for a course.
```php
$distribution = MruResult::getGradeDistribution('BEFI1101', '2023/2024');
// Returns: ['A' => 15, 'B+' => 20, 'B' => 30, ...]
```

#### 6. `getTopPerformers($courseId, $academicYear, $limit = 10)`
Get top performers for a course.
```php
$topStudents = MruResult::getTopPerformers('BEFI1101', '2023/2024', 10);
```

### Usage Examples

#### Example 1: Get all results for a student
```php
$results = MruResult::forStudent('MRU INS/2/07/BEPE/06')
    ->with('course')
    ->orderBy('acad')
    ->orderBy('semester')
    ->get();
```

#### Example 2: Calculate semester GPA
```php
$semesterResults = MruResult::forStudent('MRU INS/2/07/BEPE/06')
    ->forAcademicYear('2023/2024')
    ->forSemester(1)
    ->get();

$gpa = MruResult::calculateGPA($semesterResults);
```

#### Example 3: Get failing students for a course
```php
$failingStudents = MruResult::forCourse('BEFI1101')
    ->forAcademicYear('2023/2024')
    ->failing()
    ->with('student')
    ->get();
```

---

## 2. MruCourse Model

### Purpose
The `MruCourse` model represents courses in the MRU academic system. It maps to the `acad_course` table and contains course information including names, credit units, and course types.

### Database Table
- **Table Name:** `acad_course`
- **Primary Key:** `courseID` (string, non-incrementing)
- **Timestamps:** Not used
- **Total Records:** 6,881 courses

### Schema Structure

| Column | Type | Description |
|--------|------|-------------|
| courseID | varchar | Primary key (e.g., "BEFI1101") |
| courseName | varchar | Course name |
| CreditUnit | float | Credit units (default 3) |
| ContactHr | int | Contact hours |
| LectureHr | int | Lecture hours |
| PracticalHr | int | Practical hours |
| courseDescription | text | Course description |
| stat | varchar | Status ("Active", "Inactive") |
| CoreStatus | varchar | Type ("Core", "Optional") |

### Fillable Attributes
```php
[
    'courseID', 'courseName', 'CreditUnit', 'ContactHr',
    'LectureHr', 'PracticalHr', 'courseDescription',
    'stat', 'CoreStatus'
]
```

### Relationships

#### 1. `results()` - HasMany MruResult
Returns all results for this course.
```php
$course->results; // Collection of MruResult models
$course->results()->count(); // Number of results
```

#### 2. `students()` - HasManyThrough User
Returns students who have taken this course.
```php
$course->students; // Collection of User models
```

### Constants

```php
const STATUS_ACTIVE = 'Active';
const STATUS_INACTIVE = 'Inactive';
const CORE_STATUS_CORE = 'Core';
const CORE_STATUS_OPTIONAL = 'Optional';
```

### Query Scopes

#### 1. `core()`
Get only core courses.
```php
MruCourse::core()->get();
```

#### 2. `optional()`
Get only optional courses.
```php
MruCourse::optional()->get();
```

#### 3. `active()`
Get only active courses.
```php
MruCourse::active()->get();
```

#### 4. `search($searchTerm)`
Search courses by name or ID.
```php
MruCourse::search('AGRICULTURE')->get();
```

### Accessors

#### 1. `course_type`
Returns the course type as string.
```php
$course->course_type; // "Core" or "Optional"
```

#### 2. `course_status`
Returns the course status.
```php
$course->course_status; // "Active" or "Inactive"
```

#### 3. `short_name`
Returns truncated course name (50 chars).
```php
$course->short_name;
```

#### 4. `full_display_name`
Returns formatted display name with ID.
```php
$course->full_display_name; // "BEFI1101 - AGRICULTURE EDUCATION I"
```

### Public Methods

#### 1. `getStudentCount()`
Get total number of students who have taken this course.
```php
$count = $course->getStudentCount();
```

#### 2. `getAverageScore($academicYear = null)`
Get average score for this course.
```php
$avgScore = $course->getAverageScore('2023/2024'); // 75.5
```

#### 3. `getPassRate($academicYear = null)`
Get pass rate percentage for this course.
```php
$passRate = $course->getPassRate('2023/2024'); // 85.50
```

#### 4. `getStatistics($academicYear = null)`
Get comprehensive statistics for the course.
```php
$stats = $course->getStatistics('2023/2024');
// Returns:
[
    'total_students' => 150,
    'average_score' => 75.5,
    'highest_score' => 98,
    'lowest_score' => 45,
    'pass_rate' => 85.50,
    'fail_rate' => 14.50,
    'grade_distribution' => [...]
]
```

#### 5. `getRecentResults($limit = 10)`
Get recent results for this course.
```php
$recentResults = $course->getRecentResults(20);
```

#### 6. `hasResults()`
Check if the course has any results.
```php
if ($course->hasResults()) {
    // Course has results
}
```

#### 7. `getEnrollmentByYear()`
Get enrollment numbers grouped by academic year.
```php
$enrollment = $course->getEnrollmentByYear();
// Returns: ['2022/2023' => 120, '2023/2024' => 150]
```

### Usage Examples

#### Example 1: Get active courses with statistics
```php
$courses = MruCourse::active()
    ->get()
    ->map(function ($course) {
        return [
            'id' => $course->courseID,
            'name' => $course->courseName,
            'students' => $course->getStudentCount(),
            'pass_rate' => $course->getPassRate(),
        ];
    });
```

#### Example 2: Find course and get statistics
```php
$course = MruCourse::where('courseID', 'BEFI1101')->first();
$stats = $course->getStatistics('2023/2024');
```

---

## 3. MruResultController

### Purpose
Laravel Admin controller for managing student academic results. Provides full CRUD operations, filtering, statistics, and export functionality.

### Location
`app/Admin/Controllers/MruResultController.php`

### Features

1. **Grid View (List)**
   - Displays all results with filtering and sorting
   - Color-coded scores and grades
   - Export functionality
   - Statistics header
   - Pagination

2. **Detail View (Show)**
   - Complete result information
   - Student details
   - Course information
   - Academic information
   - Results and status

3. **Form (Create/Edit)**
   - Tabbed interface
   - Student & Course Info tab
   - Academic Details tab
   - Results tab
   - Validation rules
   - Auto-calculation of GPA

4. **Advanced Features**
   - Student transcript generation
   - Course statistics page
   - Grade distribution charts
   - Pass/Fail analytics

### Grid Columns

| Column | Description | Features |
|--------|-------------|----------|
| ID | Result ID | Sortable |
| Student Reg No. | Registration number | Sortable, Filterable, Linked |
| Student Name | From relationship | - |
| Course ID | Course identifier | Sortable, Filterable |
| Course Name | From relationship | - |
| Academic Year | E.g., 2023/2024 | Sortable, Filterable |
| Semester | 1 or 2 | Sortable, Filterable, Labeled |
| Study Year | 1-7 | Sortable, Filterable |
| Score | 0-100 | Sortable, Color-coded |
| Grade | Letter grade | Sortable, Filterable, Labeled |
| Grade Points | Point value | Sortable |
| GPA | Grade Point Average | Sortable, Formatted |
| Credit Units | Course credits | Sortable |
| Program | Program ID | Sortable, Filterable |
| Status | Pass/Fail | Labeled |

### Filters Available

1. Registration Number (like)
2. Course ID (like)
3. Academic Year (like)
4. Semester (equal, select)
5. Study Year (equal, select)
6. Grade (equal, select)
7. Score Range (between)
8. Program (like)
9. Result Status (Pass/Fail)

### Form Tabs

#### Tab 1: Student & Course Info
- Student Registration Number (required, max 85)
- Course (select with ajax, required)
- Program ID (required, max 25)

#### Tab 2: Academic Details
- Academic Year (required, format: YYYY/YYYY)
- Semester (select, required, 1-2)
- Study Year (number, required, 1-7)
- Credit Units (decimal, required, 0-10)

#### Tab 3: Results
- Score (number, required, 0-100)
- Grade (select, required, A-F)
- Grade Points (decimal, required, 0-5)
- GPA (decimal, optional, auto-calculated)
- Comment (optional, max 25)

### Additional Controller Methods

#### 1. `transcript($content, $regno)`
Display student transcript.
```php
Route: /admin/mru-results/transcript/{regno}
```

#### 2. `courseStatistics($content, $courseId)`
Display course statistics.
```php
Route: /admin/mru-results/course-statistics/{courseId}
```

### Statistics Display

The grid header displays real-time statistics:
- Total Results
- Passed Results
- Failed Results
- Pass Rate (%)
- Fail Rate (%)
- Average Score
- Average GPA

---

## 4. Database Statistics

### Current Data (as of Dec 20, 2025)

**Results (acad_results table):**
- Total records: 605,764
- Passing results: 545,346 (90.0%)
- Failing results: 11,896 (2.0%)
- Other: 48,522 (8.0%)

**Courses (acad_course table):**
- Total courses: 6,881
- Active courses: ~5,500 (estimated)
- Core courses: Variable
- Optional courses: Variable

**Sample Result:**
```
Student: MRU INS/2/07/BEPE/06
Course: BEFI1101 - HISTORICAL FOUNDATIONS OF EDUCATION
Semester: 1
Academic Year: 2007/2008
Score: 61
Grade: C (3 pts)
GPA: 4.63
Status: Pass
```

---

## 5. Best Practices

### When Working with MruResult

1. **Always load relationships when needed:**
   ```php
   MruResult::with('course', 'student')->get();
   ```

2. **Use scopes for filtering:**
   ```php
   MruResult::forStudent($regno)
       ->forAcademicYear($year)
       ->passing()
       ->get();
   ```

3. **Use static methods for calculations:**
   ```php
   $gpa = MruResult::getCumulativeGPA($regno);
   ```

### When Working with MruCourse

1. **Check if course has results before statistics:**
   ```php
   if ($course->hasResults()) {
       $stats = $course->getStatistics();
   }
   ```

2. **Use active scope for active courses:**
   ```php
   MruCourse::active()->get();
   ```

### Controller Customization

1. **Grid is optimized for performance** - avoid loading too many relationships
2. **Filters are comprehensive** - use them to narrow down results
3. **Export functionality** - uses custom column mapping
4. **Statistics are cached** - for better performance on large datasets

---

## 6. Future Enhancements

### Planned Features
1. Bulk import of results from Excel/CSV
2. Grade distribution charts
3. Student progress tracking
4. Automated result approval workflow
5. Result verification and quality checks
6. Integration with student portal
7. Email notifications for result publication
8. Comparative analytics (year-over-year)

### Technical Improvements
1. Add result caching for faster queries
2. Implement result versioning/history
3. Add audit logging for changes
4. Implement result locking after publication
5. Add data validation rules
6. Create scheduled jobs for GPA calculations

---

## 7. Contact & Support

For questions or issues with the MRU Results system:
- Check the inline code documentation
- Review this documentation
- Contact the development team

**Last Updated:** December 20, 2025
**Version:** 1.0.0
**Status:** Production Ready ✅
