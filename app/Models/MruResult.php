<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * MruResult Model
 * 
 * Represents student academic results in the MRU system.
 * Maps to the 'acad_results' table containing student grades, scores, and GPA information.
 * 
 * @property int $ID Primary key
 * @property string $regno Student registration number
 * @property string $courseid Course identifier
 * @property int $semester Semester number (1 or 2)
 * @property string $acad Academic year (e.g., 2007/2008)
 * @property int $studyyear Study year (1, 2, 3, 4, etc.)
 * @property int $score Raw score achieved (0-100)
 * @property string $grade Letter grade (A, B+, B, C+, C, D, F)
 * @property float $gradept Grade point value
 * @property float $gpa Grade Point Average
 * @property string $result_comment Additional comments on result
 * @property float $CreditUnits Credit units for the course
 * @property string $progid Program identifier
 * 
 * @method static Builder forStudent(string $regno) Get results for a specific student
 * @method static Builder forAcademicYear(string $year) Get results for a specific academic year
 * @method static Builder forSemester(int $semester) Get results for a specific semester
 * @method static Builder passing() Get only passing results
 * @method static Builder failing() Get only failing results
 * @method static Builder forCourse(string $courseId) Get results for a specific course
 */
class MruResult extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_results';

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
        'courseid',
        'semester',
        'acad',
        'studyyear',
        'score',
        'grade',
        'gradept',
        'gpa',
        'result_comment',
        'CreditUnits',
        'progid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ID' => 'integer',
        'semester' => 'integer',
        'studyyear' => 'integer',
        'score' => 'integer',
        'gradept' => 'float',
        'gpa' => 'float',
        'CreditUnits' => 'float',
    ];

    /**
     * Grade constants based on MRU grading system
     */
    const GRADE_A = 'A';
    const GRADE_B_PLUS = 'B+';
    const GRADE_B = 'B';
    const GRADE_C_PLUS = 'C+';
    const GRADE_C = 'C';
    const GRADE_D = 'D';
    const GRADE_F = 'F';

    /**
     * Passing grades
     */
    const PASSING_GRADES = [
        self::GRADE_A,
        self::GRADE_B_PLUS,
        self::GRADE_B,
        self::GRADE_C_PLUS,
        self::GRADE_C,
        self::GRADE_D,
    ];

    /**
     * Failing grades
     */
    const FAILING_GRADES = [self::GRADE_F];

    /**
     * Semester constants
     */
    const SEMESTER_ONE = 1;
    const SEMESTER_TWO = 2;

    /**
     * Get the student who owns this result.
     * Relationship with the MruStudent model via regno
     *
     * @return BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(MruStudent::class, 'regno', 'regno');
    }

    /**
     * Get the course associated with this result.
     * Relationship with the MruCourse model via courseid
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(MruCourse::class, 'courseid', 'courseID');
    }

    /**
     * Get the academic year record for this result
     *
     * @return BelongsTo
     */
    public function year(): BelongsTo
    {
        return $this->belongsTo(MruAcademicYear::class, 'acad', 'acadyear');
    }

    /**
     * Get the program associated with this result.
     * Note: This assumes a Program model exists
     *
     * @return BelongsTo
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'progid', 'code');
    }

    /**
     * Scope: Filter results by student registration number
     *
     * @param Builder $query
     * @param string $regno
     * @return Builder
     */
    public function scopeForStudent(Builder $query, string $regno): Builder
    {
        return $query->where('regno', $regno);
    }

    /**
     * Scope: Filter results by academic year
     *
     * @param Builder $query
     * @param string $year Academic year (e.g., '2023/2024')
     * @return Builder
     */
    public function scopeForAcademicYear(Builder $query, string $year): Builder
    {
        return $query->where('acad', $year);
    }

    /**
     * Scope: Filter results by semester
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
     * Scope: Get only passing results
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePassing(Builder $query): Builder
    {
        return $query->whereIn('grade', self::PASSING_GRADES);
    }

    /**
     * Scope: Get only failing results
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFailing(Builder $query): Builder
    {
        return $query->whereIn('grade', self::FAILING_GRADES);
    }

    /**
     * Scope: Filter results by course ID
     *
     * @param Builder $query
     * @param string $courseId
     * @return Builder
     */
    public function scopeForCourse(Builder $query, string $courseId): Builder
    {
        return $query->where('courseid', $courseId);
    }

    /**
     * Scope: Filter results by study year
     *
     * @param Builder $query
     * @param int $year
     * @return Builder
     */
    public function scopeForStudyYear(Builder $query, int $year): Builder
    {
        return $query->where('studyyear', $year);
    }

    /**
     * Scope: Get results with high scores (A grades)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExcellent(Builder $query): Builder
    {
        return $query->where('grade', self::GRADE_A);
    }

    /**
     * Accessor: Get formatted academic year
     *
     * @return string
     */
    public function getAcademicYearAttribute(): string
    {
        return $this->acad ?? 'N/A';
    }

    /**
     * Accessor: Check if the result is passing
     *
     * @return bool
     */
    public function getIsPassingAttribute(): bool
    {
        return in_array($this->grade, self::PASSING_GRADES);
    }

    /**
     * Accessor: Check if the result is failing
     *
     * @return bool
     */
    public function getIsFailingAttribute(): bool
    {
        return in_array($this->grade, self::FAILING_GRADES);
    }

    /**
     * Accessor: Get the score percentage
     *
     * @return float
     */
    public function getScorePercentageAttribute(): float
    {
        return $this->score ?? 0;
    }

    /**
     * Accessor: Get formatted grade with points
     *
     * @return string
     */
    public function getGradeDisplayAttribute(): string
    {
        return sprintf('%s (%s pts)', $this->grade ?? 'N/A', $this->gradept ?? '0');
    }

    /**
     * Accessor: Get semester name
     *
     * @return string
     */
    public function getSemesterNameAttribute(): string
    {
        return $this->semester === self::SEMESTER_ONE ? 'Semester 1' : 'Semester 2';
    }

    /**
     * Mutator: Ensure semester is valid
     *
     * @param int $value
     * @return void
     */
    public function setSemesterAttribute($value): void
    {
        $this->attributes['semester'] = in_array($value, [1, 2]) ? $value : 1;
    }

    /**
     * Mutator: Normalize grade to uppercase
     *
     * @param string $value
     * @return void
     */
    public function setGradeAttribute($value): void
    {
        $this->attributes['grade'] = strtoupper($value);
    }

    /**
     * Mutator: Ensure score is within valid range (0-100)
     *
     * @param int $value
     * @return void
     */
    public function setScoreAttribute($value): void
    {
        $this->attributes['score'] = max(0, min(100, $value));
    }

    /**
     * Calculate and return the weighted grade points (grade point × credit units)
     *
     * @return float
     */
    public function getWeightedPoints(): float
    {
        return ($this->gradept ?? 0) * ($this->CreditUnits ?? 0);
    }

    /**
     * Get the grade status (Pass/Fail/Retake)
     *
     * @return string
     */
    public function getGradeStatus(): string
    {
        if ($this->is_passing) {
            return 'Pass';
        } elseif ($this->grade === self::GRADE_F) {
            return 'Fail';
        } else {
            return 'Retake';
        }
    }

    /**
     * Calculate GPA for a collection of results
     * Static method to calculate cumulative GPA
     *
     * @param \Illuminate\Support\Collection $results Collection of MruResult models
     * @return float
     */
    public static function calculateGPA($results): float
    {
        if ($results->isEmpty()) {
            return 0.0;
        }

        $totalWeightedPoints = $results->sum(function ($result) {
            return $result->getWeightedPoints();
        });

        $totalCreditUnits = $results->sum('CreditUnits');

        return $totalCreditUnits > 0 ? round($totalWeightedPoints / $totalCreditUnits, 2) : 0.0;
    }

    /**
     * Get semester GPA for a specific student, academic year, and semester
     *
     * @param string $regno
     * @param string $academicYear
     * @param int $semester
     * @return float
     */
    public static function getSemesterGPA(string $regno, string $academicYear, int $semester): float
    {
        $results = self::forStudent($regno)
            ->forAcademicYear($academicYear)
            ->forSemester($semester)
            ->get();

        return self::calculateGPA($results);
    }

    /**
     * Get cumulative GPA for a student across all semesters
     *
     * @param string $regno Student registration number (maps to username in my_aspnet_users)
     * @return float
     */
    public static function getCumulativeGPA(string $regno): float
    {
        $results = self::forStudent($regno)->get();
        return self::calculateGPA($results);
    }

    /**
     * Get transcript data for a student
     *
     * @param string $regno Student registration number (maps to username in my_aspnet_users)
     * @return array
     */
    public static function getTranscriptData(string $regno): array
    {
        $results = self::forStudent($regno)
            ->with(['course', 'student'])
            ->orderBy('acad')
            ->orderBy('semester')
            ->orderBy('studyyear')
            ->get();

        $groupedByYear = $results->groupBy('acad')->map(function ($yearResults) {
            return $yearResults->groupBy('semester');
        });

        return [
            'results' => $groupedByYear,
            'cumulative_gpa' => self::getCumulativeGPA($regno),
            'total_credit_units' => $results->sum('CreditUnits'),
            'total_courses' => $results->count(),
            'passed_courses' => $results->where('is_passing', true)->count(),
            'failed_courses' => $results->where('is_failing', true)->count(),
        ];
    }

    /**
     * Get grade distribution for a course
     *
     * @param string $courseId
     * @param string|null $academicYear
     * @return array
     */
    public static function getGradeDistribution(string $courseId, ?string $academicYear = null): array
    {
        $query = self::forCourse($courseId);

        if ($academicYear) {
            $query->forAcademicYear($academicYear);
        }

        return $query->select('grade', DB::raw('COUNT(*) as count'))
            ->groupBy('grade')
            ->orderByRaw("FIELD(grade, 'A', 'B+', 'B', 'C+', 'C', 'D', 'F')")
            ->pluck('count', 'grade')
            ->toArray();
    }

    /**
     * Get top performers for a course
     *
     * @param string $courseId
     * @param string $academicYear
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTopPerformers(string $courseId, string $academicYear, int $limit = 10)
    {
        return self::forCourse($courseId)
            ->forAcademicYear($academicYear)
            ->with('student')
            ->orderByDesc('score')
            ->limit($limit)
            ->get();
    }

    /**
     * Convert the model to an array for export
     *
     * @return array
     */
    public function toExportArray(): array
    {
        return [
            'Registration Number' => $this->regno,
            'Course ID' => $this->courseid,
            'Academic Year' => $this->acad,
            'Semester' => $this->semester_name,
            'Study Year' => $this->studyyear,
            'Score' => $this->score,
            'Grade' => $this->grade,
            'Grade Points' => $this->gradept,
            'GPA' => $this->gpa,
            'Credit Units' => $this->CreditUnits,
            'Program' => $this->progid,
            'Status' => $this->getGradeStatus(),
        ];
    }
}
