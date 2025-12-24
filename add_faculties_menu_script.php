<?php

// Quick script to add Faculties menu item to Laravel Admin
// Run: php add_faculties_menu_script.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Adding MRU Faculties Menu ===\n\n";
    
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
    
    // Check if Faculties menu already exists
    $existing = DB::table('admin_menu')
        ->where('parent_id', $mruParent->id)
        ->where('uri', 'mru-faculties')
        ->first();
    
    if ($existing) {
        echo "Faculties menu already exists (ID: {$existing->id})\n";
        exit(0);
    }
    
    // Get next order number
    $maxOrder = DB::table('admin_menu')
        ->where('parent_id', $mruParent->id)
        ->max('order');
    
    $nextOrder = ($maxOrder ?? 0) + 1;
    
    // Insert Faculties menu
    $insertId = DB::table('admin_menu')->insertGetId([
        'parent_id' => $mruParent->id,
        'order' => $nextOrder,
        'title' => 'Faculties',
        'icon' => 'fa-building',
        'uri' => 'mru-faculties',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "\n✓ Successfully added Faculties menu!\n";
    echo "  Menu ID: {$insertId}\n";
    echo "  Parent ID: {$mruParent->id} (mru)\n";
    echo "  Order: {$nextOrder}\n";
    echo "  Title: Faculties\n";
    echo "  Icon: fa-building\n";
    echo "  URI: mru-faculties\n";
    echo "  URL: /admin/mru-faculties\n";
    
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
