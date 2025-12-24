<?php

/**
 * Add MRU Marks System Menu Items
 * Run: php add_marks_menu_items.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Adding MRU Marks System Menu Items ===" . PHP_EOL . PHP_EOL;

try {
    // Get MRU parent menu
    $mruMenu = DB::table('admin_menu')
        ->where('title', 'MRU')
        ->orWhere('title', 'mru')
        ->first();
    
    if (!$mruMenu) {
        echo "ERROR: MRU parent menu not found!" . PHP_EOL;
        exit(1);
    }
    
    echo "✓ Found MRU parent menu (ID: {$mruMenu->id})" . PHP_EOL . PHP_EOL;
    
    // Get next order number
    $maxOrder = DB::table('admin_menu')
        ->where('parent_id', $mruMenu->id)
        ->max('order') ?? 0;
    
    $nextOrder = $maxOrder + 1;
    
    // Menu items to add
    $menuItems = [
        ['title' => 'Exam Results (Faculty)', 'uri' => 'mru-exam-results-faculty', 'icon' => 'fa-file-text'],
        ['title' => 'Coursework Marks', 'uri' => 'mru-coursework-marks', 'icon' => 'fa-pencil-square'],
        ['title' => 'Practical Exam Marks', 'uri' => 'mru-practical-exam-marks', 'icon' => 'fa-flask'],
        ['title' => 'Exam Settings', 'uri' => 'mru-exam-settings', 'icon' => 'fa-cog'],
        ['title' => 'Coursework Settings', 'uri' => 'mru-coursework-settings', 'icon' => 'fa-wrench'],
    ];
    
    $inserted = 0;
    
    foreach ($menuItems as $item) {
        // Check if menu item already exists
        $exists = DB::table('admin_menu')
            ->where('parent_id', $mruMenu->id)
            ->where('uri', $item['uri'])
            ->exists();
        
        if (!$exists) {
            DB::table('admin_menu')->insert([
                'parent_id' => $mruMenu->id,
                'order' => $nextOrder,
                'title' => $item['title'],
                'icon' => $item['icon'],
                'uri' => $item['uri'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "✓ Added: {$item['title']} (order: {$nextOrder})" . PHP_EOL;
            $nextOrder++;
            $inserted++;
        } else {
            echo "- Already exists: {$item['title']}" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "✓ Successfully added {$inserted} new menu items!" . PHP_EOL . PHP_EOL;
    
    // Display updated menu structure
    echo str_repeat('=', 70) . PHP_EOL;
    echo "Updated MRU Menu Structure:" . PHP_EOL;
    echo str_repeat('=', 70) . PHP_EOL . PHP_EOL;
    
    $submenus = DB::table('admin_menu')
        ->where('parent_id', $mruMenu->id)
        ->orderBy('order')
        ->get();
    
    foreach ($submenus as $menu) {
        echo sprintf(
            "  [%2d] %-30s => %-30s (%s)" . PHP_EOL,
            $menu->order,
            $menu->title,
            $menu->uri,
            $menu->icon ?: 'no icon'
        );
    }
    
    echo PHP_EOL . str_repeat('=', 70) . PHP_EOL;
    echo "✓ All menu items are now accessible in the admin panel!" . PHP_EOL;
    echo PHP_EOL;
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
