# MRU System - Quick Reference Guide

## 📊 System Overview at a Glance

```
╔══════════════════════════════════════════════════════════════╗
║  MUTESA I ROYAL UNIVERSITY MANAGEMENT SYSTEM                 ║
║  Laravel 8.54 | MySQL 5.7.44 | Laravel-Admin 1.x            ║
╚══════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────┐
│ SYSTEM METRICS                                               │
├──────────────────────────────────────────────────────────────┤
│ Total Users:              14,843                             │
│ ├─ Students:              14,346 (96.65%)                    │
│ └─ Employees:                497 (3.35%)                     │
│                                                              │
│ Database:                 mru_main                           │
│ Total Tables:             413                                │
│ Controllers:              149                                │
│ Models:                   170+                               │
│                                                              │
│ Student Records:          30,916                             │
│ Account Coverage:         46.4%                              │
│ Active Status:            100%                               │
└──────────────────────────────────────────────────────────────┘
```

---

## 🔑 Key Database Tables

### Users & Authentication
```
my_aspnet_users         - 14,843 users (90 columns)
admin_users             - Administrative accounts
admin_roles             - Role definitions
admin_permissions       - Permission matrix
admin_role_users        - User-role assignments
```

### Students
```
acad_student            - 30,916 student records
acad_courses            - Course catalog
acad_programmes         - Academic programs
acad_exam_results       - Examination records
acad_timetable          - Class schedules
```

### Finance
```
accounts                - Chart of accounts
billing_invoices        - Invoice management
payments                - Payment processing
financial_transactions  - All transactions
```

### Human Resources
```
hrm_staff               - Staff master data
hrm_employee            - Employee records
staff_attendance        - Attendance tracking
payroll                 - Salary processing
```

### Multi-tenancy
```
enterprises             - Institutional entities
```

---

## 🎯 User Classification

### Student Identification
```sql
-- Primary: By Registration Number
SELECT * FROM my_aspnet_users 
WHERE name IN (SELECT regno FROM acad_student);
-- Result: 14,345 students

-- Secondary: By Email
SELECT * FROM my_aspnet_users 
WHERE email IN (
    SELECT email FROM acad_student 
    WHERE email IS NOT NULL AND email != '' AND email != '-'
);
-- Result: 1 additional student
```

### Employee Identification
```sql
-- All non-students
SELECT * FROM my_aspnet_users 
WHERE user_type = 'employee';
-- Result: 497 employees
```

---

## 🔐 Authentication

### Password Systems

```php
// Laravel (Primary)
'password' => Hash::make($password)

// ASP.NET (Legacy)
'password_hash' => base64_encode(hash_pbkdf2('sha256', $password, $salt, 10000, 32, true))
'password_salt' => base64_encode(random_bytes(16))
```

### Login Guards

```php
// Web Guard (Students, Employees)
'web' => [
    'driver' => 'session',
    'provider' => 'users',
]

// Admin Guard (Administrators)
'admin' => [
    'driver' => 'session',
    'provider' => 'admin_users',
]
```

---

## 📝 Registration Number Formats

### Format 1: Standard (Most Common)
```
Pattern: YY/U/PROGRAM/NUMBER/CAMPUS/MODE

Examples:
24/U/BIT/0001/K/DAY       - 2024, BIT, Kampala, Day
25/U/BEICT/0097/K/DAY     - 2025, BEICT, Kampala, Day
24/U/BSAF/0001/K/WKD      - 2024, BSAF, Kampala, Weekend

Components:
YY        = Year (24, 25, 23...)
U         = Undergraduate
PROGRAM   = BIT, BEICT, BSAF, BCOM, BBA, BED, BTHM, BMC
NUMBER    = 0001-9999 (sequential)
CAMPUS    = K (Kampala), M (Mengo)
MODE      = DAY, WKD (Weekend), EVE (Evening)
```

### Format 2: MRU Code
```
Pattern: MRU + YEAR + NUMBER

Examples:
MRU2024000135
MRU2024000136
MRU2023000421

Components:
MRU       = University prefix
YEAR      = 2024, 2023, 2022...
NUMBER    = 000001-999999 (6-digit)
```

