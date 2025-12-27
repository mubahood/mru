<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * MruProgrammeCourse Model
 * 
 * Represents the curriculum structure linking programmes with courses.
 * Defines which courses belong to which programme, including the year
 * and semester they should be taken.
 * 
 * Purpose:
 * - Maps courses to programmes
 * - Defines course placement in curriculum (year + semester)
 * - Facilitates curriculum management and course allocation
 * - Supports academic planning and student registration
 * 
 * Database Structure:
 * - Table: acad_programmecourses
 * - Primary Key: ID
 * - Total Records: ~3,834 curriculum mappings
 * - Foreign Keys: progcode → acad_programme, course_code → acad_course
 * 
 * @property int $ID Primary key
 * @property string $progcode Programme code (FK to acad_programme)
 * @property string $course_code Course code (FK to acad_course)
 * @property int $study_year Year of study (1, 2, 3, 4)
 * @property int $semester Semester (1 or 2)
 * @property int $CurriculumID Curriculum reference ID
 * 
 * @method static Builder forProgramme(string $progcode) Get courses for specific programme
 * @method static Builder forYear(int $year) Get courses for specific year
 * @method static Builder forSemester(int $semester) Get courses for specific semester
 * @method static Builder forYearAndSemester(int $year, int $semester) Get courses for year and semester
 * 
 * @package App\Models
 */
class MruProgrammeCourse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_programmecourses';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

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
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'progcode',
        'course_code',
        'study_year',
        'semester',
        'CurriculumID',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'progcode' => 'string',
        'course_code' => 'string',
        'study_year' => 'integer',
        'semester' => 'integer',
        'CurriculumID' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the programme that this course belongs to.
     *
     * @return BelongsTo
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(MruProgramme::class, 'progcode', 'progcode');
    }

    /**
     * Get the course details.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(MruCourse::class, 'course_code', 'courseID');
    }

    /**
     * Get the curriculum version this course mapping belongs to.
     *
     * @return BelongsTo
     */
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(MruCurriculum::class, 'CurriculumID', 'ID');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to get courses for a specific programme.
     *
     * @param Builder $query
     * @param string $progcode
     * @return Builder
     */
    public function scopeForProgramme(Builder $query, string $progcode): Builder
    {
        return $query->where('progcode', $progcode);
    }

    /**
     * Scope to get courses for a specific study year.
     *
     * @param Builder $query
     * @param int $year
     * @return Builder
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('study_year', $year);
    }

    /**
     * Scope to get courses for a specific semester.
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
     * Scope to get courses for a specific year and semester.
     *
     * @param Builder $query
     * @param int $year
     * @param int $semester
     * @return Builder
     */
    public function scopeForYearAndSemester(Builder $query, int $year, int $semester): Builder
    {
        return $query->where('study_year', $year)
                    ->where('semester', $semester);
    }

    /**
     * Scope to order by study year and semester.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('study_year')
                    ->orderBy('semester')
                    ->orderBy('course_code');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted year and semester label.
     *
     * @return string
     */
    public function getYearSemesterAttribute(): string
    {
        return "Year {$this->study_year}, Semester {$this->semester}";
    }

    /**
     * Get full programme name from relationship.
     *
     * @return string
     */
    public function getProgrammeNameAttribute(): string
    {
        return $this->programme ? $this->programme->progname : '-';
    }

    /**
     * Get full course name from relationship.
     *
     * @return string
     */
    public function getCourseNameAttribute(): string
    {
        return $this->course ? $this->course->courseName : '-';
    }
}
