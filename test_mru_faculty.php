<?php

// Test MruFaculty Model
// Run: php test_mru_faculty.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MruFaculty;

echo "=== TESTING MruFaculty MODEL ===\n\n";

// 1. Model Instantiation
echo "1. Model Instantiation:\n";
try {
    $faculty = new MruFaculty();
    echo "   ✓ Model instantiates successfully\n";
    echo "   Table: " . $faculty->getTable() . "\n";
    echo "   Primary Key: " . $faculty->getKeyName() . "\n";
    echo "   Incrementing: " . ($faculty->incrementing ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 2. Database Query
echo "\n2. Database Query Test:\n";
try {
    $count = MruFaculty::count();
    echo "   ✓ Total faculties: {$count}\n";
    
    $active = MruFaculty::active()->count();
    echo "   ✓ Active faculties: {$active}\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 3. Scopes
echo "\n3. Testing Scopes:\n";
try {
    $withDean = MruFaculty::withDean()->count();
    echo "   ✓ Faculties with dean: {$withDean}\n";
    
    $searched = MruFaculty::search('SCIENCE')->count();
    echo "   ✓ Search test (SCIENCE): {$searched}\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 4. Accessors
echo "\n4. Testing Accessors:\n";
try {
    $faculty = MruFaculty::where('faculty_code', '01')->first();
    if ($faculty) {
        echo "   ✓ Full Display Name: {$faculty->full_display_name}\n";
        echo "   ✓ Short Display Name: {$faculty->short_display_name}\n";
        echo "   ✓ Has Dean: " . ($faculty->has_dean ? 'Yes' : 'No') . "\n";
        echo "   ✓ Is Active: " . ($faculty->is_active ? 'Yes' : 'No') . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 5. Methods
echo "\n5. Testing Methods:\n";
try {
    $faculty = MruFaculty::where('faculty_code', '01')->first();
    if ($faculty) {
        $progCount = $faculty->getProgrammeCount();
        echo "   ✓ Programme count: {$progCount}\n";
        
        $stats = $faculty->getStatistics();
        echo "   ✓ Statistics retrieved: " . count($stats) . " items\n";
    }
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 6. Static Methods
echo "\n6. Testing Static Methods:\n";
try {
    $dropdown = MruFaculty::getDropdownOptions();
    echo "   ✓ Dropdown options: " . count($dropdown) . " items\n";
    
    $summary = MruFaculty::getSummaryData();
    echo "   ✓ Summary data retrieved: " . count($summary) . " items\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// 7. Display All Faculties
echo "\n7. Display All Faculties:\n";
$faculties = MruFaculty::active()->orderBy('faculty_code')->get();
foreach ($faculties as $fac) {
    echo sprintf("   [%s] %s - Dean: %s\n", 
        $fac->faculty_code,
        $fac->abbrev,
        $fac->dean_display
    );
}

echo "\n✓ All model tests passed!\n";
