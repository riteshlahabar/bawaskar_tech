<?php

namespace App\Http\Controllers\Admin\Couriers;

use App\Contracts\Location\UserLocationContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CourierController extends AdminModuleController
{
    protected string $moduleKey = 'couriers';

    public function __construct(AdminModuleServices $modules, private readonly UserLocationContract $location)
    {
        parent::__construct($modules);
    }

    protected function rules(array $module, ?Model $record = null): array
    {
        return parent::rules($module, $record) + $this->location->rules((bool) ($module['location_required'] ?? true));
    }

    /**
     * The raw State / District / Taluka inputs are swapped for the resolved
     * codes plus names, same as the People forms (Dealers/Customers/Salesmen).
     */
    protected function prepareData(array $validated, Request $request, array $module): array
    {
        $location = $this->location->attributes($validated);
        $data = array_diff_key(parent::prepareData($validated, $request, $module), array_flip(UserLocationContract::FIELDS));

        if (empty($data['courier_code'])) {
            $data['courier_code'] = 'CR'.now()->format('ymdHis').random_int(100, 999);
        }

        return $data + $location;
    }

    protected function formData(Model $record, array $module): array
    {
        $data = parent::formData($record, $module);

        foreach (UserLocationContract::FIELDS as $field) {
            $data[$field] = $record->getAttribute($field);
        }

        // A taluka typed by hand has a name but no code.
        if (blank($data['subdistrict_code']) && filled($data['subdistrict_name'])) {
            $data['subdistrict_code'] = UserLocationContract::OTHER_SUBDISTRICT;
        }

        return $data;
    }
}
