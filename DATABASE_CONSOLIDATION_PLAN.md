# MRU Database Consolidation Plan

**Date:** December 19, 2025  
**Objective:** Consolidate 4 separate databases into single centralized database `mru_main`  
**Status:** 🔴 PLANNING PHASE - NOT YET EXECUTED

---

## Executive Summary

### Critical Findings

⚠️ **ALERT: 33 Duplicate Table Names Detected Across Databases**

This consolidation requires careful handling of duplicate table names that exist in multiple databases with **different data**.

### Current State

| Database | Tables | Status |
|----------|--------|--------|
| mru_campus_dynamics | 122 | Active |
| mru_campus_dynamics_accounts | 128 | Active |
| mru_campus_dynamics_admissions | 8 | Active |
| mru_campus_dynamics_portal | 40 | Active |
| **TOTAL** | **298** | **4 Databases** |

### Target State

| Database | Tables | Status |
|----------|--------|--------|
| mru_main | 298+ | To be created |
| **TOTAL** | **298+** | **1 Database** |

---

## Problem Analysis

### Duplicate Tables (33 found)

These tables exist in multiple databases with **DIFFERENT DATA**:

#### 1. Academic Results Tables
- `acad_results` 
  - mru_campus_dynamics: **596,635 rows** (main data)
  - mru_campus_dynamics_portal: **102,690 rows** (subset/different data)
  
- `acad_results_complaints`
  - Multiple databases (need analysis)

#### 2. HR/Payroll Tables (18 duplicates)
- `hrm_allowance_deductions`
- `hrm_ded_allowance_stafflist`
- `hrm_departments`
- `hrm_emp_contracts`
- `hrm_employee` 
  - mru_campus_dynamics: **296 rows**
  - mru_campus_dynamics_accounts: **0 rows**
- `hrm_exemptions`
- `hrm_jobs`
- `hrm_monthly_ded_allowance`
- `hrm_payroll`
- `hrm_payroll_details`
- `hrm_payscales`
- `hrm_qualifications`
- `hrm_stations`

#### 3. Authentication/User Tables (13 duplicates)
- `my_aspnet_applications`
- `my_aspnet_apps`
- `my_aspnet_classes`
- `my_aspnet_membership`
- `my_aspnet_profiles`
- `my_aspnet_roles`
- `my_aspnet_roles_in_apps`
- `my_aspnet_schemaversion`
- `my_aspnet_sessioncleanup`
- `my_aspnet_sessions`
- `my_aspnet_userbranch_department`
- `my_aspnet_userphone`
- `my_aspnet_users`
  - mru_campus_dynamics: **76 rows**
  - mru_campus_dynamics_accounts: **6 rows**
  - mru_campus_dynamics_portal: **14,631 rows** (main data)
- `my_aspnet_usersinroles`
- `my_aspnet_usersubjects`

#### 4. Reference Tables (3 duplicates)
- `banks`
  - mru_campus_dynamics: **21 rows**
  - mru_campus_dynamics_accounts: **20 rows**
- `companyinfo`
- `fin_expdates`

---

## Consolidation Strategy

### Approach: Database-Prefixed Table Names

Since duplicate tables contain **different data** and serve different contexts, we'll preserve all data by prefixing table names with their source database.

#### Naming Convention

```
Original: [database].[table_name]
New:      mru_main.mru_[context]_[table_name]

Examples:
mru_campus_dynamics.acad_student          → mru_main.mru_acad_student
mru_campus_dynamics_accounts.fin_ledger   → mru_main.mru_accounts_fin_ledger
mru_campus_dynamics_portal.my_aspnet_users → mru_main.mru_portal_my_aspnet_users
```

#### Table Prefix Mapping

| Source Database | Table Prefix | Example |
|----------------|--------------|---------|
| mru_campus_dynamics | `mru_acad_` | mru_acad_student |
| mru_campus_dynamics_accounts | `mru_accounts_` | mru_accounts_fin_ledger |
| mru_campus_dynamics_admissions | `mru_admissions_` | mru_admissions_applications |
| mru_campus_dynamics_portal | `mru_portal_` | mru_portal_my_aspnet_users |

### Handling Duplicates

For the 33 duplicate tables, we have 3 options:

