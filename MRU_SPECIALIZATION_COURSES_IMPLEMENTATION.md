# MRU Specialization Courses Implementation Summary

## Overview
This document summarizes the implementation of the MRU Specialization Courses module, which links specializations with courses including year, semester, lecturer assignment, and approval workflow.

**Implementation Date:** December 29, 2025
**Module Name:** MRU Specialization Courses
**Purpose:** Manage curriculum structure by linking courses to specializations with comprehensive metadata

---

## Database Structure

### Table: `mru_specialization_courses`

**Primary Key:** `id` (bigint, auto-increment)

**Foreign Keys:**
- `specialization_id` → `acad_specialisation.spec_id`
- `course_code` → `acad_course.coursecode`
- `prog_id` → `acad_programme.progcode`
- `faculty_code` → `acad_faculty.faculty_code`
- `lecturer_id` → `my_aspnet_users.id`

**Fields:**
| Field | Type | Description | Default |
|-------|------|-------------|---------|
| id | bigint | Primary key | auto |
| specialization_id | bigint | Specialization FK | required |
| course_code | varchar(15) | Course FK | required |
| prog_id | varchar(15) | Programme FK (auto-filled) | required |
| faculty_code | varchar(15) | Faculty FK (auto-filled) | nullable |
| year | tinyint | Academic year (1-4) | required |
| semester | tinyint | Semester (1-2) | required |
| credits | decimal(5,2) | Course credits | required |
| type | enum | mandatory/elective | mandatory |
| lecturer_id | bigint | Lecturer FK | nullable |
| status | enum | active/inactive | active |
| approval_status | enum | pending/approved/rejected | pending |
| rejection_reason | text | Reason if rejected | nullable |
| created_at | timestamp | Creation timestamp | auto |
| updated_at | timestamp | Update timestamp | auto |

**Indexes:**
- `mru_specialization_courses_specialization_id_index`
- `mru_specialization_courses_course_code_index`
- `mru_specialization_courses_prog_id_index`

**Unique Constraint:**
- `(specialization_id, course_code, year, semester)` - Prevents duplicate assignments

---

## Model Implementation

### File: `app/Models/MruSpecializationHasCourse.php`

**Class:** `MruSpecializationHasCourse`
**Table:** `mru_specialization_courses`
**Lines:** 489

#### Relationships
```php
// BelongsTo relationships
specialization()  // → MruSpecialisation
course()          // → MruCourse
programme()       // → MruProgramme
faculty()         // → MruFaculty
lecturer()        // → User
```

#### Query Scopes
```php
scopeForSpecialization($query, $specializationId)
scopeForProgramme($query, $progId)
scopeForYear($query, $year)
scopeForSemester($query, $semester)
scopeMandatory($query)      // type = mandatory
scopeElective($query)       // type = elective
scopeActive($query)         // status = active
scopeApproved($query)       // approval_status = approved
scopePending($query)        // approval_status = pending
```

#### Accessors
```php
getFullNameAttribute()      // Returns "Course Name (Y1 S2)"
getYearSemesterAttribute()  // Returns "Y1 S2"
getIsApprovedAttribute()    // Returns boolean
getIsPendingAttribute()     // Returns boolean
getIsRejectedAttribute()    // Returns boolean
```

#### Methods
```php
approve()                   // Set approval_status to approved
reject($reason)             // Set approval_status to rejected with reason
activate()                  // Set status to active
deactivate()                // Set status to inactive
```

#### Constants
```php
TYPE_MANDATORY = 'mandatory'
TYPE_ELECTIVE = 'elective'
STATUS_ACTIVE = 'active'
STATUS_INACTIVE = 'inactive'
APPROVAL_PENDING = 'pending'
APPROVAL_APPROVED = 'approved'
APPROVAL_REJECTED = 'rejected'
```

---

## Controller Implementation

### File: `app/Admin/Controllers/MruSpecializationHasCourseController.php`

**Class:** `MruSpecializationHasCourseController extends AdminController`
**Title:** "Specialization Courses"
**Lines:** ~340

#### Grid View Features
- **Columns:** ID, Specialization, Course, Programme, Year, Semester, Credits, Type, Lecturer, Approval Status, Status
- **Sorting:** ID, Programme, Year, Semester, Credits, Type, Approval Status, Status
- **Filters:**
  - Specialization (select dropdown)
  - Programme (select dropdown)
  - Year (1-4)
  - Semester (1-2)
  - Type (mandatory/elective)
  - Approval Status (pending/approved/rejected)
  - Status (active/inactive)
- **Actions:** Approve/Reject buttons for pending items
- **Order By:** prog_id ASC, year ASC, semester ASC

#### Form Features
- **Fields:**
  1. Specialization (select with search, loads courses dynamically)
  2. Course (select with search, loaded from API)
  3. Year (select 1-4)
  4. Semester (select 1-2)
  5. Credits (decimal, default 3)
  6. Type (select mandatory/elective)
  7. Lecturer (select from employees)
  8. Status (select active/inactive)
  9. Approval Status (select pending/approved/rejected)
  10. Rejection Reason (textarea, optional)

