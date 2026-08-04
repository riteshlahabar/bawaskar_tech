<?php
namespace App\Http\Controllers\Admin\Collections;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Database\Eloquent\Builder;
class CollectionController extends AdminModuleController
{
    protected string $moduleKey='collections';
    protected function recordsQuery(array $module): Builder
    {
        return parent::recordsQuery($module)->whereNotNull('collected_by');
    }
}
