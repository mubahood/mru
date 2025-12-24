# MRU Database Consolidation - Table Migration Mapping

**Date:** December 19, 2025  
**Purpose:** Complete mapping of all tables from 4 databases to single `mru_main` database  
**Total Tables:** 298  
**Total Rows:** To be calculated

---

## MIGRATION VERIFICATION CHECKLIST

Use this document to verify that ALL data has been migrated successfully.

### How to Use This Document:
1. **Before Migration:** Record current row counts (✅ Done)
2. **After Migration:** Run verification queries to confirm row counts match
3. **Check off** each table after verification
4. **Document** any discrepancies immediately

---

## DATABASE 1: mru_campus_dynamics → mru_main
**Total Tables:** 122  
**Naming Convention:** Prefix with `mru_acad_` for academic tables, `mru_sys_` for system tables

| # | Original Table Name | New Table Name | Current Rows | Migrated Rows | Status | Notes |
|---|---------------------|----------------|--------------|---------------|--------|-------|
| 1 | acads | mru_sys_acads | 0 | - | ⏳ Pending | Empty table |
| 2 | acad_acadyears | mru_acad_years | 26 | - | ⏳ Pending | Academic years config |
| 3 | acad_activity_log | mru_acad_activity_log | 165,680 | - | ⏳ Pending | Large - activity tracking |
| 4 | acad_admissionletterfees | mru_acad_admission_letter_fees | 0 | - | ⏳ Pending | Empty table |
| 5 | acad_admissionletters | mru_acad_admission_letters | 2 | - | ⏳ Pending | |
| 6 | acad_applicant_choices | mru_acad_applicant_choices | 4,372 | - | ⏳ Pending | |
| 7 | acad_applicant_occupations | mru_acad_applicant_occupations | 9 | - | ⏳ Pending | |
| 8 | acad_applicant_performance | mru_acad_applicant_performance | 56 | - | ⏳ Pending | |
| 9 | acad_applicant_subjects | mru_acad_applicant_subjects | 78 | - | ⏳ Pending | |
| 10 | acad_applications | mru_acad_applications | 4,126 | - | ⏳ Pending | |
| 11 | acad_applic_settings | mru_acad_applic_settings | 0 | - | ⏳ Pending | Empty table |
| 12 | acad_bridgequalification | mru_acad_bridge_qualification | 138 | - | ⏳ Pending | |
| 13 | acad_calenda | mru_acad_calendar | 2 | - | ⏳ Pending | |
| 14 | acad_calendermonths | mru_acad_calendar_months | 12 | - | ⏳ Pending | |
| 15 | acad_campuses | mru_acad_campuses | 3 | - | ⏳ Pending | |
| 16 | acad_course | mru_acad_courses | 6,789 | - | ⏳ Pending | Large - course definitions |
| 17 | acad_coursework_settings_ratios | mru_acad_coursework_settings_ratios | 0 | - | ⏳ Pending | Empty table |
| 18 | acad_coursework_timetable | mru_acad_coursework_timetable | 12 | - | ⏳ Pending | |
| 19 | acad_curriculum | mru_acad_curriculum | 142 | - | ⏳ Pending | |
| 20 | acad_deadlineitems | mru_acad_deadline_items | 3 | - | ⏳ Pending | |
| 21 | acad_deadlines | mru_acad_deadlines | 9 | - | ⏳ Pending | |
| 22 | acad_event_days | mru_acad_event_days | 0 | - | ⏳ Pending | Empty table |
| 23 | acad_examresults_faculty | mru_acad_exam_results_faculty | 149,292 | - | ⏳ Pending | Large - exam results |
| 24 | acad_examresults_faculty_settings | mru_acad_exam_results_faculty_settings | 12,264 | - | ⏳ Pending | |
| 25 | acad_exam_timetable | mru_acad_exam_timetable | 437 | - | ⏳ Pending | |
| 26 | acad_faculty | mru_acad_faculties | 6 | - | ⏳ Pending | Faculty list |
| 27 | acad_failed_passes | mru_acad_failed_passes | 19,312 | - | ⏳ Pending | |
| 28 | acad_gradingsystem | mru_acad_grading_system | 0 | - | ⏳ Pending | Empty table |
| 29 | acad_gradingsystemcomments | mru_acad_grading_system_comments | 8 | - | ⏳ Pending | |
| 30 | acad_graduands | mru_acad_graduands | 873 | - | ⏳ Pending | |
| 31 | acad_graduate_research | mru_acad_graduate_research | 0 | - | ⏳ Pending | Empty table |
| 32 | acad_gs_award | mru_acad_gs_award | 18 | - | ⏳ Pending | |
| 33 | acad_gs_details | mru_acad_gs_details | 45 | - | ⏳ Pending | |
| 34 | acad_halls | mru_acad_halls | 0 | - | ⏳ Pending | Empty table |
| 35 | acad_hall_assigner | mru_acad_hall_assigner | 2 | - | ⏳ Pending | |
| 36 | acad_haltcases | mru_acad_halt_cases | 40 | - | ⏳ Pending | |
| 37 | acad_lecturerooms | mru_acad_lecture_rooms | 47 | - | ⏳ Pending | |
| 38 | acad_otherqualifications | mru_acad_other_qualifications | 63 | - | ⏳ Pending | |
| 39 | acad_passrates | mru_acad_pass_rates | 6 | - | ⏳ Pending | |
| 40 | acad_programme | mru_acad_programmes | 128 | - | ⏳ Pending | |
| 41 | acad_programmecourses | mru_acad_programme_courses | 3,834 | - | ⏳ Pending | |
| 42 | acad_programme_creditunits | mru_acad_programme_credit_units | 30 | - | ⏳ Pending | |
| 43 | acad_programme_entry_types | mru_acad_programme_entry_types | 9 | - | ⏳ Pending | |
| 44 | acad_programme_resultsratios | mru_acad_programme_results_ratios | 0 | - | ⏳ Pending | Empty table |
| 45 | acad_registration | mru_acad_registration | 23,359 | - | ⏳ Pending | |
| 46 | acad_research_events | mru_acad_research_events | 0 | - | ⏳ Pending | Empty table |
| 47 | acad_research_progress | mru_acad_research_progress | 0 | - | ⏳ Pending | Empty table |
| 48 | acad_residence | mru_acad_residence | 0 | - | ⏳ Pending | Empty table |
| 49 | acad_results | mru_acad_results_main | 596,635 | - | ⏳ Pending | **CRITICAL** - Largest table |
| 50 | acad_results1 | mru_acad_results1 | 0 | - | ⏳ Pending | Empty table |
| 51 | acad_resultsupdates | mru_acad_results_updates | 554 | - | ⏳ Pending | |
| 52 | acad_results_complaints | mru_acad_results_complaints_main | 0 | - | ⏳ Pending | Empty - DUPLICATE |
| 53 | acad_results_legacy | mru_acad_results_legacy | 320,584 | - | ⏳ Pending | Large - legacy results |
| 54 | acad_results_securitylevel | mru_acad_results_security_level | 1,589 | - | ⏳ Pending | |
| 55 | acad_specialisation | mru_acad_specialisation | 115 | - | ⏳ Pending | |
| 56 | acad_specialisations | mru_acad_specialisations | 10 | - | ⏳ Pending | |
| 57 | acad_student | mru_acad_students | 30,003 | - | ⏳ Pending | **CRITICAL** - Main student table |
| 58 | acad_studentby_day | mru_acad_student_by_day | 0 | - | ⏳ Pending | Empty table |
| 59 | acad_student_cards | mru_acad_student_cards | 12,938 | - | ⏳ Pending | |
| 60 | acad_student_legacy | mru_acad_student_legacy | 14,280 | - | ⏳ Pending | |
| 61 | acad_studetsponsors | mru_acad_student_sponsors | 0 | - | ⏳ Pending | Empty table |
| 62 | acad_studysessions | mru_acad_study_sessions | 4 | - | ⏳ Pending | |
| 63 | acad_stud_nms | mru_acad_stud_nms | 0 | - | ⏳ Pending | Empty table |
| 64 | acad_summary_dean_vc_list_settings | mru_acad_summary_dean_vc_list_settings | 2 | - | ⏳ Pending | |
| 65 | acad_superviors | mru_acad_supervisors | 0 | - | ⏳ Pending | Empty table |
| 66 | acad_teaching_allocation | mru_acad_teaching_allocation | 17,514 | - | ⏳ Pending | |
| 67 | acad_teaching_allocation_for_registration | mru_acad_teaching_allocation_for_registration | 931 | - | ⏳ Pending | |
| 68 | acad_tempdata | mru_acad_temp_data | 0 | - | ⏳ Pending | Empty table |
| 69 | acad_timetable_weekdays | mru_acad_timetable_weekdays | 8 | - | ⏳ Pending | |
| 70 | acad_transcript_format | mru_acad_transcript_format | 124 | - | ⏳ Pending | |
| 71 | acad_transcript_format_detail | mru_acad_transcript_format_detail | 3 | - | ⏳ Pending | |
| 72 | acad_transcript_results | mru_acad_transcript_results | 32,795 | - | ⏳ Pending | |
| 73 | acad_university | mru_acad_university | 0 | - | ⏳ Pending | Empty table |
| 74 | badregno | mru_sys_bad_regno | 0 | - | ⏳ Pending | Empty table |
| 75 | banks | mru_sys_banks_main | 21 | - | ⏳ Pending | DUPLICATE |
| 76 | companyinfo | mru_sys_company_info_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 77 | countries | mru_sys_countries | 249 | - | ⏳ Pending | |
| 78 | country | mru_sys_country | 240 | - | ⏳ Pending | |
| 79 | fin_expdates | mru_fin_expdates_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 80 | hrm_allowance_deductions | mru_hrm_allowance_deductions_main | 14 | - | ⏳ Pending | DUPLICATE |
| 81 | hrm_annual_leave | mru_hrm_annual_leave | 617 | - | ⏳ Pending | |
| 82 | hrm_ded_allowance_stafflist | mru_hrm_ded_allowance_stafflist_main | 1,510 | - | ⏳ Pending | DUPLICATE |
| 83 | hrm_departments | mru_hrm_departments_main | 18 | - | ⏳ Pending | DUPLICATE |
| 84 | hrm_employee | mru_hrm_employee_main | 296 | - | ⏳ Pending | DUPLICATE |
| 85 | hrm_emp_contracts | mru_hrm_emp_contracts_main | 283 | - | ⏳ Pending | DUPLICATE |
| 86 | hrm_exemptions | mru_hrm_exemptions_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 87 | hrm_jobs | mru_hrm_jobs_main | 115 | - | ⏳ Pending | DUPLICATE |
| 88 | hrm_leave_taken | mru_hrm_leave_taken | 15 | - | ⏳ Pending | |
| 89 | hrm_monthly_ded_allowance | mru_hrm_monthly_ded_allowance_main | 314 | - | ⏳ Pending | DUPLICATE |
| 90 | hrm_part_time_rates | mru_hrm_part_time_rates | 20 | - | ⏳ Pending | |
| 91 | hrm_payroll | mru_hrm_payroll_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 92 | hrm_payroll_details | mru_hrm_payroll_details_main | 463 | - | ⏳ Pending | DUPLICATE |
| 93 | hrm_payscales | mru_hrm_payscales_main | 7 | - | ⏳ Pending | DUPLICATE |
| 94 | hrm_qualifications | mru_hrm_qualifications_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 95 | hrm_special_payments | mru_hrm_special_payments | 43 | - | ⏳ Pending | |
| 96 | hrm_staff | mru_hrm_staff | 0 | - | ⏳ Pending | Empty table |
| 97 | hrm_stations | mru_hrm_stations_main | 2 | - | ⏳ Pending | DUPLICATE |
| 98 | my_aspnet_applications | mru_auth_aspnet_applications_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 99 | my_aspnet_apps | mru_auth_aspnet_apps_main | 16 | - | ⏳ Pending | DUPLICATE |
| 100 | my_aspnet_classes | mru_auth_aspnet_classes_main | 6 | - | ⏳ Pending | DUPLICATE |
| 101 | my_aspnet_membership | mru_auth_aspnet_membership_main | 266 | - | ⏳ Pending | DUPLICATE |
| 102 | my_aspnet_profiles | mru_auth_aspnet_profiles_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 103 | my_aspnet_roles | mru_auth_aspnet_roles_main | 26 | - | ⏳ Pending | DUPLICATE |
| 104 | my_aspnet_roles_in_apps | mru_auth_aspnet_roles_in_apps_main | 90 | - | ⏳ Pending | DUPLICATE |
| 105 | my_aspnet_schemaversion | mru_auth_aspnet_schemaversion_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 106 | my_aspnet_sessioncleanup | mru_auth_aspnet_sessioncleanup_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 107 | my_aspnet_sessions | mru_auth_aspnet_sessions_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 108 | my_aspnet_userbranch_department | mru_auth_aspnet_userbranch_department_main | 3 | - | ⏳ Pending | DUPLICATE |
| 109 | my_aspnet_userphone | mru_auth_aspnet_userphone_main | 51 | - | ⏳ Pending | DUPLICATE |
| 110 | my_aspnet_users | mru_auth_aspnet_users_main | 76 | - | ⏳ Pending | DUPLICATE |
| 111 | my_aspnet_usersinroles | mru_auth_aspnet_usersinroles_main | 163 | - | ⏳ Pending | DUPLICATE |
| 112 | my_aspnet_usersubjects | mru_auth_aspnet_usersubjects_main | 0 | - | ⏳ Pending | DUPLICATE - Empty |
| 113 | my_aspnet_user_faculties | mru_auth_aspnet_user_faculties_main | 194 | - | ⏳ Pending | DUPLICATE |
| 114 | nationalities | mru_sys_nationalities | 71 | - | ⏳ Pending | |
| 115 | nextofkin | mru_sys_next_of_kin | 4 | - | ⏳ Pending | |
| 116 | old_pplics | mru_sys_old_pplics | 13 | - | ⏳ Pending | |
| 117 | otherstudent_info | mru_sys_other_student_info | 3 | - | ⏳ Pending | |
| 118 | results_info_data | mru_acad_results_info_data | 322,674 | - | ⏳ Pending | Large - results info |
| 119 | stddoc | mru_sys_student_docs | 0 | - | ⏳ Pending | Empty table |
| 120 | students_info_data | mru_acad_students_info_data | 14,061 | - | ⏳ Pending | |
| 121 | temp_admin | mru_sys_temp_admin | 0 | - | ⏳ Pending | Empty table |
| 122 | temp_purified_codes | mru_sys_temp_purified_codes | 0 | - | ⏳ Pending | Empty table |

