<?php

namespace App\Admin\Controllers;

use App\Models\RemoteDataBaseSync;
use App\Services\RemoteSyncService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RemoteDataBaseSyncController extends AdminController
{
    /**
     * Sync service instance
     */
    protected $syncService;

    /**
     * Constructor
     */
    public function __construct(RemoteSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Remote Database Syncs';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RemoteDataBaseSync());

        $grid->model()->orderBy('id', 'desc');
        $grid->disableExport();
        
        $grid->column('id', __('ID'))->sortable();
        
        $grid->column('table_name', __('Table Name'))->display(function ($tableName) {
            return "<strong>{$tableName}</strong>";
        });
        
        $grid->column('status', __('Status'))->display(function ($status) {
            $colors = [
                'pending' => 'warning',
                'processing' => 'info',
                'completed' => 'success',
                'failed' => 'danger',
                'paused' => 'secondary',
            ];
            $color = $colors[$status] ?? 'default';
            $icon = $status === 'processing' ? 'fa-spinner fa-spin' : 
                   ($status === 'completed' ? 'fa-check' : 
                   ($status === 'failed' ? 'fa-times' : 'fa-clock-o'));
            
            return "<span class='label label-{$color}'><i class='fa {$icon}'></i> " . ucfirst($status) . "</span>";
        });
        
        $grid->column('progress', __('Progress'))->display(function () {
            $percentage = $this->progress_percentage;
            return "
                <div class='progress' style='margin-bottom: 0;'>
                    <div class='progress-bar progress-bar-striped' role='progressbar' 
                         style='width: {$percentage}%;'>
                        {$percentage}%
                    </div>
                </div>
            ";
        });
        
        $grid->column('number_of_records_synced', __('Synced'))->display(function () {
            $synced = number_format($this->number_of_records_synced);
            $total = $this->total_records ? number_format($this->total_records) : '?';
            return "{$synced} / {$total}";
        });
        
        $grid->column('statistics', __('Statistics'))->display(function () {
            return "
                <small>
                    <i class='fa fa-plus-circle text-success'></i> {$this->records_inserted} |
                    <i class='fa fa-edit text-info'></i> {$this->records_updated} |
                    <i class='fa fa-forward text-warning'></i> {$this->records_skipped} |
                    <i class='fa fa-times-circle text-danger'></i> {$this->records_failed}
                </small>
            ";
        });
        
        $grid->column('duration_seconds', __('Duration'))->display(function ($seconds) {
            if (!$seconds) return '-';
            return gmdate('H:i:s', $seconds);
        });
        
        $grid->column('triggered_by', __('Triggered By'));
        
        $grid->column('created_at', __('Created'))->display(function ($date) {
            return date('Y-m-d H:i:s', strtotime($date));
        })->sortable();
        
        $grid->column('sync_now', __('Action'))->display(function () {
            $url = url("/sync/{$this->id}/monitor");
            $statusClass = $this->status === 'pending' ? 'success' : 
                          ($this->status === 'processing' ? 'info' : 'default');
            $disabled = $this->status === 'processing' ? 'disabled' : '';
            
            return "<a href='{$url}' target='_blank' class='btn btn-xs btn-{$statusClass}' {$disabled}>
                <i class='fa fa-play'></i> Sync Now
            </a>";
        });

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            
            $filter->equal('table_name', 'Table Name');
            $filter->equal('status', 'Status')->select([
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'failed' => 'Failed',
                'paused' => 'Paused',
            ]);
            $filter->between('created_at', 'Created')->datetime();
        });

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            
            // Only allow delete for non-processing syncs
            if ($actions->row->status === 'processing') {
                $actions->disableDelete();
            }
        });

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
        $show = new Show(RemoteDataBaseSync::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('table_name', __('Table Name'));
        
        $show->field('status', __('Status'))->as(function ($status) {
            return ucfirst($status);
        })->label([
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'paused' => 'default',
        ]);
        
        $show->field('message', __('Message'));
        
        $show->divider('Sync Progress');
        
        $show->field('number_of_records_synced', __('Records Synced'))->as(function ($value) {
            return number_format($value);
        });
        $show->field('total_records', __('Total Records'))->as(function ($value) {
            return $value ? number_format($value) : 'Unknown';
        });
        $show->field('progress_percentage', __('Progress'))->as(function ($value) {
            return $value . '%';
        });
        
        $show->divider('Statistics');
        
        $show->field('records_inserted', __('Inserted'))->as(function ($value) {
            return number_format($value);
        });
        $show->field('records_updated', __('Updated'))->as(function ($value) {
            return number_format($value);
        });
        $show->field('records_skipped', __('Skipped'))->as(function ($value) {
            return number_format($value);
        });
        $show->field('records_failed', __('Failed'))->as(function ($value) {
            return number_format($value);
        });
        
        $show->divider('Timing');
        
        $show->field('sync_started_at', __('Started At'));
        $show->field('sync_completed_at', __('Completed At'));
        $show->field('duration_seconds', __('Duration'))->as(function ($seconds) {
            return $seconds ? gmdate('H:i:s', $seconds) : '-';
        });
        
        $show->divider('Configuration');
        
        $show->field('start_id', __('Start ID'));
        $show->field('range_limit', __('Batch Size'));
        $show->field('triggered_by', __('Triggered By'));
        $show->field('last_synced_at', __('Last Synced At'));
        
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));
        
        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RemoteDataBaseSync());

        // Define supported tables with transformation logic
        $supportedTables = [
            'students' => 'Students',
            'courses' => 'Courses',
            'enrollments' => 'Enrollments',
            'acad_results' => 'Results (Academic Results)',
            // Add more tables as logic is implemented
        ];

        $form->select('table_name', __('Table Name'))
            ->options($supportedTables)
            ->required()
            ->help('Select the remote database table to sync (only tables with transformation logic are shown)');
        
        $form->decimal('range_limit', __('Batch Size'));
        
        $form->divider('Optional Configuration');
        
        $form->text('triggered_by', __('Triggered By'))
            ->default(auth()->user()->name ?? 'System')
            ->readonly();
        
        $form->hidden('status')->default('pending');
        $form->hidden('start_id')->default(0);
        
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        
        // After saving, show success message with link to monitor
        $form->saved(function (Form $form) {
            $syncId = $form->model()->id;
            $monitorUrl = url("/sync/{$syncId}/monitor");
            
            admin_toastr('Sync record created successfully! Click "Sync Now" to start.', 'success');
        });

        return $form;
    }
}
