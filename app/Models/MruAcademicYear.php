<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * MruAcademicYear Model
 * 
 * Represents academic years in the MRU system.
 * Maps to 'acad_acadyears' table containing the master list of academic years
 * configured in the university system.
 * 
 * Purpose:
 * - Maintain master list of academic years
 * - Link to results, course registrations, and other academic data
 * - Provide academic year validation and selection options
 * 
 * Database Structure:
 * - Primary Key: ID (auto-incrementing)
 * - Unique Key: acadyear (e.g., "2023/2024")
 * - Total Records: 26 years (2004/2005 to 2029/2030)
 * 
 * @property int $ID Primary key
 * @property string $acadyear Academic year (e.g., "2023/2024")
 * 
 * @method static Builder current() Get current academic year
 * @method static Builder recent() Get recent academic years (last 5)
 * @method static Builder upcoming() Get upcoming academic years
 * @method static Builder past() Get past academic years
 * @method static Builder search(string $term) Search academic years
 * 
 * @package App\Models
 * @author MRU Development Team
 * @version 1.0.0
 */
class MruAcademicYear extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_acadyears';

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
        'acadyear',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ID' => 'integer',
        'acadyear' => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get all results for this academic year
     *
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(MruResult::class, 'acad', 'acadyear');
    }

    /**
     * Get all course registrations for this academic year
     *
     * @return HasMany
     */
    public function courseRegistrations(): HasMany
    {
        return $this->hasMany(MruCourseRegistration::class, 'acad_year', 'acadyear');
    }

    /**
     * Get all coursework settings for this academic year
     * Note: acad_coursework_settings uses 'acadyear' column
     *
     * @return HasMany
     */
    public function courseworkSettings(): HasMany
    {
        return $this->hasMany(MruCourseworkSetting::class, 'acadyear', 'acadyear');
    }

    /**
     * Convenience alias for courseRegistrations
     *
     * @return HasMany
     */
    public function registrations(): HasMany
    {
        return $this->courseRegistrations();
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope query to current academic year
     * Uses current date to determine year (Aug-July cycle)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrent(Builder $query): Builder
    {
        $currentYear = self::getCurrentAcademicYear();
        return $query->where('acadyear', $currentYear);
    }

    /**
     * Scope query to recent academic years (last 5)
     *
     * @param Builder $query
     * @param int $limit
     * @return Builder
     */
    public function scopeRecent(Builder $query, int $limit = 5): Builder
    {
        return $query->orderBy('acadyear', 'desc')->limit($limit);
    }

    /**
     * Scope query to upcoming academic years
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        $currentYear = self::getCurrentAcademicYear();
        return $query->where('acadyear', '>', $currentYear)
                     ->orderBy('acadyear', 'asc');
    }

    /**
     * Scope query to past academic years
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePast(Builder $query): Builder
    {
        $currentYear = self::getCurrentAcademicYear();
        return $query->where('acadyear', '<', $currentYear)
                     ->orderBy('acadyear', 'desc');
    }

    /**
     * Scope query to search academic years
     *
     * @param Builder $query
     * @param string $term
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('acadyear', 'like', "%{$term}%");
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted label
     *
     * @return string
     */
    public function getLabelAttribute(): string
    {
        return 'Academic Year ' . $this->acadyear;
    }

    /**
     * Get start year
     *
     * @return int
     */
    public function getStartYearAttribute(): int
    {
        return (int) substr($this->acadyear, 0, 4);
    }

    /**
     * Get end year
     *
     * @return int
     */
    public function getEndYearAttribute(): int
    {
        return (int) substr($this->acadyear, 5, 4);
    }

    /**
     * Check if this is the current academic year
     *
     * @return bool
     */
    public function getIsCurrentAttribute(): bool
    {
        return $this->acadyear === self::getCurrentAcademicYear();
    }

    /**
     * Check if this is a future academic year
     *
     * @return bool
     */
    public function getIsFutureAttribute(): bool
    {
        return $this->acadyear > self::getCurrentAcademicYear();
    }

    /**
     * Check if this is a past academic year
     *
     * @return bool
     */
    public function getIsPastAttribute(): bool
    {
        return $this->acadyear < self::getCurrentAcademicYear();
    }

    /**
     * Normalize academic year format
     *
     * @param string|null $value
     * @return void
     */
    public function setAcadyearAttribute(?string $value): void
    {
        $this->attributes['acadyear'] = $value ? trim($value) : $value;
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get total results count for this academic year
     *
     * @return int
     */
    public function getResultsCount(): int
    {
        return $this->results()->count();
    }

    /**
     * Get total registrations count for this academic year
     *
     * @return int
     */
    public function getRegistrationsCount(): int
    {
        return $this->courseRegistrations()->count();
    }

    /**
     * Get unique students count for this academic year
     *
     * @return int
     */
    public function getStudentsCount(): int
    {
        return $this->results()->distinct('regno')->count('regno');
    }

    /**
     * Check if academic year is active (has data)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getResultsCount() > 0 || $this->getRegistrationsCount() > 0;
    }

    /**
     * Get formatted display string
     *
     * @return string
     */
    public function getDisplayString(): string
    {
        return $this->acadyear . ($this->is_current ? ' (Current)' : '');
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get current academic year based on date
     * Academic year runs from August to July
     *
     * @return string
     */
    public static function getCurrentAcademicYear(): string
    {
        $month = (int) date('m');
        $year = (int) date('Y');
        
        // If we're in Aug-Dec, academic year is current/next
        // If we're in Jan-July, academic year is previous/current
        if ($month >= 8) {
            return $year . '/' . ($year + 1);
        } else {
            return ($year - 1) . '/' . $year;
        }
    }

    /**
     * Get all academic years as dropdown options
     *
     * @param bool $includeEmpty
     * @return array
     */
    public static function getDropdownOptions(bool $includeEmpty = false): array
    {
        $options = self::orderBy('acadyear', 'desc')
            ->pluck('acadyear', 'acadyear')
            ->toArray();

        if ($includeEmpty) {
            $options = ['' => 'Select Academic Year'] + $options;
        }

        return $options;
    }

    /**
     * Get summary statistics
     *
     * @return array
     */
    public static function getSummaryStatistics(): array
    {
        $total = self::count();
        $currentYear = self::getCurrentAcademicYear();
        
        $current = self::where('acadyear', $currentYear)->first();
        $withResults = self::whereHas('results')->count();
        $withRegistrations = self::whereHas('courseRegistrations')->count();

        return [
            'total' => $total,
            'current_year' => $currentYear,
            'has_current' => $current !== null,
            'with_results' => $withResults,
            'with_registrations' => $withRegistrations,
            'earliest' => self::orderBy('acadyear', 'asc')->value('acadyear'),
            'latest' => self::orderBy('acadyear', 'desc')->value('acadyear'),
        ];
    }

    /**
     * Get usage statistics by academic year
     *
     * @return array
     */
    public static function getUsageStatistics(): array
    {
        return self::orderBy('acadyear', 'desc')
            ->get()
            ->map(function ($year) {
                return [
                    'year' => $year->acadyear,
                    'results' => $year->getResultsCount(),
                    'registrations' => $year->getRegistrationsCount(),
                    'students' => $year->getStudentsCount(),
                    'is_active' => $year->isActive(),
                ];
            })
            ->toArray();
    }

    /**
     * Generate next academic year
     *
     * @return string
     */
    public static function generateNextYear(): string
    {
        $latest = self::orderBy('acadyear', 'desc')->value('acadyear');
        
        if (!$latest) {
            return self::getCurrentAcademicYear();
        }

        $year = (int) substr($latest, 0, 4);
        return ($year + 1) . '/' . ($year + 2);
    }

    /**
     * Validate academic year format
     *
     * @param string $year
     * @return bool
     */
    public static function isValidFormat(string $year): bool
    {
        return preg_match('/^\d{4}\/\d{4}$/', $year) === 1;
    }
}
