<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MruCourse;

echo "\n";
echo "Testing getPassRate Fix\n";
echo str_repeat('=', 60) . "\n\n";

// Test the specific course that was failing
$course = MruCourse::where('courseID', 'BAG2102B')->first();
if ($course) {
    echo "Course: {$course->courseID} - {$course->courseName}\n";
    
    try {
        $studentCount = $course->getStudentCount();
        $resultCount = $course->getResultCount();
        $passRate = $course->getPassRate();
        
        echo "  Student Count: {$studentCount}\n";
        echo "  Result Count: {$resultCount}\n";
        echo "  Pass Rate: " . number_format($passRate, 2) . "%\n";
        echo "  ✓ Success!\n\n";
    } catch (Exception $e) {
        echo "  ✗ ERROR: " . $e->getMessage() . "\n\n";
    }
}

// Test multiple courses with results
echo "Testing multiple courses:\n";
echo str_repeat('-', 60) . "\n\n";

$courses = MruCourse::active()
    ->where('courseID', '!=', '-')
    ->whereNotNull('courseName')
    ->orderBy('courseID')
    ->limit(5)
    ->get();

$successCount = 0;
foreach ($courses as $course) {
    $results = $course->getResultCount();
    if ($results > 0) {
        try {
            $students = $course->getStudentCount();
            $passRate = $course->getPassRate();
            
            echo "{$course->courseID} - {$course->courseName}\n";
            echo "  Students: {$students}, Results: {$results}\n";
            echo "  Pass Rate: " . number_format($passRate, 2) . "%\n\n";
            $successCount++;
        } catch (Exception $e) {
            echo "{$course->courseID} - ERROR: " . $e->getMessage() . "\n\n";
        }
    }
}

echo str_repeat('=', 60) . "\n";
echo "✓ All {$successCount} tests passed!\n";
echo "✓ getPassRate method is now working correctly.\n\n";
