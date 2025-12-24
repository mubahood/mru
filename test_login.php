#!/usr/bin/env php
<?php

/**
 * Test script to verify login functionality with CSRF and CAPTCHA
 */

$baseUrl = 'http://localhost:8888/mru';

echo "=== MRU Login System Test ===\n\n";

// Step 1: Get login page to extract CSRF token and session cookie
echo "Step 1: Fetching login page...\n";

$ch = curl_init($baseUrl . '/auth/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => true,
]);

$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

// Extract CSRF token
preg_match('/<input type="hidden" name="_token" value="([^"]+)"/', $body, $csrfMatches);
if (!empty($csrfMatches[1])) {
    $csrfToken = $csrfMatches[1];
    echo "✓ CSRF token extracted: " . substr($csrfToken, 0, 20) . "...\n";
} else {
    // Try meta tag
    preg_match('/<meta name="csrf-token" content="([^"]+)"/', $body, $csrfMatches);
    if (!empty($csrfMatches[1])) {
        $csrfToken = $csrfMatches[1];
        echo "✓ CSRF token extracted from meta tag: " . substr($csrfToken, 0, 20) . "...\n";
    } else {
        die("✗ Could not extract CSRF token from login page\n");
    }
}

// Extract session cookie
preg_match('/Set-Cookie: ([^;]+)/', $header, $cookieMatches);
if (!empty($cookieMatches[1])) {
    $sessionCookie = $cookieMatches[1];
    echo "✓ Session cookie extracted: " . substr($sessionCookie, 0, 40) . "...\n";
} else {
    die("✗ Could not extract session cookie\n");
}

// Step 2: Check if captcha is required
echo "\nStep 2: Checking CAPTCHA requirement...\n";
if (strpos($body, 'captcha') !== false) {
    echo "✓ CAPTCHA is required\n";
    echo "  Note: For automated testing, you may want to temporarily disable CAPTCHA\n";
    echo "  or use a test mode that accepts a fixed value.\n\n";
    
    echo "Manual Testing Instructions:\n";
    echo "1. Open browser to: {$baseUrl}/auth/login\n";
    echo "2. Use credentials:\n";
    echo "   - Username: ggg (or hamm, hammx, mpiima)\n";
    echo "   - Password: 123\n";
    echo "   - Enter the CAPTCHA shown\n";
    echo "3. Click 'Sign In'\n\n";
} else {
    echo "✓ CAPTCHA not required\n";
}

// Step 3: Check authentication system configuration
echo "Step 3: Checking authentication configuration...\n";

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $config = config('admin.auth');
    echo "✓ Admin authentication provider: " . $config['providers']['aspnet']['driver'] . "\n";
    echo "✓ Admin authentication model: " . $config['providers']['aspnet']['model'] . "\n";
    
    // Test database connection
    $userCount = DB::table('my_aspnet_users')->count();
    echo "✓ Database connected: {$userCount} users found\n";
    
    // Check test users
    $testUsers = DB::table('my_aspnet_users')
        ->whereIn('name', ['ggg', 'hamm', 'hammx', 'mpiima'])
        ->select('name', 'email')
        ->get();
    
    echo "\nTest User Accounts:\n";
    foreach ($testUsers as $user) {
        echo "  - Username: {$user->name}, Email: {$user->email}, Password: 123\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nNext Steps:\n";
echo "1. Test login via web browser at: {$baseUrl}/auth/login\n";
echo "2. Use any of the test accounts listed above\n";
echo "3. Password for all test accounts: 123\n";
echo "4. After successful login, you should be redirected to the admin dashboard\n\n";
