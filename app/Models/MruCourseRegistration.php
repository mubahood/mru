<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * MruCourseRegistration Model
 * 
 * Represents student course registrations in the MRU academic system.
 * Maps to 'acad_course_registration' table containing student enrollment data
 * for courses in specific academic years and semesters.
 * 
 * Purpose:
 * - Track which courses students have registered for
 * - Link students to courses per academic year and semester
 * - Record registration status (NORMAL, RETAKE, REGULAR)
 * - Associate registrations with programmes and study sessions
 * 
 * Database Structure:
 * - Primary Key: ID (auto-incrementing)
 * - Total Records: 99,630 registrations
 * - Foreign Keys: regno → students, courseID → acad_course, prog_id → acad_programme
 * 
 * @property int $ID Primary key
 * @property string $regno Student registration number (FK)
 * @property string $courseID Course code (FK to acad_course)
 * @property string $acad_year Academic year (e.g., "2023/2024")
 * @property int $semester Semester number (1, 2, or 3)
 * @property string $course_status Registration status (REGULAR, NORMAL, RETAKE)
 * @property string $prog_id Programme code (FK to acad_programme)
 * @property string $stud_session Study session (Day, WEEKEND, EVENING, etc.)
 * 
 * @method static Builder forStudent(string $regno) Get registrations for specific student
 * @method static Builder forCourse(string $courseId) Get registrations for specific course
 * @method static Builder forAcademicYear(string $year) Get registrations for specific academic year
 * @method static Builder forSemester(int $semester) Get registrations for specific semester
 * @method static Builder forProgramme(string $progId) Get registrations for specific programme
 * @method static Builder byStatus(string $status) Get registrations by status
 * @method static Builder bySession(string $session) Get registrations by study session
 * @method static Builder regularRegistrations() Get only REGULAR status registrations
 * @method static Builder retakes() Get only RETAKE status registrations
 * @method static Builder currentAcademicYear() Get registrations for current academic year
 * 
 * @package App\Models
 * @author MRU Development Team
 * @version 1.0.0
 */
