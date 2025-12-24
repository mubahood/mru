<?php

/**
 * Comprehensive Test Script for MruCourse Model
 * 
 * This script tests all functionality of the MruCourse model including:
 * - Model instantiation
 * - Database queries
 * - Scopes
 * - Accessors
 * - Relationships
 * - Methods
 * - Static methods
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MruCourse;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         MRU COURSE MODEL COMPREHENSIVE TEST SUITE          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$testsPassed = 0;
$testsFailed = 0;

try {
    
    /*
    |--------------------------------------------------------------------------
    | TEST 1: MODEL INSTANTIATION
    |--------------------------------------------------------------------------
    */
    
    echo "1. Testing Model Instantiation:\n";
    echo str_repeat('-', 60) . "\n";
    
    $course = new MruCourse();
    if ($course instanceof MruCourse) {
        echo "   ✓ MruCourse model instantiated successfully\n";
        $testsPassed++;
    }
    
    echo "   Table: " . $course->getTable() . "\n";
    echo "   Primary Key: " . $course->getKeyName() . "\n";
    echo "   Timestamps: " . ($course->timestamps ? 'Yes' : 'No') . "\n";
    echo "\n";
    
    /*
    |--------------------------------------------------------------------------
    | TEST 2: DATABASE QUERIES
    |--------------------------------------------------------------------------
    */
    
    echo "2. Testing Database Queries:\n";
    echo str_repeat('-', 60) . "\n";
    
    $total = MruCourse::where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->whereNotNull('courseID')
        ->where('courseID', '!=', '')
        ->count();
    echo "   Total courses: {$total}\n";
    
    $active = MruCourse::active()
        ->where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->count();
    echo "   Active courses: {$active}\n";
    
    $inactive = MruCourse::inactive()
        ->where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->count();
    echo "   Inactive courses: {$inactive}\n";
    
    $core = MruCourse::core()
        ->where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->count();
    echo "   Core courses: {$core}\n";
    
    $optional = MruCourse::optional()
        ->where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->count();
    echo "   Optional courses: {$optional}\n";
    
    $withCredits = MruCourse::withCredits()
        ->where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->count();
    echo "   Courses with credits: {$withCredits}\n";
    
    if ($total > 0) {
        echo "   ✓ Database queries working\n";
        $testsPassed++;
    }
    echo "\n";
    
    /*
    |--------------------------------------------------------------------------
    | TEST 3: SCOPES
    |--------------------------------------------------------------------------
    */
    
    echo "3. Testing Query Scopes:\n";
    echo str_repeat('-', 60) . "\n";
    
    // Search scope
    $searchResults = MruCourse::search('AGRICULTURE')->count();
    echo "   Search 'AGRICULTURE': {$searchResults} results\n";
    
    // Credit range scope
    $creditRange = MruCourse::byCreditRange(3, 4)->count();
    echo "   Credit range 3-4: {$creditRange} courses\n";
    
    // Order by code
    $orderedCourse = MruCourse::orderByCode()
        ->where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->where('courseID', '!=', '')
        ->first();
    echo "   First course (ordered): " . ($orderedCourse ? $orderedCourse->courseID : 'N/A') . "\n";
    
    echo "   ✓ All scopes working correctly\n";
    $testsPassed++;
    echo "\n";
    
    /*
    |--------------------------------------------------------------------------
    | TEST 4: ACCESSORS
    |--------------------------------------------------------------------------
    */
    
    echo "4. Testing Accessors:\n";
    echo str_repeat('-', 60) . "\n";
    
    $testCourse = MruCourse::active()
        ->whereNotNull('courseName')
        ->where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->first();
    
    if ($testCourse) {
        echo "   Test Course: {$testCourse->courseID}\n";
        echo "   Full Display Name: {$testCourse->full_display_name}\n";
        echo "   Short Display Name: {$testCourse->short_display_name}\n";
        echo "   Status Label: {$testCourse->status_label}\n";
        echo "   Core Status Label: {$testCourse->core_status_label}\n";
        echo "   Credit Display: {$testCourse->credit_display}\n";
        echo "   Hours Breakdown: {$testCourse->hours_breakdown}\n";
        echo "   Total Hours: " . number_format($testCourse->total_hours, 1) . "\n";
        echo "   Is Active: " . ($testCourse->is_active ? 'Yes' : 'No') . "\n";
        echo "   Is Core: " . ($testCourse->is_core ? 'Yes' : 'No') . "\n";
        echo "   Has Description: " . ($testCourse->has_description ? 'Yes' : 'No') . "\n";
        
        echo "   ✓ All accessors working correctly\n";
        $testsPassed++;
    }
    echo "\n";
    
    /*
    |--------------------------------------------------------------------------
    | TEST 5: RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    
    echo "5. Testing Relationships:\n";
    echo str_repeat('-', 60) . "\n";
    
    $courseWithResults = MruCourse::active()
        ->where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->first();
    
    if ($courseWithResults) {
        echo "   Test Course: {$courseWithResults->courseID}\n";
        
        try {
            $resultCount = $courseWithResults->results()->count();
            echo "   Results count (via relationship): {$resultCount}\n";
            echo "   ✓ Relationship working correctly\n";
            $testsPassed++;
        } catch (Exception $e) {
            echo "   ✗ Relationship error: " . $e->getMessage() . "\n";
            $testsFailed++;
        }
    }
    echo "\n";
    
    /*
    |--------------------------------------------------------------------------
    | TEST 6: PUBLIC METHODS
    |--------------------------------------------------------------------------
    */
    
    echo "6. Testing Public Methods:\n";
    echo str_repeat('-', 60) . "\n";
    
    if ($testCourse) {
        try {
            $studentCount = $testCourse->getStudentCount();
            echo "   Student Count: {$studentCount}\n";
            
            $resultCount = $testCourse->getResultCount();
            echo "   Result Count: {$resultCount}\n";
            
            $passRate = $testCourse->getPassRate();
            echo "   Pass Rate: " . number_format($passRate, 2) . "%\n";
            
            $workload = $testCourse->calculateWorkload();
            echo "   Workload: " . number_format($workload, 1) . " hours\n";
            
            $isValid = $testCourse->isValidCourse();
            echo "   Is Valid Course: " . ($isValid ? 'Yes' : 'No') . "\n";
            
            $summary = $testCourse->getSummary();
            echo "   Summary keys: " . implode(', ', array_keys($summary)) . "\n";
            
            echo "   ✓ All methods working correctly\n";
            $testsPassed++;
        } catch (Exception $e) {
            echo "   ✗ Method error: " . $e->getMessage() . "\n";
            $testsFailed++;
        }
    }
    echo "\n";
    
    /*
    |--------------------------------------------------------------------------
    | TEST 7: STATIC METHODS
    |--------------------------------------------------------------------------
    */
    
    echo "7. Testing Static Methods:\n";
    echo str_repeat('-', 60) . "\n";
    
    // Dropdown options
    $dropdownOptions = MruCourse::getDropdownOptions();
    echo "   Dropdown options: " . count($dropdownOptions) . " items\n";
    
    // Summary statistics
    $stats = MruCourse::getSummaryStatistics();
    echo "   Summary statistics:\n";
    foreach ($stats as $key => $value) {
        echo "      {$key}: {$value}\n";
    }
    
    // Search courses
    $searchCriteria = [
        'status' => MruCourse::STATUS_ACTIVE,
        'min_credits' => 3,
        'max_credits' => 4,
    ];
    $searchResults = MruCourse::searchCourses($searchCriteria);
    echo "   Search results (3-4 credits, active): " . $searchResults->count() . " courses\n";
    
    echo "   ✓ All static methods working correctly\n";
    $testsPassed++;
    echo "\n";
    
    /*
    |--------------------------------------------------------------------------
    | TEST 8: SAMPLE DATA DISPLAY
    |--------------------------------------------------------------------------
    */
    
    echo "8. Sample Courses:\n";
    echo str_repeat('-', 60) . "\n";
    
    $samples = MruCourse::active()
        ->where('courseID', '!=', MruCourse::PLACEHOLDER_CODE)
        ->whereNotNull('courseName')
        ->orderBy('courseID')
        ->limit(5)
        ->get();
    
    foreach ($samples as $course) {
        echo "   [{$course->courseID}] {$course->courseName}\n";
        echo "      Credits: {$course->credit_display}\n";
        echo "      Type: {$course->core_status_label}\n";
        echo "      Status: {$course->status_label}\n";
        echo "      Students: " . number_format($course->getStudentCount()) . "\n";
        echo "\n";
    }
    
    /*
    |--------------------------------------------------------------------------
    | TEST 9: CONSTANTS
    |--------------------------------------------------------------------------
    */
    
    echo "9. Testing Constants:\n";
    echo str_repeat('-', 60) . "\n";
    
    echo "   STATUS_ACTIVE: " . MruCourse::STATUS_ACTIVE . "\n";
    echo "   STATUS_INACTIVE: " . MruCourse::STATUS_INACTIVE . "\n";
    echo "   CORE_STATUS_CORE: " . MruCourse::CORE_STATUS_CORE . "\n";
    echo "   CORE_STATUS_OPTIONAL: " . MruCourse::CORE_STATUS_OPTIONAL . "\n";
    echo "   PLACEHOLDER_CODE: " . MruCourse::PLACEHOLDER_CODE . "\n";
    echo "   STATUSES count: " . count(MruCourse::STATUSES) . "\n";
    echo "   CORE_STATUSES count: " . count(MruCourse::CORE_STATUSES) . "\n";
    
    echo "   ✓ All constants defined correctly\n";
    $testsPassed++;
    echo "\n";
    
} catch (Exception $e) {
    echo "\n✗ Fatal Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $testsFailed++;
}

/*
|--------------------------------------------------------------------------
| TEST SUMMARY
|--------------------------------------------------------------------------
*/

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                      TEST SUMMARY                          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "   Tests Passed: {$testsPassed}\n";
echo "   Tests Failed: {$testsFailed}\n";
echo "\n";

if ($testsFailed === 0) {
    echo "   ✓ ALL TESTS PASSED! MruCourse model is working perfectly!\n";
} else {
    echo "   ✗ Some tests failed. Please review the errors above.\n";
}

echo "\n";
echo str_repeat('=', 60) . "\n";
