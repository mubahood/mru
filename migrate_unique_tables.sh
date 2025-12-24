#!/bin/bash
#
# MRU Database Consolidation Migration Script
# Date: December 19, 2025
# Purpose: Migrate all 298 tables from 4 databases to mru_main
#

set -e  # Exit on error

# MySQL Connection
SOCKET="/Applications/MAMP/tmp/mysql/mysql.sock"
USER="root"
PASS="root"
MYSQL_CMD="mysql --socket=$SOCKET -u $USER -p$PASS"

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Counters
TABLES_MIGRATED=0
ROWS_MIGRATED=0
START_TIME=$(date +%s)

log() {
    echo -e "${BLUE}[$(date +%H:%M:%S)]${NC} $1"
}

success() {
    echo -e "${GREEN}✓${NC} $1"
}

error() {
    echo -e "${RED}✗${NC} $1"
}

# Function to migrate a single table
migrate_table() {
    local source_db=$1
    local source_table=$2
    local target_table=$3
    
    # Get row count
    local row_count=$($MYSQL_CMD -N -e "SELECT COUNT(*) FROM \`$source_db\`.\`$source_table\`" 2>/dev/null || echo "0")
    
    log "Migrating: $source_db.$source_table → mru_main.$target_table ($row_count rows)"
    
    # Create table structure
    $MYSQL_CMD -e "CREATE TABLE \`mru_main\`.\`$target_table\` LIKE \`$source_db\`.\`$source_table\`" 2>&1 | grep -v "Warning" || true
    
    # Copy data if table has rows
    if [ "$row_count" -gt 0 ]; then
        $MYSQL_CMD -e "INSERT INTO \`mru_main\`.\`$target_table\` SELECT * FROM \`$source_db\`.\`$source_table\`" 2>&1 | grep -v "Warning" || true
    fi
    
    # Verify
    local verify_count=$($MYSQL_CMD -N -e "SELECT COUNT(*) FROM \`mru_main\`.\`$target_table\`" 2>/dev/null || echo "0")
    
    if [ "$verify_count" = "$row_count" ]; then
        success "$target_table: $verify_count rows verified"
        TABLES_MIGRATED=$((TABLES_MIGRATED + 1))
        ROWS_MIGRATED=$((ROWS_MIGRATED + row_count))
    else
        error "$target_table: MISMATCH! Expected $row_count, got $verify_count"
        return 1
    fi
}

echo "========================================"
echo "MRU DATABASE CONSOLIDATION MIGRATION"
echo "========================================"
echo "Start Time: $(date)"
echo ""

log "Step 1: Get list of all tables to migrate..."

# Get all tables excluding duplicates
EXCLUDE_PATTERN="(my_aspnet_|hrm_|^banks$|^companyinfo$|^fin_expdates$|acad_results_complaints|^acad_results$)"

echo ""
log "Step 2: Migrating unique tables from mru_campus_dynamics..."
TABLES_ACAD=$($MYSQL_CMD -N -e "SHOW TABLES FROM mru_campus_dynamics" 2>/dev/null | grep -vE "$EXCLUDE_PATTERN" || true)

for table in $TABLES_ACAD; do
    # Create clean table name
    new_name="mru_$table"
    migrate_table "mru_campus_dynamics" "$table" "$new_name"
    
    # Progress update every 10 tables
    if [ $((TABLES_MIGRATED % 10)) -eq 0 ]; then
        echo ""
        log "Progress: $TABLES_MIGRATED tables, $ROWS_MIGRATED rows migrated"
        echo ""
    fi
done

echo ""
log "Step 3: Migrating unique tables from mru_campus_dynamics_accounts..."
TABLES_ACCOUNTS=$($MYSQL_CMD -N -e "SHOW TABLES FROM mru_campus_dynamics_accounts" 2>/dev/null | grep -vE "$EXCLUDE_PATTERN" || true)

for table in $TABLES_ACCOUNTS; do
    new_name="mru_$table"
    migrate_table "mru_campus_dynamics_accounts" "$table" "$new_name"
    
    if [ $((TABLES_MIGRATED % 10)) -eq 0 ]; then
        echo ""
        log "Progress: $TABLES_MIGRATED tables, $ROWS_MIGRATED rows migrated"
        echo ""
    fi
done

echo ""
log "Step 4: Migrating unique tables from mru_campus_dynamics_admissions..."
TABLES_ADMISSIONS=$($MYSQL_CMD -N -e "SHOW TABLES FROM mru_campus_dynamics_admissions" 2>/dev/null | grep -vE "$EXCLUDE_PATTERN" || true)

for table in $TABLES_ADMISSIONS; do
    new_name="mru_admissions_$table"
    migrate_table "mru_campus_dynamics_admissions" "$table" "$new_name"
done

echo ""
log "Step 5: Migrating unique tables from mru_campus_dynamics_portal..."
TABLES_PORTAL=$($MYSQL_CMD -N -e "SHOW TABLES FROM mru_campus_dynamics_portal" 2>/dev/null | grep -vE "$EXCLUDE_PATTERN" || true)

for table in $TABLES_PORTAL; do
    new_name="mru_portal_$table"
    migrate_table "mru_campus_dynamics_portal" "$table" "$new_name"
    
    if [ $((TABLES_MIGRATED % 10)) -eq 0 ]; then
        echo ""
        log "Progress: $TABLES_MIGRATED tables, $ROWS_MIGRATED rows migrated"
        echo ""
    fi
done

# Calculate elapsed time
END_TIME=$(date +%s)
ELAPSED=$((END_TIME - START_TIME))

echo ""
echo "========================================"
echo "PHASE 1 COMPLETE: UNIQUE TABLES MIGRATED"
echo "========================================"
echo "Tables Migrated: $TABLES_MIGRATED"
echo "Rows Migrated: $ROWS_MIGRATED"
echo "Time Elapsed: ${ELAPSED}s"
echo ""
echo "NEXT: Run merge script for duplicate tables"
echo "========================================"