**Database 1 Total Rows:** 1,701,254

---

## DATABASE 2: mru_campus_dynamics_accounts → mru_main
**Total Tables:** 128  
**Naming Convention:** Prefix with `mru_fin_` for financial, `mru_inv_` for inventory, `mru_hrm_` for HR (merge with duplicates)

| # | Original Table Name | New Table Name | Current Rows | Migrated Rows | Status | Notes |
|---|---------------------|----------------|--------------|---------------|--------|-------|
| 1 | academicbillingitems | mru_fin_academic_billing_items | 25 | - | ⏳ Pending | |
| 2 | acad_graduation_clearance | mru_acad_graduation_clearance | 431 | - | ⏳ Pending | |
| 3 | accountingperiod | mru_fin_accounting_period | 0 | - | ⏳ Pending | Empty table |
| 4 | acc_activity_log | mru_fin_activity_log | 14,727 | - | ⏳ Pending | |
| 5 | assetcategory | mru_fin_asset_category | 9 | - | ⏳ Pending | |
| 6 | assetlocations | mru_fin_asset_locations_old | 0 | - | ⏳ Pending | Empty table |
| 7 | bankchargerates | mru_fin_bank_charge_rates | 2 | - | ⏳ Pending | |
| 8 | banks | mru_sys_banks_accounts | 20 | - | ⏳ Pending | **MERGE with main** |
| 9 | class_manager | mru_sys_class_manager | 0 | - | ⏳ Pending | Empty table |
| 10 | companyinfo | mru_sys_company_info_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 11 | cur_accounts | mru_fin_cur_accounts | 255 | - | ⏳ Pending | |
| 12 | depreciationrecords | mru_fin_depreciation_records | 0 | - | ⏳ Pending | Empty table |
| 13 | edit_ledger | mru_fin_edit_ledger | 4,662 | - | ⏳ Pending | |
| 14 | employeebanks | mru_hrm_employee_banks | 2 | - | ⏳ Pending | |
| 15 | exemptions | mru_fin_exemptions | 0 | - | ⏳ Pending | Empty table |
| 16 | external_funders | mru_fin_external_funders | 0 | - | ⏳ Pending | Empty table |
| 17 | fin_accounts_docs | mru_fin_accounts_docs | 0 | - | ⏳ Pending | Empty table |
| 18 | fin_assetlocations | mru_fin_asset_locations | 0 | - | ⏳ Pending | Empty table |
| 19 | fin_billing_systems | mru_fin_billing_systems | 3 | - | ⏳ Pending | |
| 20 | fin_budget | mru_fin_budget | 0 | - | ⏳ Pending | Empty table |
| 21 | fin_currency | mru_fin_currency | 2 | - | ⏳ Pending | |
| 22 | fin_deleted_ledger | mru_fin_deleted_ledger | 7,273 | - | ⏳ Pending | |
| 23 | fin_department | mru_fin_department | 2 | - | ⏳ Pending | |
| 24 | fin_expdates | mru_fin_expdates_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 25 | fin_fees_analysis_semester | mru_fin_fees_analysis_semester | 21,903 | - | ⏳ Pending | |
| 26 | fin_fees_pay_schedule | mru_fin_fees_pay_schedule | 4,728 | - | ⏳ Pending | |
| 27 | fin_fees_structure | mru_fin_fees_structure | 8,304 | - | ⏳ Pending | |
| 28 | fin_financial_periods | mru_fin_financial_periods | 0 | - | ⏳ Pending | Empty table |
| 29 | fin_journal | mru_fin_journal | 0 | - | ⏳ Pending | Empty table |
| 30 | fin_journalnumbers | mru_fin_journal_numbers | 9,978 | - | ⏳ Pending | |
| 31 | fin_journaltypes | mru_fin_journal_types | 6 | - | ⏳ Pending | |
| 32 | fin_journal_details | mru_fin_journal_details | 17,639 | - | ⏳ Pending | |
| 33 | fin_ledger | mru_fin_ledger | 119,281 | - | ⏳ Pending | **CRITICAL** - Main ledger |
| 34 | fin_ledgers_prog | mru_fin_ledgers_prog | 75,776 | - | ⏳ Pending | |
| 35 | fin_ledgertypes | mru_fin_ledger_types | 17 | - | ⏳ Pending | |
| 36 | fin_ledger_prog | mru_fin_ledger_prog | 84,162 | - | ⏳ Pending | |
| 37 | fin_mainaccounts | mru_fin_main_accounts | 18 | - | ⏳ Pending | |
| 38 | fin_notifications | mru_fin_notifications | 0 | - | ⏳ Pending | Empty table |
| 39 | fin_old_subaccounts | mru_fin_old_subaccounts | 104 | - | ⏳ Pending | |
| 40 | fin_paymenttracker | mru_fin_payment_tracker | 0 | - | ⏳ Pending | Empty table |
| 41 | fin_payrollpostrecords | mru_fin_payroll_post_records | 0 | - | ⏳ Pending | Empty table |
| 42 | fin_reconciliationstatement | mru_fin_reconciliation_statement | 0 | - | ⏳ Pending | Empty table |
| 43 | fin_reco_adjustments | mru_fin_reco_adjustments | 0 | - | ⏳ Pending | Empty table |
| 44 | fin_reco_bank_entries | mru_fin_reco_bank_entries | 0 | - | ⏳ Pending | Empty table |
| 45 | fin_registration_percent | mru_fin_registration_percent | 0 | - | ⏳ Pending | Empty table |
| 46 | fin_schoolpaydata | mru_fin_school_pay_data | 28,336 | - | ⏳ Pending | |
| 47 | fin_studentfeestracking | mru_fin_student_fees_tracking | 52,140 | - | ⏳ Pending | |
| 48 | fin_subaccounts | mru_fin_subaccounts | 193 | - | ⏳ Pending | |
| 49 | fin_temp_balance | mru_fin_temp_balance | 0 | - | ⏳ Pending | Empty table |
| 50 | fin_transaction_numbers | mru_fin_transaction_numbers | 94,848 | - | ⏳ Pending | Large - transaction IDs |
| 51 | fixedassetregister | mru_fin_fixed_asset_register | 0 | - | ⏳ Pending | Empty table |
| 52 | hrm_allowance_deductions | mru_hrm_allowance_deductions_accounts | 7 | - | ⏳ Pending | **MERGE with main** |
| 53 | hrm_ded_allowance_stafflist | mru_hrm_ded_allowance_stafflist_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 54 | hrm_departments | mru_hrm_departments_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 55 | hrm_employee | mru_hrm_employee_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 56 | hrm_employee_accprofile | mru_hrm_employee_accprofile | 0 | - | ⏳ Pending | Empty table |
| 57 | hrm_employee_emp_profile | mru_hrm_employee_emp_profile | 0 | - | ⏳ Pending | Empty table |
| 58 | hrm_employee_other | mru_hrm_employee_other | 0 | - | ⏳ Pending | Empty table |
| 59 | hrm_emp_contracts | mru_hrm_emp_contracts_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 60 | hrm_exemptions | mru_hrm_exemptions_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 61 | hrm_jobs | mru_hrm_jobs_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 62 | hrm_monthly_ded_allowance | mru_hrm_monthly_ded_allowance_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 63 | hrm_payroll | mru_hrm_payroll_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 64 | hrm_payroll_details | mru_hrm_payroll_details_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 65 | hrm_payscales | mru_hrm_payscales_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 66 | hrm_performance_appraisal | mru_hrm_performance_appraisal | 0 | - | ⏳ Pending | Empty table |
| 67 | hrm_qualifications | mru_hrm_qualifications_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 68 | hrm_stations | mru_hrm_stations_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 69 | inv_budgetrequisitions | mru_inv_budget_requisitions | 0 | - | ⏳ Pending | Empty table |
| 70 | inv_inventory | mru_inv_inventory | 0 | - | ⏳ Pending | Empty table |
| 71 | inv_inventory_out | mru_inv_inventory_out | 0 | - | ⏳ Pending | Empty table |
| 72 | inv_itemdetails | mru_inv_item_details | 0 | - | ⏳ Pending | Empty table |
| 73 | inv_itemgroup | mru_inv_item_group | 10 | - | ⏳ Pending | |
| 74 | inv_itemunitdetails | mru_inv_item_unit_details | 18 | - | ⏳ Pending | |
| 75 | inv_itemunits | mru_inv_item_units | 15 | - | ⏳ Pending | |
| 76 | inv_purchaseorder | mru_inv_purchase_order | 0 | - | ⏳ Pending | Empty table |
| 77 | inv_purchaseorder_items | mru_inv_purchase_order_items | 0 | - | ⏳ Pending | Empty table |
| 78 | inv_schoolreqdetails | mru_inv_school_req_details | 0 | - | ⏳ Pending | Empty table |
| 79 | inv_schoolrequisition | mru_inv_school_requisition | 0 | - | ⏳ Pending | Empty table |
| 80 | inv_stockcapture | mru_inv_stock_capture | 3 | - | ⏳ Pending | |
| 81 | inv_stockdeductions | mru_inv_stock_deductions | 0 | - | ⏳ Pending | Empty table |
| 82 | inv_stock_on_sheet | mru_inv_stock_on_sheet | 0 | - | ⏳ Pending | Empty table |
| 83 | inv_storelocation | mru_inv_store_location | 2 | - | ⏳ Pending | |
| 84 | inv_supplierdetails | mru_inv_supplier_details | 68 | - | ⏳ Pending | |
| 85 | inv_supplierwithitems | mru_inv_supplier_with_items | 0 | - | ⏳ Pending | Empty table |
| 86 | inv_taxdetail | mru_inv_tax_detail | 3 | - | ⏳ Pending | |
| 87 | journalentries | mru_fin_journal_entries_old | 0 | - | ⏳ Pending | Empty table |
| 88 | journalnumbers | mru_fin_journal_numbers_old | 0 | - | ⏳ Pending | Empty table |
| 89 | journals | mru_fin_journals_old | 4 | - | ⏳ Pending | |
| 90 | ledgerentries | mru_fin_ledger_entries_old | 0 | - | ⏳ Pending | Empty table |
| 91 | ledgertypes | mru_fin_ledger_types_old | 11 | - | ⏳ Pending | |
| 92 | mainaccounts | mru_fin_main_accounts_old | 6 | - | ⏳ Pending | |
| 93 | missing_tids | mru_fin_missing_tids | 406 | - | ⏳ Pending | |
| 94 | my_aspnet_applications | mru_auth_aspnet_applications_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 95 | my_aspnet_apps | mru_auth_aspnet_apps_accounts | 5 | - | ⏳ Pending | **MERGE with main** |
| 96 | my_aspnet_membership | mru_auth_aspnet_membership_accounts | 6 | - | ⏳ Pending | **MERGE with main** |
| 97 | my_aspnet_profiles | mru_auth_aspnet_profiles_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 98 | my_aspnet_roles | mru_auth_aspnet_roles_accounts | 2 | - | ⏳ Pending | **MERGE with main** |
| 99 | my_aspnet_roles_in_apps | mru_auth_aspnet_roles_in_apps_accounts | 5 | - | ⏳ Pending | **MERGE with main** |
| 100 | my_aspnet_schemaversion | mru_auth_aspnet_schemaversion_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 101 | my_aspnet_sessioncleanup | mru_auth_aspnet_sessioncleanup_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 102 | my_aspnet_sessions | mru_auth_aspnet_sessions_accounts | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 103 | my_aspnet_userbranch_department | mru_auth_aspnet_userbranch_department_accounts | 3 | - | ⏳ Pending | **MERGE with main** |
| 104 | my_aspnet_users | mru_auth_aspnet_users_accounts | 6 | - | ⏳ Pending | **MERGE with main** |
| 105 | my_aspnet_usersinroles | mru_auth_aspnet_usersinroles_accounts | 5 | - | ⏳ Pending | **MERGE with main** |
| 106 | payment_analytics | mru_fin_payment_analytics | 0 | - | ⏳ Pending | Empty table |
| 107 | payrollpoststatus | mru_fin_payroll_post_status | 0 | - | ⏳ Pending | Empty table |
| 108 | payroll_postaccounts | mru_fin_payroll_post_accounts | 0 | - | ⏳ Pending | Empty table |
| 109 | product_listing | mru_fin_product_listing | 935 | - | ⏳ Pending | |
| 110 | scholarships | mru_fin_scholarships | 7 | - | ⏳ Pending | |
| 111 | scholarshipstudents | mru_fin_scholarship_students | 0 | - | ⏳ Pending | Empty table |
| 112 | school_budget | mru_fin_school_budget | 0 | - | ⏳ Pending | Empty table |
| 113 | smis_allowancedeductionlist | mru_fin_smis_allowance_deduction_list | 11 | - | ⏳ Pending | |
| 114 | smis_longterm_ded_allowances | mru_fin_smis_longterm_ded_allowances | 5 | - | ⏳ Pending | |
| 115 | smis_payrolldetails | mru_fin_smis_payroll_details | 0 | - | ⏳ Pending | Empty table |
| 116 | smis_payrolllist | mru_fin_smis_payroll_list | 0 | - | ⏳ Pending | Empty table |
| 117 | stud_billing | mru_fin_stud_billing | 0 | - | ⏳ Pending | Empty table |
| 118 | subledgerentries | mru_fin_subledger_entries | 0 | - | ⏳ Pending | Empty table |
| 119 | supplier | mru_inv_supplier | 3 | - | ⏳ Pending | |
| 120 | temppay | mru_fin_temp_pay | 0 | - | ⏳ Pending | Empty table |
| 121 | temp_balances | mru_fin_temp_balances | 0 | - | ⏳ Pending | Empty table |
| 122 | temp_baldata | mru_fin_temp_baldata | 0 | - | ⏳ Pending | Empty table |
| 123 | temp_gta_transactions | mru_fin_temp_gta_transactions | 0 | - | ⏳ Pending | Empty table |
| 124 | temp_pending | mru_fin_temp_pending | 36,430 | - | ⏳ Pending | |
| 125 | temp_pendings | mru_fin_temp_pendings | 36,516 | - | ⏳ Pending | |
| 126 | temp_reg | mru_fin_temp_reg | 0 | - | ⏳ Pending | Empty table |
| 127 | voucherentries | mru_fin_voucher_entries | 6 | - | ⏳ Pending | |
| 128 | vouchernumbers | mru_fin_voucher_numbers | 8 | - | ⏳ Pending | |

