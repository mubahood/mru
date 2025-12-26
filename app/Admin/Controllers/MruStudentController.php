<?php

namespace App\Admin\Controllers;

use App\Models\MruStudent;
use App\Models\MruProgramme;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;

/**
 * MruStudentController
 * 
 * Laravel Admin controller for managing student information in the MRU system.
 * Handles CRUD operations for student records.
 * 
 * @package App\Admin\Controllers
 */
class MruStudentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Students';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruStudent());

        // Eager load relationships for better performance
        $grid->model()->with(['programme.faculty', 'specialisationDetails'])
            ->orderBy('regno', 'desc');

        /*
        |----------------------------------------------------------------------
        | QUICK SEARCH
        |----------------------------------------------------------------------
        */

        $grid->quickSearch(function ($model, $query) {
            $model->where(function ($q) use ($query) {
                $q->where('regno', 'like', "%{$query}%")
                    ->orWhere('firstname', 'like', "%{$query}%")
                    ->orWhere('othername', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('studPhone', 'like', "%{$query}%")
                    ->orWhere('progid', 'like', "%{$query}%")
                    ->orWhere('entryno', 'like', "%{$query}%");
            });
        })->placeholder('Search: Reg No, Name, Email, Phone, Entry No...');

        /*
        |----------------------------------------------------------------------
        | GRID COLUMNS
        |----------------------------------------------------------------------
        */

        // Registration Number
        $grid->column('regno', __('Reg No'))->sortable();

        // Full Name
        $grid->column('firstname', __('Full Name'))
            ->display(function () {
                return $this->full_name;
            })
            ->sortable();

        // Gender
        $grid->column('gender', __('Gender'))->sortable();

        // Date of Birth
        $grid->column('dob', __('DOB'))
            ->display(function ($dob) {
                return $dob ? date('d M Y', strtotime($dob)) : '-';
            })
            ->sortable();

        // Programme & Faculty
        $grid->column('programme', __('Programme'))
            ->display(function () {
                if ($this->programme) {
                    $prog = $this->programme->progname;
                    $fac = $this->programme->faculty ? $this->programme->faculty->abbrev : '-';
                    return "<div><strong>{$this->progid}</strong><br><small style='color:#666;'>{$prog}</small><br><small style='color:#999;'>{$fac}</small></div>";
                }
                return $this->progid;
            });

        // Specialisation (for education students)
        $grid->column('specialisation_display', __('Specialisation'))
            ->display(function () {
                if ($this->specialisationDetails) {
                    return "<span class='label label-info'>{$this->specialisationDetails->abbrev}</span>";
                }
                return '-';
            });

        // Entry Year
        $grid->column('entryyear', __('Entry Year'))->sortable();

        // Study Session
        $grid->column('studsesion', __('Session'))->sortable();

        // Nationality
        $grid->column('nationality', __('Nationality'))->sortable();

        // Processing Status
        $grid->column('is_processed', 'Processed') 
            ->display(function ($value) {
                return $value === 'Yes' 
                    ? '<span class="label label-success">Yes</span>' 
                    : '<span class="label label-danger">No</span>';
            });

        $grid->column('is_processed_successful', 'Success')
            ->filter('select', ['Yes' => 'Yes', 'No' => 'No'])
            ->display(function ($value) {
                if ($this->is_processed === 'Yes') {
                    return $value === 'Yes' 
                        ? '<span class="label label-success">Yes</span>' 
                        : '<span class="label label-danger">No</span>';
                }
                return '<span class="label label-default">-</span>';
            })
            ->sortable();

        $grid->column('processing_reason', 'Reason')
            ->display(function ($value) {
                if ($value) {
                    return '<span style="color: #e74c3c; font-size: 11px;">' . htmlspecialchars(substr($value, 0, 50)) . ($value && strlen($value) > 50 ? '...' : '') . '</span>';
                }
                return '-';
            })
            ->sortable();

        // Show Details
        $grid->column('show_details', __('Show Details'))
            ->display(function () {
                $url = admin_url('mru-students/' . $this->ID);
                return "<a href='{$url}' target='_blank' class='btn btn-sm btn-primary'>
                    <i class='fa fa-eye'></i> View Details
                </a>";
            });

        /*
        |----------------------------------------------------------------------
        | GRID FILTERS
        |----------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            // Remove default ID filter
            $filter->disableIdFilter();

            // Registration number
            $filter->like('regno', __('Reg No'));

            // Name search
            $filter->where(function ($query) {
                $query->where('firstname', 'like', "%{$this->input}%")
                    ->orWhere('othername', 'like', "%{$this->input}%");
            }, 'Name');

            // Programme
            $filter->equal('progid', __('Programme'))
                ->select(MruProgramme::where('progcode', '!=', '-')
                    ->pluck('progcode', 'progcode'));

            // Gender
            $filter->equal('gender', __('Gender'))
                ->select([
                    MruStudent::GENDER_MALE => 'Male',
                    MruStudent::GENDER_FEMALE => 'Female',
                ]);

            // Study session
            $filter->equal('studsesion', __('Session'))
                ->select([
                    MruStudent::SESSION_DAY => 'Day',
                    MruStudent::SESSION_WEEKEND => 'Weekend',
                    MruStudent::SESSION_EVENING => 'Evening',
                    MruStudent::SESSION_INSERVICE => 'In-Service',
                    MruStudent::SESSION_FULL_TIME => 'Full Time',
                ]);

            // Entry year
            $filter->equal('entryyear', __('Entry Year'))
                ->select(array_combine(
                    MruStudent::getEntryYears(),
                    MruStudent::getEntryYears()
                ));

            // Email
            $filter->like('email', __('Email'));

            // Phone
            $filter->like('studPhone', __('Phone'));

            // Processing Status
            $filter->equal('is_processed', __('Processed'))
                ->select(['Yes' => 'Yes', 'No' => 'No']);

            $filter->equal('is_processed_successful', __('Success'))
                ->select(['Yes' => 'Yes', 'No' => 'No']);

            $filter->where(function ($query) {
                $query->whereNotNull('processing_reason')->where('processing_reason', '!=', '');
            }, 'Has Errors')->checkbox('1');
        });

        /*
        |----------------------------------------------------------------------
        | GRID ACTIONS
        |----------------------------------------------------------------------
        */

        $grid->actions(function ($actions) {
            // Keep all actions enabled
        });

        /*
        |----------------------------------------------------------------------
        | GRID EXPORT
        |----------------------------------------------------------------------
        */

        $grid->export(function ($export) {
            $export->filename('Students_' . date('Y-m-d_His'));

            $export->column('regno', 'Reg No');
            $export->column('firstname', 'First Name');
            $export->column('othername', 'Other Name');
            $export->column('gender', 'Gender');
            $export->column('dob', 'Date of Birth');
            $export->column('progid', 'Programme');
            $export->column('entryyear', 'Entry Year');
            $export->column('studsesion', 'Session');
            $export->column('email', 'Email');
            $export->column('is_processed', 'Processed');
            $export->column('is_processed_successful', 'Success');
            $export->column('processing_reason', 'Processing Reason');
            $export->column('studPhone', 'Phone');
            $export->column('nationality', 'Nationality');
        });

        /*
        |----------------------------------------------------------------------
        | GRID SETTINGS
        |----------------------------------------------------------------------
        */

        // Set rows per page
        $grid->paginate(50);

        return $grid;
    }

    /**
     * Display the specified resource detail.
     * 
     * This method serves a comprehensive student detail view with all academic information.
     * It loads the student record with all related data using eager loading for optimal performance.
     * 
     * Features:
     * - Complete student profile (personal info, contact details, academic info)
     * - Course registration history with status badges
     * - Coursework marks with assignments and tests
     * - Academic results with grades and GPAs
     * - Practical exam marks with pass/fail status
     * - Academic progress summary with calculated metrics
     * - Semester GPA performance tracking
     * - Retakes and supplementary exam records
     * 
     * Performance Optimizations:
     * - Eager loading relationships to prevent N+1 queries
     * - Calculated attributes cached on first access
     * - Grouped database queries for semester summaries
     * 
     * Related Models:
     * @uses MruStudent - Main student model
     * @uses MruProgramme - Programme information
     * @uses MruResult - Academic results
     * @uses MruCourseRegistration - Course registration records
     * @uses MruCourseworkMark - Coursework assessment marks
     * @uses MruPracticalExamMark - Practical exam marks
     * @uses MruCourseworkSetting - Coursework configuration
     * @uses MruCourse - Course information
     * 
     * View Structure:
     * @see resources/views/admin/mru/students/show.blade.php
     * - Header Section: Photo, name, badges
     * - Personal & Contact Information
     * - Academic Information
     * - Course Registration Table
     * - Coursework Marks Table
     * - Academic Results Table
     * - Practical Exam Marks Table
     * - Academic Progress Summary (with calculations)
     * - Semester GPA Summary (grouped by semester)
     * - Retakes & Supplementary (failed courses)
     * - Programme Requirements Progress (placeholder)
     * - Exam Settings & Mark Distribution (placeholder)
     * - Financial Summary (placeholder)
     * - Documents Section (placeholder)
     * 
     * @param int $id Student primary key (ID)
     * @return \Illuminate\View\View
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If student not found
     */
    protected function detail($id)
    {
        // Load student with all related data using eager loading
        $student = MruStudent::with([
            'programme.faculty',                            // Programme information with faculty
            'specialisationDetails',                        // Specialisation/Teaching subjects
            'results',                                      // Academic results
            'courseRegistrations.course',                   // Registered courses with course details
            'courseworkMarks.settings.course',              // Coursework marks with settings and course info
            'practicalExamMarks.settings.course'            // Practical marks with settings and course info
        ])->findOrFail($id);
        
        // Calculate semester GPA summary (grouped by academic year and semester)
        $semesterGpaSummary = $student->getSemesterGpaSummary();
        
        // Get retakes and supplementary exams (failed courses)
        $retakes = $student->getRetakesAndSupplementary();

        // Return custom Blade view with all calculated data
        return view('admin.mru.students.show', compact('student', 'semesterGpaSummary', 'retakes'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruStudent());

        /*
        |----------------------------------------------------------------------
        | FORM FIELDS
        |----------------------------------------------------------------------
        */

        $form->text('regno', __('Registration Number'))
            ->rules('required|max:50|unique:acad_student,regno,' . request()->route('student'))
            ->required();

        $form->text('entryno', __('Entry Number'))
            ->rules('nullable|max:25');

        $form->text('firstname', __('First Name'))
            ->rules('required|max:45')
            ->required();

        $form->text('othername', __('Other Name'))
            ->rules('nullable|max:45');

        $form->date('dob', __('Date of Birth'))
            ->rules('nullable|date');

        $form->select('gender', __('Gender'))
            ->options([
                MruStudent::GENDER_MALE => 'Male',
                MruStudent::GENDER_FEMALE => 'Female',
            ])
            ->rules('required|in:' . implode(',', MruStudent::GENDERS))
            ->required();

        $form->text('nationality', __('Nationality'))
            ->rules('nullable|max:65')
            ->default('UGANDAN');

        $form->text('religion', __('Religion'))
            ->rules('nullable|max:25');

        $form->select('entrymethod', __('Entry Method'))
            ->options(array_combine(
                MruStudent::ENTRY_METHODS,
                MruStudent::ENTRY_METHODS
            ))
            ->rules('nullable|in:' . implode(',', MruStudent::ENTRY_METHODS));

        $form->select('progid', __('Programme'))
            ->options(MruProgramme::where('progcode', '!=', '-')
                ->pluck('progcode', 'progcode'))
            ->rules('required|max:15')
            ->required();

        $form->text('studPhone', __('Phone Number'))
            ->rules('nullable|max:65');

        $form->email('email', __('Email Address'))
            ->rules('nullable|email|max:45');

        $form->number('entryyear', __('Entry Year'))
            ->rules('nullable|integer|min:1990|max:' . (date('Y') + 1))
            ->default(date('Y'));

        $form->select('studsesion', __('Study Session'))
            ->options(array_combine(
                MruStudent::STUDY_SESSIONS,
                MruStudent::STUDY_SESSIONS
            ))
            ->rules('required|in:' . implode(',', MruStudent::STUDY_SESSIONS))
            ->default(MruStudent::SESSION_DAY)
            ->required();

        $form->text('home_dist', __('Home District'))
            ->rules('nullable|max:25');

        $form->text('intake', __('Intake'))
            ->rules('nullable|max:25')
            ->placeholder('e.g., August 2024');

        $form->number('duration', __('Programme Duration (Years)'))
            ->rules('nullable|integer|min:1|max:10')
            ->default(3);

        $form->text('specialisation', __('Specialisation/Major'))
            ->rules('nullable|max:150')
            ->placeholder('e.g., Software Engineering');

        $form->number('gradSystemID', __('Grading System ID'))
            ->rules('nullable|integer')
            ->default(1);

        $form->number('studCampus', __('Campus ID'))
            ->rules('nullable|integer');

        $form->text('StudentHall', __('Student Hall/Residence'))
            ->rules('nullable|max:45')
            ->placeholder('e.g., Hall A');

        $form->text('photofile', __('Photo Filename'))
            ->rules('nullable|max:45')
            ->placeholder('Optional: photo file reference');

        $form->number('billingID', __('Billing ID'))
            ->rules('nullable|integer');

        /*
        |----------------------------------------------------------------------
        | FORM CALLBACKS
        |----------------------------------------------------------------------
        */

        // Before saving
        $form->saving(function (Form $form) {
            // Normalize fields
            if ($form->regno) {
                $form->regno = strtoupper(trim($form->regno));
            }
            if ($form->firstname) {
                $form->firstname = strtoupper(trim($form->firstname));
            }
            if ($form->othername) {
                $form->othername = strtoupper(trim($form->othername));
            }
            if ($form->email) {
                $form->email = strtolower(trim($form->email));
            }
            if ($form->progid) {
                $form->progid = strtoupper(trim($form->progid));
            }

            // Validate entry year
            if ($form->entryyear && ($form->entryyear < 1990 || $form->entryyear > (date('Y') + 1))) {
                admin_error('Error', 'Invalid entry year');
                return back()->withInput();
            }
        });

        // After saving
        $form->saved(function (Form $form) {
            \Log::info('Student saved', [
                'regno' => $form->model()->regno,
                'name' => $form->model()->full_name,
                'programme' => $form->model()->progid,
                'user' => \Admin::user()->username ?? 'unknown',
            ]);
        });

        /*
        |----------------------------------------------------------------------
        | FORM SETTINGS
        |----------------------------------------------------------------------
        */

        $form->tools(function (Form\Tools $tools) {
            // Keep all tools enabled
        });

        return $form;
    }
}
