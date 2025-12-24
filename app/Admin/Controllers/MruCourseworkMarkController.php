<?php

namespace App\Admin\Controllers;

use App\Models\MruCourseworkMark;
use App\Models\MruCourseworkSetting;
use App\Models\MruStudent;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;

/**
 * MruCourseworkMarkController
 * 
 * Manages coursework marks (assignments and tests) entered by lecturers
 * Table: acad_coursework_marks (114,703 records)
 */
class MruCourseworkMarkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MRU Coursework Marks';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruCourseworkMark());

        // Eager load relationships
        $grid->model()->with(['student', 'settings.course'])
            ->orderBy('ID', 'desc');
        $grid->paginate(20);

        // Column 1: ID
        $grid->column('ID', __('ID'))->sortable();

        // Column 2: Student Information
        $grid->column('student_info', __('Student'))->display(function () {
            $student = $this->student;
            $name = $student ? $student->full_name : 'N/A';
            return "<div><strong style='color:#0066cc;'>{$this->reg_no}</strong><br><small style='font-size:12px;color:#666;'>{$name}</small></div>";
        });

        // Column 3: Course Information
        $grid->column('course_info', __('Course'))->display(function () {
            $settings = $this->settings;
            if (!$settings) return 'N/A';
            
            $course = $settings->course;
            $courseCode = $settings->courseID;
            $courseName = $course ? $course->courseName : 'N/A';
            $semester = $settings->semester;
            $year = $settings->acadyear;
            
            return "<div><strong>{$courseCode}</strong><br><small style='color:#666;'>{$courseName}</small><br><small style='color:#999;'>{$year} Sem {$semester}</small></div>";
        });

        // Column 4: Assignments (4 columns)
        $grid->column('assignments', __('Assignments'))->display(function () {
            $settings = $this->settings;
            $maxes = $settings ? [
                $settings->max_assn_1 ?? 0,
                $settings->max_assn_2 ?? 0,
                $settings->max_assn_3 ?? 0,
                $settings->max_assn_4 ?? 0,
            ] : [0, 0, 0, 0];
            
            $marks = [
                $this->ass_1_mark ?? 0,
                $this->ass_2_mark ?? 0,
                $this->ass_3_mark ?? 0,
                $this->ass_4_mark ?? 0,
            ];
            
            $html = '<div style="line-height: 1.5;">';
            for ($i = 0; $i < 4; $i++) {
                if ($maxes[$i] > 0) {
                    $percentage = $maxes[$i] > 0 ? round(($marks[$i] / $maxes[$i]) * 100) : 0;
                    $color = $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                    $html .= "<span class='label label-{$color}'>A" . ($i+1) . ": {$marks[$i]}/{$maxes[$i]}</span> ";
                }
            }
            $html .= '</div>';
            
            return $html;
        });

        // Column 5: Tests (3 columns)
        $grid->column('tests', __('Tests'))->display(function () {
            $settings = $this->settings;
            $maxes = $settings ? [
                $settings->max_test_1 ?? 0,
                $settings->max_test_2 ?? 0,
                $settings->max_test_3 ?? 0,
            ] : [0, 0, 0];
            
            $marks = [
                $this->test_1_mark ?? 0,
                $this->test_2_mark ?? 0,
                $this->test_3_mark ?? 0,
            ];
            
            $html = '<div style="line-height: 1.5;">';
            for ($i = 0; $i < 3; $i++) {
                if ($maxes[$i] > 0) {
                    $percentage = $maxes[$i] > 0 ? round(($marks[$i] / $maxes[$i]) * 100) : 0;
                    $color = $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                    $html .= "<span class='label label-{$color}'>T" . ($i+1) . ": {$marks[$i]}/{$maxes[$i]}</span> ";
                }
            }
            $html .= '</div>';
            
            return $html;
        });

        // Column 6: Final Score with Icon
        $grid->column('final_score', __('Final Score'))->display(function ($score) {
            $settings = $this->settings;
            $total = $settings ? $settings->total_mark : 30;
            $percentage = $total > 0 ? round(($score / $total) * 100) : 0;
            
            $icon = $percentage >= 80 ? '🏆' : ($percentage >= 70 ? '⭐' : ($percentage >= 60 ? '👍' : ($percentage >= 50 ? '✓' : '❌')));
            $color = $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
            
            return "<div style='text-align: center;'>
                <div style='font-size: 20px;'>{$icon}</div>
                <strong style='font-size: 16px; color: #333;'>{$score}/{$total}</strong><br>
                <span class='label label-{$color}'>{$percentage}%</span>
            </div>";
        })->sortable();

        // Column 7: Student Status
        $grid->column('stud_status', __('Status'))->display(function ($status) {
            $color = $this->status_color;
            return "<span class='label label-{$color}'>{$status}</span>";
        })->sortable();

        // Advanced Filters (3-column layout)
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            
            // Row 1
            $filter->column(1/3, function ($filter) {
                $filter->like('reg_no', __('Student RegNo'));
                $filter->equal('CSID', __('Setting ID'));
            });
            
            $filter->column(1/3, function ($filter) {
                $filter->equal('stud_status', __('Student Status'))->select([
                    'REGULAR' => 'REGULAR',
                    'RETAKE' => 'RETAKE',
                    'CARRY' => 'CARRY',
                    'DEAD YEAR' => 'DEAD YEAR',
                ]);
                $filter->between('final_score', __('Final Score Range'));
            });
            
            $filter->column(1/3, function ($filter) {
                $filter->where(function ($query) {
                    $query->whereHas('settings', function ($q) {
                        $q->where('courseID', 'like', "%{$this->input}%");
                    });
                }, __('Course ID'));
                
                $filter->where(function ($query) {
                    $query->whereHas('settings', function ($q) {
                        $q->where('acadyear', $this->input);
                    });
                }, __('Academic Year'));
            });
        });

        // Quick search
        $grid->quickSearch('reg_no');

        // Export
        $grid->export(function ($export) {
            $export->filename('MRU_Coursework_Marks_' . date('Y-m-d'));
            $export->column('ID', function ($value, $original) {
                return $original;
            });
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
        $show = new Show(MruCourseworkMark::findOrFail($id));

        $show->field('ID', __('ID'));
        $show->field('reg_no', __('Student RegNo'));
        
        // Student Information
        $show->divider();
        $show->field('student.sname', __('Surname'));
        $show->field('student.fname', __('First Name'));
        $show->field('student.oname', __('Other Name'));
        
        // Course Information
        $show->divider();
        $show->field('settings.courseID', __('Course ID'));
        $show->field('settings.course.CourseName', __('Course Name'));
        $show->field('settings.acadyear', __('Academic Year'));
        $show->field('settings.semester', __('Semester'));
        $show->field('settings.progID', __('Programme'));
        
        // Assignments
        $show->divider();
        $show->field('ass_1_mark', __('Assignment 1'))->as(function ($value) {
            $max = $this->settings->max_assn_1 ?? 0;
            return "{$value} / {$max}";
        });
        $show->field('ass_2_mark', __('Assignment 2'))->as(function ($value) {
            $max = $this->settings->max_assn_2 ?? 0;
            return "{$value} / {$max}";
        });
        $show->field('ass_3_mark', __('Assignment 3'))->as(function ($value) {
            $max = $this->settings->max_assn_3 ?? 0;
            return "{$value} / {$max}";
        });
        $show->field('ass_4_mark', __('Assignment 4'))->as(function ($value) {
            $max = $this->settings->max_assn_4 ?? 0;
            return "{$value} / {$max}";
        });
        
        // Tests
        $show->divider();
        $show->field('test_1_mark', __('Test 1'))->as(function ($value) {
            $max = $this->settings->max_test_1 ?? 0;
            return "{$value} / {$max}";
        });
        $show->field('test_2_mark', __('Test 2'))->as(function ($value) {
            $max = $this->settings->max_test_2 ?? 0;
            return "{$value} / {$max}";
        });
        $show->field('test_3_mark', __('Test 3'))->as(function ($value) {
            $max = $this->settings->max_test_3 ?? 0;
            return "{$value} / {$max}";
        });
        
        // Final Score
        $show->divider();
        $show->field('final_score', __('Final Score'))->as(function ($value) {
            $total = $this->settings->total_mark ?? 30;
            $percentage = $total > 0 ? round(($value / $total) * 100, 2) : 0;
            return "{$value} / {$total} ({$percentage}%)";
        });
        
        $show->field('stud_status', __('Student Status'));
        $show->field('CSID', __('Coursework Setting ID'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruCourseworkMark());

        $form->select('reg_no', __('Student'))
            ->options(function ($value) {
                if ($value) {
                    $student = MruStudent::where('regno', $value)->first();
                    return $student ? [$value => $value . ' - ' . $student->sname . ' ' . $student->fname] : [];
                }
                return MruStudent::orderBy('regno', 'desc')
                    ->limit(50)
                    ->get()
                    ->pluck(DB::raw("CONCAT(regno, ' - ', sname, ' ', fname)"), 'regno');
            })
            ->ajax('/admin/api/students')
            ->required();

        $form->select('CSID', __('Course/Semester/Year Setting'))
            ->options(function ($value) {
                if ($value) {
                    $setting = MruCourseworkSetting::find($value);
                    return $setting ? [$value => $setting->courseID . ' - ' . $setting->acadyear . ' Sem ' . $setting->semester] : [];
                }
                return MruCourseworkSetting::orderBy('ID', 'desc')
                    ->limit(100)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->ID => $item->courseID . ' - ' . $item->acadyear . ' Sem ' . $item->semester . ' (' . $item->progID . ')'];
                    });
            })
            ->required();

        $form->divider('Assignments');
        $form->decimal('ass_1_mark', __('Assignment 1 Mark'))->default(0);
        $form->decimal('ass_2_mark', __('Assignment 2 Mark'))->default(0);
        $form->decimal('ass_3_mark', __('Assignment 3 Mark'))->default(0);
        $form->decimal('ass_4_mark', __('Assignment 4 Mark'))->default(0);

        $form->divider('Tests');
        $form->decimal('test_1_mark', __('Test 1 Mark'))->default(0);
        $form->decimal('test_2_mark', __('Test 2 Mark'))->default(0);
        $form->decimal('test_3_mark', __('Test 3 Mark'))->default(0);

        $form->divider('Summary');
        $form->decimal('final_score', __('Final Score (Computed)'))
            ->help('Total of all assignments and tests')
            ->required();

        $form->select('stud_status', __('Student Status'))
            ->options([
                'REGULAR' => 'REGULAR',
                'RETAKE' => 'RETAKE',
                'CARRY' => 'CARRY',
                'DEAD YEAR' => 'DEAD YEAR',
            ])
            ->default('REGULAR')
            ->required();

        // Auto-calculate final score
        $form->saving(function (Form $form) {
            $total = ($form->ass_1_mark ?? 0) + 
                     ($form->ass_2_mark ?? 0) + 
                     ($form->ass_3_mark ?? 0) + 
                     ($form->ass_4_mark ?? 0) + 
                     ($form->test_1_mark ?? 0) + 
                     ($form->test_2_mark ?? 0) + 
                     ($form->test_3_mark ?? 0);
            
            $form->final_score = $total;
        });

        return $form;
    }
}
