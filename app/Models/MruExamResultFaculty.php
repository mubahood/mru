<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MruExamResultFaculty Model
 * 
 * Represents exam marks entered by faculty and combined results
 * Table: acad_examresults_faculty (152,122 records)
 * 
 * THIS IS THE PRIMARY EXAM MARKS SUBMISSION TABLE.
 * Lecturers enter exam marks here and the system combines with coursework.
 */
class MruExamResultFaculty extends Model
{
    protected $table = 'acad_examresults_faculty';
    
    protected $fillable = [
        'regno',
        'course_id',
        'acadyear',
        'semester',
        'cw_mark_entered',
        'cw_mark',
        'test_mark_entered',
        'test_mark',
        'exam_mark_entered',
        'ex_mark',
        'total_mark',
        'progid',
        'stud_session',
        'grade',
        'gradept',
        'exam_status',
        'cyear',
        'approved_by',
        'creditUnits',
        'gpa',
        'settingsID',
    ];

    protected $casts = [
        'cw_mark' => 'decimal:2',
        'test_mark' => 'decimal:2',
        'ex_mark' => 'decimal:2',
        'total_mark' => 'decimal:2',
        'gradept' => 'decimal:2',
        'gpa' => 'decimal:2',
        'creditUnits' => 'integer',
        'cyear' => 'integer',
    ];

    /**
     * Relationship: Exam result belongs to a student
     * Links via: regno → students.regno
     */
    public function student()
    {
        return $this->belongsTo(MruStudent::class, 'regno', 'regno');
    }

    /**
     * Relationship: Exam result belongs to a course
     * Links via: course_id → acad_courses.CourseID
     */
    public function course()
    {
        return $this->belongsTo(MruCourse::class, 'course_id', 'CourseID');
    }

    /**
     * Relationship: Exam result belongs to a programme
     * Links via: progid → acad_programmes.progid
     */
    public function programme()
    {
        return $this->belongsTo(MruProgramme::class, 'progid', 'progcode');
    }

    /**
     * Relationship: Exam result belongs to an academic year
     * Links via: acadyear → acad_academic_years.acad_year
     */
    public function academicYear()
    {
        return $this->belongsTo(MruAcademicYear::class, 'acadyear', 'acadyear');
    }

    /**
     * Relationship: Exam result uses exam settings configuration
     * Links via: settingsID → acad_examsettings.ID
     */
    public function examSettings()
    {
        return $this->belongsTo(MruExamSetting::class, 'settingsID', 'ID');
    }

    /**
     * Relationship: Exam result may produce a final published result
     * Links via: regno + course_id + semester + acadyear → acad_results
     */
    public function finalResult()
    {
        return $this->hasOne(MruResult::class, 'regno', 'regno')
            ->where('courseid', $this->course_id)
            ->where('semester', $this->semester)
            ->where('acad', $this->acadyear);
    }

    /**
     * Accessor: Check if all marks are entered
     */
    public function getAllMarksEnteredAttribute()
    {
        return $this->cw_mark_entered && 
               $this->test_mark_entered && 
               $this->exam_mark_entered;
    }

    /**
     * Accessor: Get grade description
     */
    public function getGradeDescriptionAttribute()
    {
        $descriptions = [
            'A' => 'Excellent',
            'B+' => 'Very Good',
            'B' => 'Good',
            'C+' => 'Fairly Good',
            'C' => 'Satisfactory',
            'D+' => 'Fair',
            'D' => 'Pass',
            'E' => 'Marginal Fail',
            'F' => 'Fail',
        ];

        return $descriptions[$this->grade] ?? 'Unknown';
    }

    /**
     * Accessor: Get grade color for badges
     */
    public function getGradeColorAttribute()
    {
        $colors = [
            'A' => 'success',
            'B+' => 'success',
            'B' => 'primary',
            'C+' => 'primary',
            'C' => 'info',
            'D+' => 'warning',
            'D' => 'warning',
            'E' => 'danger',
            'F' => 'danger',
        ];

        return $colors[$this->grade] ?? 'default';
    }

    /**
     * Accessor: Check if passed
     */
    public function getIsPassAttribute()
    {
        return in_array($this->grade, ['A', 'B+', 'B', 'C+', 'C', 'D+', 'D']);
    }

    /**
     * Accessor: Get status badge text
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_pass ? 'PASS' : 'FAIL';
    }

    /**
     * Accessor: Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return $this->is_pass ? 'success' : 'danger';
    }

    /**
     * Scope: Filter by student
     */
    public function scopeByStudent($query, $regno)
    {
        return $query->where('regno', $regno);
    }

    /**
     * Scope: Filter by course
     */
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope: Filter by programme
     */
    public function scopeByProgramme($query, $progId)
    {
        return $query->where('progid', $progId);
    }

    /**
     * Scope: Filter by academic year
     */
    public function scopeByAcademicYear($query, $acadYear)
    {
        return $query->where('acadyear', $acadYear);
    }

    /**
     * Scope: Filter by semester
     */
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope: Filter by grade
     */
    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    /**
     * Scope: Filter by exam status
     */
    public function scopeByExamStatus($query, $status)
    {
        return $query->where('exam_status', $status);
    }

    /**
     * Scope: Filter passed results
     */
    public function scopePassed($query)
    {
        return $query->whereIn('grade', ['A', 'B+', 'B', 'C+', 'C', 'D+', 'D']);
    }

    /**
     * Scope: Filter failed results
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('grade', ['E', 'F']);
    }

    /**
     * Scope: Filter results with all marks entered
     */
    public function scopeAllMarksEntered($query)
    {
        return $query->where('cw_mark_entered', 1)
                     ->where('test_mark_entered', 1)
                     ->where('exam_mark_entered', 1);
    }
}
