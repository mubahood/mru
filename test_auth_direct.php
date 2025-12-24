#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

echo "=== Direct Authentication Test ===\n\n";

// Test 1: Find user by username
echo "Test 1: Looking up user 'ggg'...\n";
$user = User::where('name', 'ggg')->first();

if ($user) {
    echo "✓ User found: {$user->name} (ID: {$user->id})\n";
    echo "  Email: {$user->email}\n";
    echo "  Has Laravel password: " . ($user->password_laravel ? "YES" : "NO") . "\n";
    
    if ($user->password_laravel) {
        echo "  Password hash: " . substr($user->password_laravel, 0, 30) . "...\n";
        
        // Test 2: Verify password
        echo "\nTest 2: Testing password '123'...\n";
        $verified = Hash::check('123', $user->password_laravel);
        echo "  Hash::check result: " . ($verified ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    }
    
    // Test 3: Get auth password
    echo "\nTest 3: Checking getAuthPassword() method...\n";
    $authPassword = $user->getAuthPassword();
    echo "  Method returns: " . ($authPassword ? substr($authPassword, 0, 30) . "..." : "NULL") . "\n";
    
    // Test 4: Check authentication identifier
    echo "\nTest 4: Checking authentication identifier...\n";
    echo "  Username attribute: " . $user->getUsernameAttribute() . "\n";
    
    // Test 5: Attempt authentication
    echo "\nTest 5: Attempting Auth::attempt()...\n";
    $credentials = [
        'name' => 'ggg',  // ASP.NET uses 'name' field, not 'username'
        'password' => '123'
    ];
    
    $result = Auth::guard('admin')->attempt($credentials);
    echo "  Auth::attempt result: " . ($result ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
} else {
    echo "✗ User not found!\n";
}

echo "\n=== Test Complete ===\n";
