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

        $grid->model()->with(['creator', 'programme'])
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
                'html' => 'info',
                'both' => 'primary',
            ])
            ->sortable();

        $grid->column('academic_year', __('Year'))->sortable();
        $grid->column('semester', __('Sem'))->sortable();

        $grid->column('study_year', __('Yr'))
            ->display(function ($year) {
                $colors = [
                    1 => 'info',
                    2 => 'success',
                    3 => 'warning',
                    4 => 'danger',
                ];
                $color = $colors[$year] ?? 'secondary';
                return "<span class='badge badge-{$color}'>Y{$year}</span>";
            })
            ->sortable();

        $grid->column('programme_id', __('Programme'))
            ->display(function ($progId) {
                if (!$progId) return '<span class="label label-default">All</span>';
                return $this->programme ? $this->programme->progname : $progId;
            });

        $grid->column('specialisation_id', __('Specialisation'))
            ->display(function ($specId) {
                if (!$specId) return '<span class="label label-default">All</span>';
                $spec = \DB::table('acad_specialisation')->where('spec_id', $specId)->first();
                return $spec ? $spec->abbrev : $specId;
            });

        $grid->column('range', __('Range'))
            ->display(function () {
                $start = $this->start_range ?? 1;
                $end = $this->end_range ?? 100;
                return "<span class='badge badge-info'>{$start}-{$end}</span>";
            });

        $grid->column('total_records', __('Records'))
            ->display(function ($count) {
                return $count > 0 
                    ? "<span class='badge badge-success'>{$count}</span>" 
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

        $grid->column('generate_excel', __('GEN EXCEL'))
            ->display(function () {
                $url = url("/mru-academic-result-generate?id=$this->id&type=excel");
                return "<a href='$url' target='_blank' class='btn btn-sm btn-success'>
                    <i class='fa fa-file-excel-o'></i>
                </a>";
            });

        $grid->column('generate_pdf', __('GEN PDF'))
            ->display(function () {
                $url = url("/mru-academic-result-generate?id=$this->id&type=pdf");
                return "<a href='$url' target='_blank' class='btn btn-sm btn-danger'>
                    <i class='fa fa-file-pdf-o'></i>
                </a>";
            });

        $grid->column('generate_html', __('GEN HTML'))
            ->display(function () {
                $url = url("/mru-academic-result-generate?id=$this->id&type=html");
                return "<a href='$url' target='_blank' class='btn btn-sm btn-info'>
                    <i class='fa fa-html5'></i>
                </a>";
            });

        $grid->column('view_html', __('VIEW HTML'))
            ->display(function () {
                if ($this->export_type === 'html' || $this->export_type === 'both') {
                    $url = admin_url("mru-academic-result-exports/{$this->id}/view-html");
                    return "<a href='$url' target='_blank' class='btn btn-sm btn-info'>
                        <i class='fa fa-eye'></i>
                    </a>";
                }
                return "<span class='text-muted'>-</span>";
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
                'html' => 'HTML',
                'both' => 'Both',
            ]);

            $filter->equal('academic_year', 'Academic Year')
                ->select(MruAcademicYear::pluck('acadyear', 'acadyear')->toArray());

            $filter->equal('semester', 'Semester')->select([
                '1' => 'Semester 1',
                '2' => 'Semester 2',
                '3' => 'Semester 3',
            ]);

            $filter->equal('study_year', 'Year of Study')->select([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
            ]);

            $filter->equal('programme_id', 'Programme')
                ->select(MruProgramme::pluck('progname', 'progcode')->toArray());

            $filter->equal('specialisation_id', 'Specialisation')
                ->select(\DB::table('acad_specialisation')
                    ->orderBy('spec')
                    ->pluck('spec', 'spec_id')
                    ->toArray());

            $filter->between('start_range', 'Range')->integer();

            $filter->equal('status', 'Status')->select([
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'failed' => 'Failed',
            ]);

            $filter->between('created_at', 'Created At')->datetime();
        });

        $grid->actions(function ($actions) {
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
        $show->field('study_year', __('Year of Study'))->as(function ($year) {
            return 'Year ' . $year;
        });
        $show->field('programme_id', __('Programme'))->as(function ($progId) {
            return $progId ? ($this->programme ? $this->programme->progname : $progId) : 'All Programmes';
        });
        
        $show->divider();
        
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
            ->required();

        $form->select('export_type', __('Export Type'))
            ->options([
                'excel' => 'Excel Only',
                'pdf' => 'PDF Only',
                'html' => 'HTML Only (Interactive)',
                'both' => 'Both Excel and PDF',
            ])
            ->default('excel')
            ->required();

        $form->divider('Filters');

        $form->select('programme_id', __('Programme'))
            ->options(MruProgramme::all()->mapWithKeys(function ($prog) {
                return [$prog->progcode => $prog->progcode . ' - ' . $prog->progname];
            })->toArray())
            ->required();

        $form->select('academic_year', __('Academic Year'))
            ->options(MruAcademicYear::pluck('acadyear', 'acadyear')->toArray())
            ->required();

        $form->select('semester', __('Semester'))
            ->options([
                '1' => 'Semester 1',
                '2' => 'Semester 2',
            ])
            ->required();

        $form->select('study_year', __('Year of Study'))
            ->options([
                1 => 'Year 1',
                2 => 'Year 2',
                3 => 'Year 3',
                4 => 'Year 4',
            ])
            ->required()
            ->help('Select the year of study for which to export results. Only courses taken in this year will be included.');

        $form->number('minimum_passes_required', __('Minimum Passes Required'))
            ->default(0)
            ->min(0)
            ->required()
            ->help('Number of subjects a student must pass to be considered PASSED (0 = no check). Used to calculate PASS/FAIL status.');

        $form->select('specialisation_id', __('Specialisation (Optional)'))
            ->options(\DB::table('acad_specialisation')
                ->orderBy('spec')
                ->get()
                ->mapWithKeys(function ($spec) {
                    return [$spec->spec_id => $spec->spec_id . ' - ' . $spec->spec . ' (' . $spec->abbrev . ')'];
                })->toArray())
            ->help('Leave empty to export all specialisations');

        $form->divider('Range Settings');

        $form->number('start_range', __('Start Position'))
            ->default(1)
            ->min(1)
            ->required()
            ->help('Starting position in the sorted list (e.g., 1)');

        $form->number('end_range', __('End Position'))
            ->default(100)
            ->min(1)
            ->required()
            ->help('Ending position in the sorted list (e.g., 100)');

        $form->divider('Sorting');

        $form->select('sort_by', __('Sort By'))
            ->options([
                'student' => 'Student Name (Alphabetical)',
                'regno' => 'Registration Number',
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
     * View HTML export
     */
    public function viewHtml($id)
    {
        $export = MruAcademicResultExport::findOrFail($id);
        
        if ($export->export_type !== 'html' && $export->export_type !== 'both') {
            admin_toastr('This export is not configured for HTML format', 'error');
            return back();
        }
        
        // Generate HTML on the fly
        $htmlService = new \App\Services\MruAcademicResultHtmlService($export);
        $data = $htmlService->generate();
        
        return view('mru_academic_result_export_html', $data);
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
