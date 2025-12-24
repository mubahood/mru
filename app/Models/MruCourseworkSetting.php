<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MruCourseworkSetting Model
 * 
 * Represents coursework configuration for each course offering
 * Table: acad_coursework_settings (17,983 records)
 * 
 * Defines the structure of coursework assessments including max marks for each component.
 */
class MruCourseworkSetting extends Model
{
    protected $table = 'acad_coursework_settings';
    
    protected $fillable = [
        'lecturerID',
        'max_assn_1',
        'max_assn_2',
        'max_assn_3',
        'max_assn_4',
        'max_test_1',
        'max_test_2',
        'max_test_3',
        'comp_type',
        'total_mark',
        'courseID',
        'semester',
        'acadyear',
        'progID',
        'stud_session',
        'study_yr',
        'sheet_status',
        'cw_approve_status',
        'approved_by',
        'approval_date',
    ];

    protected $casts = [
        'max_assn_1' => 'decimal:2',
        'max_assn_2' => 'decimal:2',
        'max_assn_3' => 'decimal:2',
        'max_assn_4' => 'decimal:2',
        'max_test_1' => 'decimal:2',
        'max_test_2' => 'decimal:2',
        'max_test_3' => 'decimal:2',
        'total_mark' => 'decimal:2',
        'approval_date' => 'datetime',
    ];

    /**
     * Relationship: Setting belongs to a course
     * Links via: courseID → acad_courses.courseID
     */
    public function course()
    {
        return $this->belongsTo(MruCourse::class, 'courseID', 'courseID');
    }

    /**
     * Relationship: Setting belongs to a programme
     * Links via: progID → acad_programmes.progid
     */
    public function programme()
    {
        return $this->belongsTo(MruProgramme::class, 'progID', 'progcode');
    }

    /**
     * Relationship: Setting belongs to an academic year
     * Links via: acadyear → acad_academic_years.acad_year
     */
    public function academicYear()
    {
        return $this->belongsTo(MruAcademicYear::class, 'acadyear', 'acadyear');
    }

    /**
     * Relationship: Setting has many coursework marks
     * Links via: ID → acad_coursework_marks.CSID
     */
    public function courseworkMarks()
    {
        return $this->hasMany(MruCourseworkMark::class, 'CSID', 'ID');
    }

    /**
     * Accessor: Get total possible marks
     */
    public function getTotalPossibleAttribute()
    {
        return ($this->max_assn_1 ?? 0) + 
               ($this->max_assn_2 ?? 0) + 
               ($this->max_assn_3 ?? 0) + 
               ($this->max_assn_4 ?? 0) + 
               ($this->max_test_1 ?? 0) + 
               ($this->max_test_2 ?? 0) + 
               ($this->max_test_3 ?? 0);
    }

    /**
     * Accessor: Get approval status badge color
     */
    public function getApprovalColorAttribute()
    {
        $colors = [
            'APPROVED' => 'success',
            'PENDING' => 'warning',
            'REJECTED' => 'danger',
        ];

        return $colors[$this->cw_approve_status] ?? 'default';
    }

    /**
     * Scope: Filter by course
     */
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('courseID', $courseId);
    }

    /**
     * Scope: Filter by programme
     */
    public function scopeByProgramme($query, $progId)
    {
        return $query->where('progID', $progId);
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
     * Scope: Filter by approval status
     */
    public function scopeByApprovalStatus($query, $status)
    {
        return $query->where('cw_approve_status', $status);
    }
}
