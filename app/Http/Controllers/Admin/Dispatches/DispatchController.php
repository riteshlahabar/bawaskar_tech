<?php

namespace App\Http\Controllers\Admin\Dispatches;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Http\Request;

class DispatchController extends AdminModuleController
{
    protected string $moduleKey = 'dispatches';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $data = parent::prepareData($validated, $request, $module);

        if (empty($data['dispatch_no'])) {
            $data['dispatch_no'] = 'DSP'.now()->format('ymdHis').random_int(100, 999);
        }

        return $data;
    }
}
