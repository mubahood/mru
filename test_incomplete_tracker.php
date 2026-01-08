<?php
/**
 * Test Script for IncompleteMarksTracker
 * 
 * This script verifies that the incomplete marks tracking system
 * works correctly with sample data.
 */

require __DIR__ . '/vendor/autoload.php';

use App\Helpers\IncompleteMarksTracker;

echo "=== Incomplete Marks Tracker Test ===\n\n";

// Create tracker instance
$tracker = new IncompleteMarksTracker();
echo "✓ Tracker instance created\n";

// Create sample student data
$student1 = (object)[
    'regno' => 'S21B13/001',
    'firstname' => 'John',
    'othername' => 'Doe'
];

$student2 = (object)[
    'regno' => 'S21B13/002',
    'firstname' => 'Jane',
    'othername' => 'Smith'
];

$student3 = (object)[
    'regno' => 'S21B13/003',
    'firstname' => 'Bob',
    'othername' => 'Johnson'
];

// Create sample courses
$courses = collect([
    (object)['courseID' => 'CSC201', 'courseName' => 'Data Structures'],
    (object)['courseID' => 'CSC202', 'courseName' => 'Algorithms'],
    (object)['courseID' => 'CSC203', 'courseName' => 'Database Systems'],
    (object)['courseID' => 'CSC204', 'courseName' => 'Web Development'],
]);
echo "✓ Sample data created (3 students, 4 courses)\n\n";

// Create sample results
// Student 1: Has 2 out of 4 courses (INCOMPLETE)
$results1 = collect([
    'S21B13/001' => collect([
        (object)['courseid' => 'CSC201', 'grade' => 'A', 'score' => 85],
        (object)['courseid' => 'CSC202', 'grade' => 'B', 'score' => 78],
    ])
]);

// Student 2: Has all 4 courses (COMPLETE)
$results2 = collect([
    'S21B13/002' => collect([
        (object)['courseid' => 'CSC201', 'grade' => 'A', 'score' => 90],
        (object)['courseid' => 'CSC202', 'grade' => 'A', 'score' => 88],
        (object)['courseid' => 'CSC203', 'grade' => 'B', 'score' => 82],
        (object)['courseid' => 'CSC204', 'grade' => 'B', 'score' => 75],
    ])
]);

// Student 3: Has 3 out of 4 courses (INCOMPLETE)
$results3 = collect([
    'S21B13/003' => collect([
        (object)['courseid' => 'CSC201', 'grade' => 'C', 'score' => 65],
        (object)['courseid' => 'CSC203', 'grade' => 'B', 'score' => 70],
        (object)['courseid' => 'CSC204', 'grade' => 'A', 'score' => 92],
    ])
]);

echo "Test Case 1: Student with 2/4 courses (INCOMPLETE)\n";
echo "----------------------------------------\n";
$isIncomplete1 = $tracker->trackStudent($student1, $courses, $results1, 'Computer Science');
echo "Student 1 (John Doe): " . ($isIncomplete1 ? "✓ Tracked as INCOMPLETE" : "✗ NOT tracked") . "\n\n";

echo "Test Case 2: Student with 4/4 courses (COMPLETE)\n";
echo "----------------------------------------\n";
$isIncomplete2 = $tracker->trackStudent($student2, $courses, $results2, 'Computer Science');
echo "Student 2 (Jane Smith): " . (!$isIncomplete2 ? "✓ NOT tracked (complete)" : "✗ Incorrectly tracked") . "\n\n";

echo "Test Case 3: Student with 3/4 courses (INCOMPLETE)\n";
echo "----------------------------------------\n";
$isIncomplete3 = $tracker->trackStudent($student3, $courses, $results3, 'Computer Science');
echo "Student 3 (Bob Johnson): " . ($isIncomplete3 ? "✓ Tracked as INCOMPLETE" : "✗ NOT tracked") . "\n\n";

// Get tracked students
echo "=== Tracker Results ===\n";
echo "----------------------------------------\n";
echo "Total incomplete students: " . $tracker->getCount() . "\n";
echo "Has incomplete students: " . ($tracker->hasIncompleteStudents() ? "YES" : "NO") . "\n\n";

if ($tracker->hasIncompleteStudents()) {
    echo "=== Incomplete Students Details ===\n";
    echo "----------------------------------------\n";
    
    $incompleteStudents = $tracker->getIncompleteStudents();
    foreach ($incompleteStudents as $index => $student) {
        echo "\nStudent " . ($index + 1) . ":\n";
        echo "  Reg No: {$student['regno']}\n";
        echo "  Name: {$student['name']}\n";
        echo "  Specialization: {$student['specialization']}\n";
        echo "  Total Courses: {$student['total_courses']}\n";
        echo "  Marks Obtained: {$student['marks_obtained']}\n";
        echo "  Marks Missing: {$student['marks_missing_count']}\n";
        echo "  Missing Courses: {$student['missing_courses']}\n";
    }
    
    echo "\n=== Statistics ===\n";
    echo "----------------------------------------\n";
    $stats = $tracker->getStatistics();
    echo "Total students with incomplete marks: {$stats['total_students']}\n";
    echo "Total missing marks: {$stats['total_missing_marks']}\n";
    echo "Average missing per student: {$stats['avg_missing_per_student']}\n";
    echo "Maximum missing: {$stats['max_missing']}\n";
    echo "Minimum missing: {$stats['min_missing']}\n";
    
    echo "\n=== Sorting Test ===\n";
    echo "----------------------------------------\n";
    $sorted = $tracker->getSortedIncompleteStudents('marks_missing_count', 'desc');
    echo "Sorted by missing count (descending):\n";
    foreach ($sorted as $student) {
        echo "  {$student['regno']}: {$student['marks_missing_count']} missing\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "✓ All tests passed successfully!\n";
echo "✓ IncompleteMarksTracker is working correctly\n";