### Format 3: Program-Year
```
Pattern: YEAR + PROGRAM + MODE + -J + NUMBER

Examples:
2024BSAFDAY-J001
2024BITFT-J001
2024BITDay-J001

Components:
YEAR      = 2024, 2023...
PROGRAM   = BSAF, BIT, BBA, BCOM
MODE      = DAY, FT, WKD, EVE
J         = Joint/Junction program
NUMBER    = 001-999 (3-digit)
```

---

## 🔍 Common Queries

### Get all students
```sql
SELECT u.id, u.name, u.email, s.firstname, s.progid
FROM my_aspnet_users u
INNER JOIN acad_student s ON u.name = s.regno
WHERE u.user_type = 'student';
```

### Get all employees
```sql
SELECT id, name, email, created_at
FROM my_aspnet_users
WHERE user_type = 'employee';
```

### Students without accounts
```sql
SELECT s.regno, s.firstname, s.email
FROM acad_student s
WHERE s.regno NOT IN (SELECT name FROM my_aspnet_users);
-- Result: 16,570 students
```

### Check user classification
```sql
SELECT 
    user_type,
    COUNT(*) as count,
    ROUND(COUNT(*) * 100.0 / 14843, 2) as percentage
FROM my_aspnet_users
GROUP BY user_type;

-- Expected:
-- student  | 14346 | 96.65
-- employee |   497 |  3.35
```

### Verify active status
```sql
SELECT status, COUNT(*) 
FROM my_aspnet_users 
GROUP BY status;

-- Expected:
-- 1 | 14843
```

---

## 🛠️ Maintenance Scripts

### Update User Types
```bash
cd /Applications/MAMP/htdocs/mru
php update_user_types.php
```

**What it does:**
- Classifies 14,346 users as students
- Classifies 497 users as employees
- Sets all status to 1 (active)

### Create Student Accounts (Future)
```bash
php artisan create:student-accounts
```

**What it will do:**
- Create accounts for 16,570 students
- Generate temporary passwords
- Send email notifications

---

## 📊 Status Codes

### User Status
```
1 = Active
0 = Inactive
```

### User Types
```
'student'   = Student users (14,346)
'employee'  = Employee users (497)
```

### Enterprise IDs
```
1 = Mutesa I Royal University (MRU)
```

---

## 🔗 Key Relationships

```
User → Student
  my_aspnet_users.name = acad_student.regno (14,345 matches)
  my_aspnet_users.email = acad_student.email (1 match)

User → Enterprise
  my_aspnet_users.enterprise_id = enterprises.id (14,843 users)

Student → Program
  acad_student.progid = acad_programmes.id

Student → Enterprise
  acad_student.enterprise_id = enterprises.id (30,916 students)
```

---

## 📈 Coverage Statistics

```
╔════════════════════════════════════════════════════════════╗
║  STUDENT ACCOUNT COVERAGE                                  ║
╠════════════════════════════════════════════════════════════╣
║  Total Students in DB:        30,916 (100%)                ║
║  Students with Accounts:      14,346 (46.4%)               ║
║  Students without Accounts:   16,570 (53.6%)               ║
╚════════════════════════════════════════════════════════════╝

┌────────────────────────────────────────────────────────────┐
│ [████████████████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░] │
│                     46.4%                                  │
└────────────────────────────────────────────────────────────┘
```

---

## 🎯 Quick Commands

### Laravel Artisan
```bash
# Start development server
php artisan serve

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run migrations
php artisan migrate

# Enter tinker (REPL)
php artisan tinker

# Check routes
php artisan route:list
```

### Database
```bash
# Laravel Query Builder
DB::table('my_aspnet_users')->count();
DB::table('acad_student')->where('regno', 'LIKE', '24/U/%')->count();

# Check connections
DB::connection()->getPdo();
```

---

## 📍 Important Paths

