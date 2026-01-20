# REMOTE SYNC SYSTEM - IMPORTANT INFORMATION

## Sync Direction

**CRITICAL:** The sync operates **FROM Remote Server TO Local Database**

- **Remote Server:** 137.63.185.93 (campus_dynamics database)
- **Local Database:** mru_main

## How Results Syncing Works

### 1. Data Flow
```
Remote Server (137.63.185.93)  ──sync──>  Local Database (mru_main)
     campus_dynamics                           mru_main
```

### 2. Sync Strategy (AS OF: 2026-01-20)

**NEW IMPLEMENTATION - LATEST FIRST:**
- Results are now synced in **DESCENDING ORDER** by ID
- This means **LATEST/NEWEST results sync FIRST**
- Perfect for getting recent student results quickly
- Prioritizes current academic years over historical data

**OLD IMPLEMENTATION - Oldest First (DEPRECATED):**
- ~~Previously synced in ASCENDING ORDER~~
- ~~Oldest results synced first~~
- ~~Could take hours to reach recent data~~

### 3. Composite Key for Results
- Uses `regno + courseid` as unique identifier
- **NOT** the ID field
- Prevents duplicate results
- Updates existing results if already present

### 4. Optional Filtering
You can configure the sync to filter by academic year:
```php
$config = [
    'range_limit' => 1000,
    'min_academic_year' => '2023/2024' // Only sync results from 2023/2024 onwards
];
```

## Important Notes for MRU2025002904 Case

### Why Student MRU2025002904 Had Only 1 Result

**Investigation Results:**
- Local (mru_main): 1 result for ICT1104B, 2025/2026, Semester 1, Grade C
- Remote (campus_dynamics): **0 results** for this student
- Remote database only has data up to 2024/2025 academic year
- Student MRU2025002904 is a **NEW 2025/2026 student**

### Current Database Statistics

**Local Database (mru_main):**
- Total Results: 632,111
- Max ID: 656,347
- Latest Academic Year: 2202/2203 (possibly data quality issue)
- Students with "MRU2025" prefix: 195 students with 799 results

**Remote Database (campus_dynamics @ 137.63.185.93):**
- Total Results: 53,417
- Max ID: 56,365
- Latest Academic Year: 2024/2025
- Students with "MRU2025" prefix: **NONE**

### Conclusion

The sync is working correctly, but:
1. **Remote server doesn't have 2025/2026 student data yet**
2. **Local database has MORE data than remote** (632K vs 53K)
3. **Remote needs to be updated first** before those results can sync

## Solution for New Students

### Option 1: Update Remote Server First (Recommended)
Upload the new 2025/2026 results to the remote server first:
```sql
-- Insert into remote campus_dynamics database
INSERT INTO campus_dynamics.acad_results 
    (regno, courseid, semester, acad, ...)
VALUES 
    ('MRU2025002904', '...', ...);
```

Then run sync to pull them to local.

### Option 2: Work Locally Only
If remote server is not being actively maintained:
- Continue entering results directly in local mru_main database
- Sync only serves as a backup/historical data source
- Latest data lives in local system

### Option 3: Reverse Sync (Future Enhancement)
Implement sync FROM local TO remote for new students:
- Would require new service: `LocalToRemoteSyncService`
- Push new results upstream to remote server
- Keep remote server updated

## Testing the Fix

To test the new DESCENDING sync:

```bash
# 1. Check latest results on remote
mysql -h 137.63.185.93 -u dbmanager -p24thdecember1977 -e \
  "USE campus_dynamics; 
   SELECT regno, courseid, acad, semester, ID 
   FROM acad_results 
   ORDER BY ID DESC LIMIT 5;"

# 2. Run sync via Laravel Admin or API
# Visit: /admin/remote-database-syncs-admin
# Or via route: /sync

# 3. Verify latest results appear in local first
mysql --socket=/Applications/MAMP/tmp/mysql/mysql.sock -uroot -proot -e \
  "USE mru_main; 
   SELECT regno, courseid, acad, semester, ID 
   FROM acad_results 
   WHERE regno IN (
     SELECT regno FROM acad_results ORDER BY ID DESC LIMIT 10
   );"
```

## Sync Configuration

### Default Settings
- **Batch Size:** 1,000 records per batch
- **Order:** DESC (latest first)
- **Primary Key:** ID for acad_results
- **Unique Key:** regno + courseid

### Custom Configuration Example
```php
use App\Services\RemoteSyncService;

$syncService = new RemoteSyncService();
$config = [
    'range_limit' => 2000,           // Larger batches
    'min_academic_year' => '2024/2025', // Only recent years
];

$sync = $syncService->startSync('acad_results', $config);
```

## Files Modified (2026-01-20)

1. `/app/Services/RemoteSyncService.php`
   - Changed ORDER BY from ASC to DESC
   - Added MAX ID initialization for starting from latest
   - Added optional academic year filtering
   - Updated ID advancement logic for reverse iteration
   - Added config support for min_academic_year

## Contact

For issues with remote sync or database inconsistencies:
- Check Laravel logs: `storage/logs/laravel.log`
- Review sync records: `/admin/remote-database-syncs-admin`
- Database admin: dbmanager@137.63.185.93

---

**Last Updated:** 2026-01-20  
**Updated By:** GitHub Copilot (Claude Sonnet 4.5)  
**Reason:** Fix results syncing to prioritize latest records first
