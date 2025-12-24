<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== Testing ASP.NET Password Verification ===\n\n";

// Get a test user
$testUsername = 'ggg';
echo "Testing with username: $testUsername\n\n";

// Get user data directly from database
$user = DB::table('my_aspnet_users as u')
    ->join('my_aspnet_membership as m', 'u.id', '=', 'm.userId')
    ->where('u.name', $testUsername)
    ->select('u.*', 'm.Password', 'm.PasswordKey', 'm.PasswordFormat', 'm.IsApproved', 'm.IsLockedOut')
    ->first();

if (!$user) {
    die("❌ User not found\n");
}

echo "✅ User found:\n";
echo "   - ID: {$user->id}\n";
echo "   - Username: {$user->name}\n";
echo "   - Email: {$user->email}\n";
echo "   - IsApproved: " . ($user->IsApproved ? 'Yes' : 'No') . "\n";
echo "   - IsLockedOut: " . ($user->IsLockedOut ? 'Yes' : 'No') . "\n";
echo "   - PasswordFormat: {$user->PasswordFormat}\n";
echo "   - Has Laravel password: " . ($user->password_laravel ? 'Yes' : 'No') . "\n\n";

// Test ASP.NET password hashing (you'll need to provide the actual password)
echo "To test password verification, enter the password for user '$testUsername': ";
$handle = fopen ("php://stdin","r");
$password = trim(fgets($handle));
fclose($handle);

if (empty($password)) {
    echo "\n⚠️  No password entered, skipping password verification test\n";
} else {
    echo "\nTesting ASP.NET password hashing...\n";
    
    // ASP.NET uses base64(SHA256(salt + password))
    $salt = $user->PasswordKey ?? '';
    $hashedPassword = base64_encode(hash('sha256', $salt . $password, true));
    
    echo "Computed hash: " . substr($hashedPassword, 0, 20) . "...\n";
    echo "Stored hash:   " . substr($user->Password, 0, 20) . "...\n";
    
    if ($hashedPassword === $user->Password) {
        echo "\n✅ PASSWORD MATCH! ASP.NET verification works!\n";
        
        // Test Laravel bcrypt conversion
        echo "\nTesting bcrypt migration...\n";
        $bcryptHash = Hash::make($password);
        echo "   - Generated bcrypt hash: " . substr($bcryptHash, 0, 30) . "...\n";
        
        // Verify bcrypt works
        if (Hash::check($password, $bcryptHash)) {
            echo "   - ✅ Bcrypt verification works\n";
            
            // Simulate migration
            echo "\n   - Updating database with Laravel password...\n";
            DB::table('my_aspnet_users')
                ->where('id', $user->id)
                ->update(['password_laravel' => $bcryptHash]);
            
            echo "   - ✅ Migration completed\n";
            echo "\n🎉 Authentication bridge works! User can now login with Laravel-Admin.\n";
        } else {
            echo "   - ❌ Bcrypt verification failed\n";
        }
    } else {
        echo "\n❌ PASSWORD MISMATCH! Password verification failed.\n";
        echo "Please make sure you entered the correct password.\n";
    }
}

echo "\n=== Next Steps ===\n";
echo "1. Access login page: http://localhost:8888/schools/auth/login\n";
echo "2. Login with username: $testUsername\n";
echo "3. Enter the password\n";
echo "4. Check if authentication works\n";
