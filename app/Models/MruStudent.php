<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * MruStudent Model
 * 
 * Represents student information in the MRU academic system.
 * Maps to 'acad_student' table containing comprehensive student data including
 * personal information, academic details, and enrollment information.
 * 
 * Purpose:
 * - Store and manage student personal information
 * - Track student academic programme enrollment
 * - Link students to their results and course registrations
 * - Manage student entry and session details
 * 
 * Database Structure:
 * - Primary Key: ID (auto-incrementing integer)
 * - Unique Key: regno (registration number) - e.g., "MRUUPS/U/02/07/DPE/10"
 * - Total Records: 30,916 students
 * - Foreign Keys: progid → acad_programme
 * - Related: acad_results (regno), acad_course_registration (regno)
 * 
 * @property int $ID Primary key (auto-increment)
 * @property string $entryno Entry number
 * @property string $regno Registration number (unique) - e.g., "25/U/BEICT/0097/K/DAY"
 * @property string $firstname First name
 * @property string $othername Other names
 * @property string $dob Date of birth
 * @property string $gender Gender (MALE, FEMALE)
 * @property string $nationality Nationality
 * @property string $religion Religion
 * @property string $entrymethod Entry method (A LEVEL, DIPLOMA, etc.)
 * @property string $progid Programme code (FK to acad_programme)
 * @property string $studPhone Student phone number
 * @property string $email Email address
 * @property int $entryyear Entry year
 * @property string $studsesion Study session (DAY, WEEKEND, EVENING, etc.)
 * @property string $home_dist Home district
 * @property string $intake Intake period
 * @property int $gradSystemID Grading system ID
 * @property int $duration Programme duration
 * @property string $photofile Photo filename
 * @property string $specialisation Specialisation/major
 * @property string $signfile Signature filename
 * @property int $studCampus Campus ID
 * @property string $StudentHall Student hall/residence
 * @property int $billingID Billing ID
 * 
 * @method static Builder search(string $term) Search students by regno, name, or email
 * @method static Builder forProgramme(string $progId) Get students for specific programme
 * @method static Builder byGender(string $gender) Get students by gender
 * @method static Builder bySession(string $session) Get students by study session
 * @method static Builder byEntryYear(int $year) Get students by entry year
 * @method static Builder byEntryMethod(string $method) Get students by entry method
 * @method static Builder dayStudents() Get only day session students
 * @method static Builder weekendStudents() Get only weekend students
 * @method static Builder active() Get active students with valid programme
 * 
 * @package App\Models
 * @author MRU Development Team
 * @version 1.0.0
 */
