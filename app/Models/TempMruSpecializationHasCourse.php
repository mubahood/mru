<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * TempMruSpecializationHasCourse Model
 * 
 * Temporary storage model for automatic curriculum generation.
 * Used to generate curriculum recommendations before user selects which to import.
 * 
 * Database Structure:
 * - Table: temp_mru_specialization_courses
 * - Primary Key: id (auto-incrementing)
 * 
 * Purpose:
 * Temporary table for curriculum generation workflow:
 * 1. System automatically generates curriculum based on historical data
 * 2. Records stored here for review
 * 3. User selects which courses to import
 * 4. Selected records copied to permanent mru_specialization_courses table
 * 5. Temp records cleared
 * 
 * @property int $id Primary key
 * @property int $specialization_id FK to acad_specialisation
 * @property string $course_code FK to acad_course
 * @property string $prog_id Programme code
 * @property string $faculty_code Faculty code
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
 * @package App\Models
 * @author MRU Development Team
 * @version 1.0.0
 * @created 2025-12-29
 */
class TempMruSpecializationHasCourse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'temp_mru_specialization_courses';

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
        'is_created',
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
        'is_created' => 'boolean',
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
     * Get the specialization this course belongs to
     *
     * @return BelongsTo
     */
    public function specialization(): BelongsTo
    {
        return $this->belongsTo(MruSpecialisation::class, 'specialization_id', 'spec_id');
    }

    /**
     * Get the course details
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(MruCourse::class, 'course_code', 'courseID');
    }

    /**
     * Get the programme
     *
     * @return BelongsTo
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(MruProgramme::class, 'prog_id', 'progcode');
    }

    /**
     * Get the faculty
     *
     * @return BelongsTo
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(MruFaculty::class, 'faculty_code', 'faculty_code');
    }

    /**
     * Get the lecturer assigned
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
     * Scope to filter by specialization
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
     * Scope to filter by programme
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
     * Scope to filter by year
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
     * Scope to filter by semester
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
     * Scope to filter mandatory courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeMandatory(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MANDATORY);
    }

    /**
     * Scope to filter elective courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeElective(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_ELECTIVE);
    }

    /**
     * Scope to filter active courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter approved courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    /**
     * Scope to filter pending courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    /**
     * Scope to filter created courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCreated(Builder $query): Builder
    {
        return $query->where('is_created', true);
    }

    /**
     * Scope to filter not created courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeNotCreated(Builder $query): Builder
    {
        return $query->where('is_created', false);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Copy this record to permanent table
     *
     * @return MruSpecializationHasCourse|null
     */
    public function copyToPermanent(): ?MruSpecializationHasCourse
    {
        try {
            return MruSpecializationHasCourse::create([
                'specialization_id' => $this->specialization_id,
                'course_code' => $this->course_code,
                'prog_id' => $this->prog_id,
                'faculty_code' => $this->faculty_code,
                'year' => $this->year,
                'semester' => $this->semester,
                'credits' => $this->credits,
                'type' => $this->type,
                'lecturer_id' => $this->lecturer_id,
                'status' => $this->status,
                'approval_status' => $this->approval_status,
                'rejection_reason' => $this->rejection_reason,
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Clear all temp records
     *
     * @return bool
     */
    public static function clearAll(): bool
    {
        return self::truncate();
    }

    /**
     * Clear temp records for specific specialization
     *
     * @param int $specializationId
     * @return int Number of deleted records
     */
    public static function clearForSpecialization(int $specializationId): int
    {
        return self::where('specialization_id', $specializationId)->delete();
    }
}
