```php
/**
 * Transform acad_results record from remote to local database
 * 
 * CRITICAL LOGIC FOR RESULTS SYNC
 * 
 * DATABASE CONTEXT:
 * - Remote DB (campus_dynamics): 53,409 records, ID range: 1-56,347
 * - Local DB (mru_main): 605,764 records, ID range: 64-630,000
 * - Unique Constraint: regno + courseid (prevents duplicates)
 * - All critical fields (regno, courseid, semester, acad) have NO NULL values
 * 
 * SYNC STRATEGY:
 * 1. NEVER sync the ID field - let local DB auto-generate
 * 2. Use regno+courseid as natural key for upsert
 * 3. Validate all critical fields before insert/update
 * 4. Handle data type differences (regno char size, score unsigned)
 * 5. Preserve existing local records not in remote (local has historical data)
 * 
 * EDGE CASES HANDLED:
 * - Duplicate prevention via unique constraint check
 * - NULL value validation for critical fields
 * - Data type casting and validation
 * - Trimming whitespace from text fields
 * - Missing or invalid grade/score combinations
 * - Academic year format validation
 * 
 * @param array $data Remote database record
 * @param array $config Optional configuration
 * @return array|null Transformed data ready for local insert/update, or null to skip
 */
protected function transformResult($data, $config = [])
{
    // ==========================================
    // STEP 1: VALIDATE CRITICAL FIELDS
    // ==========================================
    // These fields MUST exist and be non-empty for a valid result
    $regno = isset($data['regno']) ? trim($data['regno']) : null;
    $courseid = isset($data['courseid']) ? trim($data['courseid']) : null;
    
    // Skip if missing critical identifiers
    if (empty($regno) || empty($courseid)) {
        Log::warning("Skipping result: Missing regno or courseid", [
            'regno' => $regno,
            'courseid' => $courseid,
            'remote_id' => $data['ID'] ?? null
        ]);
        return null;
    }
    
    // Validate regno length (local DB allows up to 85 chars)
    if (strlen($regno) > 85) {
        Log::warning("Skipping result: Regno exceeds 85 characters", [
            'regno' => $regno,
            'length' => strlen($regno)
        ]);
        return null;
    }
    
    // Validate courseid length (local DB allows up to 25 chars)
    if (strlen($courseid) > 25) {
        Log::warning("Skipping result: Courseid exceeds 25 characters", [
            'courseid' => $courseid,
            'length' => strlen($courseid)
        ]);
        return null;
    }
    
    // ==========================================
    // STEP 2: EXTRACT AND VALIDATE SEMESTER
    // ==========================================
    $semester = isset($data['semester']) ? (int) $data['semester'] : null;
    
    // Validate semester (must be 1, 2, or 3)
    if ($semester === null || !in_array($semester, [1, 2, 3])) {
        Log::warning("Skipping result: Invalid semester", [
            'regno' => $regno,
            'courseid' => $courseid,
            'semester' => $semester
        ]);
        return null;
    }
    
    // ==========================================
    // STEP 3: VALIDATE ACADEMIC YEAR
    // ==========================================
    $acad = isset($data['acad']) ? trim($data['acad']) : null;
    
    // Validate academic year format (e.g., "2023/2024")
    if (empty($acad)) {
        Log::warning("Skipping result: Missing academic year", [
            'regno' => $regno,
            'courseid' => $courseid
        ]);
        return null;
    }
    
    // Optional: Validate academic year format (YYYY/YYYY)
    if (!preg_match('/^\d{4}\/\d{4}$/', $acad)) {
        Log::warning("Result has non-standard academic year format", [
            'regno' => $regno,
            'courseid' => $courseid,
            'acad' => $acad
        ]);
        // Don't skip - just log warning, as some old records might have different formats
    }
    
    // ==========================================
    // STEP 4: PROCESS SCORE AND GRADE
    // ==========================================
    // Score must be unsigned integer (0-100) or NULL
    $score = isset($data['score']) ? $data['score'] : null;
    
    // Validate score range
    if ($score !== null) {
        $score = (int) $score;
        if ($score < 0) {
            Log::warning("Negative score detected, setting to 0", [
                'regno' => $regno,
                'courseid' => $courseid,
                'original_score' => $data['score']
            ]);
            $score = 0;
        } elseif ($score > 100) {
            Log::warning("Score exceeds 100, capping at 100", [
                'regno' => $regno,
                'courseid' => $courseid,
                'original_score' => $data['score']
            ]);
            $score = 100;
        }
    }
    
    // Grade validation
    $grade = isset($data['grade']) ? trim($data['grade']) : null;
    $validGrades = ['A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'F', 'E', 'R', 'I', 'W', 'X'];
    
    if (!empty($grade) && !in_array($grade, $validGrades)) {
        Log::info("Non-standard grade detected", [
            'regno' => $regno,
            'courseid' => $courseid,
            'grade' => $grade
        ]);
        // Keep the grade anyway - might be legacy or special case
    }
    
    // ==========================================
    // STEP 5: PROCESS NUMERIC FIELDS
    // ==========================================
    $studyyear = isset($data['studyyear']) ? (int) $data['studyyear'] : null;
    $gradept = isset($data['gradept']) ? (float) $data['gradept'] : null;
    $gpa = isset($data['gpa']) ? (float) $data['gpa'] : null;
    $creditUnits = isset($data['CreditUnits']) ? (float) $data['CreditUnits'] : null;
    
    // Validate study year (typically 1-5)
    if ($studyyear !== null && ($studyyear < 1 || $studyyear > 7)) {
        Log::warning("Unusual study year detected", [
            'regno' => $regno,
            'courseid' => $courseid,
            'studyyear' => $studyyear
        ]);
        // Keep it anyway - might be valid for special programs
    }
    
    // Validate GPA range (typically 0.0-5.0)
    if ($gpa !== null && ($gpa < 0.0 || $gpa > 5.0)) {
        Log::warning("GPA out of typical range", [
            'regno' => $regno,
            'courseid' => $courseid,
            'gpa' => $gpa
        ]);
    }
    
    // Validate grade points (typically 0-5)
    if ($gradept !== null && ($gradept < 0.0 || $gradept > 5.0)) {
        Log::warning("Grade point out of typical range", [
            'regno' => $regno,
            'courseid' => $courseid,
            'gradept' => $gradept
        ]);
    }
    
    // ==========================================
    // STEP 6: PROCESS OPTIONAL TEXT FIELDS
    // ==========================================
    $result_comment = isset($data['result_comment']) ? trim($data['result_comment']) : null;
    $progid = isset($data['progid']) ? trim($data['progid']) : null;
    
    // Truncate if too long
    if (!empty($result_comment) && strlen($result_comment) > 25) {
        $result_comment = substr($result_comment, 0, 25);
    }
    
    if (!empty($progid) && strlen($progid) > 25) {
        $progid = substr($progid, 0, 25);
    }
    
    // ==========================================
    // STEP 7: BUILD TRANSFORMED RECORD
    // ==========================================
    // NOTE: We do NOT include 'ID' - local database will auto-generate
    $transformed = [
        'regno' => $regno,
        'courseid' => $courseid,
        'semester' => $semester,
        'acad' => $acad,
        'studyyear' => $studyyear,
        'score' => $score,
        'grade' => $grade,
        'gradept' => $gradept,
        'gpa' => $gpa,
        'result_comment' => $result_comment,
        'CreditUnits' => $creditUnits,  // Note: CamelCase as per local schema
        'progid' => $progid,
    ];
    
    // ==========================================
    // STEP 8: FINAL VALIDATION
    // ==========================================
    // Ensure we have minimum required data for a valid result
    if (empty($transformed['regno']) || 
        empty($transformed['courseid']) || 
        $transformed['semester'] === null || 
        empty($transformed['acad'])) {
        Log::error("Transformed result missing critical fields", [
            'transformed' => $transformed,
            'original' => $data
        ]);
        return null;
    }
    
    return $transformed;
}

/**
 * Sync individual result record with upsert logic
 * 
 * UPSERT STRATEGY:
 * - Primary matching: regno + courseid (unique constraint)
 * - If exists: UPDATE all fields
 * - If not exists: INSERT new record
 * - ID is auto-generated by database
 * 
 * @param string $tableName Should be 'acad_results'
 * @param array $record Remote record
 * @return void
 * @throws Exception On transformation or database errors
 */
protected function syncResultRecord($record)
{
    $data = (array) $record;
    
    // Apply transformation
    $transformedData = $this->transformResult($data);
    
    if (!$transformedData) {
        $this->syncRecord->incrementSkipped();
        return;
    }
    
    // Use regno + courseid as unique key
    $regno = $transformedData['regno'];
    $courseid = $transformedData['courseid'];
    
    // Check if record exists in local database
    $exists = $this->localDb->table('acad_results')
        ->where('regno', $regno)
        ->where('courseid', $courseid)
        ->exists();
    
    try {
        if ($exists) {
            // UPDATE existing record
            // Update all fields except the ones that should remain from local
            $this->localDb->table('acad_results')
                ->where('regno', $regno)
                ->where('courseid', $courseid)
                ->update($transformedData);
            
            $this->syncRecord->incrementUpdated();
            
            Log::debug("Updated result", [
                'regno' => $regno,
                'courseid' => $courseid
            ]);
        } else {
            // INSERT new record
            // Database will auto-generate ID
            $this->localDb->table('acad_results')->insert($transformedData);
            
            $this->syncRecord->incrementInserted();
            
            Log::debug("Inserted result", [
                'regno' => $regno,
                'courseid' => $courseid
            ]);
        }
    } catch (Exception $e) {
        Log::error("Failed to sync result record", [
            'regno' => $regno,
            'courseid' => $courseid,
            'error' => $e->getMessage()
        ]);
        
        $this->syncRecord->incrementFailed();
        
        // Don't throw - continue with next record
        // throw $e;
    }
}
```
