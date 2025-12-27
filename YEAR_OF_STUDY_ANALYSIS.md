# MRU SYSTEM: YEAR OF STUDY ANALYSIS AND IMPLEMENTATION

**Date:** 27 December 2025  
**Purpose:** Comprehensive analysis of how "Year of Study" is recorded in MRU system and implementation strategy

---

## EXECUTIVE SUMMARY

After deep analysis of the MRU database, code, and documentation, I have identified that **Year of Study** is a critical dimension that exists in multiple tables but was NOT being utilized in the Academic Result Export system. This document outlines findings and implementation strategy.

---

## 1. DATABASE STRUCTURE FINDINGS

### A. TABLES WITH YEAR OF STUDY DATA

#### 1.1 `acad_results` Table - PRIMARY SOURCE ✅

**Field:** `studyyear` (INT)  
**Values:** 1, 2, 3, 4 (representing Year 1, Year 2, Year 3, Year 4)  
**Purpose:** Records which year the student was in when they took the course  
**Reliability:** ⭐⭐⭐⭐⭐ (Most reliable - actual historical data)

**Sample Data:**
```sql
+----+----------------------+----------+----------+-----------+-----------+-------+-------+
| ID | regno                | courseid | semester | acad      | studyyear | score | grade |
+----+----------------------+----------+----------+-----------+-----------+-------+-------+
| 64 | MRU INS/2/07/BEPE/06 | BEFI1101 |        1 | 2007/2008 |         1 |    61 | C     |
| 65 | MRU INS/2/07/BEPE/06 | BREI1101 |        1 | 2007/2008 |         1 |    80 | A     |
+----+----------------------+----------+----------+-----------+-----------+-------+-------+
```

**Structure:**
```sql
Field          Type             Null    Key    Default
studyyear      int(11)          YES                    NULL
```

**Key Points:**
- Links: regno + courseid + acad + semester + **studyyear**
- Contains actual year student took the course
- Handles retakes correctly (shows which year they retook it)
- Available for 596,635 result records

---

#### 1.2 `acad_registration` Table - REGISTRATION TRACKING

**Field:** `studyyear` (INT UNSIGNED, NOT NULL)  
**Purpose:** Records which year the student registered for in a given academic year + semester  
**Reliability:** ⭐⭐⭐⭐ (Good for current enrollment status)

**Sample Data:**
```sql
+----+---------------+-----------+----------+--------------+-----------+
| ID | regno         | acad_year | semester | regstatus    | studyyear |
+----+---------------+-----------+----------+--------------+-----------+
|  1 | MRU2023000004 | 2023/2024 |        1 | UNREGISTERED |         1 |
|  2 | MRU2023000005 | 2023/2024 |        1 | UNREGISTERED |         1 |
+----+---------------+-----------+----------+--------------+-----------+
```

**Use Case:**
- Shows what year students REGISTERED for (current year)
- Can differ from course year (if student is retaking lower-year courses)
- Good for administrative "Who is in Year 2 right now?" queries

---

#### 1.3 `acad_programmecourses` Table - CURRICULUM MAPPING

**Field:** `study_year` (INT UNSIGNED, NOT NULL)  
**Purpose:** Maps which courses belong to which year in a programme's curriculum  
**Reliability:** ⭐⭐⭐ (Good for curriculum planning)

**Sample Data:**
```sql
+----------+-------------+------------+----------+----+--------------+
| progcode | course_code | study_year | semester | ID | CurriculumID |
+----------+-------------+------------+----------+----+--------------+
| ACJ      | ADM1106B    |          1 |        1 |  1 |            6 |
| ACJ      | ADM1107B    |          1 |        1 |  2 |            6 |
| BAED     | BEF1101     |          1 |        1 |  9 |           10 |
+----------+-------------+------------+----------+----+--------------+
```

**Use Case:**
- Shows curriculum structure
- Defines which courses SHOULD be taken in which year
- Useful for curriculum compliance reports

---

#### 1.4 `acad_course_registration` Table - NO YEAR DATA ❌

**Critical Finding:** This table does NOT have studyyear field

**Structure:**
```sql
Field                          Type                Null    Key
regno                          char(25)            NO      MUL
courseID                       char(25)            NO
acad_year                      char(25)            NO
semester                       int(10) unsigned    NO
```

