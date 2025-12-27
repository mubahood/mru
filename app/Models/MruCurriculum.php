<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * MruCurriculum Model
 * 
 * Represents curriculum versions for academic programmes.
 * Tracks different versions of programme curricula approved by NCHE
 * (National Council for Higher Education) across different years and intakes.
 * 
 * Purpose:
 * - Version control for programme curricula
 * - NCHE compliance tracking
 * - Intake-based curriculum differentiation
 * - Historical curriculum records
 * - Links to course structure via acad_programmecourses
 * 
 * Database Structure:
 * - Table: acad_curriculum
 * - Primary Key: ID
 * - Total Records: 142 curriculum versions
 * - Programmes Covered: 126 distinct programmes
 * - Date Range: 2007 to 2025
 * - Actively Used: 73 curriculum versions
 * 
 * @property int $ID Primary key
 * @property string $Tittle Curriculum title/name
 * @property string|null $Description Curriculum description and approval details
 * @property string $Progcode Programme code (FK to acad_programme)
 * @property int $StartYear Year when curriculum becomes effective
 * @property string $intake Intake season (AUGUST, JANUARY, JULY, JUNE, FEBRUARY)
 * 
 * @method static Builder forProgramme(string $progcode) Get curricula for specific programme
 * @method static Builder forYear(int $year) Get curricula starting in specific year
 * @method static Builder forIntake(string $intake) Get curricula for specific intake
 * @method static Builder active() Get currently active curricula
 * @method static Builder ordered() Get ordered by start year descending
 * 
 * @package App\Models
 */
class MruCurriculum extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_curriculum';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'Tittle',
        'Description',
        'Progcode',
        'StartYear',
        'intake',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'StartYear' => 'integer',
    ];

    /**
     * Get the programme this curriculum belongs to.
     *
     * @return BelongsTo
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(MruProgramme::class, 'Progcode', 'progcode');
    }

    /**
     * Get all programme courses linked to this curriculum.
     *
     * @return HasMany
     */
    public function programmeCourses(): HasMany
    {
        return $this->hasMany(MruProgrammeCourse::class, 'CurriculumID', 'ID');
    }

    /**
     * Scope to get curricula for a specific programme.
     *
     * @param Builder $query
     * @param string $progcode
     * @return Builder
     */
    public function scopeForProgramme(Builder $query, string $progcode): Builder
    {
        return $query->where('Progcode', $progcode);
    }

    /**
     * Scope to get curricula starting in a specific year.
     *
     * @param Builder $query
     * @param int $year
     * @return Builder
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('StartYear', $year);
    }

    /**
     * Scope to get curricula for a specific intake.
     *
     * @param Builder $query
     * @param string $intake
     * @return Builder
     */
    public function scopeForIntake(Builder $query, string $intake): Builder
    {
        return $query->where('intake', $intake);
    }

    /**
     * Scope to get currently active curricula (recent years).
     *
     * @param Builder $query
     * @param int $yearsBack Number of years to look back (default: 5)
     * @return Builder
     */
    public function scopeActive(Builder $query, int $yearsBack = 5): Builder
    {
        $currentYear = date('Y');
        $cutoffYear = $currentYear - $yearsBack;
        
        return $query->where('StartYear', '>=', $cutoffYear);
    }

    /**
     * Scope to order by start year descending.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('StartYear', 'desc')
                     ->orderBy('intake', 'asc')
                     ->orderBy('Progcode', 'asc');
    }

    /**
     * Get full curriculum display name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->Progcode} - {$this->StartYear} {$this->intake}";
    }

    /**
     * Get programme name from relationship.
     *
     * @return string
     */
    public function getProgrammeNameAttribute(): string
    {
        return $this->programme ? $this->programme->progname : '-';
    }

    /**
     * Get count of courses in this curriculum.
     *
     * @return int
     */
    public function getCourseCountAttribute(): int
    {
        return $this->programmeCourses()->count();
    }

    /**
     * Check if curriculum is recent (within last 3 years).
     *
     * @return bool
     */
    public function getIsRecentAttribute(): bool
    {
        $currentYear = date('Y');
        return $this->StartYear >= ($currentYear - 3);
    }
}
