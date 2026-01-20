<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\RemoteSyncService;
use Illuminate\Support\Facades\DB;

echo "=== REMOTE SYNC TEST ===" . PHP_EOL . PHP_EOL;

// Create service
$service = new RemoteSyncService();

// Test connection
echo "1. Testing remote connection..." . PHP_EOL;
$test = $service->testRemoteConnection();
echo "   Result: " . ($test['success'] ? 'SUCCESS ✓' : 'FAILED ✗') . PHP_EOL;
if (!$test['success']) {
    echo "   Error: " . $test['message'] . PHP_EOL;
    exit(1);
}
echo "   Database: " . $test['database'] . PHP_EOL;
echo PHP_EOL;

// Check current local results count
echo "2. Checking current local results..." . PHP_EOL;
$localCount = DB::table('acad_results')->count();
$latestLocal = DB::table('acad_results')
    ->where('acad', '>=', '2023/2024')
    ->orderBy('ID', 'DESC')
    ->limit(3)
    ->get(['regno', 'courseid', 'acad', 'semester', 'grade', 'ID']);
echo "   Total local results: " . number_format($localCount) . PHP_EOL;
echo "   Latest 3 results (2023+):" . PHP_EOL;
foreach ($latestLocal as $result) {
    echo "   - ID {$result->ID}: {$result->regno} | {$result->courseid} | {$result->acad} | Sem {$result->semester} | Grade {$result->grade}" . PHP_EOL;
}
echo PHP_EOL;

// Check remote results
echo "3. Checking remote server results..." . PHP_EOL;
$remoteCount = DB::connection('remote_mysql')->table('acad_results')->count();
$latestRemote = DB::connection('remote_mysql')->table('acad_results')
    ->where('acad', '>=', '2023/2024')
    ->orderBy('ID', 'DESC')
    ->limit(3)
    ->get(['regno', 'courseid', 'acad', 'semester', 'grade', 'ID']);
echo "   Total remote results: " . number_format($remoteCount) . PHP_EOL;
echo "   Latest 3 results (2023+):" . PHP_EOL;
foreach ($latestRemote as $result) {
    echo "   - ID {$result->ID}: {$result->regno} | {$result->courseid} | {$result->acad} | Sem {$result->semester} | Grade {$result->grade}" . PHP_EOL;
}
echo PHP_EOL;

// Start sync
echo "4. Starting sync operation..." . PHP_EOL;
echo "   Config: 50 records per batch, min year 2023/2024" . PHP_EOL;
$config = [
    'range_limit' => 50,
    'min_academic_year' => '2023/2024'
];

try {
    $sync = $service->startSync('acad_results', $config);
    
    echo PHP_EOL;
    echo "=== SYNC COMPLETED ===" . PHP_EOL;
    echo "Sync ID: " . $sync->id . PHP_EOL;
    echo "Status: " . $sync->status . PHP_EOL;
    echo "Total Records on Remote: " . number_format($sync->total_records ?? 0) . PHP_EOL;
    echo "Records Synced: " . number_format($sync->number_of_records_synced) . PHP_EOL;
    echo "  - Inserted: " . number_format($sync->records_inserted) . PHP_EOL;
    echo "  - Updated: " . number_format($sync->records_updated) . PHP_EOL;
    echo "  - Skipped: " . number_format($sync->records_skipped) . PHP_EOL;
    echo "  - Failed: " . number_format($sync->records_failed) . PHP_EOL;
    echo "Message: " . $sync->message . PHP_EOL;
    echo PHP_EOL;
    
    // Verify results after sync
    echo "5. Verifying results after sync..." . PHP_EOL;
    $newLocalCount = DB::table('acad_results')->count();
    $difference = $newLocalCount - $localCount;
    echo "   New total local results: " . number_format($newLocalCount) . PHP_EOL;
    echo "   Difference: " . ($difference > 0 ? '+' : '') . number_format($difference) . PHP_EOL;
    
    if ($difference > 0) {
        echo PHP_EOL;
        echo "   ✓ SUCCESS! " . $difference . " new results synced from remote to local!" . PHP_EOL;
        
        // Show some newly synced results
        $newResults = DB::table('acad_results')
            ->where('acad', '>=', '2023/2024')
            ->orderBy('ID', 'DESC')
            ->limit(5)
            ->get(['regno', 'courseid', 'acad', 'semester', 'grade', 'score', 'ID']);
        
        echo PHP_EOL;
        echo "   Latest 5 results after sync:" . PHP_EOL;
        foreach ($newResults as $result) {
            echo "   - ID {$result->ID}: {$result->regno} | {$result->courseid} | {$result->acad} | Sem {$result->semester} | Grade {$result->grade} (Score: {$result->score})" . PHP_EOL;
        }
    } else {
        echo PHP_EOL;
        echo "   ℹ No new results added (all remote results already exist locally)" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo PHP_EOL;
    echo "✗ SYNC FAILED!" . PHP_EOL;
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}

echo PHP_EOL;
echo "=== TEST COMPLETED ===" . PHP_EOL;
