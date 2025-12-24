<?php

namespace App\Admin\Controllers;

use App\Models\MruPracticalExamMark;
use App\Models\MruStudent;
use App\Models\MruCourseworkSetting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

/**
 * MruPracticalExamMarkController
 * 
 * Manages practical exam marks entered by lecturers
 * Table: acad_practicalexam_marks (1,141 records)
 */
class MruPracticalExamMarkController extends AdminController
{
    protected $title = 'MRU Practical Exam Marks';

    protected function grid()
    {
        $grid = new Grid(new MruPracticalExamMark());
        
        // Eager load relationships
        $grid->model()->with(['student', 'settings.course'])
            ->orderBy('ID', 'desc');
        $grid->paginate(20);

        $grid->column('ID', __('ID'))->sortable();

        $grid->column('student_info', __('Student'))->display(function () {
            $student = $this->student;
            $name = $student ? $student->full_name : 'N/A';
            return "<div><strong style='color:#0066cc;'>{$this->reg_no}</strong><br><small style='font-size:12px;color:#666;'>{$name}</small></div>";
        });

        $grid->column('course_info', __('Course'))->display(function () {
            $settings = $this->settings;
            if (!$settings) return 'N/A';
            $courseCode = $settings->courseID;
            $semester = $settings->semester;
            $year = $settings->acadyear;
            return "<strong>{$courseCode}</strong> {$year} Sem {$semester}";
        });

        $grid->column('practicals', __('Practicals'))->display(function () {
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
            
            $parts = [];
            for ($i = 0; $i < 4; $i++) {
                if ($maxes[$i] > 0) {
                    $parts[] = "P" . ($i+1) . ": {$marks[$i]}/{$maxes[$i]}";
                }
            }
            return implode(' | ', $parts);
        });

        $grid->column('tests', __('Practical Tests'))->display(function () {
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
            
            $html = '<div style="line-height:1.5;">';
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

        $grid->column('final_score', __('Final Score'))->display(function ($score) {
            $settings = $this->settings;
            $total = $settings ? $settings->total_mark : 30;
            $percentage = $total > 0 ? round(($score / $total) * 100) : 0;
            $icon = $percentage >= 80 ? '🔬' : ($percentage >= 70 ? '⭐' : ($percentage >= 60 ? '👍' : '✓'));
            $color = $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
            return "<div style='text-align:center;'>
                <div style='font-size:20px;'>{$icon}</div>
                <strong style='font-size:16px;'>{$score}/{$total}</strong><br>
                <span class='label label-{$color}'>{$percentage}%</span>
            </div>";
        })->sortable();

        $grid->column('stud_status', __('Status'))->display(function ($status) {
            $color = $this->status_color;
            return "<span class='label label-{$color}'>{$status}</span>";
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('reg_no', __('Student RegNo'));
            $filter->equal('CSID', __('Setting ID'));
            $filter->equal('stud_status', __('Status'))->select([
                'REGULAR' => 'REGULAR',
                'RETAKE' => 'RETAKE',
                'CARRY' => 'CARRY',
            ]);
            $filter->between('final_score', __('Final Score Range'));
        });

        $grid->quickSearch('reg_no');
        $grid->disableBatchActions();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(MruPracticalExamMark::findOrFail($id));
        
        $show->field('ID', __('ID'));
        $show->field('reg_no', __('Student RegNo'));
        $show->field('student.sname', __('Surname'));
        $show->field('student.fname', __('First Name'));
        
        $show->divider('Practical Assessments');
        $show->field('ass_1_mark', __('Practical 1'));
        $show->field('ass_2_mark', __('Practical 2'));
        $show->field('ass_3_mark', __('Practical 3'));
        $show->field('ass_4_mark', __('Practical 4'));
        
        $show->divider('Practical Tests');
        $show->field('test_1_mark', __('Test 1'));
        $show->field('test_2_mark', __('Test 2'));
        $show->field('test_3_mark', __('Test 3'));
        
        $show->divider('Summary');
        $show->field('final_score', __('Final Score'));
        $show->field('stud_status', __('Student Status'));
        $show->field('CSID', __('Setting ID'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new MruPracticalExamMark());

        $form->select('reg_no', __('Student'))
            ->options(function ($value) {
                if ($value) {
                    $student = MruStudent::where('regno', $value)->first();
                    return $student ? [$value => $value . ' - ' . $student->sname . ' ' . $student->fname] : [];
                }
                return [];
            })
            ->ajax('/admin/api/students')
            ->required();

        $form->select('CSID', __('Practical Setting'))
            ->options(MruCourseworkSetting::orderBy('ID', 'desc')
                ->limit(100)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->ID => $item->courseID . ' - ' . $item->acadyear . ' Sem ' . $item->semester];
                }))
            ->required();

        $form->divider('Practical Assessments');
        $form->decimal('ass_1_mark', __('Practical 1 Mark'))->default(0);
        $form->decimal('ass_2_mark', __('Practical 2 Mark'))->default(0);
        $form->decimal('ass_3_mark', __('Practical 3 Mark'))->default(0);
        $form->decimal('ass_4_mark', __('Practical 4 Mark'))->default(0);

        $form->divider('Practical Tests');
        $form->decimal('test_1_mark', __('Test 1 Mark'))->default(0);
        $form->decimal('test_2_mark', __('Test 2 Mark'))->default(0);
        $form->decimal('test_3_mark', __('Test 3 Mark'))->default(0);

        $form->divider('Summary');
        $form->decimal('final_score', __('Final Score'))->required();

        $form->select('stud_status', __('Student Status'))
            ->options(['REGULAR' => 'REGULAR', 'RETAKE' => 'RETAKE', 'CARRY' => 'CARRY'])
            ->default('REGULAR')
            ->required();

        // Auto-calculate final score
        $form->saving(function (Form $form) {
            $total = ($form->ass_1_mark ?? 0) + ($form->ass_2_mark ?? 0) + 
                     ($form->ass_3_mark ?? 0) + ($form->ass_4_mark ?? 0) + 
                     ($form->test_1_mark ?? 0) + ($form->test_2_mark ?? 0) + 
                     ($form->test_3_mark ?? 0);
            $form->final_score = $total;
        });

        return $form;
    }
}
