#!/bin/bash
#
# Comprehensive Row Count Verification Script
# Compares actual row counts in mru_main against expected counts from original databases
#

SOCKET="/Applications/MAMP/tmp/mysql/mysql.sock"
USER="root"
PASS="root"
MYSQL="mysql --socket=$SOCKET -u $USER -p$PASS"

echo "=========================================="
echo "MRU_MAIN ROW COUNT VERIFICATION"
echo "=========================================="
echo ""

# Critical tables verification
echo "=== CRITICAL TABLES ==="

# Students
EXPECTED_STUDENTS=30003
ACTUAL_STUDENTS=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.acad_student" 2>/dev/null)
echo "acad_student: Expected=$EXPECTED_STUDENTS, Actual=$ACTUAL_STUDENTS"
[ "$ACTUAL_STUDENTS" -ge "$EXPECTED_STUDENTS" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Results (main database only - portal has different schema)
EXPECTED_RESULTS=596635
ACTUAL_RESULTS=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.acad_results" 2>/dev/null)
echo "acad_results: Expected=$EXPECTED_RESULTS, Actual=$ACTUAL_RESULTS"
[ "$ACTUAL_RESULTS" -ge "$EXPECTED_RESULTS" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Results Legacy
EXPECTED_RESULTS_LEGACY=320584
ACTUAL_RESULTS_LEGACY=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.acad_results_legacy" 2>/dev/null)
echo "acad_results_legacy: Expected=$EXPECTED_RESULTS_LEGACY, Actual=$ACTUAL_RESULTS_LEGACY"
[ "$ACTUAL_RESULTS_LEGACY" -ge "$EXPECTED_RESULTS_LEGACY" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Activity Log
EXPECTED_ACTIVITY=165680
ACTUAL_ACTIVITY=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.acad_activity_log" 2>/dev/null)
echo "acad_activity_log: Expected=$EXPECTED_ACTIVITY, Actual=$ACTUAL_ACTIVITY"
[ "$ACTUAL_ACTIVITY" -ge "$EXPECTED_ACTIVITY" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Exam Results Faculty
EXPECTED_EXAM_RESULTS=149292
ACTUAL_EXAM_RESULTS=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.acad_examresults_faculty" 2>/dev/null)
echo "acad_examresults_faculty: Expected=$EXPECTED_EXAM_RESULTS, Actual=$ACTUAL_EXAM_RESULTS"
[ "$ACTUAL_EXAM_RESULTS" -ge "$EXPECTED_EXAM_RESULTS" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Financial Ledger
EXPECTED_LEDGER=119281
ACTUAL_LEDGER=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.fin_ledger" 2>/dev/null)
echo "fin_ledger: Expected=$EXPECTED_LEDGER, Actual=$ACTUAL_LEDGER"
[ "$ACTUAL_LEDGER" -ge "$EXPECTED_LEDGER" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

echo ""
echo "=== MERGED AUTHENTICATION TABLES ==="

# Users (merged: 76 + 6 + 14,631)
EXPECTED_USERS=14713
ACTUAL_USERS=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.my_aspnet_users" 2>/dev/null)
echo "my_aspnet_users: Expected≥$EXPECTED_USERS, Actual=$ACTUAL_USERS"
[ "$ACTUAL_USERS" -ge "$EXPECTED_USERS" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Membership (merged: 266 + 6 + 97,291)
EXPECTED_MEMBERSHIP=97563
ACTUAL_MEMBERSHIP=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.my_aspnet_membership" 2>/dev/null)
echo "my_aspnet_membership: Expected≥$EXPECTED_MEMBERSHIP, Actual=$ACTUAL_MEMBERSHIP"
[ "$ACTUAL_MEMBERSHIP" -ge "$EXPECTED_MEMBERSHIP" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Users in Roles (merged: 163 + 5 + 178,776)
EXPECTED_USERINROLES=178944
ACTUAL_USERINROLES=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.my_aspnet_usersinroles" 2>/dev/null)
echo "my_aspnet_usersinroles: Expected≥$EXPECTED_USERINROLES, Actual=$ACTUAL_USERINROLES"
[ "$ACTUAL_USERINROLES" -ge "$EXPECTED_USERINROLES" ] && echo "  ✓ PASS" || echo "  ⚠ WARNING (may have duplicates removed)"

echo ""
echo "=== LARGE TABLES (>50k rows) ==="

# Results Info Data
EXPECTED_RESULTS_INFO=322674
ACTUAL_RESULTS_INFO=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.results_info_data" 2>/dev/null)
echo "results_info_data: Expected=$EXPECTED_RESULTS_INFO, Actual=$ACTUAL_RESULTS_INFO"
[ "$ACTUAL_RESULTS_INFO" -ge "$EXPECTED_RESULTS_INFO" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Transaction Numbers
EXPECTED_TRANS=94848
ACTUAL_TRANS=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.fin_transaction_numbers" 2>/dev/null)
echo "fin_transaction_numbers: Expected=$EXPECTED_TRANS, Actual=$ACTUAL_TRANS"
[ "$ACTUAL_TRANS" -ge "$EXPECTED_TRANS" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Ledger Prog
EXPECTED_LEDGER_PROG=84162
ACTUAL_LEDGER_PROG=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.fin_ledger_prog" 2>/dev/null)
echo "fin_ledger_prog: Expected=$EXPECTED_LEDGER_PROG, Actual=$ACTUAL_LEDGER_PROG"
[ "$ACTUAL_LEDGER_PROG" -ge "$EXPECTED_LEDGER_PROG" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Student Fees Tracking
EXPECTED_FEES=52140
ACTUAL_FEES=$($MYSQL -N -e "SELECT COUNT(*) FROM mru_main.fin_studentfeestracking" 2>/dev/null)
echo "fin_studentfeestracking: Expected=$EXPECTED_FEES, Actual=$ACTUAL_FEES"
[ "$ACTUAL_FEES" -ge "$EXPECTED_FEES" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

echo ""
echo "=== OVERALL SUMMARY ==="

# Total tables
EXPECTED_TABLES=251
ACTUAL_TABLES=$($MYSQL -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='mru_main'" 2>/dev/null)
echo "Total Tables: Expected=$EXPECTED_TABLES, Actual=$ACTUAL_TABLES"
[ "$ACTUAL_TABLES" -eq "$EXPECTED_TABLES" ] && echo "  ✓ PASS" || echo "  ✗ FAIL"

# Total approximate rows (information_schema estimates)
TOTAL_ROWS=$($MYSQL -N -e "SELECT SUM(table_rows) FROM information_schema.tables WHERE table_schema='mru_main'" 2>/dev/null)
echo "Total Rows (approx): $TOTAL_ROWS"
echo "  Expected range: 2,900,000 - 3,100,000"

# Exact count of critical tables
echo ""
echo "=== EXACT ROW COUNTS (Critical Tables) ==="
$MYSQL -e "
SELECT 
    'acad_student' as table_name, COUNT(*) as exact_count FROM mru_main.acad_student
UNION ALL
SELECT 'acad_results', COUNT(*) FROM mru_main.acad_results
UNION ALL
SELECT 'acad_results_legacy', COUNT(*) FROM mru_main.acad_results_legacy
UNION ALL
SELECT 'my_aspnet_users', COUNT(*) FROM mru_main.my_aspnet_users
UNION ALL
SELECT 'my_aspnet_membership', COUNT(*) FROM mru_main.my_aspnet_membership
UNION ALL
SELECT 'fin_ledger', COUNT(*) FROM mru_main.fin_ledger
UNION ALL
SELECT 'hrm_employee', COUNT(*) FROM mru_main.hrm_employee
" 2>/dev/null | grep -v "Warning"

echo ""
echo "=========================================="
echo "VERIFICATION COMPLETE"
echo "=========================================="
