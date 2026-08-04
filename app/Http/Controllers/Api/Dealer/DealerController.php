<?php

namespace App\Http\Controllers\Api\Dealer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Finance\DealerStatement;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealerController extends ApiController
{
    public function profile(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success(['user' => $user->load('dealerProfile.salesman', 'addresses')]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success([
            'credit_limit' => $user->dealerProfile?->credit_limit ?? 0,
            'outstanding_balance' => $user->dealerProfile?->outstanding_balance ?? 0,
            'assigned_salesman' => $user->dealerProfile?->salesman,
            'orders_count' => Order::query()->where('dealer_id', $user->id)->count(),
            'pending_orders' => Order::query()->where('dealer_id', $user->id)->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'latest_orders' => Order::query()->with('items.product', 'dispatches')->where('dealer_id', $user->id)->latest()->limit(5)->get(),
        ]);
    }

    public function statements(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $statements = DealerStatement::query()->where('dealer_id', $user->id)->latest()->paginate($request->integer('per_page', 20));

        return $this->success(['statements' => $statements]);
    }
}
