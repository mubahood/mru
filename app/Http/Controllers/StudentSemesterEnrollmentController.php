<?php

namespace App\Http\Controllers;

use App\Models\MruProgramme;
use App\Models\MruSemester;
use App\Models\MruStudent;
use App\Models\AcademicYear;
use App\Models\StudentHasSemeter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentSemesterEnrollmentController extends Controller
{
    /**
     * Get the current active semester
     * 
     * @return MruSemester|null
     */
    public static function getCurrentSemester()
    {
        return MruSemester::where('is_active', 1)->first();
    }

    /**
     * Get students with course registrations for a programme
     * 
     * @param string $programmeCode
     * @param string $academicYear
     * @param string $semester
     * @return \Illuminate\Support\Collection
     */
    public static function getStudentsWithCourseRegistrations($programmeCode, $academicYear, $semester)
    {
        return DB::table('acad_course_registration')
            ->select('regno')
            ->where('prog_id', $programmeCode)
            ->where('acad_year', $academicYear)
            ->where('semester', $semester)
            ->distinct()
            ->get();
    }

    /**
     * Check if a student is already enrolled in a semester
     * 
     * @param int $studentId
     * @param int $termId
     * @param int $academicYearId
     * @return bool
     */
    public static function isStudentEnrolled($studentId, $termId, $academicYearId)
    {
        return StudentHasSemeter::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->exists();
    }

    /**
     * Calculate year of study for a student
     * 
     * @param User $user
     * @return int
     */
    public static function calculateYearOfStudy(User $user)
    {
        $yearOfStudy = 1;
        
        if ($user->current_class_id) {
            $class = DB::table('academic_classes')
                ->where('id', $user->current_class_id)
                ->first();
                
            if ($class) {
                // Try to extract year from class name (e.g., "Year 2", "2nd Year", etc.)
                preg_match('/\d+/', $class->name, $matches);
                if (!empty($matches)) {
                    $yearOfStudy = min((int)$matches[0], 5); // Cap at year 5
                }
            }
        }
        
        return $yearOfStudy;
    }

    /**
     * Enroll a student in a semester
     * 
     * @param User $user
     * @param int $termId
     * @param int $academicYearId
     * @param int $yearOfStudy
     * @param int $semesterNumber
     * @param int|null $enrolledById
     * @return array ['success' => bool, 'message' => string, 'enrollment' => StudentHasSemeter|null]
     */
    public static function enrollStudent(
        User $user, 
        $termId, 
        $academicYearId, 
        $yearOfStudy, 
        $semesterNumber,
        $enrolledById = null
    ) {
        // Double-check for duplicates before enrolling (with lock)
        DB::beginTransaction();
        
        try {
            // Lock the table to prevent race conditions
            $exists = StudentHasSemeter::where('student_id', $user->id)
                ->where('term_id', $termId)
                ->where('academic_year_id', $academicYearId)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Student already enrolled (duplicate prevented)',
                    'enrollment' => null
                ];
            }

            $enrollment = new StudentHasSemeter();
            $enrollment->enterprise_id = $user->enterprise_id;
            $enrollment->student_id = $user->id;
            $enrollment->term_id = $termId;
            $enrollment->academic_year_id = $academicYearId;
            $enrollment->year_name = $yearOfStudy;
            $enrollment->semester_name = $semesterNumber;
            $enrollment->registration_number = $user->user_number;
            $enrollment->schoolpay_code = $user->school_pay_payment_code;
            $enrollment->pegpay_code = $user->pegpay_code;
            $enrollment->update_fees_balance = 'No';
            $enrollment->set_fees_balance_amount = 0;
            $enrollment->enrolled_by_id = $enrolledById ?? auth()->id() ?? 1;
            $enrollment->is_processed = 'No';
            $enrollment->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Student enrolled successfully',
                'enrollment' => $enrollment
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Student enrollment error', [
                'student_id' => $user->id,
                'term_id' => $termId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'enrollment' => null
            ];
        }
    }

    /**
     * Fix/Process student semester enrollments for a programme
     * 
     * This endpoint processes all students in a programme and enrolls them
     * in the current active semester based on their course registrations.
     */
    public function fixEnrollments(Request $request)
    {
        set_time_limit(0); // Remove time limit for large processing
        
        $programmeId = $request->get('programme_id');
        
        if (!$programmeId) {
            return $this->renderErrorPage('Programme ID is required', 'Please provide a valid programme ID in the URL');
        }

        // Get programme
        $programme = MruProgramme::find($programmeId);
        if (!$programme) {
            return $this->renderErrorPage('Programme Not Found', 'The requested programme (ID: ' . $programmeId . ') could not be found in the system');
        }

        // Get current active semester using helper method
        $currentSemester = self::getCurrentSemester();
        if (!$currentSemester) {
            return $this->renderErrorPage('No Active Semester', 'There is no active semester configured in the system. Please activate a semester first.');
        }

        $academicYear = $currentSemester->academic_year;
        if (!$academicYear) {
            return $this->renderErrorPage('Academic Year Missing', 'The current semester does not have an associated academic year');
        }

        // Stream HTML output directly
        return response()->stream(function () use ($programme, $currentSemester, $academicYear) {
            echo $this->processEnrollments($programme, $currentSemester, $academicYear);
        }, 200, [
            'Content-Type' => 'text/html',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Render an error page with consistent styling
     */
    private function renderErrorPage($title, $message)
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .error-container { max-width: 600px; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .error-icon { font-size: 64px; color: #e74c3c; margin-bottom: 20px; }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        p { color: #7f8c8d; line-height: 1.6; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>' . htmlspecialchars($title) . '</h1>
        <p>' . htmlspecialchars($message) . '</p>
        <a href="javascript:window.history.back()" class="btn">Go Back</a>
    </div>
</body>
</html>';
        
        return response($html, 400);
    }

    /**
     * Process enrollments with detailed output
     */
    private function processEnrollments($programme, $currentSemester, $academicYear)
    {
        $output = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Processing</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #fff; padding: 0; line-height: 1.4; font-size: 13px; color: #1f2937; }
        .container { max-width: 900px; margin: 0 auto; border: 1px solid #e5e7eb; }
        .header { background: #fff; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; }
        .header h1 { font-size: 16px; font-weight: 600; margin-bottom: 3px; color: #111827; }
        .header .meta { font-size: 11px; color: #6b7280; }
        .info { padding: 8px 16px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-size: 12px; color: #4b5563; }
        .content { padding: 12px 16px; }
        .status { font-size: 12px; color: #6b7280; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #f3f4f6; }
        .spinner { display: inline-block; width: 10px; height: 10px; border: 2px solid #f3f4f6; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 6px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .item { padding: 6px 10px; margin: 2px 0; font-size: 12px; border-radius: 3px; border-left: 3px solid; display: flex; align-items: center; gap: 8px; }
        .item-num { font-weight: 600; opacity: 0.6; min-width: 30px; }
        .item.success { background: #ecfdf5; color: #065f46; border-color: #10b981; }
        .item.success .item-num { color: #10b981; }
        .item.exists { background: #fff7ed; color: #9a3412; border-color: #f97316; }
        .item.exists .item-num { color: #f97316; }
        .item.error { background: #fef2f2; color: #991b1b; border-color: #ef4444; }
        .item.error .item-num { color: #ef4444; }
        .summary { background: #f9fafb; padding: 12px 16px; border-top: 1px solid #e5e7eb; }
        .summary h2 { font-size: 13px; font-weight: 600; margin-bottom: 10px; color: #111827; }
        .stats { display: flex; gap: 8px; }
        .stat { flex: 1; background: #fff; padding: 8px; border-radius: 3px; text-align: center; border: 1px solid #e5e7eb; }
        .stat-num { font-size: 20px; font-weight: 600; line-height: 1; }
        .stat-label { font-size: 10px; color: #6b7280; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.3px; }
        .actions { padding: 10px 16px; border-top: 1px solid #e5e7eb; text-align: center; }
        .btn { display: inline-block; padding: 6px 14px; font-size: 12px; border-radius: 3px; text-decoration: none; margin: 0 3px; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-secondary { background: #6b7280; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Enrollment Processing</h1>
            <div class="meta">' . htmlspecialchars($programme->progcode) . ' - ' . htmlspecialchars($programme->progname) . ' • Started ' . date('H:i:s') . '</div>
        </div>
        
        <div class="content">
            <div class="status"><span class="spinner"></span>Processing...</div>
';

        echo $output;
        flush();
        ob_flush();

        $enrolled = 0;
        $alreadyEnrolled = 0;
        $errors = 0;
        $processed = 0;
        $itemCounter = 0;

        try {
            // Get ALL students from acad_student table for this programme
            $students = DB::table('acad_student')
                ->where('progid', $programme->progcode)
                ->get();

            echo '<p><strong>Found ' . count($students) . ' students enrolled in this programme</strong></p>';
            flush();
            ob_flush();

            foreach ($students as $student) {
                // Track student processing success
                $studentSuccess = true;
                $studentErrors = [];
                
                // Get all course registrations for this student
                $registrations = DB::table('acad_course_registration')
                    ->select('ID', 'acad_year', 'semester', 'regno')
                    ->where(function($q) use ($student) {
                        $q->where('regno', $student->entryno)
                          ->orWhere('regno', $student->regno);
                    })
                    ->distinct()
                    ->get();

                if ($registrations->isEmpty()) {
                    // Update student processing status
                    $updated = MruStudent::where('ID', $student->ID)
                        ->update([
                            'is_processed' => 'Yes',
                            'is_processed_successful' => 'No',
                            'processing_reason' => 'No course registrations found'
                        ]);
                    
                    Log::info("Student {$student->ID} processed - Updated: {$updated} row(s)");
                    
                    $itemCounter++;
                    echo '<div class="item error"><span class="item-num">#' . $itemCounter . '</span>' . htmlspecialchars($student->firstname . ' ' . $student->othername) . ' (' . htmlspecialchars($student->entryno) . ') - No registrations</div>';
                    $errors++;
                    flush();
                    ob_flush();
                    continue;
                }

                foreach ($registrations as $registration) {
                    $processed++;
                    
                    // Find the academic year (case insensitive match)
                    $academicYearForReg = AcademicYear::whereRaw('LOWER(name) = ?', [strtolower($registration->acad_year)])->first();
                    
                    if (!$academicYearForReg) {
                        $studentSuccess = false;
                        $studentErrors[] = "Year {$registration->acad_year} not found";
                        
                        $itemCounter++;
                        echo '<div class="item error"><span class="item-num">#' . $itemCounter . '</span>' . htmlspecialchars($student->firstname . ' ' . $student->othername) . ' - Year ' . htmlspecialchars($registration->acad_year) . ' not found</div>';
                        $errors++;
                        flush();
                        ob_flush();
                        continue;
                    }

                    // Find the semester/term for this academic year
                    $semesterForReg = MruSemester::where('academic_year_id', $academicYearForReg->id)
                        ->where('name', $registration->semester)
                        ->first();
                    
                    if (!$semesterForReg) {
                        $studentSuccess = false;
                        $studentErrors[] = "Semester {$registration->semester} for year {$registration->acad_year} not found";
                        
                        $itemCounter++;
                        echo '<div class="item error"><span class="item-num">#' . $itemCounter . '</span>' . htmlspecialchars($student->firstname . ' ' . $student->othername) . ' - Semester ' . htmlspecialchars($registration->semester) . ' for year "' . htmlspecialchars($registration->acad_year) . '" not found</div>';
                        $errors++;
                        flush();
                        ob_flush();
                        continue;
                    }

                    // Check if already enrolled using acad_student.ID - use DB query to avoid model scopes
                    $existingEnrollment = DB::table('student_has_semeters')
                        ->where('student_id', $student->ID)
                        ->where('term_id', $semesterForReg->id)
                        ->where('academic_year_id', $academicYearForReg->id)
                        ->first();

                    if ($existingEnrollment) {
                        // Already enrolled - just update the course registration reference
                        DB::table('acad_course_registration')
                            ->where('ID', $registration->ID)
                            ->update([
                                'term_id' => $semesterForReg->id,
                                'student_semester_enrollment_id' => $existingEnrollment->id
                            ]);
                        
                        $itemCounter++;
                        echo '<div class="item exists"><span class="item-num">#' . $itemCounter . '</span>' . htmlspecialchars($student->firstname . ' ' . $student->othername) . ' (' . htmlspecialchars($student->entryno) . ') - Already enrolled in ' . htmlspecialchars($registration->acad_year) . ' S' . htmlspecialchars($registration->semester) . '</div>';
                        $alreadyEnrolled++;
                        flush();
                        ob_flush();
                        continue;
                    }

                    // Enroll student directly using raw DB insert to bypass validation
                    DB::beginTransaction();
                    try {
                        // Get next ID manually since the table doesn't have auto-increment
                        $nextId = DB::table('student_has_semeters')->max('id') + 1;
                        
                        // Insert directly into student_has_semeters table to bypass model validation
                        DB::table('student_has_semeters')->insert([
                            'id' => $nextId,
                            'enterprise_id' => $student->enterprise_id ?? 1,
                            'student_id' => $student->ID, // Using acad_student.ID
                            'term_id' => $semesterForReg->id,
                            'academic_year_id' => $academicYearForReg->id,
                            'year_name' => 1,
                            'semester_name' => (int)$registration->semester,
                            'registration_number' => $student->regno,
                            'schoolpay_code' => null,
                            'pegpay_code' => null,
                            'update_fees_balance' => 'No',
                            'set_fees_balance_amount' => 0,
                            'enrolled_by_id' => auth()->id() ?? 1,
                            'is_processed' => 'No',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Update course registration references
                        DB::table('acad_course_registration')
                            ->where('ID', $registration->ID)
                            ->update([
                                'term_id' => $semesterForReg->id,
                                'student_semester_enrollment_id' => $nextId
                            ]);

                        DB::commit();

                            $itemCounter++;
                            echo '<div class="item success"><span class="item-num">#' . $itemCounter . '</span>' . htmlspecialchars($student->firstname . ' ' . $student->othername) . ' (' . htmlspecialchars($student->entryno) . ') - ' . htmlspecialchars($registration->acad_year) . ' S' . htmlspecialchars($registration->semester) . '</div>';
                        $enrolled++;
                        
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $studentSuccess = false;
                        $studentErrors[] = $e->getMessage();
                        
                            $itemCounter++;
                            echo '<div class="item error"><span class="item-num">#' . $itemCounter . '</span>' . htmlspecialchars($student->firstname . ' ' . $student->othername) . ' - ' . htmlspecialchars($e->getMessage()) . '</div>';
                        $errors++;
                        Log::error('Student enrollment error', [
                            'student_id' => $student->ID,
                            'error' => $e->getMessage()
                        ]);
                    }

                    flush();
                    ob_flush();
                }
                
                // Update student processing status at the end of each student
                if (!$registrations->isEmpty()) {
                    $updated = MruStudent::where('ID', $student->ID)
                        ->update([
                            'is_processed' => 'Yes',
                            'is_processed_successful' => $studentSuccess ? 'Yes' : 'No',
                            'processing_reason' => !empty($studentErrors) ? implode('; ', $studentErrors) : null
                        ]);
                    
                    Log::info("Student {$student->ID} final update - Success: {$studentSuccess}, Updated: {$updated} row(s)");
                }
            }

        } catch (\Exception $e) {
            echo '<div class="item error"><span class="item-num">#' . ($itemCounter + 1) . '</span>Fatal Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            Log::error('Fatal enrollment processing error', ['error' => $e->getMessage()]);
        }

        // Update programme processing status
        $programmeSuccess = ($errors === 0 && $enrolled > 0);
        $errorMessage = null;
        
        if ($errors > 0) {
            $errorMessage = "Processing completed with {$errors} error(s). Check logs for details.";
        } elseif ($enrolled === 0 && $alreadyEnrolled === 0) {
            $programmeSuccess = false;
            $errorMessage = "No students were processed. No course registrations found.";
        }
        
        DB::table('acad_programme')
            ->where('progcode', $programme->progcode)
            ->update([
                'is_processed' => 'Yes',
                'process_passed' => $programmeSuccess ? 'Yes' : 'No',
                'error_mess' => $errorMessage
            ]);

        // Close student list and content divs, add summary
        $summary = '
        </div>
        
        <div class="summary">
            <h2>Summary</h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-num">' . $processed . '</div>
                    <div class="stat-label">Processed</div>
                </div>
                <div class="stat">
                    <div class="stat-num" style="color: #10b981;">' . $enrolled . '</div>
                    <div class="stat-label">Enrolled</div>
                </div>
                <div class="stat">
                    <div class="stat-num" style="color: #f59e0b;">' . $alreadyEnrolled . '</div>
                    <div class="stat-label">Existing</div>
                </div>
                <div class="stat">
                    <div class="stat-num" style="color: #ef4444;">' . $errors . '</div>
                    <div class="stat-label">Errors</div>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <a href="javascript:window.close()" class="btn btn-secondary">Close</a>
            <a href="' . admin_url('mru-student-semester-enrollments') . '" target="_blank" class="btn btn-primary">View Enrollments</a>
        </div>
    </div>
</body>
</html>';

        echo $summary;
        flush();
        ob_flush();

        return '';
    }
}
