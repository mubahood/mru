<?php

// Add MRU Programmes menu item
// Run: php add_programmes_menu_script.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Adding MRU Programmes Menu ===\n\n";
    
    // Get MRU parent menu
    $mruParent = DB::table('admin_menu')
        ->where('title', 'mru')
        ->where('parent_id', 0)
        ->first();
    
    if (!$mruParent) {
        echo "ERROR: MRU parent menu not found!\n";
        exit(1);
    }
    
    echo "Found MRU parent menu (ID: {$mruParent->id})\n";
    
    // Check if Programmes menu already exists
    $existing = DB::table('admin_menu')
        ->where('parent_id', $mruParent->id)
        ->where('uri', 'mru-programmes')
        ->first();
    
    if ($existing) {
        echo "Programmes menu already exists (ID: {$existing->id})\n";
        exit(0);
    }
    
    // Get next order number
    $maxOrder = DB::table('admin_menu')
        ->where('parent_id', $mruParent->id)
        ->max('order');
    
    $nextOrder = ($maxOrder ?? 0) + 1;
    
    // Insert Programmes menu
    $insertId = DB::table('admin_menu')->insertGetId([
        'parent_id' => $mruParent->id,
        'order' => $nextOrder,
        'title' => 'Programmes',
        'icon' => 'fa-graduation-cap',
        'uri' => 'mru-programmes',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "\n✓ Successfully added Programmes menu!\n";
    echo "  Menu ID: {$insertId}\n";
    echo "  Parent ID: {$mruParent->id} (mru)\n";
    echo "  Order: {$nextOrder}\n";
    echo "  Title: Programmes\n";
    echo "  Icon: fa-graduation-cap\n";
    echo "  URI: mru-programmes\n";
    echo "  URL: /admin/mru-programmes\n";
    
    // Show current MRU submenu
    echo "\n=== Current MRU Submenu ===\n";
    $submenus = DB::table('admin_menu')
        ->where('parent_id', $mruParent->id)
        ->orderBy('order')
        ->get();
    
    foreach ($submenus as $sub) {
        echo sprintf("  [%d] %s -> %s\n", $sub->order, $sub->title, $sub->uri);
    }
    
    echo "\n✓ Done!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