class MruCourseRegistration extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_course_registration';

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
        'regno',
        'courseID',
        'acad_year',
        'semester',
        'course_status',
        'prog_id',
        'stud_session',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ID' => 'integer',
        'regno' => 'string',
        'courseID' => 'string',
        'acad_year' => 'string',
        'semester' => 'integer',
        'course_status' => 'string',
        'prog_id' => 'string',
        'stud_session' => 'string',
    ];

    /**
     * Course status constants
     */
    const STATUS_REGULAR = 'REGULAR';
    const STATUS_NORMAL = 'NORMAL';
    const STATUS_RETAKE = 'RETAKE';

    /**
     * Array of all valid statuses
     */
    const STATUSES = [
        self::STATUS_REGULAR,
        self::STATUS_NORMAL,
        self::STATUS_RETAKE,
    ];

    /**
     * Study session constants
     */
    const SESSION_DAY = 'Day';
    const SESSION_WEEKEND = 'WEEKEND';
    const SESSION_EVENING = 'EVENING';
    const SESSION_INSERVICE = 'INSERVICE';
    const SESSION_FULL_TIME = 'Full Time';
    const SESSION_PART_TIME = 'Part Time';

    /**
     * Array of all valid study sessions
     */
    const STUDY_SESSIONS = [
        self::SESSION_DAY,
        self::SESSION_WEEKEND,
        self::SESSION_EVENING,
        self::SESSION_INSERVICE,
        self::SESSION_FULL_TIME,
        self::SESSION_PART_TIME,
    ];

    /**
     * Semester constants
     */
    const SEMESTER_ONE = 1;
    const SEMESTER_TWO = 2;
    const SEMESTER_THREE = 3;

    /**
     * Array of valid semesters
     */
    const SEMESTERS = [
        self::SEMESTER_ONE,
        self::SEMESTER_TWO,
        self::SEMESTER_THREE,
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the course this registration belongs to
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(MruCourse::class, 'courseID', 'courseID');
    }

    /**
     * Get the student this registration belongs to
     *
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(MruStudent::class, 'regno', 'regno');
    }

    /**
     * Get the programme this registration belongs to
     *
     * @return BelongsTo
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(MruProgramme::class, 'prog_id', 'progcode');
    }

    /**
     * Get the academic year for this registration
     *
     * @return BelongsTo
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(MruAcademicYear::class, 'acad_year', 'acadyear');
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope query to specific student
     *
     * @param Builder $query
     * @param string $regno Student registration number
     * @return Builder
     */
    public function scopeForStudent(Builder $query, string $regno): Builder
    {
        return $query->where('regno', $regno);
    }

    /**
     * Scope query to specific course
     *
     * @param Builder $query
     * @param string $courseId Course identifier
     * @return Builder
     */
    public function scopeForCourse(Builder $query, string $courseId): Builder
    {
        return $query->where('courseID', $courseId);
    }

    /**
     * Scope query to specific academic year
     *
     * @param Builder $query
     * @param string $year Academic year (e.g., "2023/2024")
     * @return Builder
     */
    public function scopeForAcademicYear(Builder $query, string $year): Builder
    {
        return $query->where('acad_year', $year);
    }

    /**
     * Scope query to specific semester
     *
     * @param Builder $query
     * @param int $semester Semester number (1, 2, or 3)
     * @return Builder
     */
    public function scopeForSemester(Builder $query, int $semester): Builder
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope query to specific programme
     *
     * @param Builder $query
     * @param string $progId Programme code
     * @return Builder
     */
    public function scopeForProgramme(Builder $query, string $progId): Builder
    {
        return $query->where('prog_id', $progId);
    }

    /**
     * Scope query by registration status
     *
     * @param Builder $query
     * @param string $status Registration status
     * @return Builder
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('course_status', $status);
    }

    /**
     * Scope query by study session
     *
     * @param Builder $query
     * @param string $session Study session
     * @return Builder
     */
    public function scopeBySession(Builder $query, string $session): Builder
    {
        return $query->where('stud_session', $session);
    }

    /**
     * Scope query to only REGULAR registrations
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRegularRegistrations(Builder $query): Builder
    {
        return $query->where('course_status', self::STATUS_REGULAR);
    }

    /**
     * Scope query to only RETAKE registrations
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRetakes(Builder $query): Builder
    {
        return $query->where('course_status', self::STATUS_RETAKE);
    }

    /**
     * Scope query to current academic year
     * Gets the most recent academic year from the database
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrentAcademicYear(Builder $query): Builder
    {
        $currentYear = DB::table('acad_course_registration')
            ->select('acad_year')
            ->orderByRaw('acad_year DESC')
            ->limit(1)
            ->value('acad_year');

        return $query->where('acad_year', $currentYear ?? '');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted status label
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->course_status) {
            self::STATUS_REGULAR => '<span class="label label-success">Regular</span>',
            self::STATUS_NORMAL => '<span class="label label-info">Normal</span>',
            self::STATUS_RETAKE => '<span class="label label-warning">Retake</span>',
            default => '<span class="label label-default">' . $this->course_status . '</span>',
        };
    }

    /**
     * Get formatted session label
     *
     * @return string
     */
    public function getSessionLabelAttribute(): string
    {
        return match($this->stud_session) {
            self::SESSION_DAY => '<span class="label label-primary">Day</span>',
            self::SESSION_WEEKEND => '<span class="label label-info">Weekend</span>',
            self::SESSION_EVENING => '<span class="label label-warning">Evening</span>',
            self::SESSION_INSERVICE => '<span class="label label-success">In-Service</span>',
            self::SESSION_FULL_TIME => '<span class="label label-primary">Full Time</span>',
            self::SESSION_PART_TIME => '<span class="label label-info">Part Time</span>',
            default => '<span class="label label-default">' . $this->stud_session . '</span>',
        };
    }

    /**
     * Get semester label
     *
     * @return string
     */
    public function getSemesterLabelAttribute(): string
    {
        return match($this->semester) {
            self::SEMESTER_ONE => 'Semester 1',
            self::SEMESTER_TWO => 'Semester 2',
            self::SEMESTER_THREE => 'Semester 3',
            default => 'Semester ' . $this->semester,
        };
    }

    /**
     * Get course name from relationship
     *
     * @return string
     */
    public function getCourseNameAttribute(): string
    {
        return $this->course ? $this->course->courseName : 'Unknown Course';
    }

    /**
     * Get programme name from relationship
     *
     * @return string
     */
    public function getProgrammeNameAttribute(): string
    {
        return $this->programme ? $this->programme->progname : 'Unknown Programme';
    }

    /**
     * Normalize course ID to uppercase
     *
     * @param string|null $value
     * @return void
     */
    public function setCourseIDAttribute(?string $value): void
    {
        $this->attributes['courseID'] = $value ? strtoupper(trim($value)) : $value;
    }

    /**
     * Normalize registration number to uppercase
     *
     * @param string|null $value
     * @return void
     */
    public function setRegnoAttribute(?string $value): void
    {
        $this->attributes['regno'] = $value ? strtoupper(trim($value)) : $value;
    }

    /**
     * Normalize programme ID to uppercase
     *
     * @param string|null $value
     * @return void
     */
    public function setProgIdAttribute(?string $value): void
    {
        $this->attributes['prog_id'] = $value ? strtoupper(trim($value)) : $value;
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if registration is a retake
     *
     * @return bool
     */
    public function isRetake(): bool
    {
        return $this->course_status === self::STATUS_RETAKE;
    }

    /**
     * Check if registration is regular
     *
     * @return bool
     */
    public function isRegular(): bool
    {
        return $this->course_status === self::STATUS_REGULAR;
    }

    /**
     * Check if student is in day session
     *
     * @return bool
     */
    public function isDaySession(): bool
    {
        return $this->stud_session === self::SESSION_DAY;
    }

    /**
     * Check if student is in weekend session
     *
     * @return bool
     */
    public function isWeekendSession(): bool
    {
        return $this->stud_session === self::SESSION_WEEKEND;
    }

    /**
     * Get formatted display string
     *
     * @return string
     */
    public function getDisplayString(): string
    {
        return sprintf(
            '%s - %s (%s, Sem %d)',
            $this->regno,
            $this->courseID,
            $this->acad_year,
            $this->semester
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get summary statistics
     *
     * @return array
     */
    public static function getSummaryStatistics(): array
    {
        $total = self::count();
        $regularCount = self::where('course_status', self::STATUS_REGULAR)->count();
        $retakeCount = self::where('course_status', self::STATUS_RETAKE)->count();
        $normalCount = self::where('course_status', self::STATUS_NORMAL)->count();

        return [
            'total' => $total,
            'regular' => $regularCount,
            'retake' => $retakeCount,
            'normal' => $normalCount,
            'regular_percent' => $total > 0 ? round(($regularCount / $total) * 100, 2) : 0,
            'retake_percent' => $total > 0 ? round(($retakeCount / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get unique academic years
     *
     * @return array
     */
    public static function getAcademicYears(): array
    {
        return self::select('acad_year')
            ->distinct()
            ->orderBy('acad_year', 'desc')
            ->pluck('acad_year')
            ->toArray();
    }

    /**
     * Get registrations count by academic year
     *
     * @return array
     */
    public static function getCountByAcademicYear(): array
    {
        return self::select('acad_year', DB::raw('COUNT(*) as count'))
            ->groupBy('acad_year')
            ->orderBy('acad_year', 'desc')
            ->pluck('count', 'acad_year')
            ->toArray();
    }

    /**
     * Get registrations count by semester
     *
     * @return array
     */
    public static function getCountBySemester(): array
    {
        return self::select('semester', DB::raw('COUNT(*) as count'))
            ->groupBy('semester')
            ->orderBy('semester')
            ->pluck('count', 'semester')
            ->toArray();
    }

    /**
     * Get most popular courses (by registration count)
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getPopularCourses(int $limit = 10)
    {
        return self::select('courseID', DB::raw('COUNT(*) as registration_count'))
            ->groupBy('courseID')
            ->orderBy('registration_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