#### Option 1: Keep Separate with Prefixes (RECOMMENDED)
- Preserves ALL data
- No data loss
- Clear context in table names
- May require application code updates

**Example:**
- `mru_acad_my_aspnet_users` (76 rows - academic users)
- `mru_accounts_my_aspnet_users` (6 rows - accounting users)
- `mru_portal_my_aspnet_users` (14,631 rows - portal users)

#### Option 2: Merge with Source Column
- Add `source_database` column to identify origin
- Merge all duplicate data into one table
- Risk: Schema differences between duplicates
- Requires schema analysis

#### Option 3: Keep Primary, Archive Others
- Identify "primary" version of each duplicate
- Archive other versions to separate tables
- Risk: Data loss if not carefully analyzed

### **DECISION: Option 1 - Keep Separate with Prefixes**

**Rationale:**
- Zero data loss guaranteed
- Maintains data integrity
- Easier to rollback if needed
- Can merge later after analysis if needed

---

## Detailed Execution Plan

### Phase 1: Pre-Migration Safety

#### 1.1 Full Backup
```bash
# Backup all 4 databases
mysqldump --socket=/Applications/MAMP/tmp/mysql/mysql.sock -u root -proot \
  --databases mru_campus_dynamics \
              mru_campus_dynamics_accounts \
              mru_campus_dynamics_admissions \
              mru_campus_dynamics_portal \
  > backup_before_consolidation_$(date +%Y%m%d_%H%M%S).sql
```

#### 1.2 Row Count Verification
```bash
# Export row counts for all tables
mysql --socket=/Applications/MAMP/tmp/mysql/mysql.sock -u root -proot -e "
SELECT table_schema, table_name, table_rows 
FROM information_schema.tables 
WHERE table_schema LIKE 'mru_%' 
ORDER BY table_schema, table_name
" > pre_migration_row_counts.txt
```

### Phase 2: Database Creation

#### 2.1 Create Target Database
```sql
CREATE DATABASE mru_main 
  DEFAULT CHARACTER SET utf8 
  DEFAULT COLLATE utf8_general_ci;
```

### Phase 3: Schema Migration

#### 3.1 Generate CREATE TABLE Statements

For each table in each database, generate CREATE TABLE with new name:

```bash
# Campus Dynamics tables (122 tables)
for table in $(mysql --socket=/Applications/MAMP/tmp/mysql/mysql.sock -u root -proot -N -e \
  "SELECT table_name FROM information_schema.tables WHERE table_schema='mru_campus_dynamics'"); do
  
  # Get CREATE TABLE statement
  mysql --socket=/Applications/MAMP/tmp/mysql/mysql.sock -u root -proot -N -e \
    "SHOW CREATE TABLE mru_campus_dynamics.$table" | \
    sed "s/CREATE TABLE \`$table\`/CREATE TABLE \`mru_acad_$table\`/" \
    >> mru_main_schema.sql
    
  echo ";" >> mru_main_schema.sql
done

# Repeat for other databases with appropriate prefixes
```

#### 3.2 Import Schema to mru_main
```bash
mysql --socket=/Applications/MAMP/tmp/mysql/mysql.sock -u root -proot mru_main < mru_main_schema.sql
```

### Phase 4: Data Migration (In Chunks)

#### 4.1 Migration Order (by priority)

**Group 1: Reference/Lookup Tables (Small, low risk)**
- Banks, countries, faculties, campuses
- ~40 tables, <1,000 rows each

**Group 2: User & Authentication**
- my_aspnet_* tables
- ~13 unique tables + duplicates

**Group 3: Academic Data (Large tables)**
- Students, registrations, courses
- Handle acad_student (30,916 rows) first
- Then acad_registration, acad_results

**Group 4: Financial Data**
- Ledgers, fees, payments
- Handle large ledger tables carefully

**Group 5: HR/Payroll Data**
- Employee, payroll tables
- Sensitive data - verify encryption

#### 4.2 Data Copy Strategy

**For non-duplicate tables:**
```sql
-- Example: Copy student data
INSERT INTO mru_main.mru_acad_acad_student 
SELECT * FROM mru_campus_dynamics.acad_student;

-- Verify
SELECT 
  (SELECT COUNT(*) FROM mru_campus_dynamics.acad_student) as source_count,
  (SELECT COUNT(*) FROM mru_main.mru_acad_acad_student) as target_count;
```

