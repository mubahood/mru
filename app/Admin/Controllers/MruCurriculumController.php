<?php

namespace App\Admin\Controllers;

use App\Models\MruCurriculum;
use App\Models\MruProgramme;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MruCurriculumController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Curriculum Versions';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruCurriculum());

        // Remove batch actions
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });

        // Disable create button for safety (curricula should be managed carefully)
        $grid->disableCreateButton();

        // Set default ordering
        $grid->model()->ordered();

        // Configure grid display
        $grid->column('ID', 'ID')->sortable();

        $grid->column('Progcode', 'Programme')
            ->display(function () {
                $name = $this->programme ? $this->programme->progname : '-';
                return "<div><strong>{$this->Progcode}</strong><br><small style='color:#666;'>{$name}</small></div>";
            })
            ->sortable();

        $grid->column('Tittle', 'Title')
            ->display(function ($title) {
                return "<strong>{$title}</strong>";
            })
            ->sortable();

        $grid->column('StartYear', 'Start Year')
            ->display(function ($year) {
                $color = $year >= 2020 ? 'success' : ($year >= 2015 ? 'primary' : 'default');
                return "<span class='label label-{$color}'>{$year}</span>";
            })
            ->sortable();

        $grid->column('intake', 'Intake')
            ->display(function ($intake) {
                $colors = [
                    'AUGUST' => 'info',
                    'JANUARY' => 'warning',
                    'JULY' => 'primary',
                    'JUNE' => 'success',
                    'FEBRUARY' => 'danger'
                ];
                $color = $colors[$intake] ?? 'default';
                return "<span class='label label-{$color}'>{$intake}</span>";
            })
            ->sortable();

        $grid->column('course_count', 'Courses')
            ->display(function () {
                $count = $this->programmeCourses()->count();
                $color = $count > 50 ? 'success' : ($count > 0 ? 'info' : 'default');
                return "<span class='label label-{$color}'>{$count}</span>";
            });

        $grid->column('Description', 'Description')
            ->display(function ($desc) {
                return $desc ? substr($desc, 0, 100) . '...' : '-';
            });

        // Quick search
        $grid->quickSearch('Tittle', 'Progcode', 'Description');

        // Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->equal('Progcode', 'Programme')
                ->select(function () {
                    return MruProgramme::orderBy('progcode')->pluck('progname', 'progcode');
                });

            $filter->equal('StartYear', 'Start Year');

            $filter->equal('intake', 'Intake')->select([
                'AUGUST' => 'August',
                'JANUARY' => 'January',
                'JULY' => 'July',
                'JUNE' => 'June',
                'FEBRUARY' => 'February',
            ]);

            $filter->where(function ($query) {
                $currentYear = date('Y');
                $cutoffYear = $currentYear - 5;
                $query->where('StartYear', '>=', $cutoffYear);
            }, 'Recent Only (Last 5 Years)')->checkbox('1');

            $filter->where(function ($query) {
                $query->whereHas('programmeCourses');
            }, 'With Courses')->checkbox('1');
        });

        // Pagination
        $grid->paginate(50);

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
        $show = new Show(MruCurriculum::findOrFail($id));

        $show->field('ID', 'ID');
        $show->field('Tittle', 'Title');
        $show->field('Description', 'Description');
        $show->field('Progcode', 'Programme Code');
        $show->field('programme.progname', 'Programme Name');
        $show->field('StartYear', 'Start Year');
        $show->field('intake', 'Intake');
        $show->field('course_count', 'Total Courses');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruCurriculum());

        $form->text('Tittle', 'Title')
            ->required()
            ->rules('required|max:45')
            ->help('Example: ACAD 2025 AUGUST');

        $form->select('Progcode', 'Programme')
            ->options(function () {
                return MruProgramme::orderBy('progcode')->pluck('progname', 'progcode');
            })
            ->required()
            ->rules('required');

        $form->number('StartYear', 'Start Year')
            ->required()
            ->min(2000)
            ->max(2050)
            ->default(date('Y'))
            ->rules('required|integer|min:2000|max:2050');

        $form->select('intake', 'Intake')
            ->options([
                'AUGUST' => 'August',
                'JANUARY' => 'January',
                'JULY' => 'July',
                'JUNE' => 'June',
                'FEBRUARY' => 'February',
            ])
            ->required()
            ->rules('required');

        $form->textarea('Description', 'Description')
            ->rows(3)
            ->rules('max:500')
            ->help('Brief description of curriculum approval and details');

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