**Database 2 Total Rows:** 596,535

---

## DATABASE 3: mru_campus_dynamics_admissions → mru_main
**Total Tables:** 8  
**Naming Convention:** Prefix with `mru_admissions_`

| # | Original Table Name | New Table Name | Current Rows | Migrated Rows | Status | Notes |
|---|---------------------|----------------|--------------|---------------|--------|-------|
| 1 | applic_form | mru_admissions_form | 778 | - | ⏳ Pending | |
| 2 | applic_payments | mru_admissions_payments | 0 | - | ⏳ Pending | Empty table |
| 3 | applic_results | mru_admissions_results | 0 | - | ⏳ Pending | Empty table |
| 4 | doc_uploads | mru_admissions_doc_uploads | 481 | - | ⏳ Pending | |
| 5 | educ_background | mru_admissions_educ_background | 842 | - | ⏳ Pending | |
| 6 | fin_expdates | mru_fin_expdates_admissions | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 7 | programme_data | mru_admissions_programme_data | 0 | - | ⏳ Pending | Empty table |
| 8 | student_recruiter | mru_admissions_student_recruiter | 2 | - | ⏳ Pending | |

**Database 3 Total Rows:** 2,103

---

## DATABASE 4: mru_campus_dynamics_portal → mru_main
**Total Tables:** 40  
**Naming Convention:** Prefix with `mru_portal_` for portal-specific, merge authentication tables

