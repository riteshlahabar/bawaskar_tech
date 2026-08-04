<?php
namespace App\Http\Controllers\Admin\Salary;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Field\SalarySlip;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class SalaryController extends AdminModuleController
{
    protected string $moduleKey='salary';
    protected function persist(array $data, ?\Illuminate\Database\Eloquent\Model $record): \Illuminate\Database\Eloquent\Model
    {
        $data['net_salary'] = max(0,
            (float) ($data['basic_salary'] ?? $record?->basic_salary ?? 0)
            + (float) ($data['allowances'] ?? $record?->allowances ?? 0)
            + (float) ($data['bonus'] ?? $record?->bonus ?? 0)
            + (float) ($data['incentives'] ?? $record?->incentives ?? 0)
            + (float) ($data['commission'] ?? $record?->commission ?? 0)
            - (float) ($data['deductions'] ?? $record?->deductions ?? 0)
        );
        return parent::persist($data, $record);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data=$request->validate(['salary_year'=>['required','integer','min:2020','max:2100'],'salary_month'=>['required','integer','between:1,12']]);
        $count=0;
        User::with('salesmanProfile')->where('role',User::ROLE_SALESMAN)->where('status','active')->chunkById(100,function($salesmen)use($data,&$count){
            foreach($salesmen as $salesman){
                $basic=(float)($salesman->salesmanProfile?->basic_salary ?? 0);
                SalarySlip::updateOrCreate(['salesman_id'=>$salesman->id,'salary_year'=>$data['salary_year'],'salary_month'=>$data['salary_month']],['basic_salary'=>$basic,'net_salary'=>$basic,'status'=>'draft']);
                $count++;
            }
        });
        return back()->with('success',"Salary generated for {$count} salesmen.");
    }
}
