<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * MruCourse Model
 * 
 * Represents academic courses in the MRU system.
 * Maps to the acad_course table which stores information about all courses
 * offered across different academic programmes and faculties.
 * 
 * @property string $courseID Primary key - unique course identifier
 * @property string|null $courseName Full name of the course
 * @property float|null $CreditUnit Credit units/hours for the course
 * @property float|null $ContactHr Total contact hours
 * @property float|null $LectureHr Lecture hours
 * @property float|null $PracticalHr Practical/lab hours
 * @property string|null $courseDescription Detailed course description
 * @property string|null $stat Course status (Active/InActive)
 * @property string|null $CoreStatus Core or Optional course designation
 * 
 * @method static Builder search(string $term) Search by course code or name
 * @method static Builder active() Filter active courses only
 * @method static Builder inactive() Filter inactive courses only
 * @method static Builder core() Filter core courses only
 * @method static Builder optional() Filter optional courses only
 * @method static Builder withCredits() Filter courses with credit units
 * @method static Builder byStatus(string $status) Filter by specific status
 * @method static Builder byCoreStatus(string $coreStatus) Filter by core status
 * @method static Builder orderByCode() Order by course code
 */
class MruCourse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_course';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'courseID';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
        'courseID',
        'courseName',
        'CreditUnit',
        'ContactHr',
        'LectureHr',
        'PracticalHr',
        'courseDescription',
        'stat',
        'CoreStatus',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'CreditUnit' => 'float',
        'ContactHr' => 'float',
        'LectureHr' => 'float',
        'PracticalHr' => 'float',
    ];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'InActive';
    
    /**
     * Core status constants
     */
    const CORE_STATUS_CORE = 'Core';
    const CORE_STATUS_OPTIONAL = 'Optional';

    /**
     * Array of all status values
     */
    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    /**
     * Array of all core status values
     */
    const CORE_STATUSES = [
        self::CORE_STATUS_CORE,
        self::CORE_STATUS_OPTIONAL,
    ];

    /**
     * Placeholder course code for "no course" or "all courses"
     */
    const PLACEHOLDER_CODE = '-';

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get all results for this course.
     * 
     * Note: acad_results table uses 'courseid' column to reference courses
     * 
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(MruResult::class, 'courseid', 'courseID');
    }

    /**
     * Get all course registrations for this course.
     * 
     * Note: acad_course_registration table uses 'courseID' column
     * 
     * @return HasMany
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(MruCourseRegistration::class, 'courseID', 'courseID');
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Search by course code or name
     *
     * @param Builder $query
     * @param string $term Search term
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('courseID', 'LIKE', "%{$term}%")
              ->orWhere('courseName', 'LIKE', "%{$term}%")
              ->orWhere('courseDescription', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Scope: Filter active courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('stat', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Filter inactive courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('stat', self::STATUS_INACTIVE);
    }

    /**
     * Scope: Filter core courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCore(Builder $query): Builder
    {
        return $query->where('CoreStatus', self::CORE_STATUS_CORE);
    }

    /**
     * Scope: Filter optional courses
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOptional(Builder $query): Builder
    {
        return $query->where('CoreStatus', self::CORE_STATUS_OPTIONAL);
    }

    /**
     * Scope: Filter courses with credit units
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithCredits(Builder $query): Builder
    {
        return $query->whereNotNull('CreditUnit')
                     ->where('CreditUnit', '>', 0);
    }

    /**
     * Scope: Filter by specific status
     *
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('stat', $status);
    }

    /**
     * Scope: Filter by core status
     *
     * @param Builder $query
     * @param string $coreStatus
     * @return Builder
     */
    public function scopeByCoreStatus(Builder $query, string $coreStatus): Builder
    {
        return $query->where('CoreStatus', $coreStatus);
    }

    /**
     * Scope: Order by course code
     *
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    public function scopeOrderByCode(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('courseID', $direction);
    }

    /**
     * Scope: Filter courses by credit unit range
     *
     * @param Builder $query
     * @param float $min Minimum credit units
     * @param float|null $max Maximum credit units (optional)
     * @return Builder
     */
    public function scopeByCreditRange(Builder $query, float $min, ?float $max = null): Builder
    {
        $query->where('CreditUnit', '>=', $min);
        
        if ($max !== null) {
            $query->where('CreditUnit', '<=', $max);
        }
        
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (GETTERS)
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor: Get full display name (code + name)
     *
     * @return string
     */
    public function getFullDisplayNameAttribute(): string
    {
        if (empty($this->courseName)) {
            return $this->courseID;
        }
        return trim($this->courseID) . ' - ' . trim($this->courseName);
    }

    /**
     * Accessor: Get short display name (code only)
     *
     * @return string
     */
    public function getShortDisplayNameAttribute(): string
    {
        return $this->courseID;
    }

    /**
     * Accessor: Check if course is active
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->stat === self::STATUS_ACTIVE;
    }

    /**
     * Accessor: Check if course is inactive
     *
     * @return bool
     */
    public function getIsInactiveAttribute(): bool
    {
        return $this->stat === self::STATUS_INACTIVE;
    }

    /**
     * Accessor: Check if course is core
     *
     * @return bool
     */
    public function getIsCoreAttribute(): bool
    {
        return $this->CoreStatus === self::CORE_STATUS_CORE;
    }

    /**
     * Accessor: Check if course is optional
     *
     * @return bool
     */
    public function getIsOptionalAttribute(): bool
    {
        return $this->CoreStatus === self::CORE_STATUS_OPTIONAL;
    }

    /**
     * Accessor: Get status label with color coding info
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->stat) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            default => 'Not Set'
        };
    }

    /**
     * Accessor: Get core status label
     *
     * @return string
     */
    public function getCoreStatusLabelAttribute(): string
    {
        return match($this->CoreStatus) {
            self::CORE_STATUS_CORE => 'Core',
            self::CORE_STATUS_OPTIONAL => 'Optional',
            default => 'Not Set'
        };
    }

    /**
     * Accessor: Get formatted credit unit display
     *
     * @return string
     */
    public function getCreditDisplayAttribute(): string
    {
        if ($this->CreditUnit === null || $this->CreditUnit == 0) {
            return 'Not Set';
        }
        return number_format($this->CreditUnit, 1) . ' ' . ($this->CreditUnit == 1 ? 'credit' : 'credits');
    }

    /**
     * Accessor: Get total hours (lecture + practical)
     *
     * @return float
     */
    public function getTotalHoursAttribute(): float
    {
        $lecture = $this->LectureHr ?? 0;
        $practical = $this->PracticalHr ?? 0;
        return $lecture + $practical;
    }

    /**
     * Accessor: Get hours breakdown display
     *
     * @return string
     */
    public function getHoursBreakdownAttribute(): string
    {
        $parts = [];
        
        if ($this->LectureHr && $this->LectureHr > 0) {
            $parts[] = $this->LectureHr . 'L';
        }
        
        if ($this->PracticalHr && $this->PracticalHr > 0) {
            $parts[] = $this->PracticalHr . 'P';
        }
        
        if (empty($parts)) {
            return 'Not Set';
        }
        
        return implode(' + ', $parts) . ' hrs';
    }

    /**
     * Accessor: Check if course has description
     *
     * @return bool
     */
    public function getHasDescriptionAttribute(): bool
    {
        return !empty($this->courseDescription);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS (SETTERS)
    |--------------------------------------------------------------------------
    */

    /**
     * Mutator: Ensure course code is trimmed and uppercase
     *
     * @param string|null $value
     * @return void
     */
    public function setCourseIDAttribute(?string $value): void
    {
        $this->attributes['courseID'] = $value ? strtoupper(trim($value)) : null;
    }

    /**
     * Mutator: Ensure course name is properly capitalized
     *
     * @param string|null $value
     * @return void
     */
    public function setCourseNameAttribute(?string $value): void
    {
        $this->attributes['courseName'] = $value ? trim($value) : null;
    }

    /**
     * Mutator: Ensure credit unit is positive or null
     *
     * @param float|null $value
     * @return void
     */
    public function setCreditUnitAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['CreditUnit'] = null;
        } else {
            $this->attributes['CreditUnit'] = max(0, (float) $value);
        }
    }

    /**
     * Mutator: Ensure status value is valid
     *
     * @param string|null $value
     * @return void
     */
    public function setStatAttribute(?string $value): void
    {
        if ($value && in_array($value, self::STATUSES)) {
            $this->attributes['stat'] = $value;
        } else {
            $this->attributes['stat'] = null;
        }
    }

    /**
     * Mutator: Ensure core status value is valid
     *
     * @param string|null $value
     * @return void
     */
    public function setCoreStatusAttribute(?string $value): void
    {
        if ($value && in_array($value, self::CORE_STATUSES)) {
            $this->attributes['CoreStatus'] = $value;
        } else {
            $this->attributes['CoreStatus'] = null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the number of students who have taken this course
     * 
     * @return int
     */
    public function getStudentCount(): int
    {
        return \DB::table('acad_results')
            ->where('courseid', $this->courseID)
            ->distinct('regno')
            ->count('regno');
    }

    /**
     * Get the number of results (enrollments) for this course
     * 
     * @return int
     */
    public function getResultCount(): int
    {
        return \DB::table('acad_results')
            ->where('courseid', $this->courseID)
            ->count();
    }

    /**
     * Get pass rate for this course
     * 
     * Note: Passing grades are A, B+, B, C+, C, D+, D (grade != 'F' and grade != 'E')
     * 
     * @return float Percentage (0-100)
     */
    public function getPassRate(): float
    {
        $total = $this->getResultCount();
        
        if ($total === 0) {
            return 0.0;
        }
        
        // Count passed results (all grades except F and E)
        $passed = \DB::table('acad_results')
            ->where('courseid', $this->courseID)
            ->whereNotNull('grade')
            ->where('grade', '!=', 'F')
            ->where('grade', '!=', 'E')
            ->where('grade', '!=', '')
            ->count();
        
        return round(($passed / $total) * 100, 2);
    }

    /**
     * Calculate total workload hours
     * 
     * @return float
     */
    public function calculateWorkload(): float
    {
        $contact = $this->ContactHr ?? 0;
        $lecture = $this->LectureHr ?? 0;
        $practical = $this->PracticalHr ?? 0;
        
        // If contact hours specified, use that; otherwise sum lecture + practical
        return $contact > 0 ? $contact : ($lecture + $practical);
    }

    /**
     * Check if this is a valid course (not placeholder)
     * 
     * @return bool
     */
    public function isValidCourse(): bool
    {
        return !empty($this->courseID) && 
               $this->courseID !== self::PLACEHOLDER_CODE &&
               $this->courseID !== 'ALL';
    }

    /**
     * Get course summary information
     * 
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'code' => $this->courseID,
            'name' => $this->courseName,
            'credits' => $this->CreditUnit,
            'hours' => $this->calculateWorkload(),
            'status' => $this->status_label,
            'core_status' => $this->core_status_label,
            'students' => $this->getStudentCount(),
            'pass_rate' => $this->getPassRate(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get dropdown options for course selection
     * Format: courseID => "courseID - courseName"
     * 
     * @param bool $activeOnly Only include active courses
     * @return array
     */
    public static function getDropdownOptions(bool $activeOnly = false): array
    {
        $query = self::query()
            ->where('courseID', '!=', self::PLACEHOLDER_CODE)
            ->where('courseID', '!=', '')
            ->whereNotNull('courseID')
            ->orderBy('courseID');
        
        if ($activeOnly) {
            $query->active();
        }
        
        return $query->get()
            ->pluck('full_display_name', 'courseID')
            ->toArray();
    }

    /**
     * Get summary statistics for all courses
     * 
     * @return array
     */
    public static function getSummaryStatistics(): array
    {
        $total = self::where('courseID', '!=', self::PLACEHOLDER_CODE)
            ->whereNotNull('courseID')
            ->where('courseID', '!=', '')
            ->count();
        
        $active = self::active()
            ->where('courseID', '!=', self::PLACEHOLDER_CODE)
            ->count();
        
        $inactive = self::inactive()
            ->where('courseID', '!=', self::PLACEHOLDER_CODE)
            ->count();
        
        $core = self::core()
            ->where('courseID', '!=', self::PLACEHOLDER_CODE)
            ->count();
        
        $optional = self::optional()
            ->where('courseID', '!=', self::PLACEHOLDER_CODE)
            ->count();
        
        $withCredits = self::withCredits()
            ->where('courseID', '!=', self::PLACEHOLDER_CODE)
            ->count();
        
        $avgCredits = self::withCredits()
            ->where('courseID', '!=', self::PLACEHOLDER_CODE)
            ->avg('CreditUnit');
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'core' => $core,
            'optional' => $optional,
            'with_credits' => $withCredits,
            'avg_credits' => round($avgCredits ?? 0, 2),
        ];
    }

    /**
     * Search courses by multiple criteria
     * 
     * @param array $criteria
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function searchCourses(array $criteria)
    {
        $query = self::query()->where('courseID', '!=', self::PLACEHOLDER_CODE);
        
        if (!empty($criteria['search'])) {
            $query->search($criteria['search']);
        }
        
        if (!empty($criteria['status'])) {
            $query->byStatus($criteria['status']);
        }
        
        if (!empty($criteria['core_status'])) {
            $query->byCoreStatus($criteria['core_status']);
        }
        
        if (isset($criteria['min_credits'])) {
            $max = $criteria['max_credits'] ?? null;
            $query->byCreditRange($criteria['min_credits'], $max);
        }
        
        return $query->orderByCode()->get();
    }
}