| # | Original Table Name | New Table Name | Current Rows | Migrated Rows | Status | Notes |
|---|---------------------|----------------|--------------|---------------|--------|-------|
| 1 | acad_coursework_exceluploads | mru_portal_coursework_excel_uploads | 265 | - | ⏳ Pending | |
| 2 | acad_coursework_marks | mru_portal_coursework_marks | 113,741 | - | ⏳ Pending | Large - coursework marks |
| 3 | acad_coursework_settings | mru_portal_coursework_settings | 17,810 | - | ⏳ Pending | |
| 4 | acad_course_contents | mru_portal_course_contents | 0 | - | ⏳ Pending | Empty table |
| 5 | acad_course_registration | mru_portal_course_registration | 98,482 | - | ⏳ Pending | Large - course reg |
| 6 | acad_examination_papers | mru_portal_examination_papers | 37 | - | ⏳ Pending | |
| 7 | acad_examination_papers_approvalcomments | mru_portal_examination_papers_approvalcomments | 0 | - | ⏳ Pending | Empty table |
| 8 | acad_examination_papers_questions | mru_portal_examination_papers_questions | 21 | - | ⏳ Pending | |
| 9 | acad_examsettings | mru_portal_exam_settings | 14,940 | - | ⏳ Pending | |
| 10 | acad_exam_exceluploads | mru_portal_exam_excel_uploads | 8 | - | ⏳ Pending | |
| 11 | acad_facultyresultsheets | mru_portal_faculty_result_sheets | 2 | - | ⏳ Pending | |
| 12 | acad_notices | mru_portal_notices | 9 | - | ⏳ Pending | |
| 13 | acad_practicalexam_marks | mru_portal_practical_exam_marks | 1,141 | - | ⏳ Pending | |
| 14 | acad_practicalexam_settings | mru_portal_practical_exam_settings | 371 | - | ⏳ Pending | |
| 15 | acad_researchexamsettings | mru_portal_research_exam_settings | 25,025 | - | ⏳ Pending | |
| 16 | acad_researchresults | mru_portal_research_results | 7,119 | - | ⏳ Pending | |
| 17 | acad_results | mru_acad_results_portal | 102,690 | - | ⏳ Pending | **MERGE with main results** |
| 18 | acad_results_complaints | mru_acad_results_complaints_portal | 9 | - | ⏳ Pending | **MERGE with main** |
| 19 | acc_redo_info | mru_portal_acc_redo_info | 85 | - | ⏳ Pending | |
| 20 | fin_expdates | mru_fin_expdates_portal | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 21 | fin_studentlocks | mru_portal_student_locks | 30,777 | - | ⏳ Pending | |
| 22 | my_aspnet_applications | mru_auth_aspnet_applications_portal | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 23 | my_aspnet_apps | mru_auth_aspnet_apps_portal | 7 | - | ⏳ Pending | **MERGE with main** |
| 24 | my_aspnet_classes | mru_auth_aspnet_classes_portal | 6 | - | ⏳ Pending | **MERGE with main** |
| 25 | my_aspnet_membership | mru_auth_aspnet_membership_portal | 97,291 | - | ⏳ Pending | **MERGE with main** - Large |
| 26 | my_aspnet_profiles | mru_auth_aspnet_profiles_portal | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 27 | my_aspnet_roles | mru_auth_aspnet_roles_portal | 4 | - | ⏳ Pending | **MERGE with main** |
| 28 | my_aspnet_roles_in_apps | mru_auth_aspnet_roles_in_apps_portal | 9 | - | ⏳ Pending | **MERGE with main** |
| 29 | my_aspnet_schemaversion | mru_auth_aspnet_schemaversion_portal | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 30 | my_aspnet_sessioncleanup | mru_auth_aspnet_sessioncleanup_portal | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 31 | my_aspnet_sessions | mru_auth_aspnet_sessions_portal | 0 | - | ⏳ Pending | **MERGE with main** - Empty |
| 32 | my_aspnet_userbranch_department | mru_auth_aspnet_userbranch_department_portal | 3 | - | ⏳ Pending | **MERGE with main** |
| 33 | my_aspnet_userphone | mru_auth_aspnet_userphone_portal | 3 | - | ⏳ Pending | **MERGE with main** |
| 34 | my_aspnet_users | mru_auth_aspnet_users_portal | 14,631 | - | ⏳ Pending | **MERGE with main** - Largest user table |
| 35 | my_aspnet_usersinroles | mru_auth_aspnet_usersinroles_portal | 178,776 | - | ⏳ Pending | **MERGE with main** - Largest |
| 36 | my_aspnet_usersubjects | mru_auth_aspnet_usersubjects_portal | 8 | - | ⏳ Pending | **MERGE with main** |
| 37 | portal_resultscomplaint | mru_portal_results_complaint | 0 | - | ⏳ Pending | Empty table |
| 38 | portal_resultscomplaintreaction | mru_portal_results_complaint_reaction | 0 | - | ⏳ Pending | Empty table |
| 39 | portal_results_complaints | mru_portal_results_complaints | 0 | - | ⏳ Pending | Empty table |
| 40 | prob_cases | mru_portal_prob_cases | 6 | - | ⏳ Pending | |

