<?php

namespace App\Http\Controllers\Api\Dealer;

use App\Contracts\Sales\Orders\OrderWorkflowContract;
use App\Contracts\Sales\OrderStatusContract;
use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\Sales\Orders\OrderItemImageAttacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealerOrderController extends ApiController
{
    public function index(Request $request, OrderItemImageAttacher $images): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $orders = Order::query()
            ->with('items.product.images', 'items.variant', 'invoice', 'dispatches', 'salesman')
            ->where('dealer_id', $user->id)
            ->latest()
            ->paginate($request->integer('per_page', 20));

        $images->attach($orders->getCollection());

        return $this->success(['orders' => $orders]);
    }

    public function store(Request $request, OrderWorkflowContract $orders): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        // The delivery and payment fields are optional so an older build of
        // the dealer app keeps working; when they are sent they land in the
        // real order columns instead of being crammed into `notes`, which is
        // what the app used to do.
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_mobile' => ['nullable', 'string', 'max:20'],
            'address_type' => ['nullable', 'string', 'max:30'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:12'],
            // `credit` is dealer-only: buying against the credit limit is the
            // normal B2B case, and is what the outstanding-balance figures
            // are built on.
            'payment_method' => ['nullable', 'in:cod,credit,bank_transfer,upi'],
            'notes' => ['nullable', 'string'],
        ]);

        $paymentMethod = $validated['payment_method'] ?? null;

        $checkoutData = [
            'contact_name' => $validated['contact_name'] ?? null,
            'contact_mobile' => $validated['contact_mobile'] ?? null,
            'address_type' => $validated['address_type'] ?? 'shipping',
            'address_line1' => $validated['address_line1'] ?? null,
            'address_line2' => $validated['address_line2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'payment_method' => $paymentMethod,
            'payment_status' => in_array($paymentMethod, [null, 'cod', 'credit'], true)
                ? 'pending'
                : 'awaiting_confirmation',
        ];

        $order = $orders->createForDealer(
            $user,
            $validated['items'],
            $validated['notes'] ?? null,
            $checkoutData,
        );

        return $this->success(['order' => $order], 'Dealer order sent to assigned salesman.', 201);
    }

    public function show(Request $request, Order $order, OrderItemImageAttacher $images): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        if ((int) $order->dealer_id !== (int) $user->id) {
            return $this->fail('Order not found.', 404);
        }

        $order->load('items.product.images', 'items.variant', 'invoice', 'dispatches', 'salesman');
        $images->attach([$order]);

        return $this->success(['order' => $order]);
    }

    /**
     * Self-service cancel, only while the order has not yet been approved
     * into production — after that, packing/dispatch is already under way
     * and only admin can cancel it.
     */
    public function cancel(Request $request, OrderStatusContract $status, Order $order): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        if ((int) $order->dealer_id !== (int) $user->id) {
            return $this->fail('Order not found.', 404);
        }

        if (! in_array($order->status, ['salesman_review', 'admin_review'], true)) {
            return $this->fail('This order is already being processed and can no longer be cancelled here. Please contact support.', 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $status->cancel($order, $validated['reason'], $user->id);

        return $this->success(['order' => $order->fresh('items.product')], 'Order cancelled.');
    }
}
