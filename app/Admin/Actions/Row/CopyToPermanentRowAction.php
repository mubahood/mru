<?php

namespace App\Admin\Actions\Row;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class CopyToPermanentRowAction extends RowAction
{
    public $name = 'Copy';

    public function handle(Model $model)
    {
        // Check if already created
        if ($model->is_created) {
            return $this->response()->warning('Record already created in permanent table.')->refresh();
        }

        // Check if duplicate exists in permanent table
        $exists = \App\Models\MruSpecializationHasCourse::where('specialization_id', $model->specialization_id)
            ->where('course_code', $model->course_code)
            ->where('year', $model->year)
            ->where('semester', $model->semester)
            ->exists();

        if ($exists) {
            // Mark as created
            $model->is_created = true;
            $model->save();
            return $this->response()->warning('Record already exists in permanent table. Marked as created.')->refresh();
        }

        // Copy to permanent
        if ($model->copyToPermanent()) {
            // Mark as created instead of deleting
            $model->is_created = true;
            $model->save();
            return $this->response()->success('Record copied to permanent table successfully.')->refresh();
        }

        return $this->response()->error('Failed to copy record.');
    }

    public function dialog()
    {
        $this->confirm('Copy this record to permanent curriculum table?');
    }
}