**Database 4 Total Rows:** 703,270

---

## GRAND TOTALS

| Metric | Count |
|--------|-------|
| **Total Databases** | 4 |
| **Total Tables** | 298 |
| **Total Data Rows (Estimated)** | **3,003,162** |
| **Unique Tables (after merging duplicates)** | 265 |
| **Duplicate Tables to Merge** | 33 |

---

## CRITICAL TABLES (>50,000 rows)

These tables require special attention during migration:

| Table Name | Source DB | Rows | Priority | Notes |
|------------|-----------|------|----------|-------|
| acad_results | campus_dynamics | 596,635 | **HIGHEST** | Main results table |
| acad_results_legacy | campus_dynamics | 320,584 | **HIGH** | Legacy results |
| results_info_data | campus_dynamics | 322,674 | **HIGH** | Results information |
| acad_activity_log | campus_dynamics | 165,680 | MEDIUM | Activity tracking |
| acad_examresults_faculty | campus_dynamics | 149,292 | HIGH | Faculty exam results |
| fin_ledger | accounts | 119,281 | **HIGHEST** | Financial ledger |
| acad_coursework_marks | portal | 113,741 | HIGH | Coursework marks |
| acad_results | portal | 102,690 | **HIGHEST** | Portal results (MERGE) |
| acad_course_registration | portal | 98,482 | HIGH | Course registration |
| my_aspnet_membership | portal | 97,291 | HIGH | User membership |
| fin_transaction_numbers | accounts | 94,848 | MEDIUM | Transaction tracking |
| fin_ledger_prog | accounts | 84,162 | HIGH | Program ledger |
| fin_ledgers_prog | accounts | 75,776 | HIGH | Program ledgers |
| fin_studentfeestracking | accounts | 52,140 | HIGH | Student fees |