**Implication:**
- Course registration records don't directly show year
- Must JOIN with `acad_registration` or `acad_results` to get year context

---

#### 1.5 `student_has_semeters` Table - MODERN ENROLLMENT

**Fields:** `year_name`, `semester_name`  
**Purpose:** Modern enrollment system tracking year and semester of study  
**Usage:** Used in newer Laravel Admin system (not legacy MRU tables)

**Sample from Controller:**
```php
$filter->equal('year_name', 'Year of Study')
    ->select([
        1 => 'Year 1',
        2 => 'Year 2',
        3 => 'Year 3',
        4 => 'Year 4',
    ]);
```

---

## 2. CURRENT MRU EXPORT SYSTEM ANALYSIS

### 2.1 Current Query Logic (BEFORE Implementation)

**Location:** `app/Services/MruAcademicResultPdfService.php`

```php
$results = MruResult::select('regno', 'courseid', 'grade', 'score')
    ->where('progid', $this->export->programme_id)
    ->where('acad', $this->export->academic_year)      // ✅ Filter by year
    ->where('semester', $this->export->semester)       // ✅ Filter by semester
    ->whereIn('regno', $specRegnos)
    ->get()
```

**CRITICAL PROBLEM:** 
- ❌ NO filter on `studyyear`
- ❌ Mixes Year 1, Year 2, Year 3, Year 4 students in same export
- ❌ Cannot generate "Year 2 results only" reports

---

### 2.2 Export Table Structure (BEFORE Implementation)

**Table:** `mru_academic_result_exports`

**Current Fields:**
```
- export_name
- export_type (excel/pdf/both)
- academic_year (e.g., "2023/2024")
- semester (1 or 2)
- programme_id (e.g., "BCE")
- specialisation_id (nullable)
- start_range (default 1)
- end_range (default 100)
- sort_by (student/regno)
```

**Missing:** `study_year` field ❌

---

## 3. PROBLEM ILLUSTRATION

### Example Scenario: BCE Programme Export

**Request:** Generate results for BCE, 2024/2025, Semester 1

**Current System Output (WITHOUT year filter):**
```
BACHELOR OF CIVIL ENGINEERING - 2024/2025 SEMESTER 1
==============================================================
REG NO          NAME                COURSE1  COURSE2  COURSE3
MRU2021000001   John Doe (Y4)       A        B+       A-      <- Year 4 student
MRU2022000050   Jane Smith (Y3)     B        B        C+      <- Year 3 student  
MRU2023000100   Bob Lee (Y2)        A-       B+       B       <- Year 2 student
MRU2024000200   Alice Wu (Y1)       B+       A        A-      <- Year 1 student
```

**Problem:** All years mixed together! Different courses, different expectations.

**Desired Output (WITH year filter = 2):**
```
BACHELOR OF CIVIL ENGINEERING - 2024/2025 SEMESTER 1 - YEAR 2
==============================================================
REG NO          NAME                ENG2201  MATH2102 PHY2103
MRU2023000100   Bob Lee             A-       B+       B
MRU2023000101   Carol King          B        A-       B+
MRU2023000102   David Ouma          A        A        A-
```

**Benefit:** Clean, focused report for specific year cohort.

---

## 4. IMPLEMENTATION STRATEGY

### 4.1 Selected Approach: USE `acad_results.studyyear`

**Rationale:**
1. ✅ Data already exists in database
2. ✅ Most accurate - shows actual year course was taken
3. ✅ Handles retakes correctly
4. ✅ No complex JOINs needed
5. ✅ Consistent with existing data model

**Decision:** Make `study_year` **REQUIRED** (not optional)

---

### 4.2 Implementation Steps

#### Step 1: Database Migration
- Add `study_year` column to `mru_academic_result_exports` table
- Type: `integer` (NOT NULL, no default)
- Position: After `semester` column

#### Step 2: Model Update
- Add `study_year` to `$fillable` array
- Add validation rules

#### Step 3: Admin Form Update
- Add required select field for Year of Study
- Options: Year 1, Year 2, Year 3, Year 4
- Help text explaining what it filters

#### Step 4: Admin Grid Update
- Display year column
- Add to quick filters
- Show in badges/labels

#### Step 5: PDF Service Update
- Add WHERE clause on `studyyear`
- Display year in PDF header
- Update info section

