<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;

/**
 * MruFaculty Model
 * 
 * Represents academic faculties within the MRU system.
 * Maps to acad_faculty table which stores faculty information including
 * faculty names, codes, dean details, and contact information.
 * 
 * Database Structure:
 * - Primary Key: faculty_code (non-incrementing string)
 * - 6 faculties in total
 * - Related to: programmes, users (through user_faculties junction table)
 * 
 * @property string $faculty_code Faculty code (PK) - e.g., "01", "02"
 * @property string $faculty_name Full faculty name
 * @property string $faculty_dean Name of the faculty dean
 * @property string $faculty_contacts Contact information (phone numbers)
 * @property string $abbrev Faculty abbreviation/short name
 * 
 * @method static Builder search(string $term) Search faculties by name, code, or abbreviation
 * @method static Builder withDean() Filter faculties that have a dean assigned
 * @method static Builder active() Get active faculties (excluding placeholder)
 * @method static Builder byCode(string $code) Get faculty by code
 * 
 * @package App\Models
 * @author MRU Development Team
 * @version 1.0.0
 */
class MruFaculty extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_faculty';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'faculty_code';

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
        'faculty_code',
        'faculty_name',
        'faculty_dean',
        'faculty_contacts',
        'abbrev',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'faculty_code' => 'string',
        'faculty_name' => 'string',
        'faculty_dean' => 'string',
        'faculty_contacts' => 'string',
        'abbrev' => 'string',
    ];

    /**
     * Faculty code constants for common faculties
     */
    const FSTEAD_CODE = '01'; // Faculty of Science, Technology, Engineering, Art and Design
    const FSSAH_CODE = '02';  // Faculty of Social Sciences, Arts and Humanities
    const FOE_CODE = '04';    // Faculty of Education
    const FBM_CODE = '05';    // Faculty of Business and Management
    const GC_CODE = '06';     // Graduate Centre
    const PLACEHOLDER_CODE = '00'; // Placeholder/Unassigned

    /**
     * Array of all valid faculty codes
     */
    const VALID_CODES = [
        self::FSTEAD_CODE,
        self::FSSAH_CODE,
        self::FOE_CODE,
        self::FBM_CODE,
        self::GC_CODE,
    ];

    /**
     * Faculty abbreviation constants
     */
    const FSTEAD = 'FSTEAD';
    const FSSAH = 'FSSAH';
    const FOE = 'FOE';
    const FBM = 'FBM';
    const GC = 'GC';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get all programmes belonging to this faculty.
     * 
     * Relationship: Faculty has many Programmes
     * Foreign Key: faculty_code in acad_programme table
     *
     * @return HasMany
     */
    public function programmes(): HasMany
    {
        return $this->hasMany(MruProgramme::class, 'faculty_code', 'faculty_code');
    }

    /**
     * Get all users associated with this faculty.
     * 
     * Relationship: Faculty belongs to many Users (through user_faculties junction table)
     * Junction Table: my_aspnet_user_faculties
     * Foreign Keys: fax_code -> faculty_code, user_name -> username
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'my_aspnet_user_faculties',
            'fax_code',      // Foreign key on junction table for this model
            'user_name',     // Foreign key on junction table for related model
            'faculty_code',  // Local key on this model
            'username'       // Local key on related model
        );
    }

    /**
     * Get all students in this faculty (through programmes).
     * 
     * Relationship: Faculty has many Students through Programmes
     * Via: programmes.students
     *
     * @return HasManyThrough
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            MruStudent::class,
            MruProgramme::class,
            'faculty_code',  // Foreign key on programmes table
            'progid',        // Foreign key on students table
            'faculty_code',  // Local key on faculties table
            'progcode'       // Local key on programmes table
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Search faculties by name, code, or abbreviation
     * 
     * Performs a LIKE search across faculty_name, faculty_code, and abbrev fields.
     *
     * @param Builder $query
     * @param string $term Search term
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        
        return $query->where(function ($q) use ($term) {
            $q->where('faculty_name', 'LIKE', "%{$term}%")
              ->orWhere('faculty_code', 'LIKE', "%{$term}%")
              ->orWhere('abbrev', 'LIKE', "%{$term}%")
              ->orWhere('faculty_dean', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Scope: Filter faculties that have a dean assigned
     * 
     * Excludes faculties with placeholder dean values ("-" or empty)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithDean(Builder $query): Builder
    {
        return $query->where('faculty_dean', '!=', '-')
                    ->where('faculty_dean', '!=', '')
                    ->whereNotNull('faculty_dean');
    }

    /**
     * Scope: Get active/valid faculties only
     * 
     * Excludes the placeholder faculty (code "00")
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('faculty_code', '!=', self::PLACEHOLDER_CODE);
    }

    /**
     * Scope: Get faculty by specific code
     *
     * @param Builder $query
     * @param string $code Faculty code
     * @return Builder
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('faculty_code', $code);
    }

    /**
     * Scope: Order by faculty name
     *
     * @param Builder $query
     * @param string $direction Sort direction (asc or desc)
     * @return Builder
     */
    public function scopeOrderByName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('faculty_name', $direction);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor: Get full display name
     * 
     * Returns formatted name: "Code - Name (Abbreviation)"
     * Example: "01 - FACULTY OF SCIENCE... (FSTEAD)"
     *
     * @return string
     */
    public function getFullDisplayNameAttribute(): string
    {
        return "{$this->faculty_code} - {$this->faculty_name} ({$this->abbrev})";
    }

    /**
     * Accessor: Get short display name
     * 
     * Returns: "Code - Abbreviation"
     * Example: "01 - FSTEAD"
     *
     * @return string
     */
    public function getShortDisplayNameAttribute(): string
    {
        return "{$this->faculty_code} - {$this->abbrev}";
    }

    /**
     * Accessor: Check if faculty has a dean assigned
     *
     * @return bool
     */
    public function getHasDeanAttribute(): bool
    {
        return !empty($this->faculty_dean) 
            && $this->faculty_dean !== '-' 
            && $this->faculty_dean !== 'N/A';
    }

    /**
     * Accessor: Get formatted dean name
     * 
     * Returns dean name or "Not Assigned" if empty
     *
     * @return string
     */
    public function getDeanDisplayAttribute(): string
    {
        if ($this->has_dean) {
            return ucwords(strtolower($this->faculty_dean));
        }
        
        return 'Not Assigned';
    }

    /**
     * Accessor: Get formatted contact information
     * 
     * Returns formatted contacts or "Not Available" if empty
     *
     * @return string
     */
    public function getContactsDisplayAttribute(): string
    {
        if (!empty($this->faculty_contacts) && $this->faculty_contacts !== '-') {
            return $this->faculty_contacts;
        }
        
        return 'Not Available';
    }

    /**
     * Accessor: Check if this is a valid/active faculty
     * 
     * Returns false for placeholder faculty (code "00")
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->faculty_code !== self::PLACEHOLDER_CODE;
    }

    /**
     * Accessor: Check if this is the placeholder faculty
     *
     * @return bool
     */
    public function getIsPlaceholderAttribute(): bool
    {
        return $this->faculty_code === self::PLACEHOLDER_CODE;
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Mutator: Normalize faculty name to uppercase
     *
     * @param string $value
     * @return void
     */
    public function setFacultyNameAttribute($value): void
    {
        $this->attributes['faculty_name'] = strtoupper(trim($value));
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
     * Mutator: Normalize faculty code
     *
     * @param string $value
     * @return void
     */
    public function setFacultyCodeAttribute($value): void
    {
        $this->attributes['faculty_code'] = str_pad(trim($value), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Mutator: Clean contact information
     *
     * @param string $value
     * @return void
     */
    public function setFacultyContactsAttribute($value): void
    {
        $cleaned = trim($value);
        $this->attributes['faculty_contacts'] = empty($cleaned) ? '-' : $cleaned;
    }

    /*
    |--------------------------------------------------------------------------
    | Public Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get total number of programmes in this faculty
     *
     * @return int
     */
    public function getProgrammeCount(): int
    {
        return \DB::table('acad_programme')
            ->where('faculty_code', $this->faculty_code)
            ->count();
    }

    /**
     * Get total number of users associated with this faculty
     *
     * @return int
     */
    public function getUserCount(): int
    {
        return $this->users()->count();
    }

    /**
     * Get all programmes with their student counts
     * 
     * Returns collection of programmes
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProgrammesWithStudentCounts()
    {
        return \DB::table('acad_programme')
            ->where('faculty_code', $this->faculty_code)
            ->get();
    }

    /**
     * Get faculty statistics
     * 
     * Returns array with:
     * - total_programmes: Total programmes in faculty
     * - total_users: Total users associated
     * - has_dean: Whether dean is assigned
     * - has_contacts: Whether contacts are available
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'faculty_code' => $this->faculty_code,
            'faculty_name' => $this->faculty_name,
            'abbreviation' => $this->abbrev,
            'total_programmes' => $this->getProgrammeCount(),
            'total_users' => $this->getUserCount(),
            'has_dean' => $this->has_dean,
            'dean_name' => $this->dean_display,
            'has_contacts' => $this->faculty_contacts !== '-',
            'contacts' => $this->contacts_display,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Check if faculty code is valid
     *
     * @param string $code Faculty code to validate
     * @return bool
     */
    public static function isValidCode(string $code): bool
    {
        return in_array($code, self::VALID_CODES);
    }

    /**
     * Get all active faculties as key-value pairs for dropdowns
     * 
     * Returns: ['01' => '01 - FSTEAD', '02' => '02 - FSSAH', ...]
     *
     * @return array
     */
    public static function getDropdownOptions(): array
    {
        return self::active()
            ->orderBy('faculty_code')
            ->get()
            ->pluck('short_display_name', 'faculty_code')
            ->toArray();
    }

    /**
     * Get faculty by abbreviation
     *
     * @param string $abbrev Faculty abbreviation (e.g., "FSTEAD")
     * @return self|null
     */
    public static function findByAbbreviation(string $abbrev): ?self
    {
        return self::where('abbrev', strtoupper($abbrev))->first();
    }

    /**
     * Get faculty statistics for all faculties
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllStatistics()
    {
        return self::active()
            ->orderBy('faculty_code')
            ->get()
            ->map(function ($faculty) {
                return $faculty->getStatistics();
            });
    }

    /**
     * Export faculty data to array format
     * 
     * Useful for exporting faculty information
     *
     * @return array
     */
    public function toExportArray(): array
    {
        return [
            'Faculty Code' => $this->faculty_code,
            'Faculty Name' => $this->faculty_name,
            'Abbreviation' => $this->abbrev,
            'Dean' => $this->dean_display,
            'Contacts' => $this->contacts_display,
            'Total Programmes' => $this->getProgrammeCount(),
            'Total Users' => $this->getUserCount(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get all active faculties ordered by code
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveFaculties()
    {
        return self::active()
            ->orderBy('faculty_code')
            ->get();
    }

    /**
     * Get faculties with dean information
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getFacultiesWithDeans()
    {
        return self::withDean()
            ->orderBy('faculty_code')
            ->get();
    }

    /**
     * Search faculties by term
     *
     * @param string $term Search term
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function searchFaculties(string $term)
    {
        return self::search($term)
            ->active()
            ->orderBy('faculty_code')
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
        $withDean = self::active()->withDean()->count();
        $totalProgrammes = \DB::table('acad_programme')->count();
        
        return [
            'total_faculties' => $total,
            'faculties_with_dean' => $withDean,
            'faculties_without_dean' => $total - $withDean,
            'total_programmes' => $totalProgrammes,
            'average_programmes_per_faculty' => $total > 0 ? round($totalProgrammes / $total, 2) : 0,
        ];
    }
}