**Total Critical Rows:** 2,093,000+ (70% of all data)

---

## DUPLICATE TABLES - MERGE STRATEGY

These 33 tables exist in multiple databases and must be merged:

### Authentication Tables (13 duplicates)

| Table Base Name | Main DB Rows | Accounts Rows | Portal Rows | Total After Merge | Strategy |
|-----------------|--------------|---------------|-------------|-------------------|----------|
| my_aspnet_applications | 0 | 0 | 0 | 0 | Skip - all empty |
| my_aspnet_apps | 16 | 5 | 7 | 28 | UNION ALL |
| my_aspnet_classes | 6 | - | 6 | 12 | UNION ALL |
| my_aspnet_membership | 266 | 6 | 97,291 | 97,563 | UNION ALL with dedup |
| my_aspnet_profiles | 0 | 0 | 0 | 0 | Skip - all empty |
| my_aspnet_roles | 26 | 2 | 4 | 32 | UNION ALL with dedup |
| my_aspnet_roles_in_apps | 90 | 5 | 9 | 104 | UNION ALL |
| my_aspnet_schemaversion | 0 | 0 | 0 | 0 | Skip - all empty |
| my_aspnet_sessioncleanup | 0 | 0 | 0 | 0 | Skip - all empty |
| my_aspnet_sessions | 0 | 0 | 0 | 0 | Skip - all empty |
| my_aspnet_userbranch_department | 3 | 3 | 3 | 9 | UNION ALL |
| my_aspnet_userphone | 51 | - | 3 | 54 | UNION ALL |
| my_aspnet_users | 76 | 6 | 14,631 | 14,713 | **UNION ALL with dedup** |
| my_aspnet_usersinroles | 163 | 5 | 178,776 | 178,944 | UNION ALL |
| my_aspnet_usersubjects | 0 | - | 8 | 8 | UNION ALL |
| my_aspnet_user_faculties | 194 | - | - | 194 | Direct copy |

