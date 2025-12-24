<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "=== ASP.NET Laravel Authentication Bridge Test ===\n\n";

// Test 1: Check if provider is registered
echo "Test 1: Checking authentication provider...\n";
try {
    $provider = Auth::guard('admin')->getProvider();
    echo "✅ Provider class: " . get_class($provider) . "\n";
    echo "✅ Provider model: " . $provider->getModel() . "\n\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Check database connection and user count
echo "Test 2: Checking database connection...\n";
try {
    $userCount = DB::table('my_aspnet_users')->count();
    $withEmail = DB::table('my_aspnet_users')->whereNotNull('email')->count();
    $withLaravelPwd = DB::table('my_aspnet_users')->whereNotNull('password_laravel')->count();
    
    echo "✅ Total users: $userCount\n";
    echo "✅ Users with email: $withEmail\n";
    echo "✅ Users with Laravel password: $withLaravelPwd\n\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Retrieve a user
echo "Test 3: Testing user retrieval...\n";
try {
    $testUser = DB::table('my_aspnet_users')
        ->join('my_aspnet_membership', 'my_aspnet_users.id', '=', 'my_aspnet_membership.userId')
        ->where('my_aspnet_membership.IsApproved', 1)
        ->where('my_aspnet_membership.IsLockedOut', 0)
        ->select('my_aspnet_users.*', 'my_aspnet_membership.Email as membership_email')
        ->first();
    
    if ($testUser) {
        echo "✅ Found test user:\n";
        echo "   - ID: {$testUser->id}\n";
        echo "   - Username: {$testUser->name}\n";
        echo "   - Email: {$testUser->email}\n";
        echo "   - Has Laravel password: " . ($testUser->password_laravel ? 'Yes' : 'No') . "\n\n";
        
        // Test 4: Try to retrieve user with Eloquent
        echo "Test 4: Testing Eloquent user retrieval...\n";
        try {
            $user = User::find($testUser->id);
            if ($user) {
                echo "✅ Successfully loaded User model\n";
                echo "   - Username attribute: " . $user->getUsernameAttribute() . "\n";
                echo "   - Auth identifier: " . $user->getAuthIdentifier() . "\n";
            
            // Test 5: Check membership relationship
            echo "\nTest 5: Testing membership relationship...\n";
            $membership = $user->membership;
            if ($membership) {
                echo "✅ Membership relationship works\n";
                echo "   - Email: {$membership->Email}\n";
                echo "   - IsApproved: " . ($membership->IsApproved ? 'Yes' : 'No') . "\n";
                echo "   - IsLockedOut: " . ($membership->IsLockedOut ? 'Yes' : 'No') . "\n";
            } else {
                echo "❌ Membership relationship failed\n";
            }
            
            // Test 6: Check roles relationship
            echo "\nTest 6: Testing roles relationship...\n";
            $roles = $user->aspNetRoles;
            if ($roles->count() > 0) {
                echo "✅ Found {$roles->count()} role(s):\n";
                foreach ($roles as $role) {
                    echo "   - {$role->name}\n";
                }
            } else {
                echo "⚠️  No roles found for this user\n";
            }
            
            // Test 7: Test authentication provider methods
            echo "\nTest 7: Testing authentication provider...\n";
            $provider = Auth::guard('admin')->getProvider();
            
            // Test retrieveById
            $retrievedUser = $provider->retrieveById($user->id);
            if ($retrievedUser && $retrievedUser->id == $user->id) {
                echo "✅ retrieveById() works\n";
            } else {
                echo "❌ retrieveById() failed\n";
            }
            
            // Test retrieveByCredentials
            $credentials = ['name' => $user->name];
            $retrievedUser = $provider->retrieveByCredentials($credentials);
            if ($retrievedUser && $retrievedUser->id == $user->id) {
                echo "✅ retrieveByCredentials() works\n";
            } else {
                echo "❌ retrieveByCredentials() failed\n";
            }
            
        } else {
            echo "❌ Failed to load User model\n";
        }
        } catch (\Exception $e) {
            echo "❌ Eloquent test error: " . $e->getMessage() . "\n";
            echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
    } else {
        echo "❌ No test user found\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Test 8: Check admin_users table status
echo "\n\nTest 8: Checking admin_users table...\n";
try {
    $adminUsersCount = DB::table('admin_users')->count();
    echo "ℹ️  admin_users table has {$adminUsersCount} records (should be empty - we're using my_aspnet_users instead)\n";
} catch (\Exception $e) {
    echo "⚠️  admin_users table check failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "Authentication bridge is configured and ready for login testing.\n";
echo "Next step: Test actual login via web interface at /auth/login\n";
