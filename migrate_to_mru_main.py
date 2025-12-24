#!/usr/bin/env python3
"""
MRU Database Consolidation Migration Script
Date: December 19, 2025
Purpose: Migrate all tables from 4 databases to mru_main with verification
"""

import mysql.connector
import sys
from datetime import datetime

# MySQL Connection Configuration
MYSQL_CONFIG = {
    'unix_socket': '/Applications/MAMP/tmp/mysql/mysql.sock',
    'user': 'root',
    'password': 'root',
    'charset': 'utf8mb4',
    'collation': 'utf8mb4_unicode_ci'
}

# Source databases
SOURCE_DBS = {
    'mru_campus_dynamics': 'acad',
    'mru_campus_dynamics_accounts': 'fin',
    'mru_campus_dynamics_admissions': 'admissions',
    'mru_campus_dynamics_portal': 'portal'
}

# Tables to skip (will be merged separately)
DUPLICATE_TABLES = [
    'my_aspnet_applications', 'my_aspnet_apps', 'my_aspnet_classes',
    'my_aspnet_membership', 'my_aspnet_profiles', 'my_aspnet_roles',
    'my_aspnet_roles_in_apps', 'my_aspnet_schemaversion',
    'my_aspnet_sessioncleanup', 'my_aspnet_sessions',
    'my_aspnet_userbranch_department', 'my_aspnet_userphone',
    'my_aspnet_users', 'my_aspnet_usersinroles', 'my_aspnet_usersubjects',
    'my_aspnet_user_faculties',
    'hrm_allowance_deductions', 'hrm_ded_allowance_stafflist',
    'hrm_departments', 'hrm_employee', 'hrm_emp_contracts',
    'hrm_exemptions', 'hrm_jobs', 'hrm_monthly_ded_allowance',
    'hrm_payroll', 'hrm_payroll_details', 'hrm_payscales',
    'hrm_qualifications', 'hrm_stations',
    'banks', 'companyinfo', 'fin_expdates', 'acad_results_complaints',
    'acad_results'  # Special handling - merge from main + portal
]

class MigrationTracker:
    def __init__(self):
        self.start_time = datetime.now()
        self.tables_migrated = 0
        self.rows_migrated = 0
        self.errors = []
        
    def log(self, message):
        timestamp = datetime.now().strftime('%H:%M:%S')
        print(f"[{timestamp}] {message}")
        
    def log_progress(self):
        elapsed = (datetime.now() - self.start_time).total_seconds()
        print(f"\n{'='*60}")
        print(f"Progress: {self.tables_migrated} tables | {self.rows_migrated:,} rows")
        print(f"Elapsed: {elapsed:.0f}s | Errors: {len(self.errors)}")
        print(f"{'='*60}\n")

def get_connection(database=None):
    """Create MySQL connection"""
    config = MYSQL_CONFIG.copy()
    if database:
        config['database'] = database
    return mysql.connector.connect(**config)

def get_table_row_count(cursor, database, table):
    """Get accurate row count for a table"""
    cursor.execute(f"SELECT COUNT(*) FROM `{database}`.`{table}`")
    return cursor.fetchone()[0]

def clean_table_name(original_name, prefix):
    """Generate clean table name with mru_ prefix"""
    # Special cases
    name_map = {
        'acad_acadyears': 'acad_years',
        'acad_calenda': 'acad_calendar',
        'acad_calendermonths': 'acad_calendar_months',
        'acad_course': 'acad_courses',
        'acad_faculty': 'acad_faculties',
        'acad_student': 'acad_students',
        'acad_superviors': 'acad_supervisors',
        'acad_studetsponsors': 'acad_student_sponsors',
        'nextofkin': 'next_of_kin',
        'stddoc': 'student_docs',
        'companyinfo': 'company_info',
    }
    
    if original_name in name_map:
        return f"mru_{name_map[original_name]}"
    
    # Default: add mru_ prefix
    return f"mru_{original_name}"