### HR Tables (11 duplicates)

| Table Base Name | Main DB Rows | Accounts Rows | Total After Merge | Strategy |
|-----------------|--------------|---------------|-------------------|----------|
| hrm_allowance_deductions | 14 | 7 | 21 | UNION ALL |
| hrm_ded_allowance_stafflist | 1,510 | 0 | 1,510 | Direct copy |
| hrm_departments | 18 | 0 | 18 | Direct copy |
| hrm_employee | 296 | 0 | 296 | Direct copy |
| hrm_emp_contracts | 283 | 0 | 283 | Direct copy |
| hrm_exemptions | 0 | 0 | 0 | Skip - all empty |
| hrm_jobs | 115 | 0 | 115 | Direct copy |
| hrm_monthly_ded_allowance | 314 | 0 | 314 | Direct copy |
| hrm_payroll | 0 | 0 | 0 | Skip - all empty |
| hrm_payroll_details | 463 | 0 | 463 | Direct copy |
| hrm_payscales | 7 | 0 | 7 | Direct copy |
| hrm_qualifications | 0 | 0 | 0 | Skip - all empty |
| hrm_stations | 2 | 0 | 2 | Direct copy |

### Other Tables (9 duplicates)

| Table Base Name | Main DB Rows | Accounts Rows | Admissions Rows | Portal Rows | Total | Strategy |
|-----------------|--------------|---------------|-----------------|-------------|-------|----------|
| acad_results_complaints | 0 | - | - | 9 | 9 | UNION ALL |
| banks | 21 | 20 | - | - | 41 | UNION ALL with dedup |
| companyinfo | 0 | 0 | - | - | 0 | Skip - all empty |
| fin_expdates | 0 | 0 | 0 | 0 | 0 | Skip - all empty |

