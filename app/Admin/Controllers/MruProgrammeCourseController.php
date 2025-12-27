<?php

namespace App\Admin\Controllers;

use App\Models\MruProgrammeCourse;
use App\Models\MruProgramme;
use App\Models\MruCourse;
use App\Models\MruCurriculum;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * MruProgrammeCourseController
 * 
 * Manages programme curriculum - the mapping between programmes and courses.
 * 
 * Purpose:
 * - Define which courses belong to which programmes
 * - Specify when courses should be taken (year and semester)
 * - Manage curriculum structure for academic planning
 * - Facilitate course allocation and student registration
 * 
 * Features:
 * - View all programme-course mappings
 * - Filter by programme, year, or semester
 * - Add/edit/delete curriculum entries
 * - Bulk import/export curriculum data
 * 
 * @package App\Admin\Controllers
 */
class MruProgrammeCourseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Programme Courses (Curriculum)';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruProgrammeCourse());

        // Load relationships
        $grid->model()
            ->with(['programme', 'course', 'curriculum'])
            ->orderBy('progcode')
            ->orderBy('study_year')
            ->orderBy('semester')
            ->orderBy('course_code');

        // Enable batch actions
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        // Quick search
        $grid->quickSearch(function ($model, $query) {
            $model->where(function ($q) use ($query) {
                $q->where('progcode', 'like', "%{$query}%")
                  ->orWhere('course_code', 'like', "%{$query}%")
                  ->orWhereHas('programme', function($q2) use ($query) {
                      $q2->where('progname', 'like', "%{$query}%");
                  })
                  ->orWhereHas('course', function($q2) use ($query) {
                      $q2->where('coursename', 'like', "%{$query}%");
                  });
            });
        })->placeholder('Search: Programme, Course...');

        /*
        |--------------------------------------------------------------------------
        | GRID COLUMNS
        |--------------------------------------------------------------------------
        */

        $grid->column('ID', 'ID')->sortable();

        $grid->column('progcode', 'Programme')
            ->display(function () {
                $name = $this->programme ? $this->programme->progname : '-';
                return "<div><strong>{$this->progcode}</strong><br><small style='color:#666;'>{$name}</small></div>";
            })
            ->sortable();

        $grid->column('course_code', 'Course')
            ->display(function () {
                $name = $this->course ? $this->course->courseName : '-';
                return "<div><strong>{$this->course_code}</strong><br><small style='color:#666;'>{$name}</small></div>";
            })
            ->sortable();

        $grid->column('study_year', 'Year')->sortable();
        
        $grid->column('semester', 'Semester')
            ->display(function ($semester) {
                $colors = [1 => 'primary', 2 => 'success'];
                $color = $colors[$semester] ?? 'default';
                return "<span class='label label-{$color}'>Sem {$semester}</span>";
            })
            ->sortable();

        $grid->column('year_semester', 'Year & Sem')
            ->display(function () {
                return "Y{$this->study_year}S{$this->semester}";
            });

        $grid->column('CurriculumID', 'Curriculum')
            ->display(function () {
                if (!$this->curriculum) {
                    return "<span class='label label-default'>ID: {$this->CurriculumID}</span>";
                }
                $year = $this->curriculum->StartYear;
                $intake = $this->curriculum->intake;
                return "<div><small style='color:#666;'>ID: {$this->CurriculumID}</small><br><span class='label label-info'>{$year} {$intake}</span></div>";
            })
            ->sortable();

        /*
        |--------------------------------------------------------------------------
        | GRID FILTERS
        |--------------------------------------------------------------------------
        */

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            // Programme filter
            $filter->equal('progcode', 'Programme')
                ->select(MruProgramme::where('progcode', '!=', '-')
                    ->where('progcode', '!=', 'PLACEHOLDER')
                    ->pluck('progcode', 'progcode'));

            // Year filter
            $filter->equal('study_year', 'Study Year')
                ->select([
                    1 => 'Year 1',
                    2 => 'Year 2',
                    3 => 'Year 3',
                    4 => 'Year 4',
                    5 => 'Year 5',
                ]);

            // Semester filter
            $filter->equal('semester', 'Semester')
                ->select([
                    1 => 'Semester 1',
                    2 => 'Semester 2',
                ]);

            // Course filter
            $filter->like('course_code', 'Course Code');

            // Curriculum filter with dropdown
            $filter->equal('CurriculumID', 'Curriculum')
                ->select(function () {
                    return MruCurriculum::orderBy('StartYear', 'desc')
                        ->orderBy('intake')
                        ->get()
                        ->pluck('full_name', 'ID');
                });
        });

        /*
        |--------------------------------------------------------------------------
        | GRID ACTIONS
        |--------------------------------------------------------------------------
        */

        $grid->actions(function ($actions) {
            // Keep all default actions
        });

        /*
        |--------------------------------------------------------------------------
        | GRID SETTINGS
        |--------------------------------------------------------------------------
        */

        $grid->paginate(50);
        $grid->disableExport();

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
        $show = new Show(MruProgrammeCourse::findOrFail($id));

        $show->field('ID', 'ID');
        $show->field('progcode', 'Programme Code');
        $show->field('programme.progname', 'Programme Name');
        $show->field('course_code', 'Course Code');
        $show->field('course.courseName', 'Course Name');
        $show->field('study_year', 'Study Year');
        $show->field('semester', 'Semester');
        $show->field('CurriculumID', 'Curriculum ID');
        $show->field('curriculum.Tittle', 'Curriculum Title');
        $show->field('curriculum.StartYear', 'Curriculum Start Year');
        $show->field('curriculum.intake', 'Curriculum Intake');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruProgrammeCourse());

        // Programme selection
        $form->select('progcode', 'Programme')
            ->options(MruProgramme::where('progcode', '!=', '-')
                ->where('progcode', '!=', 'PLACEHOLDER')
                ->pluck('progname', 'progcode'))
            ->required()
            ->rules('required');

        // Course selection
        $form->select('course_code', 'Course')
            ->options(MruCourse::pluck('coursename', 'courseID'))
            ->required()
            ->rules('required');

        // Study year
        $form->select('study_year', 'Study Year')
            ->options([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
                5 => 'Year 5',
            ])
            ->default(1)
            ->required()
            ->rules('required|integer|min:1|max:5');

        // Semester
        $form->select('semester', 'Semester')
            ->options([
                1 => 'Semester 1',
                2 => 'Semester 2',
            ])
            ->default(1)
            ->required()
            ->rules('required|integer|in:1,2');

        // Curriculum selection
        $form->select('CurriculumID', 'Curriculum')
            ->options(function () {
                return MruCurriculum::orderBy('StartYear', 'desc')
                    ->orderBy('intake')
                    ->get()
                    ->pluck('full_name', 'ID');
            })
            ->help('Select the approved curriculum version for this course mapping');

        return $form;
    }
}
