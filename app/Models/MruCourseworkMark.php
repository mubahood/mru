<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MruCourseworkMark Model
 * 
 * Represents coursework marks (assignments and tests) entered by lecturers
 * Table: acad_coursework_marks (114,703 records)
 * 
 * This is where lecturers submit individual assignment and test marks during the semester.
 */
class MruCourseworkMark extends Model
{
    protected $table = 'acad_coursework_marks';
    
    protected $fillable = [
        'reg_no',
        'ass_1_mark',
        'ass_2_mark',
        'ass_3_mark',
        'ass_4_mark',
        'test_1_mark',
        'test_2_mark',
        'test_3_mark',
        'final_score',
        'stud_status',
        'CSID',
    ];

    protected $casts = [
        'ass_1_mark' => 'decimal:2',
        'ass_2_mark' => 'decimal:2',
        'ass_3_mark' => 'decimal:2',
        'ass_4_mark' => 'decimal:2',
        'test_1_mark' => 'decimal:2',
        'test_2_mark' => 'decimal:2',
        'test_3_mark' => 'decimal:2',
        'final_score' => 'decimal:2',
    ];

    /**
     * Relationship: Coursework marks belong to a student
     * Links via: reg_no → students.regno
     */
    public function student()
    {
        return $this->belongsTo(MruStudent::class, 'reg_no', 'regno');
    }

    /**
     * Relationship: Coursework marks are configured by coursework settings
     * Links via: CSID → acad_coursework_settings.ID
     */
    public function settings()
    {
        return $this->belongsTo(MruCourseworkSetting::class, 'CSID', 'ID');
    }

    /**
     * Accessor: Get total assignments marks
     */
    public function getTotalAssignmentsAttribute()
    {
        return ($this->ass_1_mark ?? 0) + 
               ($this->ass_2_mark ?? 0) + 
               ($this->ass_3_mark ?? 0) + 
               ($this->ass_4_mark ?? 0);
    }

    /**
     * Accessor: Get total tests marks
     */
    public function getTotalTestsAttribute()
    {
        return ($this->test_1_mark ?? 0) + 
               ($this->test_2_mark ?? 0) + 
               ($this->test_3_mark ?? 0);
    }

    /**
     * Accessor: Get student status badge color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'REGULAR' => 'success',
            'RETAKE' => 'warning',
            'CARRY' => 'info',
            'DEAD YEAR' => 'danger',
        ];

        return $colors[$this->stud_status] ?? 'default';
    }

    /**
     * Scope: Filter by student registration number
     */
    public function scopeByStudent($query, $regno)
    {
        return $query->where('reg_no', $regno);
    }

    /**
     * Scope: Filter by coursework setting (course/semester/year)
     */
    public function scopeBySetting($query, $csid)
    {
        return $query->where('CSID', $csid);
    }

    /**
     * Scope: Filter by student status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('stud_status', $status);
    }
}
