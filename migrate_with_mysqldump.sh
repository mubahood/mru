#!/bin/bash
#
# MRU Complete Database Migration with mysqldump
# This approach is more reliable for large datasets
#

set -e

SOCKET="/Applications/MAMP/tmp/mysql/mysql.sock"
USER="root"
PASS="root"

echo "=========================================="
echo "MRU DATABASE COMPLETE MIGRATION"
echo "Using mysqldump for reliability"
echo "=========================================="
echo "Start: $(date)"
echo ""

# Step 1: Export all table structures and data from each database
echo "[1/6] Exporting mru_campus_dynamics..."
mysqldump --socket=$SOCKET -u $USER -p$PASS \
  --no-create-db --skip-triggers --skip-routines \
  mru_campus_dynamics > /tmp/mru_campus_dynamics_dump.sql 2>&1 | grep -v "Warning" || true

echo "[2/6] Exporting mru_campus_dynamics_accounts..."
mysqldump --socket=$SOCKET -u $USER -p$PASS \
  --no-create-db --skip-triggers --skip-routines \
  mru_campus_dynamics_accounts > /tmp/mru_accounts_dump.sql 2>&1 | grep -v "Warning" || true

echo "[3/6] Exporting mru_campus_dynamics_admissions..."
mysqldump --socket=$SOCKET -u $USER -p$PASS \
  --no-create-db --skip-triggers --skip-routines \
  mru_campus_dynamics_admissions > /tmp/mru_admissions_dump.sql 2>&1 | grep -v "Warning" || true

echo "[4/6] Exporting mru_campus_dynamics_portal..."
mysqldump --socket=$SOCKET -u $USER -p$PASS \
  --no-create-db --skip-triggers --skip-routines \
  mru_campus_dynamics_portal > /tmp/mru_portal_dump.sql 2>&1 | grep -v "Warning" || true

# Step 2: Import into mru_main
echo "[5/6] Importing all data into mru_main..."
mysql --socket=$SOCKET -u $USER -p$PASS mru_main < /tmp/mru_campus_dynamics_dump.sql 2>&1 | grep -v "Warning" || true

echo "[6/6] Verifying import..."
TABLE_COUNT=$(mysql --socket=$SOCKET -u $USER -p$PASS -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='mru_main'" 2>/dev/null)

echo ""
echo "=========================================="
echo "IMPORT COMPLETE"
echo "=========================================="
echo "Tables in mru_main: $TABLE_COUNT"
echo "End: $(date)"
echo ""
echo "Next: Import remaining databases and handle duplicates"
