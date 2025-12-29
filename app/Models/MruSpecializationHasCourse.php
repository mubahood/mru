<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * MruSpecializationHasCourse Model
 * 
 * Represents the assignment of courses to specializations within the MRU academic system.
 * Links specializations with specific courses, defining when (year/semester) they are taught,
 * who teaches them (lecturer), their status, type, and approval workflow.
 * 
 * Database Structure:
 * - Table: mru_specialization_courses
 * - Primary Key: id (auto-incrementing)
 * - Foreign Keys: 
 *   - specialization_id → acad_specialisation.spec_id
 *   - course_code → acad_course.coursecode
 *   - prog_id → acad_programme.progcode
 *   - faculty_code → acad_faculty.faculty_code
 *   - lecturer_id → my_aspnet_users.id
 * 
 * Purpose:
 * Manages curriculum structure by defining which courses are taught in which specializations,
 * during which year and semester, by which lecturer, with approval workflow.
 * 
 * @property int $id Primary key
 * @property int $specialization_id FK to acad_specialisation
 * @property string $course_code FK to acad_course
 * @property string $prog_id Programme code (auto-filled from specialization)
 * @property string $faculty_code Faculty code (auto-filled from programme)
 * @property int $year Year taught (1, 2, 3, 4)
 * @property int $semester Semester taught (1, 2)
 * @property float $credits Course credits
 * @property string $type Course type (mandatory, elective)
 * @property int|null $lecturer_id FK to users (lecturer assigned)
 * @property string $status Status (active, inactive)
 * @property string $approval_status Approval status (pending, approved, rejected)
 * @property string|null $rejection_reason Reason for rejection
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @method static Builder forSpecialization(int $specId)
 * @method static Builder forProgramme(string $progId)
 * @method static Builder forYear(int $year)
 * @method static Builder forSemester(int $semester)
 * @method static Builder mandatory()
 * @method static Builder elective()
 * @method static Builder active()
 * @method static Builder approved()
 * @method static Builder pending()
 * 
 * @package App\Models
 * @author MRU Development Team
 * @version 1.0.0
 * @created 2025-12-29
 */