**For duplicate tables:**
```sql
-- Example: Copy users from each database separately
INSERT INTO mru_main.mru_acad_my_aspnet_users 
SELECT * FROM mru_campus_dynamics.my_aspnet_users;

INSERT INTO mru_main.mru_accounts_my_aspnet_users 
SELECT * FROM mru_campus_dynamics_accounts.my_aspnet_users;

INSERT INTO mru_main.mru_portal_my_aspnet_users 
SELECT * FROM mru_campus_dynamics_portal.my_aspnet_users;
```

#### 4.3 Chunk Size Strategy

**Small tables (< 1,000 rows):** Single transaction  
**Medium tables (1,000 - 100,000 rows):** 10,000 rows per chunk  
**Large tables (> 100,000 rows):** 50,000 rows per chunk

**Example chunking for large table:**
```sql
-- Get total rows
SELECT @total := COUNT(*) FROM mru_campus_dynamics.acad_results;

-- Copy in chunks
SET @offset = 0;
SET @chunk_size = 50000;

WHILE @offset < @total DO
  INSERT INTO mru_main.mru_acad_acad_results
  SELECT * FROM mru_campus_dynamics.acad_results
  LIMIT @offset, @chunk_size;
  
  SET @offset = @offset + @chunk_size;
  
  -- Progress log
  SELECT CONCAT('Copied ', @offset, ' of ', @total, ' rows') as progress;
END WHILE;
```

### Phase 5: Verification

#### 5.1 Row Count Comparison
```sql
-- Compare all table row counts
SELECT 
  'mru_campus_dynamics' as source_db,
  table_name,
  table_rows as source_rows,
  (SELECT table_rows FROM information_schema.tables 
   WHERE table_schema='mru_main' 
   AND table_name=CONCAT('mru_acad_', t.table_name)) as target_rows
FROM information_schema.tables t
WHERE table_schema = 'mru_campus_dynamics';
```

#### 5.2 Data Sampling
```sql
-- Random sample verification
SELECT * FROM mru_campus_dynamics.acad_student ORDER BY RAND() LIMIT 10;
-- Compare with:
SELECT * FROM mru_main.mru_acad_acad_student ORDER BY RAND() LIMIT 10;
```

#### 5.3 Critical Data Checks
- Student count matches
- Fee totals match
- User accounts match
- Key relationships intact

### Phase 6: Cleanup

#### 6.1 Final Backup Before Deletion
```bash
# One more backup before dropping old databases
mysqldump --socket=/Applications/MAMP/tmp/mysql/mysql.sock -u root -proot \
  --databases mru_campus_dynamics \
              mru_campus_dynamics_accounts \
              mru_campus_dynamics_admissions \
              mru_campus_dynamics_portal \
  > final_backup_before_drop_$(date +%Y%m%d_%H%M%S).sql
```

#### 6.2 Drop Old Databases
```sql
-- Only after complete verification!
DROP DATABASE IF EXISTS mru_campus_dynamics;
DROP DATABASE IF EXISTS mru_campus_dynamics_accounts;
DROP DATABASE IF EXISTS mru_campus_dynamics_admissions;
DROP DATABASE IF EXISTS mru_campus_dynamics_portal;
```

---

## Risk Assessment

### High Risk Items

| Risk | Impact | Mitigation |
|------|--------|------------|
| Data loss during migration | CRITICAL | Multiple backups, chunk verification |
| Table name conflicts | HIGH | Database-prefixed naming strategy |
| Schema incompatibilities | MEDIUM | Test migrations on sample data first |
| Foreign key violations | MEDIUM | Disable FK checks during migration, re-enable after |
| Application breaking | CRITICAL | Update connection strings, test before deleting old DBs |

### Safety Measures

1. ✅ **Multiple Backups**
   - Before migration starts
   - After each phase
   - Before deleting old databases

2. ✅ **Verification at Each Step**
   - Row counts after each table
   - Sample data comparison
   - Critical business data validation

3. ✅ **Rollback Plan**
   - Keep old databases until verified
   - SQL dumps for quick restoration
   - Document rollback procedures

4. ✅ **Incremental Approach**
   - Migrate in small chunks
   - Verify each chunk
   - Don't proceed if verification fails

---

## Application Impact

### Required Code Changes

