<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Http\Controllers\Api\ApiController;
use App\Models\DealerProfile;
use App\Models\Field\AttendanceLog;
use App\Models\Field\DealerVisit;
use App\Models\Field\Expense;
use App\Models\Field\LeaveApplication;
use App\Models\Field\SalesmanAsset;
use App\Models\Field\SalarySlip;
use App\Models\Field\SalesmanTarget;
use App\Models\Field\TourPlan;
use App\Models\Finance\Payment;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Order;
use App\Models\User;
use App\Contracts\Sales\Orders\OrderWorkflowContract;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesmanController extends ApiController
{
    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success([
            'assigned_dealers' => DealerProfile::query()->where('salesman_id', $user->id)->count(),
            'pending_orders' => Order::query()->where('salesman_id', $user->id)->where('status', 'salesman_review')->count(),
            'today_collections' => Payment::query()->where('collected_by', $user->id)->whereDate('created_at', today())->sum('amount'),
            'profile' => $user->load('salesmanProfile'),
        ]);
    }

    public function dealers(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $dealers = DealerProfile::query()->with('user.addresses')->where('salesman_id', $user->id)->paginate($request->integer('per_page', 20));

        return $this->success(['dealers' => $dealers]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate(['latitude' => ['required', 'numeric'], 'longitude' => ['required', 'numeric']]);

        $attendance = AttendanceLog::query()->updateOrCreate(
            ['salesman_id' => $user->id, 'attendance_date' => today()],
            ['check_in_at' => now(), 'check_in_latitude' => $validated['latitude'], 'check_in_longitude' => $validated['longitude'], 'status' => 'present']
        );

        return $this->success(['attendance' => $attendance], 'Checked in.');
    }

    public function checkOut(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate(['latitude' => ['required', 'numeric'], 'longitude' => ['required', 'numeric']]);
        $attendance = AttendanceLog::query()->where('salesman_id', $user->id)->where('attendance_date', today())->first();

        if (! $attendance || ! $attendance->check_in_at) {
            return $this->fail('Check in is required before check out.');
        }

        $minutes = Carbon::parse($attendance->check_in_at)->diffInMinutes(now());
        $attendance->update(['check_out_at' => now(), 'check_out_latitude' => $validated['latitude'], 'check_out_longitude' => $validated['longitude'], 'working_minutes' => $minutes]);

        return $this->success(['attendance' => $attendance], 'Checked out.');
    }

    public function visits(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['visits' => DealerVisit::query()->with('dealer.dealerProfile')->where('salesman_id', $user->id)->latest()->paginate(20)]);
    }

    public function storeVisit(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'dealer_id' => ['required', 'integer', 'exists:users,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ((int) User::query()->findOrFail($validated['dealer_id'])->dealerProfile?->salesman_id !== (int) $user->id) {
            return $this->fail('Dealer is not assigned to this salesman.', 403);
        }

        $visit = DealerVisit::query()->create($validated + ['salesman_id' => $user->id, 'visited_at' => now()]);

        return $this->success(['visit' => $visit], 'Dealer visit saved.', 201);
    }

    public function orders(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $orders = Order::query()->with('dealer.dealerProfile', 'items.product', 'items.variant', 'dispatches')->where('salesman_id', $user->id)->latest()->paginate($request->integer('per_page', 20));

        return $this->success(['orders' => $orders]);
    }

    public function storeDealerOrder(Request $request, OrderWorkflowContract $orders): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'dealer_id' => ['required', 'integer', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string'],
        ]);

        $dealer = User::query()->where('role', User::ROLE_DEALER)->findOrFail($validated['dealer_id']);
        $order = $orders->createBySalesman($user, $dealer, $validated['items'], $validated['notes'] ?? null);

        return $this->success(['order' => $order], 'Dealer order created.', 201);
    }

    public function forwardOrderToAdmin(
    Request $request,
    Order $order
): JsonResponse {
    $user = $this->requireUser(
        $request,
        User::ROLE_SALESMAN
    );

    if ($user instanceof JsonResponse) {
        return $user;
    }

    if (
        (int) $order->salesman_id !==
        (int) $user->id
    ) {
        return $this->fail(
            'Order not assigned to this salesman.',
            403
        );
    }

    if (
        $order->status !==
        'salesman_review'
    ) {
        return $this->fail(
            'Only orders waiting for salesman review can be forwarded to admin.',
            422
        );
    }

    $order->update([
        'status' => 'admin_review',
    ]);

    return $this->success(
        [
            'order' => $order->fresh(
                'items.product'
            ),
        ],
        'Order forwarded to admin.'
    );
}

   public function collectPayment(
    Request $request
): JsonResponse {
    $user = $this->requireUser(
        $request,
        User::ROLE_SALESMAN
    );

    if ($user instanceof JsonResponse) {
        return $user;
    }

    $validated = $request->validate([
        'dealer_id' => [
            'required',
            'integer',
            'exists:users,id',
        ],

        'order_id' => [
            'nullable',
            'integer',
            'exists:orders,id',
        ],

        'payment_mode' => [
            'required',
            'string',
            'max:40',
        ],

        'amount' => [
            'required',
            'numeric',
            'min:0.01',
        ],

        'transaction_ref' => [
            'nullable',
            'string',
            'max:255',
        ],
    ]);

    $dealer = User::query()
        ->where(
            'role',
            User::ROLE_DEALER
        )
        ->findOrFail(
            $validated['dealer_id']
        );

    if (
        (int) $dealer
            ->dealerProfile
            ?->salesman_id !==
        (int) $user->id
    ) {
        return $this->fail(
            'Dealer is not assigned to this salesman.',
            403
        );
    }

    if (
        ! empty(
            $validated['order_id']
        )
    ) {
        $order = Order::query()
            ->findOrFail(
                $validated['order_id']
            );

        if (
            (int) $order->dealer_id !==
                (int) $dealer->id ||
            (int) $order->salesman_id !==
                (int) $user->id
        ) {
            return $this->fail(
                'Selected order does not belong to this dealer or salesman.',
                403
            );
        }
    }

    $payment = Payment::query()->create([
        'payment_no' =>
            'PAY'
            .now()->format('ymdHis')
            .random_int(100, 999),

        'order_id' =>
            $validated['order_id']
            ?? null,

        'payer_id' =>
            $dealer->id,

        'collected_by' =>
            $user->id,

        'payment_mode' =>
            $validated[
                'payment_mode'
            ],

        'status' =>
            'collected',

        'amount' =>
            $validated['amount'],

        'transaction_ref' =>
            $validated[
                'transaction_ref'
            ] ?? null,

        'paid_at' => now(),
    ]);

    return $this->success(
        [
            'payment' => $payment,
        ],
        'Payment collected.',
        201
    );
}

    public function expenses(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['expenses' => Expense::query()->where('salesman_id', $user->id)->latest()->paginate(20)]);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'expense_type' => ['required', 'string', 'max:40'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'receipt_path' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $expense = Expense::query()->create($validated + ['salesman_id' => $user->id]);

        return $this->success(['expense' => $expense], 'Expense submitted.', 201);
    }

    public function leaves(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['leaves' => LeaveApplication::query()->where('salesman_id', $user->id)->latest()->paginate(20)]);
    }

    public function storeLeave(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'leave_type' => ['required', 'string', 'max:40'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string'],
        ]);

        $leave = LeaveApplication::query()->create($validated + ['salesman_id' => $user->id]);

        return $this->success(['leave' => $leave], 'Leave application submitted.', 201);
    }

    public function assets(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['assets' => SalesmanAsset::query()->where('salesman_id', $user->id)->latest()->get()]);
    }

    public function salary(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['salary_slips' => SalarySlip::query()->where('salesman_id', $user->id)->latest()->paginate(12)]);
    }

    public function targets(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['targets' => SalesmanTarget::query()->where('salesman_id', $user->id)->latest()->paginate(12)]);
    }

    public function tourPlans(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['tour_plans' => TourPlan::query()->where('salesman_id', $user->id)->latest()->paginate(20)]);
    }

    public function deliveries(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $dispatches = Dispatch::query()->with('order.dealer.dealerProfile')->whereHas('order', fn ($query) => $query->where('salesman_id', $user->id))->latest()->paginate(20);

        return $this->success(['dispatches' => $dispatches]);
    }
}
