#!/usr/bin/env php
<?php

/**
 * MRU User Type Classification and Status Update
 * 
 * This script analyzes the MRU database and correctly classifies users:
 * - Students: Users who have corresponding records in acad_student table
 * - Employees: All other users (staff, faculty, administrators)
 * 
 * All users will have status set to 1 (active)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MRU User Type Classification System ===\n\n";

// Step 1: Analyze current state
echo "Step 1: Analyzing current database state...\n";

$totalUsers = DB::table('my_aspnet_users')->count();
$totalStudents = DB::table('acad_student')->count();

echo "  Total ASP.NET users: " . number_format($totalUsers) . "\n";
echo "  Total student records: " . number_format($totalStudents) . "\n";

// Check current user_type distribution
$userTypes = DB::table('my_aspnet_users')
    ->select('user_type', DB::raw('COUNT(*) as count'))
    ->groupBy('user_type')
    ->get();

echo "\n  Current user_type distribution:\n";
foreach ($userTypes as $type) {
    echo "    - {$type->user_type}: " . number_format($type->count) . "\n";
}

// Check current status distribution
$statusDist = DB::table('my_aspnet_users')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->get();

echo "\n  Current status distribution:\n";
foreach ($statusDist as $status) {
    $statusLabel = $status->status == 1 ? 'Active' : 'Inactive';
    echo "    - {$statusLabel} ({$status->status}): " . number_format($status->count) . "\n";
}

// Step 2: Identify students by matching with acad_student table
echo "\n\nStep 2: Identifying students...\n";

// PRIMARY METHOD: Match by regno (username = registration number)
$studentsByRegno = DB::table('my_aspnet_users')
    ->whereIn('name', function($query) {
        $query->select('regno')->from('acad_student');
    })
    ->count();

echo "  Students matched by regno: " . number_format($studentsByRegno) . "\n";

// SECONDARY METHOD: Match by email
$studentsByEmail = DB::table('my_aspnet_users')
    ->join('acad_student', function($join) {
        $join->on(DB::raw('LOWER(TRIM(my_aspnet_users.email))'), '=', DB::raw('LOWER(TRIM(acad_student.email))'))
             ->whereNotNull('acad_student.email')
             ->where('acad_student.email', '!=', '')
             ->where('acad_student.email', '!=', '-');
    })
    ->whereNotIn('my_aspnet_users.id', function($query) {
        $query->select('id')->from('my_aspnet_users')
              ->whereIn('name', function($q) {
                  $q->select('regno')->from('acad_student');
              });
    })
    ->count();

echo "  Additional students matched by email: " . number_format($studentsByEmail) . "\n";

$totalStudents = $studentsByRegno + $studentsByEmail;
echo "  Total students identified: " . number_format($totalStudents) . "\n";

// Step 3: Calculate employees (all non-students)
$potentialEmployees = $totalUsers - $totalStudents;
echo "\n  Potential employees: " . number_format($potentialEmployees) . "\n";

// Step 4: Show sample classifications
echo "\n\nStep 3: Sample user classifications...\n";

echo "\n  Sample students (matched by regno):\n";
// Get sample student regnos first
$sampleRegnos = DB::table('acad_student')->limit(5)->pluck('regno');
$sampleStudents = DB::table('my_aspnet_users')
    ->whereIn('name', $sampleRegnos)
    ->limit(5)
    ->get(['id', 'name', 'email', 'user_type', 'status']);

foreach ($sampleStudents as $student) {
    echo "    ID: {$student->id} | {$student->name} | {$student->email} | Current: {$student->user_type}\n";
}

echo "\n  Sample non-students (potential employees):\n";
// Get all student regnos to exclude
$allStudentRegnos = DB::table('acad_student')->pluck('regno');
$sampleEmployees = DB::table('my_aspnet_users')
    ->whereNotIn('name', $allStudentRegnos)
    ->whereNotExists(function($query) {
        $query->select(DB::raw(1))
              ->from('acad_student')
              ->whereRaw('LOWER(TRIM(my_aspnet_users.email)) = LOWER(TRIM(acad_student.email))')
              ->whereNotNull('acad_student.email')
              ->where('acad_student.email', '!=', '')
              ->where('acad_student.email', '!=', '-');
    })
    ->limit(5)
    ->get(['id', 'name', 'email', 'user_type']);

foreach ($sampleEmployees as $employee) {
    echo "    ID: {$employee->id} | {$employee->name} | {$employee->email} | Current: {$employee->user_type}\n";
}

// Step 5: Confirm before updating
echo "\n\n=== PROPOSED CHANGES ===\n";
echo "1. Set user_type = 'student' for " . number_format($totalStudents) . " users:\n";
echo "   - " . number_format($studentsByRegno) . " matched by regno (username = registration number)\n";
echo "   - " . number_format($studentsByEmail) . " matched by email\n";
echo "2. Set user_type = 'employee' for " . number_format($potentialEmployees) . " users (all others)\n";
echo "3. Set status = 1 (active) for ALL " . number_format($totalUsers) . " users\n";

echo "\n\nDo you want to proceed with these updates? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "\nUpdate cancelled.\n\n";
    exit(0);
}

// Step 6: Perform updates
echo "\n\nStep 4: Performing updates...\n";

DB::beginTransaction();

try {
    // Update 1: Set students (matched by regno)
    echo "  Updating students (by regno)...\n";
    $studentsUpdatedByRegno = DB::table('my_aspnet_users')
        ->whereIn('name', function($query) {
            $query->select('regno')->from('acad_student');
        })
        ->update([
            'user_type' => 'student',
            'status' => 1
        ]);
    echo "    ✓ Updated {$studentsUpdatedByRegno} students by regno\n";
    
    // Update 2: Set students (matched by email, not already matched by regno)
    echo "  Updating students (by email)...\n";
    $studentsUpdatedByEmail = DB::table('my_aspnet_users')
        ->whereExists(function($query) {
            $query->select(DB::raw(1))
                  ->from('acad_student')
                  ->whereRaw('LOWER(TRIM(my_aspnet_users.email)) = LOWER(TRIM(acad_student.email))')
                  ->whereNotNull('acad_student.email')
                  ->where('acad_student.email', '!=', '')
                  ->where('acad_student.email', '!=', '-');
        })
        ->where('user_type', '!=', 'student') // Not already updated
        ->update([
            'user_type' => 'student',
            'status' => 1
        ]);
    echo "    ✓ Updated {$studentsUpdatedByEmail} students by email\n";
    
    $totalStudentsUpdated = $studentsUpdatedByRegno + $studentsUpdatedByEmail;
    
    // Update 3: Set employees (all users not matched as students)
    echo "  Updating employees...\n";
    $employeesUpdated = DB::table('my_aspnet_users')
        ->where('user_type', '!=', 'student')
        ->update([
            'user_type' => 'employee',
            'status' => 1
        ]);
    echo "    ✓ Updated {$employeesUpdated} employees\n";
    
    // Update 4: Ensure all users have status = 1
    echo "  Ensuring all users are active...\n";
    $statusUpdated = DB::table('my_aspnet_users')
        ->where(function($query) {
            $query->whereNull('status')->orWhere('status', '!=', 1);
        })
        ->update(['status' => 1]);
    echo "    ✓ Updated {$statusUpdated} status values\n";
    
    DB::commit();
    echo "\n  ✓ All updates committed successfully!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n  ✗ Error occurred: " . $e->getMessage() . "\n";
    echo "  All changes have been rolled back.\n\n";
    exit(1);
}

// Step 7: Verify results
echo "\n\nStep 5: Verifying results...\n";

$finalUserTypes = DB::table('my_aspnet_users')
    ->select('user_type', DB::raw('COUNT(*) as count'))
    ->groupBy('user_type')
    ->get();

echo "\n  Final user_type distribution:\n";
foreach ($finalUserTypes as $type) {
    echo "    - {$type->user_type}: " . number_format($type->count) . "\n";
}

$finalStatus = DB::table('my_aspnet_users')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->get();

echo "\n  Final status distribution:\n";
foreach ($finalStatus as $status) {
    $statusLabel = $status->status == 1 ? 'Active' : 'Inactive';
    echo "    - {$statusLabel} ({$status->status}): " . number_format($status->count) . "\n";
}

// Show some verification samples
echo "\n  Sample verification (students):\n";
$verifyStudents = DB::table('my_aspnet_users')
    ->where('user_type', 'student')
    ->limit(3)
    ->get(['id', 'name', 'email', 'user_type', 'status']);

foreach ($verifyStudents as $student) {
    echo "    ID: {$student->id} | {$student->name} | Type: {$student->user_type} | Status: {$student->status}\n";
}

echo "\n  Sample verification (employees):\n";
$verifyEmployees = DB::table('my_aspnet_users')
    ->where('user_type', 'employee')
    ->limit(3)
    ->get(['id', 'name', 'email', 'user_type', 'status']);

foreach ($verifyEmployees as $employee) {
    echo "    ID: {$employee->id} | {$employee->name} | Type: {$employee->user_type} | Status: {$employee->status}\n";
}

echo "\n\n=== Update Complete! ===\n";
echo "\nSummary:\n";
echo "✓ Students classified: " . number_format($totalStudentsUpdated) . "\n";
echo "✓ Employees classified: " . number_format($employeesUpdated) . "\n";
echo "✓ All users set to active status\n";
echo "✓ Total users processed: " . number_format($totalUsers) . "\n\n";

echo "The MRU user classification is now complete!\n\n";