### Application
```
/Applications/MAMP/htdocs/mru/              - Root directory
/Applications/MAMP/htdocs/mru/app/          - Application code
/Applications/MAMP/htdocs/mru/app/Admin/    - Laravel-Admin
/Applications/MAMP/htdocs/mru/config/       - Configuration
/Applications/MAMP/htdocs/mru/database/     - Migrations, seeders
```

### Documentation
```
/Applications/MAMP/htdocs/mru/docs/                      - Documentation
/Applications/MAMP/htdocs/mru/docs/MRU_SYSTEM_ANALYSIS.md
/Applications/MAMP/htdocs/mru/docs/USER_CLASSIFICATION_TECHNICAL.md
/Applications/MAMP/htdocs/mru/docs/QUICK_REFERENCE.md
```

### Scripts
```
/Applications/MAMP/htdocs/mru/update_user_types.php
```

---

## 🚦 System Status

### ✅ Completed
- [x] User type classification (14,843 users)
- [x] Multi-tenancy implementation
- [x] Database AUTO_INCREMENT fixes
- [x] Status field updates (100% active)
- [x] System documentation

### 📋 Pending
- [ ] Create accounts for 16,570 students
- [ ] Email validation improvements
- [ ] Employee-staff linkage
- [ ] Performance optimization
- [ ] Security hardening

---

## ⚡ Quick Checks

### Verify System Health
```sql
-- Total users
SELECT COUNT(*) FROM my_aspnet_users;
-- Expected: 14,843

-- User distribution
SELECT user_type, COUNT(*) FROM my_aspnet_users GROUP BY user_type;
-- Expected: student=14,346, employee=497

-- Active status
SELECT COUNT(*) FROM my_aspnet_users WHERE status = 1;
-- Expected: 14,843

-- Enterprise assignment
SELECT COUNT(*) FROM my_aspnet_users WHERE enterprise_id = 1;
-- Expected: 14,843
```

### Verify Classification
```sql
-- Students by regno
SELECT COUNT(*) 
FROM my_aspnet_users u
INNER JOIN acad_student s ON u.name = s.regno;
-- Expected: 14,345

-- Total classified students
SELECT COUNT(*) FROM my_aspnet_users WHERE user_type = 'student';
-- Expected: 14,346

-- Total classified employees
SELECT COUNT(*) FROM my_aspnet_users WHERE user_type = 'employee';
-- Expected: 497
```

---

## 📞 Support

### Issue Resolution
1. Check logs: `storage/logs/laravel.log`
2. Verify database connection: `php artisan tinker`
3. Clear cache: `php artisan cache:clear`
4. Check .env configuration
5. Verify permissions: `chmod -R 755 storage bootstrap/cache`

### Common Issues

**Issue:** Login fails  
**Solution:** Check password hash, verify user status, clear cache

**Issue:** Wrong user type  
**Solution:** Run `php update_user_types.php`

**Issue:** Database connection error  
**Solution:** Check .env file, verify MySQL is running

---

## 📚 Additional Resources

### Documentation
- [MRU System Analysis](docs/MRU_SYSTEM_ANALYSIS.md)
- [User Classification Technical](docs/USER_CLASSIFICATION_TECHNICAL.md)
- [Laravel 8 Documentation](https://laravel.com/docs/8.x)
- [Laravel-Admin Documentation](https://laravel-admin.org/docs)

### Database
- Database: `mru_main`
- User: `root`
- Password: `root`
- Host: `127.0.0.1`
- Port: `3306`

---

## ✅ System Verification Checklist

```
✅ Database accessible
✅ 14,843 users in system
✅ 14,346 students classified (96.65%)
✅ 497 employees classified (3.35%)
✅ All users status = 1 (active)
✅ All users enterprise_id = 1
✅ 30,916 student records in acad_student
✅ 46.4% student-account coverage
✅ AUTO_INCREMENT values corrected
✅ Multi-tenancy implemented
✅ Documentation complete
```

---

**Document Version:** 1.0  
**Last Updated:** December 2024  
**System Status:** ✅ Production Ready  

---

*Keep this guide handy for quick reference and troubleshooting.*
