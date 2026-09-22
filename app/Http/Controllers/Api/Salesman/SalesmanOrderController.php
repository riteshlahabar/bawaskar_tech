<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Contracts\Sales\Orders\OrderWorkflowContract;
use App\Contracts\Sales\OrderStatusContract;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Order;
use App\Models\User;
use App\Services\Sales\Orders\OrderItemImageAttacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class SalesmanOrderController extends SalesmanApiController
{
    public function index(Request $request, OrderItemImageAttacher $images): JsonResponse
    {
        $orders = Order::query()
            ->with('dealer.dealerProfile', 'items.product.images', 'items.variant', 'dispatches')
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate($request->integer('per_page', 20));

        // Same `product_image_url` the dealer and customer apps already get,
        // so the salesman's order detail screen can show thumbnails too.
        $images->attach($orders->getCollection());

        return $this->success(['orders' => $orders]);
    }

    public function store(Request $request, OrderWorkflowContract $orders): JsonResponse
    {
        $user = $this->salesman($request);

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

    public function forwardToAdmin(Request $request, Order $order): JsonResponse
    {
        $user = $this->salesman($request);

        if ((int) $order->salesman_id !== (int) $user->id) {
            return $this->fail('Order not assigned to this salesman.', 403);
        }

        if ($order->status !== 'salesman_review') {
            return $this->fail('Only orders waiting for salesman review can be forwarded to admin.', 422);
        }

        $order->update([
            'status' => 'admin_review',
            // Approving settles the stock question, so a stale "not available
            // now" note never travels with the order to the admin.
            'availability' => null,
            'available_on' => null,
            'availability_note' => null,
        ]);

        return $this->success(['order' => $order->fresh('items.product')], 'Order forwarded to admin.');
    }

    /**
     * The salesman's stock answer on an order they are still reviewing:
     * "not available now", or "available on <date/time>".
     *
     * This does not move the order — it stays in `salesman_review` so the
     * same salesman can still approve or cancel it later. The dealer is told
     * by the order notification observer, which watches these columns.
     */
    public function availability(Request $request, Order $order): JsonResponse
    {
        $user = $this->salesman($request);

        if ((int) $order->salesman_id !== (int) $user->id) {
            return $this->fail('Order not assigned to this salesman.', 403);
        }

        if ($order->status !== 'salesman_review') {
            return $this->fail('Only orders waiting for your review can be marked.', 422);
        }

        $validated = $request->validate([
            'availability' => ['required', 'string', Rule::in(Order::AVAILABILITY_OPTIONS)],
            'available_on' => [
                'nullable',
                'date',
                'after:now',
                Rule::requiredIf($request->input('availability') === Order::AVAILABILITY_AVAILABLE_ON),
            ],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $order->update([
            'availability' => $validated['availability'],
            'available_on' => $validated['availability'] === Order::AVAILABILITY_AVAILABLE_ON
                ? $validated['available_on']
                : null,
            'availability_note' => $validated['note'] ?? null,
        ]);

        return $this->success(['order' => $order->fresh('items.product')], 'Availability updated.');
    }

    /**
     * A dealer's order the salesman disagrees with — wrong items, pricing
     * dispute, and so on. Only reachable while it still sits in the
     * salesman's own review stage; once forwarded, only admin can cancel it.
     */
    public function reject(Request $request, OrderStatusContract $status, Order $order): JsonResponse
    {
        $user = $this->salesman($request);

        if ((int) $order->salesman_id !== (int) $user->id) {
            return $this->fail('Order not assigned to this salesman.', 403);
        }

        if ($order->status !== 'salesman_review') {
            return $this->fail('Only orders waiting for your review can be rejected.', 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $status->cancel($order, $validated['reason'], $user->id);

        return $this->success(['order' => $order->fresh('items.product')], 'Order rejected.');
    }

    public function deliveries(Request $request): JsonResponse
    {
        $salesmanId = $this->salesman($request)->id;

        $dispatches = Dispatch::query()
            ->with('order.dealer.dealerProfile')
            ->whereHas('order', fn ($query) => $query->where('salesman_id', $salesmanId))
            ->latest()
            ->paginate(20);

        return $this->success(['dispatches' => $dispatches]);
    }
}