class MruSpecializationHasCourse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mru_specialization_courses';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'specialization_id',
        'course_code',
        'prog_id',
        'faculty_code',
        'year',
        'semester',
        'credits',
        'type',
        'lecturer_id',
        'status',
        'approval_status',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'specialization_id' => 'integer',
        'year' => 'integer',
        'semester' => 'integer',
        'credits' => 'decimal:2',
        'lecturer_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================
    // BOOT METHOD
    // ============================================

    /**
     * The "booted" method of the model.
     * 
     * Automatically populate prog_id and faculty_code from specialization relationship.
     *
     * @return void
     */
    protected static function booted()
    {
        // Auto-populate prog_id and faculty_code when creating
        static::creating(function ($model) {
            if ($model->specialization_id && !$model->prog_id) {
                $specialization = MruSpecialisation::find($model->specialization_id);
                if ($specialization) {
                    $model->prog_id = $specialization->prog_id;
                    
                    // Get faculty_code from programme
                    if ($specialization->prog_id) {
                        $programme = MruProgramme::where('progcode', $specialization->prog_id)->first();
                        if ($programme) {
                            $model->faculty_code = $programme->faculty_code;
                        }
                    }
                }
            }
        });

        // Auto-populate prog_id and faculty_code when updating specialization_id
        static::updating(function ($model) {
            if ($model->isDirty('specialization_id')) {
                $specialization = MruSpecialisation::find($model->specialization_id);
                if ($specialization) {
                    $model->prog_id = $specialization->prog_id;
                    
                    // Get faculty_code from programme
                    if ($specialization->prog_id) {
                        $programme = MruProgramme::where('progcode', $specialization->prog_id)->first();
                        if ($programme) {
                            $model->faculty_code = $programme->faculty_code;
                        }
                    }
                }
            }
        });
    }

    /**
     * Constants for type values
     */
    const TYPE_MANDATORY = 'mandatory';
    const TYPE_ELECTIVE = 'elective';

    /**
     * Constants for status values
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Constants for approval status values
     */
    const APPROVAL_PENDING = 'pending';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the specialization this course assignment belongs to.
     * 
     * @return BelongsTo
     */
    public function specialization(): BelongsTo
    {
        return $this->belongsTo(MruSpecialisation::class, 'specialization_id', 'spec_id');
    }

    /**
     * Get the course for this assignment.
     * 
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(MruCourse::class, 'course_code', 'courseID');
    }

    /**
     * Get the programme this assignment belongs to.
     * 
     * @return BelongsTo
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(MruProgramme::class, 'prog_id', 'progcode');
    }

    /**
     * Get the faculty this assignment belongs to.
     * 
     * @return BelongsTo
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(MruFaculty::class, 'faculty_code', 'faculty_code');
    }

    /**
     * Get the lecturer assigned to teach this course.
     * 
     * @return BelongsTo
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id', 'id');
    }

    // ============================================
    // QUERY SCOPES
    // ============================================

    /**
     * Scope query to get courses for a specific specialization.
     * 
     * @param Builder $query
     * @param int $specId
     * @return Builder
     */
    public function scopeForSpecialization(Builder $query, int $specId): Builder
    {
        return $query->where('specialization_id', $specId);
    }

    /**
     * Scope query to get courses for a specific programme.
     * 
     * @param Builder $query
     * @param string $progId
     * @return Builder
     */
    public function scopeForProgramme(Builder $query, string $progId): Builder
    {
        return $query->where('prog_id', $progId);
    }

    /**
     * Scope query to get courses for a specific year.
     * 
     * @param Builder $query
     * @param int $year
     * @return Builder
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope query to get courses for a specific semester.
     * 
     * @param Builder $query
     * @param int $semester
     * @return Builder
     */
    public function scopeForSemester(Builder $query, int $semester): Builder
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope query to get mandatory courses only.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeMandatory(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MANDATORY);
    }

    /**
     * Scope query to get elective courses only.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeElective(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_ELECTIVE);
    }

    /**
     * Scope query to get active courses only.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope query to get approved courses only.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    /**
     * Scope query to get pending approval courses.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    // ============================================
    // ACCESSORS
    // ============================================

    /**
     * Get the full display name.
     * 
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $courseName = $this->course ? $this->course->coursename : $this->course_code;
        $specName = $this->specialization ? $this->specialization->spec : '';
        return "{$courseName} - {$specName} (Y{$this->year}S{$this->semester})";
    }

    /**
     * Get year-semester display.
     * 
     * @return string
     */
    public function getYearSemesterAttribute(): string
    {
        return "Year {$this->year}, Semester {$this->semester}";
    }

    /**
     * Check if approved.
     * 
     * @return bool
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    /**
     * Check if pending.
     * 
     * @return bool
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    /**
     * Check if rejected.
     * 
     * @return bool
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->approval_status === self::APPROVAL_REJECTED;
    }

    // ============================================
    // PUBLIC METHODS
    // ============================================

    /**
     * Approve this course assignment.
     * 
     * @return bool
     */
    public function approve(): bool
    {
        $this->approval_status = self::APPROVAL_APPROVED;
        $this->rejection_reason = null;
        return $this->save();
    }

    /**
     * Reject this course assignment.
     * 
     * @param string $reason
     * @return bool
     */
    public function reject(string $reason): bool
    {
        $this->approval_status = self::APPROVAL_REJECTED;
        $this->rejection_reason = $reason;
        return $this->save();
    }

    /**
     * Activate this course assignment.
     * 
     * @return bool
     */
    public function activate(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Deactivate this course assignment.
     * 
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->status = self::STATUS_INACTIVE;
        return $this->save();
    }
}
