<?php

namespace App\Admin\Controllers;

use App\Models\MruAcademicResultExport;
use App\Models\MruProgramme;
use App\Models\MruFaculty;
use App\Models\MruAcademicYear;
use App\Exports\MruAcademicResultExcelExport;
use App\Services\MruAcademicResultPdfService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MruAcademicResultExportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MRU Academic Result Exports';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MruAcademicResultExport());

        $grid->model()->with(['creator', 'programme', 'faculty'])
            ->orderBy('id', 'desc');

        $grid->column('id', __('ID'))->sortable();
        
        $grid->column('export_name', __('Export Name'))
            ->display(function ($name) {
                return "<strong>{$name}</strong>";
            });

        $grid->column('export_type', __('Type'))
            ->label([
                'excel' => 'success',
                'pdf' => 'danger',
                'both' => 'primary',
            ])
            ->sortable();

        $grid->column('academic_year', __('Year'))->sortable();
        $grid->column('semester', __('Sem'))->sortable();

        $grid->column('programme_id', __('Programme'))
            ->display(function ($progId) {
                if (!$progId) return '<span class="label label-default">All</span>';
                return $this->programme ? $this->programme->progname : $progId;
            });

        $grid->column('faculty_code', __('Faculty'))
            ->display(function ($code) {
                if (!$code) return '<span class="label label-default">All</span>';
                return $this->faculty ? $this->faculty->faculty_name : $code;
            });

        $grid->column('total_records', __('Records'))
            ->display(function ($count) {
                return $count > 0 
                    ? "<span class='badge badge-info'>{$count}</span>" 
                    : "<span class='badge badge-secondary'>0</span>";
            })
            ->sortable();

        $grid->column('status', __('Status'))
            ->label([
                'pending' => 'warning',
                'processing' => 'info',
                'completed' => 'success',
                'failed' => 'danger',
            ])
            ->sortable();

        $grid->column('created_by', __('Created By'))
            ->display(function ($userId) {
                return $this->creator ? $this->creator->name : 'N/A';
            });

        $grid->column('created_at', __('Created At'))
            ->display(function ($date) {
                return date('d M Y H:i', strtotime($date));
            })
            ->sortable();

        $grid->column('generate', __('GENERATE'))
            ->display(function () {
                $url = url("/mru-academic-result-generate?id=$this->id");
                return "<a href='$url' target='_blank' class='btn btn-sm btn-primary'>
                    <i class='fa fa-play'></i> GENERATE
                </a>";
            });

        $grid->column('download_link', __('Download Link'))
            ->display(function () {
                $actions = '';
                
                if ($this->excel_path && file_exists(storage_path('app/' . $this->excel_path))) {
                    $actions .= "<a href='" . admin_url("mru-academic-result-exports/{$this->id}/download-excel") . "' class='btn btn-xs btn-success' style='margin-right: 5px;'>
                        <i class='fa fa-file-excel-o'></i> Excel
                    </a>";
                }
                
                if ($this->pdf_path && file_exists(storage_path('app/' . $this->pdf_path))) {
                    $actions .= "<a href='" . admin_url("mru-academic-result-exports/{$this->id}/download-pdf") . "' class='btn btn-xs btn-danger'>
                        <i class='fa fa-file-pdf-o'></i> PDF
                    </a>";
                }
                
                return $actions ?: '<span class="text-muted">Not generated yet</span>';
            });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            
            $filter->like('export_name', 'Export Name');
            
            $filter->equal('export_type', 'Type')->select([
                'excel' => 'Excel',
                'pdf' => 'PDF',
                'both' => 'Both',
            ]);

            $filter->equal('academic_year', 'Academic Year')
                ->select(MruAcademicYear::pluck('acadyear', 'acadyear')->toArray());

            $filter->equal('semester', 'Semester')->select([
                '1' => 'Semester 1',
                '2' => 'Semester 2',
                '3' => 'Semester 3',
            ]);

            $filter->equal('programme_id', 'Programme')
                ->select(MruProgramme::pluck('progname', 'progcode')->toArray());

            $filter->equal('faculty_code', 'Faculty')
                ->select(MruFaculty::pluck('faculty_name', 'faculty_code')->toArray());

            $filter->equal('status', 'Status')->select([
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'failed' => 'Failed',
            ]);

            $filter->between('created_at', 'Created At')->datetime();
        });

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            
            $row = $actions->row;
            if ($row->status === 'failed' || $row->status === 'pending') {
                $actions->append("<a href='" . admin_url("mru-academic-result-exports/{$row->id}/regenerate") . "' class='btn btn-xs btn-warning'>
                    <i class='fa fa-refresh'></i> Regenerate
                </a>");
            }
        });

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
        $show = new Show(MruAcademicResultExport::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('export_name', __('Export Name'));
        $show->field('export_type', __('Export Type'))->as(function ($type) {
            return ucfirst($type);
        });
        
        $show->divider();
        
        $show->field('academic_year', __('Academic Year'));
        $show->field('semester', __('Semester'));
        $show->field('programme_id', __('Programme'))->as(function ($progId) {
            return $progId ? ($this->programme ? $this->programme->progname : $progId) : 'All Programmes';
        });
        $show->field('faculty_code', __('Faculty'))->as(function ($code) {
            return $code ? ($this->faculty ? $this->faculty->faculty_name : $code) : 'All Faculties';
        });
        
        $show->divider();
        
        $show->field('include_coursework', __('Include Coursework'))->as(function ($val) {
            return $val ? 'Yes' : 'No';
        });
        $show->field('include_practical', __('Include Practical'))->as(function ($val) {
            return $val ? 'Yes' : 'No';
        });
        $show->field('include_summary', __('Include Summary'))->as(function ($val) {
            return $val ? 'Yes' : 'No';
        });
        $show->field('sort_by', __('Sort By'))->as(function ($sort) {
            return ucfirst($sort);
        });
        
        $show->divider();
        
        $show->field('total_records', __('Total Records'));
        $show->field('status', __('Status'))->as(function ($status) {
            return ucfirst($status);
        });
        $show->field('error_message', __('Error Message'));
        
        $show->divider();
        
        $show->field('excel_path', __('Excel File'))->as(function ($path) {
            return $path && file_exists(storage_path('app/' . $path)) 
                ? "<a href='" . admin_url("mru-academic-result-exports/{$this->id}/download-excel") . "' class='btn btn-success'>Download Excel</a>" 
                : 'Not generated';
        })->unescape();
        
        $show->field('pdf_path', __('PDF File'))->as(function ($path) {
            return $path && file_exists(storage_path('app/' . $path)) 
                ? "<a href='" . admin_url("mru-academic-result-exports/{$this->id}/download-pdf") . "' class='btn btn-danger'>Download PDF</a>" 
                : 'Not generated';
        })->unescape();
        
        $show->divider();
        
        $show->field('created_by', __('Created By'))->as(function ($userId) {
            return $this->creator ? $this->creator->name : 'N/A';
        });
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MruAcademicResultExport());

        $form->text('export_name', __('Export Name'))
            ->required()
            ->help('Enter a descriptive name for this export');

        $form->select('export_type', __('Export Type'))
            ->options([
                'excel' => 'Excel Only',
                'pdf' => 'PDF Only',
                'both' => 'Both Excel and PDF',
            ])
            ->default('excel')
            ->required();

        $form->divider('Filters');

        $form->select('academic_year', __('Academic Year'))
            ->options(MruAcademicYear::pluck('acadyear', 'acadyear')->toArray())
            ->help('Leave empty for all years');

        $form->select('semester', __('Semester'))
            ->options([
                '1' => 'Semester 1',
                '2' => 'Semester 2',
                '3' => 'Semester 3',
            ])
            ->help('Leave empty for all semesters');

        $form->select('programme_id', __('Programme'))
            ->options(MruProgramme::pluck('progname', 'progcode')->toArray())
            ->help('Leave empty for all programmes');

        $form->select('faculty_code', __('Faculty'))
            ->options(MruFaculty::pluck('faculty_name', 'faculty_code')->toArray())
            ->help('Leave empty for all faculties');

        $form->divider('Export Options');

        $form->switch('include_coursework', __('Include Coursework Marks'))
            ->default(1);

        $form->switch('include_practical', __('Include Practical Marks'))
            ->default(1);

        $form->switch('include_summary', __('Include Summary Statistics'))
            ->default(1);

        $form->select('sort_by', __('Sort By'))
            ->options([
                'student' => 'Student Name',
                'course' => 'Course Code',
                'grade' => 'Grade',
                'programme' => 'Programme',
            ])
            ->default('student')
            ->required();

        $form->hidden('created_by')->default(Admin::user()->id);
        $form->hidden('status')->default('pending');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });

        return $form;
    }

    /**
     * Download Excel file
     */
    public function downloadExcel($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        
        if (!$export->excel_path || !file_exists(storage_path('app/' . $export->excel_path))) {
            admin_toastr('Excel file not found', 'error');
            return back();
        }

        return Storage::download($export->excel_path, basename($export->excel_path));
    }

    /**
     * Download PDF file
     */
    public function downloadPdf($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        
        if (!$export->pdf_path || !file_exists(storage_path('app/' . $export->pdf_path))) {
            admin_toastr('PDF file not found', 'error');
            return back();
        }

        return Storage::download($export->pdf_path, basename($export->pdf_path));
    }

    /**
     * Regenerate export
     */
    public function regenerate($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        
        $this->processExport($export);
        
        return redirect(admin_url('mru-academic-result-exports'));
    }
}