- **Auto-fill Logic:**
  - When specialization is selected, automatically fills:
    - `prog_id` from specialization's programme
    - `faculty_code` from specialization's programme's faculty
  - Implemented in `saving()` form callback

- **Validation:**
  - Required: specialization_id, course_code, year, semester, credits, type, status, approval_status
  - Year: between 1-4
  - Semester: between 1-2
  - Type: in (mandatory, elective)
  - Status: in (active, inactive)
  - Approval Status: in (pending, approved, rejected)

#### API Endpoint
```php
GET /admin/api/courses-by-specialization?q={search_term}
```
- Returns courses in Select2 format
- Searches by course code or course name
- Limit: 20 results
- Response format:
```json
[
    {
        "id": "COURSE001",
        "text": "COURSE001 - Course Name"
    }
]
```

---

## Routing

### File: `app/Admin/routes.php`

**Resource Route:**
```php
$router->resource('mru-specialization-courses', MruSpecializationHasCourseController::class);
```

**API Route:**
```php
$router->get('api/courses-by-specialization', 'MruSpecializationHasCourseController@getCoursesBySpecialization');
```

**Generated URLs:**
- Index: `/admin/mru-specialization-courses`
- Create: `/admin/mru-specialization-courses/create`
- Store: `POST /admin/mru-specialization-courses`
- Show: `/admin/mru-specialization-courses/{id}`
- Edit: `/admin/mru-specialization-courses/{id}/edit`
- Update: `PUT /admin/mru-specialization-courses/{id}`
- Delete: `DELETE /admin/mru-specialization-courses/{id}`

---

## Menu Integration

### Admin Menu Entry

**Database Table:** `admin_menu`
**Record Details:**
- **ID:** 219
- **Parent ID:** 195 (MRU submenu)
- **Order:** 200
- **Title:** Specialization Courses
- **Icon:** fa-link
- **URI:** mru-specialization-courses
- **Created:** 2025-12-29

**Menu Path:** 
```
MRU → Specialization Courses
```

---

## Usage Examples

### Creating a New Specialization-Course Link

1. Navigate to **MRU → Specialization Courses**
2. Click **Create** button
3. Select a **Specialization** (e.g., "Bachelor of Computer Science")
   - Programme and Faculty are automatically filled
4. Select a **Course** (e.g., "COMP101 - Programming I")
5. Select **Year** (e.g., Year 1)
6. Select **Semester** (e.g., Semester 1)
7. Enter **Credits** (e.g., 3)
8. Select **Type** (Mandatory or Elective)
9. Optionally select a **Lecturer**
10. Set **Status** (Active)
11. Set **Approval Status** (Pending)
12. Click **Submit**

### Approving a Pending Course Assignment

1. Navigate to **MRU → Specialization Courses**
2. Find the pending item in the grid
3. Click the **Approve** button in the actions column
4. The approval_status will be set to 'approved'

### Filtering Courses

Example queries:
```php
// Get all mandatory courses for Year 1, Semester 1
MruSpecializationHasCourse::mandatory()
    ->forYear(1)
    ->forSemester(1)
    ->get();

// Get all approved courses for a specialization
MruSpecializationHasCourse::forSpecialization(45)
    ->approved()
    ->get();

// Get all active elective courses
MruSpecializationHasCourse::elective()
    ->active()
    ->get();
```

---

## Data Statistics

**Current Records:**
- Specializations: 115
- Courses: 6,881
- Programmes: 128
- Specialization-Course Links: 0 (newly created)

---

## Files Modified/Created

### Created Files:
1. `database/migrations/2025_12_29_150916_create_mru_specialization_courses_table.php`
2. `app/Models/MruSpecializationHasCourse.php`
3. `app/Admin/Controllers/MruSpecializationHasCourseController.php`
4. `MRU_SPECIALIZATION_COURSES_IMPLEMENTATION.md` (this file)

### Modified Files:
1. `app/Admin/routes.php` - Added resource route and API route
2. Database: `admin_menu` table - Added menu item (ID: 219)

---

## Testing Checklist

- [x] Migration runs successfully
- [x] Model can be instantiated
- [x] Model relationships work correctly
- [x] Controller can be instantiated
- [x] Route is registered
- [x] Menu item is created
- [x] All caches cleared

### Next Steps for Testing:
- [ ] Access the grid at `/admin/mru-specialization-courses`
- [ ] Test filters functionality
- [ ] Create a new specialization-course link
- [ ] Verify auto-fill for programme and faculty
- [ ] Test course selection dropdown
- [ ] Test approval workflow
- [ ] Test validation rules
- [ ] Test unique constraint (try to create duplicate)

---

## Design Guidelines Compliance

This implementation follows the **MRU_MODEL_CONTROLLER_GUIDELINES.md**:

✅ Simple, straight form (no tabs)
✅ No help hints
✅ Clear field labels
✅ Proper validation rules
✅ Eager loading in grid
✅ Efficient query scopes
✅ Proper relationships
✅ Clear documentation
✅ Professional code structure

---

## Support

For issues or questions about this module:
1. Check model scopes in `MruSpecializationHasCourse.php`
2. Review controller form logic in `MruSpecializationHasCourseController.php`
3. Verify database constraints in migration file
4. Check API endpoint for course loading

---

**End of Implementation Summary**
