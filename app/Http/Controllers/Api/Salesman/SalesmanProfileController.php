<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Communication\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Own profile and support-ticket creation for the signed-in salesman.
 */
final class SalesmanProfileController extends SalesmanApiController
{
    /**
     * The department, designation and reporting manager are eager-loaded so
     * the app can name them: the profile row carries only their ids, and
     * loading `salesmanProfile` alone left the app with numbers it could not
     * resolve. Department, Designation and Joining Date are all Employee
     * Profile items in the Phase 1 spec.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        return $this->success([
            'user' => $user->load([
                'salesmanProfile.department:id,name',
                'salesmanProfile.designation:id,name',
                'salesmanProfile.reportingManager:id,name',
            ]),
        ]);
    }

    public function support(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::query()->create([
            'user_id' => $user->id,
            'ticket_no' => 'TKT'.now()->format('ymdHis').random_int(100, 999),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return $this->success(['ticket' => $ticket], 'Support ticket created.', 201);
    }
}
