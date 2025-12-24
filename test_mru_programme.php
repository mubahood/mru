<?php

// Test MruProgramme Model
// Run: php test_mru_programme.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MruProgramme;
use App\Models\MruFaculty;

echo "=== TESTING MruProgramme MODEL ===\n\n";

// 1. Model Instantiation
echo "1. Model Instantiation:\n";
try {
    $programme = new MruProgramme();
    echo "   ✓ Model instantiates successfully\n";
    echo "   Table: " . $programme->getTable() . "\n";
    echo "   Primary Key: " . $programme->getKeyName() . "\n";
    echo "   Incrementing: " . ($programme->incrementing ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 2. Database Query
echo "\n2. Database Query Test:\n";
try {
    $count = MruProgramme::count();
    echo "   ✓ Total programmes: {$count}\n";
    
    $active = MruProgramme::active()->count();
    echo "   ✓ Active programmes: {$active}\n";
    
    $undergraduate = MruProgramme::undergraduate()->count();
    echo "   ✓ Undergraduate programmes: {$undergraduate}\n";
    
    $postgraduate = MruProgramme::postgraduate()->count();
    echo "   ✓ Postgraduate programmes: {$postgraduate}\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 3. Scopes
echo "\n3. Testing Scopes:\n";
try {
    $semester = MruProgramme::bySemester()->count();
    echo "   ✓ Semester-based programmes: {$semester}\n";
    
    $session = MruProgramme::bySession()->count();
    echo "   ✓ Session-based programmes: {$session}\n";
    
    $searched = MruProgramme::search('EDUCATION')->count();
    echo "   ✓ Search test (EDUCATION): {$searched}\n";
    
    $degree = MruProgramme::byLevel(3)->count();
    echo "   ✓ Degree programmes (level 3): {$degree}\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 4. Accessors
echo "\n4. Testing Accessors:\n";
try {
    $programme = MruProgramme::where('progcode', 'BED')->first();
    if ($programme) {
        echo "   ✓ Full Display Name: {$programme->full_display_name}\n";
        echo "   ✓ Short Display Name: {$programme->short_display_name}\n";
        echo "   ✓ Level Label: {$programme->level_label}\n";
        echo "   ✓ Duration Display: {$programme->duration_display}\n";
        echo "   ✓ Credit Display: {$programme->credit_display}\n";
        echo "   ✓ Is Active: " . ($programme->is_active ? 'Yes' : 'No') . "\n";
        echo "   ✓ Is Undergraduate: " . ($programme->is_undergraduate ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ⚠ Programme 'BED' not found, testing with first active programme\n";
        $programme = MruProgramme::active()->first();
        if ($programme) {
            echo "   ✓ Testing with: {$programme->progcode}\n";
            echo "   ✓ Full Display Name: {$programme->full_display_name}\n";
            echo "   ✓ Level Label: {$programme->level_label}\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 5. Relationships
echo "\n5. Testing Relationships:\n";
try {
    $programme = MruProgramme::active()->with('faculty')->first();
    if ($programme) {
        echo "   ✓ Programme: {$programme->progcode}\n";
        if ($programme->faculty) {
            echo "   ✓ Faculty relationship working: {$programme->faculty->abbrev}\n";
        } else {
            echo "   ⚠ No faculty assigned to this programme\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 6. Methods
echo "\n6. Testing Methods:\n";
try {
    $programme = MruProgramme::active()->first();
    if ($programme) {
        $studentCount = $programme->getStudentCount();
        echo "   ✓ Student count: {$studentCount}\n";
        
        $stats = $programme->getStatistics();
        echo "   ✓ Statistics retrieved: " . count($stats) . " items\n";
    }
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 7. Static Methods
echo "\n7. Testing Static Methods:\n";
try {
    $dropdown = MruProgramme::getDropdownOptions();
    echo "   ✓ Dropdown options: " . count($dropdown) . " items\n";
    
    $summary = MruProgramme::getSummaryData();
    echo "   ✓ Summary data retrieved: " . count($summary) . " items\n";
    echo "     - Total: {$summary['total_programmes']}\n";
    echo "     - Undergraduate: {$summary['undergraduate']}\n";
    echo "     - Postgraduate: {$summary['postgraduate']}\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 8. Display Sample Programmes
echo "\n8. Sample Programmes by Faculty:\n";
try {
    $faculties = MruFaculty::active()->orderBy('faculty_code')->get();
    foreach ($faculties as $faculty) {
        $progCount = MruProgramme::forFaculty($faculty->faculty_code)->count();
        echo sprintf("   [%s] %s: %d programmes\n", 
            $faculty->faculty_code,
            $faculty->abbrev,
            $progCount
        );
    }
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 9. Test Faculty->Programmes relationship
echo "\n9. Testing Faculty->Programmes Relationship:\n";
try {
    $faculty = MruFaculty::where('faculty_code', '01')->first();
    if ($faculty) {
        $programmes = $faculty->programmes;
        echo "   ✓ Faculty: {$faculty->abbrev}\n";
        echo "   ✓ Programmes count: " . $programmes->count() . "\n";
        if ($programmes->count() > 0) {
            echo "   ✓ First programme: {$programmes->first()->progcode} - {$programmes->first()->abbrev}\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

echo "\n✓ All model tests completed!\n";