class MruStudent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_student';

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
        'entryno',
        'regno',
        'firstname',
        'othername',
        'dob',
        'gender',
        'nationality',
        'religion',
        'entrymethod',
        'progid',
        'studPhone',
        'email',
        'entryyear',
        'studsesion',
        'home_dist',
        'intake',
        'gradSystemID',
        'duration',
        'photofile',
        'specialisation',
        'signfile',
        'studCampus',
        'StudentHall',
        'billingID',
        'is_processed',
        'is_processed_successful',
        'processing_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'regno' => 'string',
        'entryno' => 'string',
        'firstname' => 'string',
        'othername' => 'string',
        'dob' => 'date',
        'gender' => 'string',
        'nationality' => 'string',
        'religion' => 'string',
        'entrymethod' => 'string',
        'progid' => 'string',
        'studPhone' => 'string',
        'email' => 'string',
        'entryyear' => 'integer',
        'studsesion' => 'string',
        'home_dist' => 'string',
        'intake' => 'string',
        'gradSystemID' => 'integer',
        'duration' => 'integer',
        'photofile' => 'string',
        'specialisation' => 'string',
        'signfile' => 'string',
        'studCampus' => 'integer',
        'StudentHall' => 'string',
        'billingID' => 'integer',
    ];

    /**
     * Gender constants
     */
    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';

    /**
     * Array of all valid genders
     */
    const GENDERS = [
        self::GENDER_MALE,
        self::GENDER_FEMALE,
    ];

    /**
     * Study session constants
     */
    const SESSION_DAY = 'DAY';
    const SESSION_WEEKEND = 'WEEKEND';
    const SESSION_EVENING = 'EVENING';
    const SESSION_INSERVICE = 'INSERVICE';
    const SESSION_FULL_TIME = 'Full Time';

    /**
     * Array of valid study sessions
     */
    const STUDY_SESSIONS = [
        self::SESSION_DAY,
        self::SESSION_WEEKEND,
        self::SESSION_EVENING,
        self::SESSION_INSERVICE,
        self::SESSION_FULL_TIME,
    ];

    /**
     * Entry method constants
     */
    const ENTRY_A_LEVEL = 'A LEVEL';
    const ENTRY_DIRECT = 'DIRECT';
    const ENTRY_CERTIFICATE = 'CERTIFICATE';
    const ENTRY_ORDINARY_DIPLOMA = 'ORDINARY DIPLOMA';
    const ENTRY_HIGHER_DIPLOMA = 'HIGHER DIPLOMA';
    const ENTRY_DIPLOMA = 'DIPLOMA';
    const ENTRY_BACHELORS = 'BACHELORS DEGREE';
    const ENTRY_MATURE_AGE = 'MATURE AGE';
    const ENTRY_ACCESS = 'ACCESS';
    const ENTRY_O_LEVEL = 'O LEVEL';

    /**
     * Array of valid entry methods
     */
    const ENTRY_METHODS = [
        self::ENTRY_A_LEVEL,
        self::ENTRY_DIRECT,
        self::ENTRY_CERTIFICATE,
        self::ENTRY_ORDINARY_DIPLOMA,
        self::ENTRY_HIGHER_DIPLOMA,
        self::ENTRY_DIPLOMA,
        self::ENTRY_BACHELORS,
        self::ENTRY_MATURE_AGE,
        self::ENTRY_ACCESS,
        self::ENTRY_O_LEVEL,
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the programme this student is enrolled in
     *
     * @return BelongsTo
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(MruProgramme::class, 'progid', 'progcode');
    }

    /**
     * Get all results for this student
     *
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(MruResult::class, 'regno', 'regno');
    }

    /**
     * Get all course registrations for this student
     *
     * @return HasMany
     */
    public function courseRegistrations(): HasMany
    {
        return $this->hasMany(MruCourseRegistration::class, 'regno', 'regno');
    }

    /**
     * Get all coursework marks for this student
     *
     * @return HasMany
     */
    public function courseworkMarks(): HasMany
    {
        return $this->hasMany(MruCourseworkMark::class, 'reg_no', 'regno');
    }

    /**
     * Get all practical exam marks for this student
     *
     * @return HasMany
     */
    public function practicalExamMarks(): HasMany
    {
        return $this->hasMany(MruPracticalExamMark::class, 'reg_no', 'regno');
    }

    /**
     * Get the specialisation/teaching subject(s) for this student
     * 
     * For Education students, this returns the teaching subject combination
     * (e.g., "Luganda & History", "Mathematics & Physics")
     *
     * @return BelongsTo
     */
    public function specialisationDetails(): BelongsTo
    {
        return $this->belongsTo(MruSpecialisation::class, 'specialisation', 'spec_id');
    }

    /**
     * Get the user account associated with this student (one-to-one)
     * Relationship is through email field
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'email', 'email');
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope query to search students
     *
     * @param Builder $query
     * @param string $term Search term
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('regno', 'like', "%{$term}%")
              ->orWhere('firstname', 'like', "%{$term}%")
              ->orWhere('othername', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('studPhone', 'like', "%{$term}%");
        });
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
        return $query->where('progid', $progId);
    }

    /**
     * Scope query by gender
     *
     * @param Builder $query
     * @param string $gender Gender
     * @return Builder
     */
    public function scopeByGender(Builder $query, string $gender): Builder
    {
        return $query->where('gender', $gender);
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
        return $query->where('studsesion', $session);
    }

    /**
     * Scope query by entry year
     *
     * @param Builder $query
     * @param int $year Entry year
     * @return Builder
     */
    public function scopeByEntryYear(Builder $query, int $year): Builder
    {
        return $query->where('entryyear', $year);
    }

    /**
     * Scope query by entry method
     *
     * @param Builder $query
     * @param string $method Entry method
     * @return Builder
     */
    public function scopeByEntryMethod(Builder $query, string $method): Builder
    {
        return $query->where('entrymethod', $method);
    }

    /**
     * Scope query to only day students
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDayStudents(Builder $query): Builder
    {
        return $query->where('studsesion', self::SESSION_DAY);
    }

    /**
     * Scope query to only weekend students
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWeekendStudents(Builder $query): Builder
    {
        return $query->where('studsesion', self::SESSION_WEEKEND);
    }

    /**
     * Scope query to active students with valid programme
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('progid')
                     ->where('progid', '!=', '')
                     ->where('progid', '!=', '-');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get full name
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->firstname . ' ' . $this->othername);
    }

    /**
     * Get formatted gender label
     *
     * @return string
     */
    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            self::GENDER_MALE => '<span class="label label-primary">Male</span>',
            self::GENDER_FEMALE => '<span class="label label-danger">Female</span>',
            default => '<span class="label label-default">' . $this->gender . '</span>',
        };
    }

    /**
     * Get formatted session label
     *
     * @return string
     */
    public function getSessionLabelAttribute(): string
    {
        return match($this->studsesion) {
            self::SESSION_DAY => '<span class="label label-primary">Day</span>',
            self::SESSION_WEEKEND => '<span class="label label-info">Weekend</span>',
            self::SESSION_EVENING => '<span class="label label-warning">Evening</span>',
            self::SESSION_INSERVICE => '<span class="label label-success">In-Service</span>',
            self::SESSION_FULL_TIME => '<span class="label label-primary">Full Time</span>',
            default => '<span class="label label-default">' . $this->studsesion . '</span>',
        };
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
     * Get age from date of birth
     *
     * @return int|null
     */
    public function getAgeAttribute(): ?int
    {
        return $this->dob ? now()->diffInYears($this->dob) : null;
    }

    /**
     * Get formatted entry year display
     *
     * @return string
     */
    public function getEntryYearDisplayAttribute(): string
    {
        return $this->entryyear ? (string)$this->entryyear : 'N/A';
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
     * Normalize first name
     *
     * @param string|null $value
     * @return void
     */
    public function setFirstnameAttribute(?string $value): void
    {
        $this->attributes['firstname'] = $value ? strtoupper(trim($value)) : $value;
    }

    /**
     * Normalize other name
     *
     * @param string|null $value
     * @return void
     */
    public function setOthernameAttribute(?string $value): void
    {
        $this->attributes['othername'] = $value ? strtoupper(trim($value)) : $value;
    }

    /**
     * Normalize email to lowercase
     *
     * @param string|null $value
     * @return void
     */
    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : $value;
    }

    /**
     * Normalize programme ID to uppercase
     *
     * @param string|null $value
     * @return void
     */
    public function setProgidAttribute(?string $value): void
    {
        $this->attributes['progid'] = $value ? strtoupper(trim($value)) : $value;
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if student is male
     *
     * @return bool
     */
    public function isMale(): bool
    {
        return $this->gender === self::GENDER_MALE;
    }

    /**
     * Check if student is female
     *
     * @return bool
     */
    public function isFemale(): bool
    {
        return $this->gender === self::GENDER_FEMALE;
    }

    /**
     * Check if student is in day session
     *
     * @return bool
     */
    public function isDayStudent(): bool
    {
        return $this->studsesion === self::SESSION_DAY;
    }

    /**
     * Check if student is in weekend session
     *
     * @return bool
     */
    public function isWeekendStudent(): bool
    {
        return $this->studsesion === self::SESSION_WEEKEND;
    }

    /**
     * Get student's result count
     *
     * @return int
     */
    public function getResultCount(): int
    {
        return $this->results()->count();
    }

    /**
     * Get student's course registration count
     *
     * @return int
     */
    public function getRegistrationCount(): int
    {
        return $this->courseRegistrations()->count();
    }

    /**
     * Get student's average GPA
     *
     * @return float
     */
    public function getAverageGPA(): float
    {
        return $this->results()->avg('gpa') ?? 0.0;
    }

    /**
     * Get formatted display string
     *
     * @return string
     */
    public function getDisplayString(): string
    {
        return sprintf(
            '%s - %s (%s)',
            $this->regno,
            $this->full_name,
            $this->progid
        );
    }

    /**
     * Check if student has a user account
     *
     * @return bool
     */
    public function hasUserAccount(): bool
    {
        return $this->user !== null;
    }

    /**
     * Get years since entry
     *
     * @return int|null
     */
    public function getYearsSinceEntry(): ?int
    {
        return $this->entryyear ? (date('Y') - $this->entryyear) : null;
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
        $maleCount = self::where('gender', self::GENDER_MALE)->count();
        $femaleCount = self::where('gender', self::GENDER_FEMALE)->count();
        $dayCount = self::where('studsesion', self::SESSION_DAY)->count();
        $weekendCount = self::where('studsesion', self::SESSION_WEEKEND)->count();

        return [
            'total' => $total,
            'male' => $maleCount,
            'female' => $femaleCount,
            'day' => $dayCount,
            'weekend' => $weekendCount,
            'male_percent' => $total > 0 ? round(($maleCount / $total) * 100, 2) : 0,
            'female_percent' => $total > 0 ? round(($femaleCount / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get unique entry years
     *
     * @return array
     */
    public static function getEntryYears(): array
    {
        return self::select('entryyear')
            ->distinct()
            ->whereNotNull('entryyear')
            ->where('entryyear', '>', 0)
            ->orderBy('entryyear', 'desc')
            ->pluck('entryyear')
            ->toArray();
    }

    /**
     * Get students count by programme
     *
     * @return array
     */
    public static function getCountByProgramme(): array
    {
        return self::select('progid', DB::raw('COUNT(*) as count'))
            ->whereNotNull('progid')
            ->where('progid', '!=', '')
            ->where('progid', '!=', '-')
            ->groupBy('progid')
            ->orderBy('count', 'desc')
            ->pluck('count', 'progid')
            ->toArray();
    }

    /**
     * Get students count by gender
     *
     * @return array
     */
    public static function getCountByGender(): array
    {
        return self::select('gender', DB::raw('COUNT(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();
    }

    /**
     * Get students count by study session
     *
     * @return array
     */
    public static function getCountBySession(): array
    {
        return self::select('studsesion', DB::raw('COUNT(*) as count'))
            ->whereNotNull('studsesion')
            ->where('studsesion', '!=', '')
            ->groupBy('studsesion')
            ->orderBy('count', 'desc')
            ->pluck('count', 'studsesion')
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC CALCULATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate cumulative GPA for the student
     *
     * @return float
     */
    public function getCumulativeGpaAttribute(): float
    {
        $results = $this->results()
            ->whereNotNull('gpa')
            ->where('gpa', '>', 0)
            ->get();

        if ($results->isEmpty()) {
            return 0.0;
        }

        $totalGPA = $results->sum('gpa');
        return round($totalGPA / $results->count(), 2);
    }

    /**
     * Get total credits earned by the student
     *
     * @return int
     */
    public function getTotalCreditsEarnedAttribute(): int
    {
        return $this->results()
            ->where('grade', '!=', 'F')
            ->whereNotNull('grade')
            ->sum('CreditUnits') ?? 0;
    }

    /**
     * Get expected graduation year
     *
     * @return int|null
     */
    public function getExpectedGraduationYearAttribute(): ?int
    {
        if (!$this->entryyear || !$this->duration) {
            return null;
        }
        return $this->entryyear + $this->duration;
    }

    /**
     * Get current year of study
     *
     * @return int
     */
    public function getCurrentYearOfStudyAttribute(): int
    {
        if (!$this->entryyear) {
            return 1;
        }
        
        $currentYear = date('Y');
        $yearOfStudy = $currentYear - $this->entryyear + 1;
        
        // Cap at programme duration
        if ($this->duration && $yearOfStudy > $this->duration) {
            return $this->duration;
        }
        
        return max(1, $yearOfStudy);
    }

    /**
     * Get academic standing
     *
     * @return string
     */
    public function getAcademicStandingAttribute(): string
    {
        $gpa = $this->cumulative_gpa;
        
        if ($gpa == 0) {
            return 'Pending';
        } elseif ($gpa >= 4.5) {
            return 'Dean\'s List';
        } elseif ($gpa >= 3.0) {
            return 'Good Standing';
        } elseif ($gpa >= 2.0) {
            return 'Probation';
        } else {
            return 'Academic Warning';
        }
    }

    /**
     * Get completion percentage
     *
     * @return int
     */
    public function getCompletionPercentageAttribute(): int
    {
        if (!$this->duration) {
            return 0;
        }
        
        $totalCUs = $this->duration * 30; // Assuming 30 CUs per year
        $earnedCUs = $this->total_credits_earned;
        
        return min(100, round(($earnedCUs / $totalCUs) * 100));
    }

    /**
     * Get semester GPA summary
     *
     * @return array
     */
    public function getSemesterGpaSummary(): array
    {
        return $this->results()
            ->select(
                'acad',
                'semester',
                DB::raw('COUNT(*) as courses_taken'),
                DB::raw('SUM(CreditUnits) as credits_earned'),
                DB::raw('AVG(gpa) as semester_gpa')
            )
            ->whereNotNull('gpa')
            ->where('gpa', '>', 0)
            ->groupBy('acad', 'semester')
            ->orderBy('acad', 'desc')
            ->orderBy('semester', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get retakes and supplementary exams
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRetakesAndSupplementary()
    {
        return $this->results()
            ->where('grade', 'F')
            ->orWhere('grade', 'R')
            ->get();
    }
}
