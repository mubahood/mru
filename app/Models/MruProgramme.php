<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * MruProgramme Model
 * 
 * Represents academic programmes within the MRU system.
 * Maps to acad_programme table which stores programme information including
 * programme codes, names, faculty associations, credit requirements, and duration.
 * 
 * Database Structure:
 * - Primary Key: progcode (non-incrementing string)
 * - 128 programmes in total
 * - Related to: faculty (acad_faculty), results (acad_results)
 * 
 * @property string $progcode Programme code (PK) - e.g., "ACAD", "BED"
 * @property string $progname Full programme name
 * @property float $mincredit Minimum credit hours required
 * @property string $abbrev Programme abbreviation
 * @property float $couselength Course length in years
 * @property float $maxduration Maximum duration allowed
 * @property string $faculty_code Faculty code (FK to acad_faculty)
 * @property int $levelCode Programme level (1=Certificate, 2=Diploma, 3=Degree, 4=Masters, 5=PhD)
 * @property string $study_system Study system (Semester/Session)
 * 
 * @method static Builder search(string $term) Search programmes by code, name, or abbreviation
 * @method static Builder forFaculty(string $facultyCode) Get programmes for specific faculty
 * @method static Builder byLevel(int $level) Get programmes by level code
 * @method static Builder bySemester() Get semester-based programmes
 * @method static Builder bySession() Get session-based programmes
 * @method static Builder active() Get active programmes (excluding placeholder)
 * @method static Builder undergraduate() Get undergraduate programmes (levels 1-3)
 * @method static Builder postgraduate() Get postgraduate programmes (levels 4-5)
 * 
 * @package App\Models
 * @author MRU Development Team
 * @version 1.0.0
 */
