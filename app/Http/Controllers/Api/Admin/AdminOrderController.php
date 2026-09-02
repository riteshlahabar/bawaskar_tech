<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Sales\Dispatch;
use App\Models\Sales\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AdminOrderController extends AdminApiController
{
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $admin = $this->admin($request);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'packing', 'dispatched', 'delivered', 'cancelled'])],
        ]);

        $approving = $validated['status'] === 'approved';

        $order->forceFill([
            'status' => $validated['status'],
            'approved_by' => $approving ? $admin->id : $order->approved_by,
            'approved_at' => $approving ? now() : $order->approved_at,
        ])->save();

        return $this->success(['order' => $order->fresh('items.product')], 'Order status updated.');
    }

    public function upsertDispatch(Request $request, Order $order): JsonResponse
    {
        $this->admin($request);

        $validated = $request->validate([
            'dispatch_no' => ['required', 'string', 'max:80'],
            'status' => ['required', 'string', 'max:40'],
            'courier_name' => ['nullable', 'string', 'max:255'],
            'tracking_no' => ['nullable', 'string', 'max:255'],
            'tracking_url' => ['nullable', 'string', 'max:255'],
            'current_latitude' => ['nullable', 'numeric'],
            'current_longitude' => ['nullable', 'numeric'],
        ]);

        $dispatch = Dispatch::query()->updateOrCreate(
            ['order_id' => $order->id, 'dispatch_no' => $validated['dispatch_no']],
            $validated
        );

        return $this->success(['dispatch' => $dispatch], 'Dispatch updated.');
    }
}
