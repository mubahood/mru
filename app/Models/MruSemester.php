<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * MruSemester Model
 * 
 * Represents academic semesters/terms in the MRU system.
 * This model wraps the existing 'terms' table.
 * 
 * IMPORTANT: Academic Year Structure
 * ---------------------------------
 * This model links to AcademicYear (academic_years table), NOT MruAcademicYear (acad_acadyears table).
 * - AcademicYear: Main system table for academic years (used by terms, student enrollments, etc.)
 * - MruAcademicYear: Legacy academic years table (used only for historical results)
 * 
 * When an AcademicYear is created for a University enterprise, it automatically creates
 * 2 semesters (Semester 1 and Semester 2) via the AcademicYear::boot() method.
 * 
 * Semester Structure:
 * - Semester 1: First semester (typically Aug-Dec)
 * - Semester 2: Second semester (typically Jan-Jul)
 * - Only ONE semester can be active (is_active=1) at a time per enterprise
 * 
 * @property int $id Primary key
 * @property int $enterprise_id Enterprise ID
 * @property int $academic_year_id Links to academic_years.id (NOT acad_acadyears.ID)
 * @property string $name Semester number ("1", "2")
 * @property string $term_name Semester term name (same as name)
 * @property \Carbon\Carbon $starts Start date
 * @property \Carbon\Carbon $ends End date
 * @property string $details Additional details
 * @property int $is_active Whether this is the current active semester (1=Yes, 0=No)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @method static Builder current() Get current active semester
 * @method static Builder forAcademicYear(int $yearId) Filter by academic year
 * @method static Builder forEnterprise(int $enterpriseId) Filter by enterprise
 */
class MruSemester extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'terms';

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
        'enterprise_id',
        'academic_year_id',
        'name',
        'term_name',
        'starts',
        'ends',
        'details',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'enterprise_id' => 'integer',
        'academic_year_id' => 'integer',
        'is_active' => 'integer',
        'starts' => 'date',
        'ends' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_current',
        'name_text',
    ];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * Get the academic year that owns this semester.
     * 
     * IMPORTANT: This links to AcademicYear (academic_years table), not MruAcademicYear.
     * The academic_years table is the main system table used for:
     * - Terms/Semesters
     * - Student enrollments (student_has_semeters)
     * - Classes and university programmes
     * 
     * @return BelongsTo
     */
    public function academic_year(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'id');
    }

    /**
     * Get the enterprise that owns this semester.
     * 
     * @return BelongsTo
     */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id', 'id');
    }

    /**
     * Get all exams for this semester.
     * 
     * @return HasMany
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'term_id', 'id');
    }

    /**
     * Get all mark records for this semester.
     * 
     * @return HasMany
     */
    public function mark_records(): HasMany
    {
        return $this->hasMany(MarkRecord::class, 'term_id', 'id');
    }

    /**
     * Get all student semester enrollments.
     * 
     * @return HasMany
     */
    public function student_enrollments(): HasMany
    {
        return $this->hasMany(StudentHasSemeter::class, 'term_id', 'id');
    }

    /**
     * Scope: Get the current active semester
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_active', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Get active semesters
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Get inactive semesters
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', self::STATUS_INACTIVE);
    }

    /**
     * Scope: Filter by academic year
     *
     * @param Builder $query
     * @param int $yearId
     * @return Builder
     */
    public function scopeForAcademicYear(Builder $query, int $yearId): Builder
    {
        return $query->where('academic_year_id', $yearId);
    }

    /**
     * Scope: Filter by enterprise
     *
     * @param Builder $query
     * @param int $enterpriseId
     * @return Builder
     */
    public function scopeForEnterprise(Builder $query, int $enterpriseId): Builder
    {
        return $query->where('enterprise_id', $enterpriseId);
    }

    /**
     * Scope: Search by name or details
     *
     * @param Builder $query
     * @param string $term
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('term_name', 'LIKE', "%{$term}%")
              ->orWhere('details', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Accessor: Get is_current as Yes/No string
     *
     * @return string
     */
    public function getIsCurrentAttribute(): string
    {
        return $this->is_active == self::STATUS_ACTIVE ? 'Yes' : 'No';
    }

    /**
     * Accessor: Get formatted name with academic year
     *
     * @return string
     */
    public function getNameTextAttribute(): string
    {
        if ($this->academic_year) {
            return "Semester {$this->name} - {$this->academic_year->name}";
        }
        return "Semester {$this->name}";
    }

    /**
     * Accessor: Get formatted date range
     *
     * @return string
     */
    public function getDateRangeAttribute(): string
    {
        if ($this->starts && $this->ends) {
            return $this->starts->format('M d, Y') . ' - ' . $this->ends->format('M d, Y');
        }
        return 'N/A';
    }

    /**
     * Mutator: Ensure name is trimmed
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = trim($value);
        $this->attributes['term_name'] = trim($value);
    }

    /**
     * Check if this semester is currently active
     *
     * @return bool
     */
    public function isCurrent(): bool
    {
        return $this->is_active == self::STATUS_ACTIVE;
    }

    /**
     * Activate this semester (deactivates others in same enterprise)
     *
     * @return bool
     */
    public function activate(): bool
    {
        // Deactivate all other semesters in this enterprise
        DB::table('terms')
            ->where('enterprise_id', $this->enterprise_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => self::STATUS_INACTIVE]);

        $this->is_active = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Deactivate this semester
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->is_active = self::STATUS_INACTIVE;
        return $this->save();
    }

    /**
     * Get current active semester for an enterprise
     *
     * @param int $enterpriseId
     * @return MruSemester|null
     */
    public static function getCurrentSemester(int $enterpriseId): ?MruSemester
    {
        return self::where('enterprise_id', $enterpriseId)
            ->where('is_active', self::STATUS_ACTIVE)
            ->first();
    }

    /**
     * Get all semesters for an academic year
     *
     * @param int $academicYearId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSemestersForYear(int $academicYearId)
    {
        return self::where('academic_year_id', $academicYearId)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get semesters as dropdown array
     *
     * @param array $conditions
     * @return array
     */
    public static function getItemsToArray(array $conditions = []): array
    {
        $arr = [];
        $query = self::query();
        
        foreach ($conditions as $key => $value) {
            $query->where($key, $value);
        }
        
        foreach ($query->orderBy('id', 'desc')->get() as $semester) {
            $arr[$semester->id] = "Semester " . $semester->name_text;
        }
        
        return $arr;
    }
}
