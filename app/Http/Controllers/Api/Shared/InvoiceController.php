<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\Invoice;
use App\Services\Sales\Access\OrderOwnershipScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Invoices belonging to the authenticated account's own orders.
 */
class InvoiceController extends ApiController
{
    public function __construct(private readonly OrderOwnershipScope $scope) {}

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $invoices = Invoice::query()
            ->whereIn('order_id', $this->scope->forUser($user)->select('id'))
            ->with('order:id,order_no,status,grand_total')
            ->latest('invoice_date')
            ->paginate(min((int) $request->integer('per_page', 20), 50));

        return $this->success([
            'invoices' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    public function show(Request $request, int $invoice): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $model = Invoice::query()
            ->whereIn('order_id', $this->scope->forUser($user)->select('id'))
            ->with('order.items.product:id,name,sku')
            ->whereKey($invoice)
            ->first();

        if ($model === null) {
            return $this->fail('Invoice not found.', 404);
        }

        return $this->success(['invoice' => $model]);
    }
}
