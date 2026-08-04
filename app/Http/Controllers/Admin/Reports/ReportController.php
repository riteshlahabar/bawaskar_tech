<?php
namespace App\Http\Controllers\Admin\Reports;
use App\Http\Controllers\Controller;
use App\Models\Field\AttendanceLog;
use App\Models\Field\Expense;
use App\Models\Field\SalarySlip;
use App\Models\Finance\Payment;
use App\Models\Sales\Order;
use Illuminate\Contracts\View\View;
class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index',['pageTitle'=>'Reports','breadcrumbs'=>['Admin','Reports'],'summary'=>[
            'b2b_sales'=>Order::where('order_type','dealer')->whereNotIn('status',['cancelled'])->sum('grand_total'),
            'b2c_sales'=>Order::where('order_type','customer')->whereNotIn('status',['cancelled'])->sum('grand_total'),
            'collections'=>Payment::whereIn('status',['paid','collected','verified'])->sum('amount'),
            'expenses'=>Expense::where('status','approved')->sum('amount'),
            'salary'=>SalarySlip::whereIn('status',['approved','paid'])->sum('net_salary'),
            'attendance'=>AttendanceLog::whereDate('attendance_date',today())->count(),
        ]]);
    }
}
