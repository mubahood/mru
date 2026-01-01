<?php

namespace App\Admin\Actions\Row;

use Encore\Admin\Actions\RowAction;

class SyncNowAction extends RowAction
{
    public $name = 'Sync Now';

    public function handle()
    {
        // This action doesn't do anything server-side
        // It just opens the monitor page in a new tab via JavaScript
        return $this->response()->success('Opening sync monitor...')->refresh();
    }

    public function html()
    {
        $syncId = $this->getKey();
        $url = url("/sync/{$syncId}/monitor");
        
        return <<<HTML
<a href="{$url}" target="_blank" class="btn btn-sm btn-success">
    <i class="fa fa-play"></i> {$this->name}
</a>
HTML;
    }
}