class MruProgramme extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_programme';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'progcode';

    /**
     * The "type" of the auto-incrementing ID.
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
        'progcode',
        'progname',
        'mincredit',
        'abbrev',
        'couselength',
        'maxduration',
        'faculty_code',
        'levelCode',
        'study_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'progcode' => 'string',
        'progname' => 'string',
        'mincredit' => 'float',
        'abbrev' => 'string',
        'couselength' => 'float',
        'maxduration' => 'float',
        'faculty_code' => 'string',
        'levelCode' => 'integer',
        'study_system' => 'string',
    ];

    /**
     * Programme level constants
     */
    const LEVEL_CERTIFICATE = 1;
    const LEVEL_DIPLOMA = 2;
    const LEVEL_DEGREE = 3;
    const LEVEL_MASTERS = 4;
    const LEVEL_PHD = 5;

    /**
     * Study system constants
     */
    const SYSTEM_SEMESTER = 'Semester';
    const SYSTEM_SESSION = 'Session';

    /**
     * Placeholder programme code
     */
    const PLACEHOLDER_CODE = '-';

    /**
     * Array of all valid levels
     */
    const VALID_LEVELS = [
        self::LEVEL_CERTIFICATE,
        self::LEVEL_DIPLOMA,
        self::LEVEL_DEGREE,
        self::LEVEL_MASTERS,
        self::LEVEL_PHD,
    ];

    /**
     * Level labels for display
     */
    const LEVEL_LABELS = [
        self::LEVEL_CERTIFICATE => 'Certificate',
        self::LEVEL_DIPLOMA => 'Diploma',
        self::LEVEL_DEGREE => 'Degree',
        self::LEVEL_MASTERS => 'Masters',
        self::LEVEL_PHD => 'PhD',
    ];

    /**
     * Study system options
     */
    const STUDY_SYSTEMS = [
        self::SYSTEM_SEMESTER,
        self::SYSTEM_SESSION,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the faculty that owns this programme.
     * 
     * Relationship: Programme belongs to Faculty
     * Foreign Key: faculty_code
     *
     * @return BelongsTo
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(MruFaculty::class, 'faculty_code', 'faculty_code');
    }

    /**
     * Get all results/enrollments for this programme.
     * 
     * Relationship: Programme has many Results
     * Foreign Key: progcode in acad_results table (if exists)
     * Note: Disabled temporarily until results table structure is confirmed
     *
     * @return HasMany
     */
    public function results(): HasMany
    {
        // Return empty relation to avoid errors
        return $this->hasMany(self::class, 'progcode', 'progcode')->whereRaw('1 = 0');
    }

    /**
     * Get all students enrolled in this programme.
     * 
     * Relationship: Programme has many Students
     * Foreign Key: progid in acad_student table
     *
     * @return HasMany
     */
    public function students(): HasMany
    {
        return $this->hasMany(MruStudent::class, 'progid', 'progcode');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Search programmes by code, name, or abbreviation
     * 
     * Performs a LIKE search across progcode, progname, and abbrev fields.
     *
     * @param Builder $query
     * @param string $term Search term
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        
        return $query->where(function ($q) use ($term) {
            $q->where('progcode', 'LIKE', "%{$term}%")
              ->orWhere('progname', 'LIKE', "%{$term}%")
              ->orWhere('abbrev', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Scope: Get programmes for specific faculty
     *
     * @param Builder $query
     * @param string $facultyCode Faculty code
     * @return Builder
     */
    public function scopeForFaculty(Builder $query, string $facultyCode): Builder
    {
        return $query->where('faculty_code', $facultyCode);
    }

    /**
     * Scope: Get programmes by level code
     *
     * @param Builder $query
     * @param int $level Level code
     * @return Builder
     */
    public function scopeByLevel(Builder $query, int $level): Builder
    {
        return $query->where('levelCode', $level);
    }

    /**
     * Scope: Get semester-based programmes
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeBySemester(Builder $query): Builder
    {
        return $query->where('study_system', self::SYSTEM_SEMESTER);
    }

    /**
     * Scope: Get session-based programmes
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeBySession(Builder $query): Builder
    {
        return $query->where('study_system', self::SYSTEM_SESSION);
    }

    /**
     * Scope: Get active/valid programmes only
     * 
     * Excludes placeholder programmes (code "-" or "ALL")
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('progcode', '!=', self::PLACEHOLDER_CODE)
                    ->where('progcode', '!=', 'ALL');
    }

    /**
     * Scope: Get undergraduate programmes
     * 
     * Includes Certificate, Diploma, and Degree (levels 1-3)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUndergraduate(Builder $query): Builder
    {
        return $query->whereIn('levelCode', [
            self::LEVEL_CERTIFICATE,
            self::LEVEL_DIPLOMA,
            self::LEVEL_DEGREE,
        ]);
    }

    /**
     * Scope: Get postgraduate programmes
     * 
     * Includes Masters and PhD (levels 4-5)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePostgraduate(Builder $query): Builder
    {
        return $query->whereIn('levelCode', [
            self::LEVEL_MASTERS,
            self::LEVEL_PHD,
        ]);
    }

    /**
     * Scope: Order by programme name
     *
     * @param Builder $query
     * @param string $direction Sort direction (asc or desc)
     * @return Builder
     */
    public function scopeOrderByName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('progname', $direction);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor: Get full display name
     * 
     * Returns formatted name: "Code - Name (Abbrev)"
     * Example: "BED - BACHELOR OF EDUCATION (BED)"
     *
     * @return string
     */
    public function getFullDisplayNameAttribute(): string
    {
        return "{$this->progcode} - {$this->progname}" . 
               ($this->abbrev ? " ({$this->abbrev})" : '');
    }

    /**
     * Accessor: Get short display name
     * 
     * Returns: "Code - Abbreviation" or "Code - Name" if no abbrev
     *
     * @return string
     */
    public function getShortDisplayNameAttribute(): string
    {
        $display = $this->abbrev ?: substr($this->progname, 0, 30);
        return "{$this->progcode} - {$display}";
    }

    /**
     * Accessor: Get level label
     * 
     * Returns human-readable level name
     *
     * @return string
     */
    public function getLevelLabelAttribute(): string
    {
        return self::LEVEL_LABELS[$this->levelCode] ?? 'Unknown';
    }

    /**
     * Accessor: Get formatted duration
     * 
     * Returns: "X years" or "X-Y years" if max duration specified
     *
     * @return string
     */
    public function getDurationDisplayAttribute(): string
    {
        if (!$this->couselength) {
            return 'N/A';
        }
        
        $min = $this->couselength;
        $max = $this->maxduration;
        
        if ($max && $max != $min) {
            return "{$min}-{$max} years";
        }
        
        return "{$min} " . ($min == 1 ? 'year' : 'years');
    }

    /**
     * Accessor: Get credit requirement display
     *
     * @return string
     */
    public function getCreditDisplayAttribute(): string
    {
        if (!$this->mincredit) {
            return 'Not specified';
        }
        
        return number_format($this->mincredit, 0) . ' credits';
    }

    /**
     * Accessor: Check if this is an active programme
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->progcode !== self::PLACEHOLDER_CODE 
            && $this->progcode !== 'ALL';
    }

    /**
     * Accessor: Check if this is a placeholder programme
     *
     * @return bool
     */
    public function getIsPlaceholderAttribute(): bool
    {
        return $this->progcode === self::PLACEHOLDER_CODE 
            || $this->progcode === 'ALL';
    }

    /**
     * Accessor: Check if programme is undergraduate
     *
     * @return bool
     */
    public function getIsUndergraduateAttribute(): bool
    {
        return in_array($this->levelCode, [
            self::LEVEL_CERTIFICATE,
            self::LEVEL_DIPLOMA,
            self::LEVEL_DEGREE,
        ]);
    }

    /**
     * Accessor: Check if programme is postgraduate
     *
     * @return bool
     */
    public function getIsPostgraduateAttribute(): bool
    {
        return in_array($this->levelCode, [
            self::LEVEL_MASTERS,
            self::LEVEL_PHD,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Mutator: Normalize programme code to uppercase
     *
     * @param string $value
     * @return void
     */
    public function setProgcodeAttribute($value): void
    {
        $this->attributes['progcode'] = strtoupper(trim($value));
    }

    /**
     * Mutator: Normalize programme name to uppercase
     *
     * @param string $value
     * @return void
     */
    public function setPrognameAttribute($value): void
    {
        $this->attributes['progname'] = strtoupper(trim($value));
    }

    /**
     * Mutator: Normalize abbreviation to uppercase
     *
     * @param string $value
     * @return void
     */
    public function setAbbrevAttribute($value): void
    {
        $this->attributes['abbrev'] = strtoupper(trim($value));
    }

    /**
     * Mutator: Validate and set level code
     *
     * @param int $value
     * @return void
     */
    public function setLevelCodeAttribute($value): void
    {
        if (!in_array($value, self::VALID_LEVELS)) {
            throw new \InvalidArgumentException("Invalid level code: {$value}");
        }
        
        $this->attributes['levelCode'] = (int) $value;
    }

    /**
     * Mutator: Validate and set study system
     *
     * @param string $value
     * @return void
     */
    public function setStudySystemAttribute($value): void
    {
        $value = ucfirst(strtolower(trim($value)));
        
        if (!in_array($value, self::STUDY_SYSTEMS)) {
            throw new \InvalidArgumentException("Invalid study system: {$value}");
        }
        
        $this->attributes['study_system'] = $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Public Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get total number of students enrolled in this programme
     * Note: Uses progid column in acad_results table
     *
     * @return int
     */
    public function getStudentCount(): int
    {
        // Query directly from results table using progid column
        return \DB::table('acad_results')
            ->where('progid', $this->progcode)
            ->distinct('regno')
            ->count('regno');
    }

    /**
     * Get programme statistics
     * 
     * Returns array with:
     * - programme details
     * - student count
     * - faculty information
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'progcode' => $this->progcode,
            'progname' => $this->progname,
            'abbreviation' => $this->abbrev,
            'level' => $this->level_label,
            'duration' => $this->duration_display,
            'credits' => $this->credit_display,
            'study_system' => $this->study_system,
            'faculty_code' => $this->faculty_code,
            'faculty_name' => $this->faculty ? $this->faculty->faculty_name : 'N/A',
            'student_count' => $this->getStudentCount(),
            'is_active' => $this->is_active,
            'is_undergraduate' => $this->is_undergraduate,
            'is_postgraduate' => $this->is_postgraduate,
        ];
    }

    /**
     * Check if programme code is valid
     *
     * @param string $code Programme code to validate
     * @return bool
     */
    public static function isValidCode(string $code): bool
    {
        return self::where('progcode', strtoupper($code))->exists();
    }

    /**
     * Get all active programmes as key-value pairs for dropdowns
     * 
     * Returns: ['BED' => 'BED - BACHELOR OF EDUCATION', ...]
     *
     * @return array
     */
    public static function getDropdownOptions(): array
    {
        return self::active()
            ->orderBy('progname')
            ->get()
            ->pluck('short_display_name', 'progcode')
            ->toArray();
    }

    /**
     * Get programmes grouped by faculty
     *
     * @return array
     */
    public static function getGroupedByFaculty(): array
    {
        $programmes = self::active()
            ->with('faculty')
            ->orderBy('progname')
            ->get();
        
        return $programmes->groupBy('faculty_code')->map(function ($items, $key) {
            $faculty = $items->first()->faculty;
            return [
                'faculty_code' => $key,
                'faculty_name' => $faculty ? $faculty->abbrev : 'Unknown',
                'programmes' => $items->pluck('short_display_name', 'progcode')->toArray(),
            ];
        })->toArray();
    }

    /**
     * Export programme data to array format
     * 
     * Useful for exporting programme information
     *
     * @return array
     */
    public function toExportArray(): array
    {
        return [
            'Programme Code' => $this->progcode,
            'Programme Name' => $this->progname,
            'Abbreviation' => $this->abbrev,
            'Level' => $this->level_label,
            'Faculty' => $this->faculty ? $this->faculty->abbrev : 'N/A',
            'Duration' => $this->duration_display,
            'Credits' => $this->credit_display,
            'Study System' => $this->study_system,
            'Students' => $this->getStudentCount(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get all active programmes ordered by name
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveProgrammes()
    {
        return self::active()
            ->orderBy('progname')
            ->get();
    }

    /**
     * Get programmes by level
     *
     * @param int $level Level code
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getProgrammesByLevel(int $level)
    {
        return self::byLevel($level)
            ->active()
            ->orderBy('progname')
            ->get();
    }

    /**
     * Search programmes by term
     *
     * @param string $term Search term
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function searchProgrammes(string $term)
    {
        return self::search($term)
            ->active()
            ->orderBy('progname')
            ->get();
    }

    /**
     * Get summary data for dashboard/reports
     *
     * @return array
     */
    public static function getSummaryData(): array
    {
        $total = self::active()->count();
        $undergraduate = self::active()->undergraduate()->count();
        $postgraduate = self::active()->postgraduate()->count();
        $semester = self::active()->bySemester()->count();
        $session = self::active()->bySession()->count();
        
        $byFaculty = self::active()
            ->select('faculty_code', \DB::raw('COUNT(*) as count'))
            ->groupBy('faculty_code')
            ->pluck('count', 'faculty_code')
            ->toArray();
        
        return [
            'total_programmes' => $total,
            'undergraduate' => $undergraduate,
            'postgraduate' => $postgraduate,
            'semester_based' => $semester,
            'session_based' => $session,
            'by_faculty' => $byFaculty,
        ];
    }

    /**
     * Get programmes with student counts
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getProgrammesWithStudentCounts()
    {
        return self::active()
            ->get()
            ->map(function ($programme) {
                $programme->student_count = $programme->getStudentCount();
                return $programme;
            })
            ->sortByDesc('student_count');
    }
}
