<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MruExamSetting Model
 * 
 * Represents exam configuration and mark distribution
 * Table: acad_examsettings (15,229 records)
 * 
 * Defines exam structure including percentage weights for exam, coursework, and practicals.
 */
class MruExamSetting extends Model
{
    protected $table = 'acad_examsettings';
    
    protected $fillable = [
        'empCode',
        'courseID',
        'acad_year',
        'semester',
        'prog_id',
        'max_Q1',
        'max_Q2',
        'max_Q3',
        'max_Q4',
        'max_Q5',
        'max_Q6',
        'max_Q7',
        'max_Q8',
        'max_Q9',
        'max_Q10',
        'exam_percent',
        'cw_percent',
        'practical_percent',
        'final_total',
        'sheet_status',
        'ExamFormat',
        'stud_session',
        'study_yr',
    ];

    protected $casts = [
        'max_Q1' => 'decimal:2',
        'max_Q2' => 'decimal:2',
        'max_Q3' => 'decimal:2',
        'max_Q4' => 'decimal:2',
        'max_Q5' => 'decimal:2',
        'max_Q6' => 'decimal:2',
        'max_Q7' => 'decimal:2',
        'max_Q8' => 'decimal:2',
        'max_Q9' => 'decimal:2',
        'max_Q10' => 'decimal:2',
        'exam_percent' => 'decimal:2',
        'cw_percent' => 'decimal:2',
        'practical_percent' => 'decimal:2',
        'final_total' => 'decimal:2',
    ];

    /**
     * Relationship: Setting belongs to a course
     * Links via: courseID → acad_courses.CourseID
     */
    public function course()
    {
        return $this->belongsTo(MruCourse::class, 'courseID', 'CourseID');
    }

    /**
     * Relationship: Setting belongs to a programme
     * Links via: prog_id → acad_programmes.progid
     */
    public function programme()
    {
        return $this->belongsTo(MruProgramme::class, 'prog_id', 'progcode');
    }

    /**
     * Relationship: Setting belongs to an academic year
     * Links via: acad_year → acad_academic_years.acad_year
     */
    public function academicYear()
    {
        return $this->belongsTo(MruAcademicYear::class, 'acad_year', 'acadyear');
    }

    /**
     * Relationship: Setting has many exam results
     * Links via: ID → acad_examresults_faculty.settingsID
     */
    public function examResults()
    {
        return $this->hasMany(MruExamResultFaculty::class, 'settingsID', 'ID');
    }

    /**
     * Accessor: Get total exam marks possible
     */
    public function getTotalExamMarksAttribute()
    {
        return ($this->max_Q1 ?? 0) + 
               ($this->max_Q2 ?? 0) + 
               ($this->max_Q3 ?? 0) + 
               ($this->max_Q4 ?? 0) + 
               ($this->max_Q5 ?? 0) + 
               ($this->max_Q6 ?? 0) + 
               ($this->max_Q7 ?? 0) + 
               ($this->max_Q8 ?? 0) + 
               ($this->max_Q9 ?? 0) + 
               ($this->max_Q10 ?? 0);
    }

    /**
     * Accessor: Get weight distribution display
     */
    public function getWeightDistributionAttribute()
    {
        return sprintf(
            'Exam: %s%% | CW: %s%% | Practical: %s%%',
            $this->exam_percent,
            $this->cw_percent,
            $this->practical_percent
        );
    }

    /**
     * Accessor: Check if has practical component
     */
    public function getHasPracticalAttribute()
    {
        return $this->practical_percent > 0;
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
        return $query->where('prog_id', $progId);
    }

    /**
     * Scope: Filter by academic year
     */
    public function scopeByAcademicYear($query, $acadYear)
    {
        return $query->where('acad_year', $acadYear);
    }

    /**
     * Scope: Filter by semester
     */
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope: Filter settings with practical component
     */
    public function scopeWithPractical($query)
    {
        return $query->where('practical_percent', '>', 0);
    }
}