#### 1. Database Connection Updates

**Before:**
```php
'databases' => [
    'academics' => 'mru_campus_dynamics',
    'accounts' => 'mru_campus_dynamics_accounts',
    'admissions' => 'mru_campus_dynamics_admissions',
    'portal' => 'mru_campus_dynamics_portal',
]
```

**After:**
```php
'databases' => [
    'main' => 'mru_main',  // Single database
]
```

#### 2. Table Name Updates

All queries need table name updates:

**Before:**
```php
DB::connection('academics')->table('acad_student')->get();
DB::connection('accounts')->table('fin_ledger')->get();
```

**After:**
```php
DB::connection('main')->table('mru_acad_acad_student')->get();
DB::connection('main')->table('mru_accounts_fin_ledger')->get();
```

#### 3. Model Updates

All Eloquent models need `$table` property updates:

**Before:**
```php
class Student extends Model {
    protected $connection = 'academics';
    protected $table = 'acad_student';
}
```

**After:**
```php
class Student extends Model {
    protected $connection = 'main';
    protected $table = 'mru_acad_acad_student';
}
```

---

## Timeline Estimate

| Phase | Duration | Description |
|-------|----------|-------------|
| Planning & Documentation | ✅ Complete | This document |
| Backup & Preparation | 30 minutes | Full dumps, verification scripts |
| Schema Migration | 1-2 hours | Generate and import 298 table structures |
| Data Migration - Group 1 | 30 minutes | Reference tables |
| Data Migration - Group 2 | 1 hour | User/auth tables |
| Data Migration - Group 3 | 2-3 hours | Academic data (large tables) |
| Data Migration - Group 4 | 2-3 hours | Financial data (large tables) |
| Data Migration - Group 5 | 1 hour | HR/Payroll data |
| Verification | 2 hours | Complete validation |
| Application Updates | TBD | Depends on application size |
| Final Testing | 2 hours | End-to-end testing |
| Old DB Removal | 30 minutes | After verified working |
| **TOTAL** | **12-15 hours** | Plus application updates |

---

## Success Criteria

### Must Complete Before Proceeding

- [ ] All 298 tables created in mru_main
- [ ] All row counts match source databases
- [ ] Sample data verification passed
- [ ] Critical queries tested and working
- [ ] Backup files created and verified
- [ ] Rollback procedure documented and tested

### Before Deleting Old Databases

- [ ] Application fully tested with mru_main
- [ ] All features working correctly
- [ ] Final backup created
- [ ] Business stakeholders sign-off
- [ ] 48-hour monitoring period completed

---

## Alternative Approaches Considered

### Alternative 1: Keep Separate Databases
**Pros:** No migration needed, no risk  
**Cons:** Doesn't meet requirement for single database  
**Decision:** Rejected

### Alternative 2: Merge Duplicate Tables
**Pros:** Fewer tables in final database  
**Cons:** High risk of data loss, schema conflicts  
**Decision:** Rejected - too risky

### Alternative 3: Views Instead of Tables
**Pros:** Could keep old databases, create views in mru_main  
**Cons:** Performance overhead, doesn't meet "single DB" requirement  
**Decision:** Rejected

---

## Recommendations

### Before Starting

1. ✅ Review this plan with stakeholders
2. ⏳ Schedule migration during low-usage period
3. ⏳ Notify users of potential downtime
4. ⏳ Prepare rollback communication plan

### During Migration

1. Monitor each phase completion
2. Don't skip verification steps
3. Stop if any verification fails
4. Keep detailed logs of all operations

### After Migration

1. Monitor application performance
2. Watch for errors in logs
3. Keep old databases for 30 days
4. Update all documentation

---

## Next Steps

**User Decision Required:**

1. **Approve naming strategy?**
   - Option 1: Database prefixes (mru_acad_, mru_accounts_, etc.)
   - Option 2: Different prefix scheme
   
2. **Approve duplicate handling?**
   - Keep all separate with prefixes (recommended)
   - Attempt to merge after analysis
   
3. **Ready to proceed?**
   - Yes: Begin Phase 1 (Backup)
   - No: What concerns need addressing?

---

**Status:** 📋 AWAITING USER APPROVAL TO PROCEED

**Prepared by:** GitHub Copilot  
**Date:** December 19, 2025  
**Version:** 1.0