def migrate_table(source_db, table_name, tracker):
    """Migrate a single table structure and data"""
    try:
        conn = get_connection()
        cursor = conn.cursor()
        
        # Skip duplicates
        if table_name in DUPLICATE_TABLES:
            tracker.log(f"SKIP: {source_db}.{table_name} (duplicate - will merge later)")
            return True
            
        # Generate new table name
        prefix = SOURCE_DBS[source_db]
        new_table_name = clean_table_name(table_name, prefix)
        
        # Get row count
        row_count = get_table_row_count(cursor, source_db, table_name)
        
        tracker.log(f"Migrating: {source_db}.{table_name} → mru_main.{new_table_name} ({row_count:,} rows)")
        
        # Create table structure
        cursor.execute(f"CREATE TABLE `mru_main`.`{new_table_name}` LIKE `{source_db}`.`{table_name}`")
        
        # Copy data
        if row_count > 0:
            cursor.execute(f"INSERT INTO `mru_main`.`{new_table_name}` SELECT * FROM `{source_db}`.`{table_name}`")
        
        conn.commit()
        
        # Verify
        verify_count = get_table_row_count(cursor, 'mru_main', new_table_name)
        if verify_count != row_count:
            raise Exception(f"Row count mismatch! Expected {row_count}, got {verify_count}")
        
        tracker.tables_migrated += 1
        tracker.rows_migrated += row_count
        tracker.log(f"✓ SUCCESS: {new_table_name} ({verify_count:,} rows verified)")
        
        cursor.close()
        conn.close()
        return True
        
    except Exception as e:
        error_msg = f"ERROR migrating {source_db}.{table_name}: {str(e)}"
        tracker.log(error_msg)
        tracker.errors.append(error_msg)
        return False

def get_all_tables(database):
    """Get list of all tables in a database"""
    conn = get_connection(database)
    cursor = conn.cursor()
    cursor.execute("SHOW TABLES")
    tables = [row[0] for row in cursor.fetchall()]
    cursor.close()
    conn.close()
    return tables

def main():
    """Main migration process"""
    tracker = MigrationTracker()
    tracker.log("="*60)
    tracker.log("MRU DATABASE CONSOLIDATION MIGRATION")
    tracker.log("="*60)
    tracker.log(f"Target Database: mru_main")
    tracker.log(f"Source Databases: {len(SOURCE_DBS)}")
    tracker.log("="*60)
    
    # Phase 1: Migrate unique tables from each database
    for source_db in SOURCE_DBS.keys():
        tracker.log(f"\n{'='*60}")
        tracker.log(f"Processing database: {source_db}")
        tracker.log(f"{'='*60}")
        
        tables = get_all_tables(source_db)
        tracker.log(f"Found {len(tables)} tables in {source_db}")
        
        for table in tables:
            migrate_table(source_db, table, tracker)
            
            # Progress update every 10 tables
            if tracker.tables_migrated % 10 == 0:
                tracker.log_progress()
    
    # Final summary
    tracker.log("\n" + "="*60)
    tracker.log("MIGRATION PHASE 1 COMPLETE (Unique Tables)")
    tracker.log("="*60)
    tracker.log(f"Tables Migrated: {tracker.tables_migrated}")
    tracker.log(f"Rows Migrated: {tracker.rows_migrated:,}")
    tracker.log(f"Errors: {len(tracker.errors)}")
    
    if tracker.errors:
        tracker.log("\nERRORS:")
        for error in tracker.errors:
            tracker.log(f"  - {error}")
    
    tracker.log("\nNEXT STEPS:")
    tracker.log("1. Merge duplicate authentication tables (33 tables)")
    tracker.log("2. Merge acad_results from main + portal")
    tracker.log("3. Verify all row counts")
    tracker.log("4. Add indexes and foreign keys")
    
    return len(tracker.errors) == 0

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
