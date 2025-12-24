<?php

/**
 * Script to add Courses menu item to Laravel Admin
 * Run this script once to add the menu item
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Adding Courses menu item to Laravel Admin...\n";
echo str_repeat('=', 60) . "\n\n";

try {
    // Find MRU parent menu
    $mruMenu = DB::table('admin_menu')->where('title', 'MRU')->first();
    
    if (!$mruMenu) {
        echo "✗ ERROR: MRU parent menu not found!\n";
        exit(1);
    }
    
    echo "✓ Found MRU parent menu (ID: {$mruMenu->id})\n\n";
    
    // Check if Courses menu already exists
    $existing = DB::table('admin_menu')
        ->where('parent_id', $mruMenu->id)
        ->where('title', 'Courses')
        ->first();
    
    if ($existing) {
        echo "✓ Courses menu already exists (ID: {$existing->id})\n";
        echo "  URI: {$existing->uri}\n";
        echo "  Order: {$existing->order}\n";
    } else {
        // Get the highest order number in MRU submenu
        $maxOrder = DB::table('admin_menu')
            ->where('parent_id', $mruMenu->id)
            ->max('order');
        
        $newOrder = $maxOrder + 1;
        
        // Insert new menu item
        $menuId = DB::table('admin_menu')->insertGetId([
            'parent_id' => $mruMenu->id,
            'order' => $newOrder,
            'title' => 'Courses',
            'icon' => 'fa-book',
            'uri' => 'mru-courses',
            'permission' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "✓ Created Courses menu item successfully!\n";
        echo "  ID: {$menuId}\n";
        echo "  URI: mru-courses\n";
        echo "  Order: {$newOrder}\n";
        echo "  Icon: fa-book\n";
    }
    
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "Current MRU Menu Structure:\n";
    echo str_repeat('=', 60) . "\n\n";
    
    $submenus = DB::table('admin_menu')
        ->where('parent_id', $mruMenu->id)
        ->orderBy('order')
        ->get();
    
    foreach ($submenus as $menu) {
        echo sprintf(
            "  [%d] %-15s => %-20s (ID: %d, Icon: %s)\n",
            $menu->order,
            $menu->title,
            $menu->uri,
            $menu->id,
            $menu->icon ?: 'none'
        );
    }
    
    echo "\n✓ Menu setup completed successfully!\n";
    echo "\nYou can now access the Courses page at:\n";
    echo "  /admin/mru-courses\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