---

## VERIFICATION QUERIES

Use these queries after migration to verify data integrity:

```sql
-- 1. Verify total row count per table
SELECT 
    table_name,
    table_rows
FROM information_schema.tables 
WHERE table_schema = 'mru_main'
ORDER BY table_rows DESC;

-- 2. Verify critical tables
SELECT COUNT(*) FROM mru_main.mru_acad_students; -- Should be 30,003
SELECT COUNT(*) FROM mru_main.mru_acad_results_main; -- Should be 596,635
SELECT COUNT(*) FROM mru_main.mru_fin_ledger; -- Should be 119,281

-- 3. Verify merged authentication tables
SELECT 
    'mru_auth_aspnet_users' as table_name,
    COUNT(*) as total_rows,
    COUNT(DISTINCT UserId) as unique_users
FROM mru_main.mru_auth_aspnet_users;
-- Expected: 14,713 total, check for duplicates

-- 4. Check for duplicate primary keys in merged tables
SELECT UserId, COUNT(*) 
FROM mru_main.mru_auth_aspnet_users
GROUP BY UserId
HAVING COUNT(*) > 1;
-- Should return 0 rows

-- 5. Verify grand total
SELECT SUM(table_rows) as total_rows
FROM information_schema.tables
WHERE table_schema = 'mru_main';
-- Should be approximately 3,003,162
```

---

## POST-MIGRATION CHECKLIST

- [ ] All 298 tables created in mru_main
- [ ] All row counts match this document
- [ ] No duplicate primary keys in merged tables
- [ ] All foreign key relationships validated
- [ ] Indexes recreated on mru_main tables
- [ ] Application tested with new database
- [ ] Old databases backed up before deletion
- [ ] Old databases deleted (mru_campus_dynamics, mru_campus_dynamics_accounts, mru_campus_dynamics_admissions, mru_campus_dynamics_portal)
- [ ] .env file updated to point to mru_main
- [ ] Documentation updated

---

## NOTES FOR MIGRATION EXECUTION

1. **Empty Tables:** 77 tables have 0 rows - these will be created with structure only
2. **Merge Conflicts:** Check for primary key conflicts when merging authentication tables
3. **Large Tables:** Tables with >100k rows should be migrated in chunks
4. **Indexes:** Create indexes AFTER data migration for better performance
5. **Foreign Keys:** Add foreign key constraints AFTER all data is migrated
6. **Testing:** Test application thoroughly before deleting old databases

---

*This document must be updated as migration progresses. Mark each table as ✅ Complete after verification.*

**Last Updated:** December 19, 2025  
**Status:** Ready for Migration Execution
