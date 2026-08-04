<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $orders = Order::query()
            ->with('items.product', 'invoice', 'dispatches')
            ->where('customer_id', $user->id)
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->success(['orders' => $orders]);
    }

    public function store(Request $request, OrderService $orders): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = $orders->createForCustomer($user, $validated['items'], $validated['notes'] ?? null);

        return $this->success(['order' => $order], 'Customer order sent to admin.', 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_CUSTOMER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        if ((int) $order->customer_id !== (int) $user->id) {
            return $this->fail('Order not found.', 404);
        }

        return $this->success(['order' => $order->load('items.product', 'invoice', 'dispatches')]);
    }
}
