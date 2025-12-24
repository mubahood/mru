<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing MruProgramme getStudentCount() fix:\n";
echo str_repeat('=', 50) . "\n\n";

try {
    // Get a few sample programmes
    $programmes = App\Models\MruProgramme::where('progcode', '!=', '-')
        ->where('progcode', '!=', 'ALL')
        ->take(5)
        ->get();
    
    echo "Testing student count for 5 programmes:\n\n";
    
    foreach ($programmes as $prog) {
        echo "Programme: {$prog->progcode} ({$prog->abbrev})\n";
        echo "  Name: {$prog->progname}\n";
        
        try {
            $count = $prog->getStudentCount();
            echo "  Student Count: {$count}\n";
            echo "  ✓ Success!\n\n";
        } catch (Exception $e) {
            echo "  ✗ ERROR: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo str_repeat('=', 50) . "\n";
    echo "✓ Test completed!\n";
    
} catch (Exception $e) {
    echo "✗ Fatal Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
