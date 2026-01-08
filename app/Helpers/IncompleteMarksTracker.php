<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

/**
 * IncompleteMarksTracker
 * 
 * A reusable helper class for tracking students with incomplete marks.
 * This helper identifies students who have submitted some results but are missing
 * marks for certain courses, making it easy to follow up with students.
 * 
 * Features:
 * - Identifies students with partial marks (has some but not all course results)
 * - Skips students with zero marks (completely absent)
 * - Tracks missing course details for each student
 * - Provides detailed information for reporting and follow-up
 * 
 * Usage:
 * ```php
 * $tracker = new IncompleteMarksTracker();
 * 
 * foreach ($students as $student) {
 *     $tracker->trackStudent($student, $courses, $results, $specializationName);
 * }
 * 
 * $incompleteStudents = $tracker->getIncompleteStudents();
 * ```
 * 
 * @package App\Helpers
 * @version 1.0.0
 * @author MRU Development Team
 */
class IncompleteMarksTracker
{
    /**
     * Array to store incomplete students data
     * 
     * @var array
     */
    protected $incompleteStudents = [];

    /**
     * Track a student and determine if they have incomplete marks
     * 
     * This method checks if a student has submitted results for all required courses.
     * If the student has marks for some courses but not all, they are recorded as incomplete.
     * 
     * @param object $student Student object with regno, firstname, othername
     * @param Collection $courses Collection of course objects for the specialization
     * @param Collection $results Collection of student results grouped by regno
     * @param string $specializationName Name of the specialization
     * @return bool True if student has incomplete marks, false otherwise
     */
    public function trackStudent($student, $courses, $results, $specializationName)
    {
        // Get results for this specific student
        $studentResults = $results->get($student->regno, collect());
        
        // Skip students with no marks at all
        if ($studentResults->isEmpty()) {
            return false;
        }
        
        // Count courses and identify missing ones
        $totalCourses = $courses->count();
        $coursesWithResults = 0;
        $missingCourses = [];
        
        foreach ($courses as $course) {
            // Check if result exists for this course
            $hasResult = $this->hasResultForCourse($studentResults, $course);
            
            if ($hasResult) {
                $coursesWithResults++;
            } else {
                $missingCourses[] = $this->getCourseIdentifier($course);
            }
        }
        
        // Student is incomplete if they have some but not all marks
        if ($coursesWithResults > 0 && $coursesWithResults < $totalCourses) {
            $this->recordIncompleteStudent(
                $student,
                $specializationName,
                $totalCourses,
                $coursesWithResults,
                $missingCourses
            );
            return true;
        }
        
        return false;
    }

    /**
     * Check if a result exists for a specific course
     * 
     * Handles both keyBy('courseid') and regular collections
     * 
     * @param Collection $studentResults Student's results
     * @param object $course Course object
     * @return bool
     */
    protected function hasResultForCourse($studentResults, $course)
    {
        $courseId = $this->getCourseIdentifier($course);
        
        // Check if results are keyed by courseid (from keyBy('courseid'))
        if ($studentResults->has($courseId)) {
            return true;
        }
        
        // Check if results have courseid field (regular collection)
        return $studentResults->firstWhere('courseid', $courseId) !== null;
    }

    /**
     * Get course identifier from course object
     * 
     * @param object $course Course object
     * @return string Course identifier
     */
    protected function getCourseIdentifier($course)
    {
        return $course->courseID ?? $course->courseid ?? '';
    }

    /**
     * Record an incomplete student
     * 
     * @param object $student Student object
     * @param string $specializationName Specialization name
     * @param int $totalCourses Total number of courses
     * @param int $coursesWithResults Number of courses with results
     * @param array $missingCourses Array of missing course IDs
     * @return void
     */
    protected function recordIncompleteStudent(
        $student,
        $specializationName,
        $totalCourses,
        $coursesWithResults,
        $missingCourses
    ) {
        $fullName = trim(($student->firstname ?? '') . ' ' . ($student->othername ?? ''));
        
        $this->incompleteStudents[] = [
            'regno' => $student->regno,
            'name' => $fullName ?: $student->regno,
            'specialization' => $specializationName,
            'total_courses' => $totalCourses,
            'marks_obtained' => $coursesWithResults,
            'marks_missing_count' => count($missingCourses),
            'missing_courses' => implode(', ', $missingCourses),
        ];
    }

    /**
     * Get all incomplete students
     * 
     * @return array Array of incomplete student records
     */
    public function getIncompleteStudents()
    {
        return $this->incompleteStudents;
    }

    /**
     * Get count of incomplete students
     * 
     * @return int
     */
    public function getCount()
    {
        return count($this->incompleteStudents);
    }

    /**
     * Check if there are any incomplete students
     * 
     * @return bool
     */
    public function hasIncompleteStudents()
    {
        return !empty($this->incompleteStudents);
    }

    /**
     * Clear all tracked incomplete students
     * 
     * Useful for resetting the tracker between different exports
     * 
     * @return void
     */
    public function clear()
    {
        $this->incompleteStudents = [];
    }

    /**
     * Sort incomplete students by various criteria
     * 
     * @param string $sortBy Field to sort by (regno, name, marks_missing_count)
     * @param string $direction Sort direction (asc, desc)
     * @return array Sorted incomplete students
     */
    public function getSortedIncompleteStudents($sortBy = 'regno', $direction = 'asc')
    {
        $students = $this->incompleteStudents;
        
        usort($students, function($a, $b) use ($sortBy, $direction) {
            $valueA = $a[$sortBy] ?? '';
            $valueB = $b[$sortBy] ?? '';
            
            $comparison = $valueA <=> $valueB;
            
            return $direction === 'desc' ? -$comparison : $comparison;
        });
        
        return $students;
    }

    /**
     * Filter incomplete students by specialization
     * 
     * @param string $specialization Specialization name to filter by
     * @return array Filtered incomplete students
     */
    public function getIncompleteStudentsBySpecialization($specialization)
    {
        return array_filter($this->incompleteStudents, function($student) use ($specialization) {
            return $student['specialization'] === $specialization;
        });
    }

    /**
     * Get statistics about incomplete marks
     * 
     * @return array Statistics array
     */
    public function getStatistics()
    {
        if (empty($this->incompleteStudents)) {
            return [
                'total_students' => 0,
                'total_missing_marks' => 0,
                'avg_missing_per_student' => 0,
                'max_missing' => 0,
                'min_missing' => 0,
            ];
        }
        
        $totalMissing = array_sum(array_column($this->incompleteStudents, 'marks_missing_count'));
        $missingCounts = array_column($this->incompleteStudents, 'marks_missing_count');
        
        return [
            'total_students' => count($this->incompleteStudents),
            'total_missing_marks' => $totalMissing,
            'avg_missing_per_student' => round($totalMissing / count($this->incompleteStudents), 2),
            'max_missing' => max($missingCounts),
            'min_missing' => min($missingCounts),
        ];
    }
}
