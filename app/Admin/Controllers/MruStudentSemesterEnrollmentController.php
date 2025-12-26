<?php

namespace App\Admin\Controllers;

use App\Models\StudentHasSemeter;
use App\Models\MruSemester;
use App\Models\AcademicYear;
use App\Models\Service;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;

/**
 * MruStudentSemesterEnrollmentController
 * 
 * Manages student semester enrollments in the MRU system.
 * Links students to specific semesters for academic tracking.
 */
class MruStudentSemesterEnrollmentController extends AdminController
{
    /**
     * Title for current resource.
     */
    protected $title = 'MRU Student Semester Enrollments';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StudentHasSemeter());

        // Quick search
        $grid->quickSearch('registration_number', 'schoolpay_code', 'pegpay_code')
            ->placeholder('Search by Reg #, SchoolPay, or PegPay Code');

        // Scope to user's enterprise
        $grid->model()
            ->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'desc');

        // Columns
        $grid->column('id', 'ID')->sortable();
        
        $grid->column('student.name', 'Student')->sortable()
            ->display(function () {
                if (!$this->student) return 'N/A';
                $url = admin_url('students/' . $this->student_id);
                return "<a href='{$url}' target='_blank'><b>{$this->student->name_text}</b></a>";
            });

        $grid->column('registration_number', 'Reg #')->sortable();
        
        $grid->column('academic_year.name', 'Academic Year')->sortable();
        
        $grid->column('term.name', 'Semester')->sortable()
            ->display(function () {
                return $this->term ? "Semester {$this->term->name}" : 'N/A';
            });
        
        $grid->column('year_name', 'Year')->sortable()
            ->display(function ($year) {
                return "Year {$year}";
            });
        
        $grid->column('semester_name', 'Sem')->sortable();

        $grid->column('set_fees_balance_amount', 'Balance')->sortable()
            ->display(function ($amount) {
                return 'UGX ' . number_format($amount ?? 0);
            })->hide();

        $grid->column('is_processed', 'Processed')
            ->using(['Yes' => 'Yes', 'No' => 'No'])
            ->label([
                'Yes' => 'success',
                'No'  => 'warning',
            ])
            ->sortable();

        $grid->column('created_at', 'Enrolled')->sortable()
            ->display(function () {
                return $this->created_at ? $this->created_at->format('M d, Y') : 'N/A';
            });

        // Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            
            $u = Admin::user();
            
            // Student filter (Ajax)
            $ajaxUrl = url("/api/ajax-users?enterprise_id={$u->enterprise_id}&search_by_1=name&search_by_2=id&user_type=student&model=User");
            $filter->equal('student_id', 'Student')
                ->select(function ($id) {
                    $user = User::find($id);
                    return $user ? [$user->id => $user->name_text] : [];
                })
                ->ajax($ajaxUrl);

            // Academic Year filter
            $filter->equal('academic_year_id', 'Academic Year')
                ->select(AcademicYear::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('name', 'desc')
                    ->pluck('name', 'id'));

            // Semester filter using MruSemester
            $filter->equal('term_id', 'Semester')
                ->select(MruSemester::where('enterprise_id', $u->enterprise_id)
                    ->orderBy('academic_year_id', 'desc')
                    ->orderBy('name', 'asc')
                    ->get()
                    ->mapWithKeys(function ($sem) {
                        return [$sem->id => "Semester {$sem->name} - {$sem->academic_year->name}"];
                    }));

            // Year of Study
            $filter->equal('year_name', 'Year of Study')->select([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
            ]);

            // Semester of Study
            $filter->equal('semester_name', 'Semester of Study')->select([
                1 => 'Semester 1',
                2 => 'Semester 2',
            ]);

            // Registration Number
            $filter->like('registration_number', 'Reg #');

            // Processed status
            $filter->equal('is_processed', 'Processed')->select([
                'Yes' => 'Yes',
                'No' => 'No',
            ]);

            // Date range
            $filter->between('created_at', 'Enrolled Date')->date();
        });

        // Disable batch actions
        $grid->disableBatchActions();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(StudentHasSemeter::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('student.name', 'Student');
        $show->field('registration_number', 'Registration Number');
        $show->field('academic_year.name', 'Academic Year');
        $show->field('term.name', 'Semester')->as(function ($name) {
            return "Semester {$name}";
        });
        $show->field('year_name', 'Year of Study')->as(function ($year) {
            return "Year {$year}";
        });
        $show->field('semester_name', 'Semester of Study');
        $show->field('set_fees_balance_amount', 'Last Semester Balance')->as(function ($amount) {
            return 'UGX ' . number_format($amount ?? 0);
        });
        $show->field('schoolpay_code', 'SchoolPay Code');
        $show->field('pegpay_code', 'PegPay Code');
        $show->field('services', 'Services')->as(function ($ids) {
            if (empty($ids)) return 'None';
            $services = Service::whereIn('id', $ids)->pluck('name')->toArray();
            return implode(', ', $services);
        });
        $show->field('is_processed', 'Processed')->as(function ($val) {
            return $val == 'Yes' ? 'Yes' : 'No';
        });
        $show->field('remarks', 'Remarks');
        $show->field('enrolled_by.name', 'Enrolled By');
        $show->field('created_at', 'Enrolled At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StudentHasSemeter());

        $u = Admin::user();
        
        // Hidden fields
        $form->hidden('enterprise_id')->default($u->enterprise_id);
        $form->hidden('enrolled_by_id')->default($u->id);

        $form->divider('Student Information');

        // Student selection
        if ($form->isCreating()) {
            $ajaxUrl = url("/api/ajax-users?enterprise_id={$u->enterprise_id}&search_by_1=name&search_by_2=id&user_type=student&model=User");
            
            $form->select('student_id', 'Student')
                ->options(function ($id) {
                    $user = User::find($id);
                    return $user ? [$user->id => $user->name_text] : [];
                })
                ->ajax($ajaxUrl)
                ->rules('required')
                ->help('Search and select a student');
        } else {
            $form->display('student.name', 'Student');
        }

        $form->divider('Semester Information');

        // Academic Year
        $form->select('academic_year_id', 'Academic Year')
            ->options(AcademicYear::where('enterprise_id', $u->enterprise_id)
                ->orderBy('name', 'desc')
                ->pluck('name', 'id'))
            ->rules('required')
            ->load('term_id', '/api/get-semesters'); // Dynamic load semesters

        // Semester using MruSemester
        $form->select('term_id', 'Semester')
            ->options(function ($id) {
                if (!$id) return [];
                $sem = MruSemester::find($id);
                return $sem ? [$sem->id => "Semester {$sem->name}"] : [];
            })
            ->rules('required');

        // Year of Study
        $form->radio('year_name', 'Year of Study')
            ->options([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
            ])
            ->rules('required')
            ->default(1);

        // Semester of Study
        $form->radio('semester_name', 'Semester of Study')
            ->options([
                1 => 'Semester 1',
                2 => 'Semester 2',
            ])
            ->rules('required')
            ->default(1);

        $form->divider('Registration Details');

        // Registration Number
        $form->text('registration_number', 'Registration Number')
            ->rules('required')
            ->help('Student registration number');

        // Payment Codes
        $form->text('schoolpay_code', 'SchoolPay Code');
        $form->text('pegpay_code', 'PegPay Code');

        $form->divider('Fees & Services');

        // Last Semester Balance
        $form->decimal('set_fees_balance_amount', 'Last Semester Balance')
            ->default(0)
            ->rules('required|numeric|min:0')
            ->help('Previous semester balance to carry forward');

        // Update Fees Balance
        $form->hidden('update_fees_balance')->default('Yes');

        // Services
        $services = Service::where('enterprise_id', $u->enterprise_id)
            ->where('is_compulsory', 'No')
            ->pluck('name', 'id');

        $form->checkbox('services', 'Optional Services')
            ->options($services)
            ->help('Select additional services to subscribe');

        // Remarks
        $form->textarea('remarks', 'Remarks')
            ->rows(3)
            ->placeholder('Optional notes about this enrollment');

        // Processing status
        if ($form->isCreating()) {
            $form->hidden('is_processed')->default('No');
        } else {
            $form->radio('is_processed', 'Process This Enrollment?')
                ->options([
                    'No' => 'Not Yet',
                    'Yes' => 'Process Now',
                ])
                ->help('Processing will update fees and activate services');
        }

        // Form display options
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableReset();

        return $form;
    }
}
