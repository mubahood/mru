# Laravel Admin Tables Import Documentation
**Date**: 2025-12-19  
**Target Database**: mru_main  
**Source File**: laraevl_admin_schools_db.sql

## Analysis Summary

### Tables Already Existing in mru_main (will be skipped)
1. academic_classes
2. academic_class_fees
3. academic_class_levels
4. academic_class_sctreams
5. academic_years
6. accounts
7. account_parents
8. activities
9. admin_menu

**Total Existing**: 9 tables

### New Laravel Admin Tables to Import
**Total New Tables**: 168 tables (excluding 2 log tables)

#### Core Laravel Admin Framework Tables
- admin_users
- admin_roles
- admin_permissions
- admin_role_menu
- admin_role_permissions
- admin_role_users
- admin_user_extensions
- admin_user_permissions

#### Application Tables (categorized)
**Authentication & Users**:
- users
- password_resets
- personal_access_tokens
- email_verification_tokens

**Academic Management**:
- assessment_sheets
- competences
- courses
- exams
- exam_has_classes
- grade_ranges
- grading_scales
- main_courses
- marks
- mark_records
- subjects
- subject_settings
- subject_teacher_remarks

**Student Management**:
- student_applications
- student_data_imports
- student_has_classes
- student_has_fees
- student_has_optional_subjects
- student_has_secondary_subjects
- student_has_semeters
- student_has_subject_old_curricula
- student_has_theology_classes
- student_optional_subject_pickers

**Report Cards**:
- report_card_prints
- report_comments
- report_finances
- student_report_cards
- student_report_card_items
- nursery_student_report_cards
- nursery_student_report_card_items
- nursery_termly_report_cards
- secondary_report_cards
- secondary_report_card_items
- secondary_termly_report_cards
- termly_report_cards
- termly_secondary_report_cards
- theologry_student_report_cards
- theology_student_report_card_items
- theology_termly_report_cards

**Theology Program**:
- generate_theology_classes
- theology_classes
- theology_courses
- theology_exams
- theology_exam_has_classes
- theology_marks
- theology_mark_records
- theology_streams
- theology_subjects

**Secondary Education**:
- secondary_competences
- secondary_subjects

**Financial Management**:
- bank_accounts
- credit_purchases
- deleted_transactions
- fee_deposit_confirmations
- fees_data_imports
- fees_data_import_records
- financial_records
- reconcilers
- school_fees_demands
- school_pay_hooks
- school_pay_transactions
- termly_school_fees_balancings
- transactions
- wallet_records

**Transport Management**:
- passenger_records
- transport_drivers
- transport_routes
- transport_stages
- transport_subscriptions
- transport_vehicles
- trips

**Library Management**:
- books
- books_categories
- book_authors
- book_borrows
- book_borrow_books

**Asset Management**:
- fixed_assets
- fixed_asset_categories
- fixed_asset_prints
- fixed_asset_records
- stock_batches
- stock_item_categories
- stock_records

**Facilities Management**:
- buildings
- rooms
- room_slots
- room_slot_allocations

**Communication**:
- bulk_messages
- bulk_photo_uploads
- bulk_photo_upload_items
- direct_messages
- support_messages

**Administrative**:
- bursaries
- bursary_beneficiaries
- class_teacher_comments
- companies
- demos
- disciplinary_records
- diseases
- documents
- enterprises
- head_teacher_comments
- identification_cards
- medical_records
- menu_items
- migrations
- on_board_wizards
- print_admission_letters
- promotions
- schem_work_items
- school_reports
- visitors
- visitor_records

**Service Management**:
- services
- service_categories
- service_items_to_be_offered
- service_subscriptions
- service_subscription_items
- batch_service_subscriptions

**Session Management**:
- sessions
- session_reports
- slots
- terms

**Supplier Management**:
- supplier_orders
- supplier_order_items
- supplier_products

**Parent Portal**:
- parent_courses
- participants

**Knowledge Base**:
- knowledge_base_articles
- knowledge_base_categories

**Content Management**:
- posts
- post_views

**Utilities**:
- data_exports
- failed_jobs
- fund_requisitions
- generic_skills
- gens
- import_school_pay_transactions
- university_programmes
- user_batch_importers
- _mark_has_classes

### Excluded Tables (Logs - as per requirement)
1. admin_operation_log
2. logs

## Import Strategy

### Step 1: Extract only NEW tables from SQL file
- Read original SQL file
- Filter out tables that already exist in mru_main
- Filter out log tables (admin_operation_log, logs)
- Replace database name from 'schools' to 'mru_main'

### Step 2: Import filtered SQL
- Use mysql command line import
- Target database: mru_main
- Verify no errors during import

### Step 3: Verification
- Count total tables after import
- Verify row counts of critical tables
- Check Laravel Admin core tables exist
- Verify data integrity

## Import Execution Log

### Pre-Import State
- mru_main total tables: 251

### Import Process
**Timestamp**: 2025-12-19 [In Progress]
**Command**: Filtered SQL import excluding existing and log tables

### Post-Import Verification
[Will be updated after import completion]

## Notes
- All Laravel Admin framework tables (admin_*) will be imported fresh
- Existing academic tables will be preserved from original MRU data
- This ensures no data loss while adding Laravel Admin functionality
