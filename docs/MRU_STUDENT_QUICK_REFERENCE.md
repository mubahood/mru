# MRU Student Detail Page - Quick Reference

## Critical Column Names (MUST USE EXACTLY!)

### acad_results
```php
'acad'          // NOT acad_year
'gpa'           // lowercase, NOT GPA
'CreditUnits'   // camelCase, NOT credit_units
'gradept'       // NOT points
'grade'         // NOT status
'courseid'      // lowercase
```

### acad_course_registration
```php
'courseID'      // capital ID, NOT courseid
'course_status' // NOT reg_status
'stud_session'  // NOT session
'acad_year'     // OK
'semester'      // OK
```

### acad_coursework_settings
```php
'courseID'      // capital ID, NOT courseid
'acadyear'      // NOT acad_year
'semester'      // OK
'progID'        // capital ID
```

### acad_course
```php
'courseID'      // Primary Key (lowercase c, capital ID)
'courseName'    // camelCase, NOT course_name
'CreditUnit'    // camelCase
```

## Relationship Keys

```php
// Student → Programme
$student->programme  // via 'progid' → 'progcode'

// Student → Results
$student->results    // via 'regno' → 'regno'

// Student → Registrations
$student->courseRegistrations  // via 'regno' → 'regno'

// Registration → Course
$registration->course  // via 'courseID' → 'courseID'

// Coursework Setting → Course
$setting->course  // via 'courseID' → 'courseID'

// Result → Course
$result->course  // via 'courseid' → 'courseID'
```

## Calculated Attributes

```php
$student->cumulative_gpa              // float (0.00 - 5.00)
$student->total_credits_earned        // int
$student->expected_graduation_year    // int|null
$student->current_year_of_study       // int (1 - duration)
$student->academic_standing           // string
$student->completion_percentage       // int (0 - 100)
$student->full_name                   // string
$student->age                         // int|null
```

## Key Methods

```php
// Semester GPA Summary
$semesterGpaSummary = $student->getSemesterGpaSummary();

// Retakes & Supplementary
$retakes = $student->getRetakesAndSupplementary();

// Result Count
$count = $student->getResultCount();

// Average GPA
$avg = $student->getAverageGPA();
```

## Eager Loading Pattern

```php
$student = MruStudent::with([
    'programme',
    'results',
    'courseRegistrations.course',
    'courseworkMarks.settings.course',
    'practicalExamMarks.settings.course'
])->findOrFail($id);
```

## View Data Access

```php
// Course Registration
$registration->courseID              // Course code
$registration->course->courseName    // Course name
$registration->course_status         // Status (REGULAR/RETAKE/NORMAL)
$registration->stud_session          // Session

// Coursework Marks
$mark->settings->courseID            // Course code
$mark->settings->course->courseName  // Course name
$mark->settings->acadyear            // Academic year
$mark->total_assignments             // Total assignments
$mark->final_score                   // Final score

// Academic Results
$result->acad                        // Academic year
$result->courseid                    // Course code
$result->course->courseName          // Course name
$result->CreditUnits                 // Credit units
$result->gpa                         // GPA (lowercase)

// Practical Marks
$practical->settings->courseID       // Course code
$practical->settings->course->courseName  // Course name
$practical->settings->acadyear       // Academic year
$practical->settings->total_mark     // Total marks
$practical->final_score              // Score obtained
```

## Badge Colors

```php
// Status Badges (Course Registration)
'REGULAR' → bg-success (green)
'RETAKE'  → bg-warning (yellow)
'NORMAL'  → bg-info (blue)

// GPA Badges
>= 4.5 → bg-success (green)
>= 3.0 → bg-info (blue)
>= 2.0 → bg-warning (yellow)
< 2.0  → bg-danger (red)

// Pass/Fail Badges
Pass → bg-success (green)
Fail → bg-danger (red)
```

## Common Mistakes to Avoid

❌ **DON'T:**
```php
$course->course_name          // Wrong column name
$result->GPA                  // Wrong case
$result->credit_units         // Wrong case
$registration->courseid       // Wrong case
$registration->reg_status     // Wrong column
$setting->acad_year           // Wrong column
$this->belongsTo(MruCourse::class, 'courseID', 'CourseID')  // Wrong case
```

✅ **DO:**
```php
$course->courseName           // Correct (camelCase)
$result->gpa                  // Correct (lowercase)
$result->CreditUnits          // Correct (camelCase)
$registration->courseID       // Correct (capital ID)
$registration->course_status  // Correct column
$setting->acadyear            // Correct column
$this->belongsTo(MruCourse::class, 'courseID', 'courseID')  // Correct
```

## Performance Tips

1. **Always eager load** relationships in controller
2. **Use groupBy** for semester summaries
3. **Cache** calculated attributes
4. **Specify columns** instead of SELECT *
5. **Add database indexes** on foreign keys

## Testing Commands

```bash
# Check if student loads
php artisan tinker
>>> $student = App\Models\MruStudent::with(['programme', 'results'])->first()
>>> $student->full_name
>>> $student->cumulative_gpa

# Check relationship
>>> $student->courseRegistrations->first()->course->courseName

# Check semester GPA
>>> $student->getSemesterGpaSummary()
```

## Files Reference

**Models:**
- `app/Models/MruStudent.php`
- `app/Models/MruCourse.php`
- `app/Models/MruResult.php`
- `app/Models/MruCourseRegistration.php`
- `app/Models/MruCourseworkMark.php`
- `app/Models/MruCourseworkSetting.php`
- `app/Models/MruPracticalExamMark.php`

**Controller:**
- `app/Admin/Controllers/MruStudentController.php`

**View:**
- `resources/views/admin/mru/students/show.blade.php`

**Documentation:**
- `docs/STUDENT_DETAIL_PAGE_DOCUMENTATION.md` (Full)
- `docs/MRU_STUDENT_QUICK_REFERENCE.md` (This file)

---

**Version:** 1.0.0  
**Last Updated:** December 24, 2025