#### Step 6: Excel Service Update
- Add WHERE clause on `studyyear`
- Update sheet names to include year

#### Step 7: Controller Update
- Update grid filters
- Update display columns

---

## 5. DATA FLOW DIAGRAM

```
USER SELECTS:
├── Programme: BCE
├── Academic Year: 2024/2025
├── Semester: 1
└── Year of Study: 2          <- NEW PARAMETER

    ↓

SYSTEM QUERIES:
SELECT * FROM acad_results
WHERE progid = 'BCE'
  AND acad = '2024/2025'
  AND semester = 1
  AND studyyear = 2           <- NEW FILTER
  
    ↓

RESULT SET:
Only students who took courses in Year 2
- May include Year 3 students retaking Year 2 courses
- Excludes Year 1, Year 3, Year 4 courses

    ↓

EXPORT OUTPUT:
PDF/Excel showing ONLY Year 2 results
Header: "YEAR 2 RESULTS - 2024/2025 SEMESTER 1"
```

---

## 6. EDGE CASES HANDLED

### 6.1 Students Retaking Courses
**Scenario:** Year 3 student retaking a Year 2 course

**Query:**
```sql
WHERE studyyear = 2
```

**Result:** 
- ✅ Course WILL appear (because it was taken/retaken in Year 2 context)
- Student's current year is irrelevant - we care about COURSE year

---

### 6.2 Advanced Standing Students
**Scenario:** Transfer student entering Year 2 directly

**Result:**
- ✅ Only their Year 2 courses appear in Year 2 export
- Their transfer credits (if any) don't appear

---

### 6.3 Part-Time Students
**Scenario:** Student taking longer than normal to complete

**Result:**
- ✅ Each course appears in the year it was taken
- Time elapsed is irrelevant

---

## 7. VALIDATION WITH REAL DATA

### Test Query:
```sql
SELECT DISTINCT studyyear 
FROM acad_results 
WHERE studyyear IS NOT NULL 
ORDER BY studyyear;

RESULT:
+-----------+
| studyyear |
+-----------+
|         1 |
|         2 |
|         3 |
|         4 |
+-----------+
```

**Confirmation:** 
- ✅ Data exists
- ✅ Values are consistent (1-4)
- ✅ Ready for implementation

---

## 8. BENEFITS OF IMPLEMENTATION

### 8.1 For Administrators
- 📊 Generate year-specific result reports
- 📈 Track cohort performance by year
- 📋 Separate Year 1 from Year 4 reports
- 🎯 Focused data for decision-making

### 8.2 For Faculty
- 👨‍🏫 Review Year 2 student performance specifically
- 📚 Identify struggling students in specific years
- 🔍 Course-level analysis by year

### 8.3 For System
- ⚡ Cleaner data exports
- 🎨 Better formatted PDFs (less columns per year)
- 💾 More logical data organization
- 🔒 Accurate historical records

---

## 9. IMPLEMENTATION CHECKLIST

- [ ] Create migration file
- [ ] Update MruAcademicResultExport model
- [ ] Update form in MruAcademicResultExportController
- [ ] Update grid in MruAcademicResultExportController
- [ ] Update grid filters
- [ ] Update MruAcademicResultPdfService query logic
- [ ] Update PDF header to display year
- [ ] Update MruAcademicResultExcelExport query logic
- [ ] Update Excel sheet names to include year
- [ ] Update MruAcademicResultSpecializationSheet query logic
- [ ] Test with real data
- [ ] Run migration
- [ ] Verify existing exports still work
- [ ] Create new test export with year filter

---

## 10. MIGRATION SAFETY

**Backward Compatibility:**
- Existing export records without `study_year` will fail validation ❌
- **Solution:** This is INTENTIONAL - all NEW exports MUST specify year
- **Action:** Add year to existing test exports or delete them

**Data Integrity:**
- No changes to source tables (`acad_results`)
- Only adding filter capability
- Zero risk to existing data ✅

---

## 11. CONCLUSION

The implementation of Year of Study filtering is:
- ✅ **NECESSARY** - Critical missing dimension
- ✅ **FEASIBLE** - Data already exists
- ✅ **SIMPLE** - One column, one WHERE clause
- ✅ **SAFE** - No risk to existing data
- ✅ **VALUABLE** - Major improvement to reporting

**Recommendation:** Proceed with implementation immediately.

---

**End of Analysis Document**
