<?php

namespace App\Http\Controllers\Admin\Salesmen;

use App\Http\Controllers\Admin\Concerns\PeopleModuleController;
use App\Models\SalesmanProfile;
use App\Models\User;

class SalesmanController extends PeopleModuleController
{
    protected string $moduleKey = 'salesmen';
    protected string $role = User::ROLE_SALESMAN;
    protected string $profileRelation = 'salesmanProfile';
    protected string $profileModel = SalesmanProfile::class;
    protected array $profileFields = ['employee_code', 'joining_date', 'basic_salary', 'target_amount', 'territory'];
}
