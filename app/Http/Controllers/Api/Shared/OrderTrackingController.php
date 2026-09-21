<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\Dispatch;
use App\Models\Sales\Order;
use App\Services\Sales\Access\OrderOwnershipScope;
use App\Services\Sales\Orders\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Order timeline for the "track my order" screen in all three apps.
 */
class OrderTrackingController extends ApiController
{
    public function __construct(private readonly OrderOwnershipScope $scope) {}

    public function show(Request $request, int $order): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $model = $this->scope->find($user, $order, ['dispatches', 'invoice']);

        if ($model === null) {
            return $this->fail('Order not found.', 404);
        }

        $dispatch = $model->dispatches->sortByDesc('created_at')->first();

        return $this->success([
            'order' => $model->only(['id', 'order_no', 'status', 'grand_total', 'created_at']),
            'stages' => $this->stages($model, $dispatch),
            'courier' => $this->courier($dispatch),
            'invoice' => $model->invoice,
        ]);
    }

    /**
     * The ordered checkpoint list the UI draws as a vertical stepper. Each
     * stage carries the moment it happened, or null while it is still ahead of
     * the order, so the app needs no status-to-step mapping of its own.
     *
     * "Done" for packed/dispatched/out-for-delivery/delivered comes from the
     * order's own status position in OrderStatusService::FLOW — the single
     * authority on order progress — not from a dispatch timestamp column,
     * since admin can move status on Dispatch & Delivery (the quick status
     * change, or the Status field) without ever filling those columns.
     */
    private function stages(Order $order, ?Dispatch $dispatch): array
    {
        $orderFlowIndex = array_search($order->status, OrderStatusService::FLOW, true);
        $fallbackAt = $dispatch?->updated_at ?? $order->updated_at;

        return [
            $this->stage('placed', 'Order placed', $order->created_at, true),
            $this->stage('approved', 'Approved', $order->approved_at, $order->approved_at !== null),
            $this->flowStage('packed', 'Packed', 'packing', $orderFlowIndex, $dispatch?->created_at, $fallbackAt),
            $this->flowStage('dispatched', 'Dispatched', 'dispatched', $orderFlowIndex, $dispatch?->dispatched_at, $fallbackAt),
            $this->flowStage('out_for_delivery', 'Out for delivery', 'out_for_delivery', $orderFlowIndex, $dispatch?->out_for_delivery_at, $fallbackAt),
            $this->flowStage('delivered', 'Delivered', 'delivered', $orderFlowIndex, $dispatch?->delivered_at, $fallbackAt),
        ];
    }

    private function flowStage(string $key, string $label, string $flowStatus, int|false $orderFlowIndex, ?object $recordedAt, ?object $fallbackAt): array
    {
        $stageIndex = array_search($flowStatus, OrderStatusService::FLOW, true);
        $done = $orderFlowIndex !== false && $stageIndex !== false && $orderFlowIndex >= $stageIndex;

        return $this->stage($key, $label, $recordedAt ?? ($done ? $fallbackAt : null), $done);
    }

    private function stage(string $key, string $label, ?object $at, bool $done): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'at' => $at?->toIso8601String(),
            'done' => $done,
        ];
    }

    private function courier(?Dispatch $dispatch): ?array
    {
        if ($dispatch === null) {
            return null;
        }

        return [
            'name' => $dispatch->courier_name,
            'tracking_no' => $dispatch->tracking_no,
            'tracking_url' => $dispatch->tracking_url,
            'status' => $dispatch->status,
        ];
    }
}
