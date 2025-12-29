<?php

namespace App\Admin\Actions\Batch;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CopyToPermanentAction extends BatchAction
{
    public $name = 'Copy to Permanent';

    public function handle(Collection $collection, Request $request)
    {
        $copied = 0;
        $failed = 0;
        $alreadyCreated = 0;
        $duplicates = 0;

        foreach ($collection as $model) {
            // Skip if already created
            if ($model->is_created) {
                $alreadyCreated++;
                continue;
            }

            // Check if duplicate exists in permanent table
            $exists = \App\Models\MruSpecializationHasCourse::where('specialization_id', $model->specialization_id)
                ->where('course_code', $model->course_code)
                ->where('year', $model->year)
                ->where('semester', $model->semester)
                ->exists();

            if ($exists) {
                $duplicates++;
                // Mark as created even though it was a duplicate
                $model->is_created = true;
                $model->save();
                continue;
            }

            // Copy to permanent
            if ($model->copyToPermanent()) {
                $copied++;
                // Mark as created instead of deleting
                $model->is_created = true;
                $model->save();
            } else {
                $failed++;
            }
        }

        $message = "Copied {$copied} new record(s) to permanent table.";
        if ($duplicates > 0) {
            $message .= " {$duplicates} duplicate(s) skipped (already in permanent).";
        }
        if ($alreadyCreated > 0) {
            $message .= " {$alreadyCreated} already created.";
        }
        if ($failed > 0) {
            $message .= " {$failed} failed.";
        }

        return $this->response()->success($message)->refresh();
    }

    public function dialog()
    {
        $this->confirm('Are you sure you want to copy selected records to the permanent curriculum table?');
    }
}
