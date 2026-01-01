<?php

namespace App\Services;

use App\Models\RemoteDataBaseSync;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * RemoteSyncService
 * 
 * Service for synchronizing data from remote Campus Dynamics database to local database.
 * Handles data transformation, validation, and incremental sync operations.
 * 
 * @package App\Services
 * @author MRU Development Team
 * @version 1.0.0
 */
class RemoteSyncService
{
    /**
     * Remote database connection
     */
    protected $remoteDb;

    /**
     * Local database connection
     */
    protected $localDb;

    /**
     * Current sync record
     */
    protected $syncRecord;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->remoteDb = DB::connection('remote_mysql');
        $this->localDb = DB::connection('mysql');
    }

    /**
     * Start sync operation for a table
     * 
     * @param string $tableName
     * @param array $config
     * @return RemoteDataBaseSync
     */
    public function startSync($tableName, $config = [])
    {
        // Create sync record
        $this->syncRecord = RemoteDataBaseSync::createNewSync($tableName, $config);
        
        return $this->processSyncRecord($this->syncRecord);
    }

    /**
     * Process an existing sync record
     * 
     * @param RemoteDataBaseSync $syncRecord
     * @return RemoteDataBaseSync
     */
    public function processSyncRecord(RemoteDataBaseSync $syncRecord)
    {
        $this->syncRecord = $syncRecord;
        $tableName = $syncRecord->table_name;
        
        try {
            // Mark as started
            $this->syncRecord->markAsStarted();
            
            // Get total records count
            $totalRecords = $this->getRemoteTotalRecords($tableName);
            $this->syncRecord->update(['total_records' => $totalRecords]);
            
            // Perform sync
            $this->performSync($tableName);
            
            // Mark as completed
            $this->syncRecord->markAsCompleted(
                "Successfully synced {$this->syncRecord->number_of_records_synced} records. " .
                "Inserted: {$this->syncRecord->records_inserted}, " .
                "Updated: {$this->syncRecord->records_updated}, " .
                "Skipped: {$this->syncRecord->records_skipped}, " .
                "Failed: {$this->syncRecord->records_failed}"
            );
            
            return $this->syncRecord;
            
        } catch (Exception $e) {
            Log::error("Sync failed for table {$tableName}: " . $e->getMessage());
            $this->syncRecord->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Get total records from remote table
     */
    protected function getRemoteTotalRecords($tableName)
    {
        try {
            return $this->remoteDb->table($tableName)->count();
        } catch (Exception $e) {
            Log::warning("Could not get total count for {$tableName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Perform the actual sync operation
     */
    protected function performSync($tableName)
    {
        $startId = $this->syncRecord->start_id;
        $limit = $this->syncRecord->range_limit;
        $processed = 0;
        
        // Determine the primary key column
        $primaryKey = $this->getPrimaryKeyColumn($tableName);
        
        while (true) {
            // Fetch batch from remote
            // CRITICAL: Use raw column name to preserve case sensitivity
            $records = $this->remoteDb->table($tableName)
                ->whereRaw("`{$primaryKey}` > ?", [$startId])
                ->orderByRaw("`{$primaryKey}` ASC")
                ->limit($limit)
                ->get();
            
            if ($records->isEmpty()) {
                break;
            }
            
            // Process each record
            foreach ($records as $record) {
                // CRITICAL: Handle case-insensitive property names from MySQL
                // Try uppercase first, then lowercase
                $currentRecordId = null;
                if (property_exists($record, $primaryKey)) {
                    $currentRecordId = $record->{$primaryKey};
                } elseif (property_exists($record, strtolower($primaryKey))) {
                    $currentRecordId = $record->{strtolower($primaryKey)};
                } else {
                    Log::error("Primary key {$primaryKey} not found in record", [
                        'available_properties' => array_keys((array)$record)
                    ]);
                    $this->syncRecord->incrementSkipped();
                    $processed++;
                    continue;
                }
                
                // CRITICAL: Skip records with ID = 0 or NULL to prevent infinite loops
                // WHERE ID > 0 would refetch ID=0 indefinitely
                if (empty($currentRecordId) || $currentRecordId === 0 || $currentRecordId === '0') {
                    Log::warning("Skipping record with invalid {$primaryKey}={$currentRecordId} in {$tableName}");
                    $this->syncRecord->incrementSkipped();
                    // Still need to advance cursor - use previous startId + 1 as fallback
                    $startId = $startId + 1;
                    $processed++;
                    continue;
                }
                
                try {
                    $this->syncRecord($tableName, $record);
                } catch (Exception $e) {
                    // If this is a "transformation logic not implemented" error, fail immediately
                    if (strpos($e->getMessage(), 'Transformation logic for table') !== false) {
                        throw $e; // Re-throw to fail the entire sync
                    }
                    
                    // For other errors, log and continue with next record
                    Log::error("Failed to sync record {$primaryKey}={$currentRecordId} from {$tableName}: " . $e->getMessage());
                    $this->syncRecord->incrementFailed();
                }
                
                // CRITICAL: Always update startId, even if record failed
                // This prevents infinite loops by ensuring we move forward
                $startId = $currentRecordId;
                
                $processed++;
                
                // Update progress every 100 records
                if ($processed % 100 == 0) {
                    $this->syncRecord->update([
                        'start_id' => $startId,
                        'message' => "Processing... {$processed} records synced",
                    ]);
                }
            }
            
            // If we got fewer records than limit, we're done
            if ($records->count() < $limit) {
                break;
            }
        }
        
        // Final update
        $this->syncRecord->update(['start_id' => $startId]);
    }

    /**
     * Sync individual record with transformation logic
     */
    protected function syncRecord($tableName, $record)
    {
        // Convert record to array
        $data = (array) $record;
        
        // Apply table-specific transformation
        $transformedData = $this->transformRecord($tableName, $data);
        
        if (!$transformedData) {
            $this->syncRecord->incrementSkipped();
            return;
        }
        
        // Special handling for acad_results (uses composite key: regno + courseid)
        if ($tableName === 'acad_results') {
            $this->syncResultRecord($transformedData);
            return;
        }
        
        // Determine primary key for other tables
        $primaryKey = $this->getPrimaryKeyColumn($tableName);
        $primaryValue = $data[$primaryKey] ?? null;
        
        if (!$primaryValue) {
            $this->syncRecord->incrementSkipped();
            return;
        }
        
        // Check if record exists locally
        $exists = $this->localDb->table($tableName)
            ->where($primaryKey, $primaryValue)
            ->exists();
        
        try {
            if ($exists) {
                // Update existing record
                $this->localDb->table($tableName)
                    ->where($primaryKey, $primaryValue)
                    ->update($transformedData);
                $this->syncRecord->incrementUpdated();
            } else {
                // Insert new record
                $this->localDb->table($tableName)->insert($transformedData);
                $this->syncRecord->incrementInserted();
            }
        } catch (Exception $e) {
            Log::error("Failed to sync record: " . $e->getMessage());
            $this->syncRecord->incrementFailed();
            throw $e;
        }
    }

    /**
     * Transform record based on table-specific logic
     * 
     * Override this method or use configuration to define transformation rules
     */
    protected function transformRecord($tableName, $data)
    {
        // Get transformation config if provided
        $config = $this->syncRecord->sync_config ?? [];
        
        // Apply custom transformations based on table name
        switch ($tableName) {
            case 'students':
                return $this->transformStudent($data, $config);
                
            case 'courses':
                return $this->transformCourse($data, $config);
                
            case 'enrollments':
                return $this->transformEnrollment($data, $config);
                
            case 'acad_results':
                return $this->transformResult($data, $config);
                
            default:
                // Table not implemented - throw clear error
                throw new Exception(
                    "Transformation logic for table '{$tableName}' has not been implemented yet. " .
                    "Please implement the transform" . ucfirst($tableName) . "() method in RemoteSyncService.php " .
                    "before attempting to sync this table."
                );
        }
    }

    /**
     * Transform student record
     * Example implementation - customize based on your schema
     */
    protected function transformStudent($data, $config = [])
    {
        // Example: Map remote fields to local fields
        return [
            'id' => $data['id'] ?? null,
            'student_id' => $data['student_id'] ?? $data['StudentID'] ?? null,
            'name' => $data['name'] ?? $data['FullName'] ?? null,
            'email' => $data['email'] ?? $data['Email'] ?? null,
            // Add more field mappings as needed
            'created_at' => $data['created_at'] ?? now(),
        ];
    }

    /**
     * Transform course record
     */
    protected function transformCourse($data, $config = [])
    {
        // Implement course-specific transformation
        return $this->defaultTransform($data, $config);
    }

    /**
     * Transform enrollment record
     */
    protected function transformEnrollment($data, $config = [])
    {
        // Implement enrollment-specific transformation
        return $this->defaultTransform($data, $config);
    }

    /**
     * Transform acad_results record from remote to local database
     * 
     * CRITICAL LOGIC FOR RESULTS SYNC
     * 
     * DATABASE CONTEXT:
     * - Remote DB: 53,409 records, ID range: 1-56,347
     * - Local DB: 605,764 records, ID range: 64-630,000
     * - Unique Constraint: regno + courseid (prevents duplicates)
     * - All critical fields have NO NULL values
     * 
     * SYNC STRATEGY:
     * 1. NEVER sync the ID field - let local DB auto-generate
     * 2. Use regno+courseid as natural key for upsert
     * 3. Validate all critical fields before insert/update
     * 4. Handle data type differences
     * 5. Preserve existing local records not in remote
     * 
     * @param array $data Remote database record
     * @param array $config Optional configuration
     * @return array|null Transformed data or null to skip
     */
    protected function transformResult($data, $config = [])
    {
        // Validate critical identifiers
        $regno = isset($data['regno']) ? trim($data['regno']) : null;
        $courseid = isset($data['courseid']) ? trim($data['courseid']) : null;
        
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
        
        // Extract and validate semester (must be 1, 2, or 3)
        $semester = isset($data['semester']) ? (int) $data['semester'] : null;
        if ($semester === null || !in_array($semester, [1, 2, 3])) {
            Log::warning("Skipping result: Invalid semester", [
                'regno' => $regno,
                'courseid' => $courseid,
                'semester' => $semester
            ]);
            return null;
        }
        
        // Validate academic year
        $acad = isset($data['acad']) ? trim($data['acad']) : null;
        if (empty($acad)) {
            Log::warning("Skipping result: Missing academic year", [
                'regno' => $regno,
                'courseid' => $courseid
            ]);
            return null;
        }
        
        // Validate academic year format (YYYY/YYYY)
        if (!preg_match('/^\d{4}\/\d{4}$/', $acad)) {
            Log::warning("Result has non-standard academic year format", [
                'regno' => $regno,
                'courseid' => $courseid,
                'acad' => $acad
            ]);
        }
        
        // Process score (must be 0-100 or NULL)
        $score = isset($data['score']) ? $data['score'] : null;
        if ($score !== null) {
            $score = (int) $score;
            if ($score < 0) {
                $score = 0;
            } elseif ($score > 100) {
                $score = 100;
            }
        }
        
        // Grade validation
        $grade = isset($data['grade']) ? trim($data['grade']) : null;
        
        // Process numeric fields
        $studyyear = isset($data['studyyear']) ? (int) $data['studyyear'] : null;
        $gradept = isset($data['gradept']) ? (float) $data['gradept'] : null;
        $gpa = isset($data['gpa']) ? (float) $data['gpa'] : null;
        $creditUnits = isset($data['CreditUnits']) ? (float) $data['CreditUnits'] : null;
        
        // Validate GPA range (typically 0.0-5.0)
        if ($gpa !== null && ($gpa < 0.0 || $gpa > 5.0)) {
            Log::warning("GPA out of typical range", [
                'regno' => $regno,
                'courseid' => $courseid,
                'gpa' => $gpa
            ]);
        }
        
        // Process optional text fields
        $result_comment = isset($data['result_comment']) ? trim($data['result_comment']) : null;
        $progid = isset($data['progid']) ? trim($data['progid']) : null;
        
        // Truncate if too long
        if (!empty($result_comment) && strlen($result_comment) > 25) {
            $result_comment = substr($result_comment, 0, 25);
        }
        
        if (!empty($progid) && strlen($progid) > 25) {
            $progid = substr($progid, 0, 25);
        }
        
        // Build transformed record (NO ID - local DB will auto-generate)
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
            'CreditUnits' => $creditUnits,  // CamelCase as per local schema
            'progid' => $progid,
        ];
        
        // Final validation
        if (empty($transformed['regno']) || 
            empty($transformed['courseid']) || 
            $transformed['semester'] === null || 
            empty($transformed['acad'])) {
            Log::error("Transformed result missing critical fields", [
                'transformed' => $transformed
            ]);
            return null;
        }
        
        return $transformed;
    }

    /**
     * Sync individual result record using composite key (regno + courseid)
     * 
     * @param array $transformedData Transformed data ready for upsert
     * @return void
     */
    protected function syncResultRecord($transformedData)
    {
        $regno = $transformedData['regno'];
        $courseid = $transformedData['courseid'];
        
        try {
            // Check if record exists using composite key
            $exists = $this->localDb->table('acad_results')
                ->where('regno', $regno)
                ->where('courseid', $courseid)
                ->exists();
            
            if ($exists) {
                // UPDATE existing record
                $this->localDb->table('acad_results')
                    ->where('regno', $regno)
                    ->where('courseid', $courseid)
                    ->update($transformedData);
                
                $this->syncRecord->incrementUpdated();
            } else {
                // INSERT new record (ID will auto-generate)
                $this->localDb->table('acad_results')->insert($transformedData);
                
                $this->syncRecord->incrementInserted();
            }
        } catch (Exception $e) {
            Log::error("Failed to sync result record", [
                'regno' => $regno,
                'courseid' => $courseid,
                'error' => $e->getMessage()
            ]);
            
            $this->syncRecord->incrementFailed();
        }
    }

    /**
     * Get primary key column for a table
     */
    protected function getPrimaryKeyColumn($tableName)
    {
        // Try to get from config first
        $config = $this->syncRecord->sync_config ?? [];
        if (isset($config['primary_key'])) {
            return $config['primary_key'];
        }
        
        // Table-specific primary keys
        $primaryKeys = [
            'acad_results' => 'ID',  // uppercase
            'acad_student' => 'ID',
            'acad_course' => 'CourseID',
            // Add more as needed
        ];
        
        return $primaryKeys[$tableName] ?? 'id'; // default lowercase
    }

    /**
     * Test remote connection
     */
    public function testRemoteConnection()
    {
        try {
            $this->remoteDb->getPdo();
            return [
                'success' => true,
                'message' => 'Successfully connected to remote database',
                'database' => $this->remoteDb->getDatabaseName(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of tables from remote database
     */
    public function getRemoteTables()
    {
        try {
            $tables = $this->remoteDb->select('SHOW TABLES');
            $databaseName = $this->remoteDb->getDatabaseName();
            $key = "Tables_in_{$databaseName}";
            
            return collect($tables)->map(function($table) use ($key) {
                return $table->{$key};
            })->toArray();
        } catch (Exception $e) {
            Log::error("Failed to get remote tables: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get sync statistics
     */
    public function getSyncStatistics($tableName = null)
    {
        $query = RemoteDataBaseSync::query();
        
        if ($tableName) {
            $query->forTable($tableName);
        }
        
        return [
            'total_syncs' => $query->count(),
            'completed' => $query->completed()->count(),
            'failed' => $query->failed()->count(),
            'processing' => $query->processing()->count(),
            'total_records_synced' => $query->sum('number_of_records_synced'),
            'latest_sync' => $query->latest()->first(),
        ];
    }
}
