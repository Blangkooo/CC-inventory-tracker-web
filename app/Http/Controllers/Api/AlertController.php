<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\DiscrepancyAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertController extends Controller
{
    use AuthorizesBranchAccess;

    /**
     * Single-endpoint alternative to review()/dismiss() below — those stay as
     * they are for existing callers, this just gives new callers one verb
     * instead of two.
     */
    public function update(Request $request, DiscrepancyAlert $alert): JsonResponse
    {
        $this->authorizeBranch($alert->branch_id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['reviewed', 'dismissed'])],
        ]);

        $alert->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Alert '.$validated['status'].'.',
            'alert' => $alert,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $alerts = DiscrepancyAlert::with('ingredient', 'shiftLog')
            ->where('branch_id', $validated['branch_id'])
            ->latest()
            ->paginate(20);

        return response()->json($alerts);
    }

    public function show(DiscrepancyAlert $alert): JsonResponse
    {
        $this->authorizeBranch($alert->branch_id);

        return response()->json($alert->load('ingredient', 'shiftLog', 'branch'));
    }

    public function review(DiscrepancyAlert $alert): JsonResponse
    {
        $this->authorizeBranch($alert->branch_id);

        $alert->update(['status' => 'reviewed']);

        return response()->json([
            'message' => 'Alert marked as reviewed.',
            'alert' => $alert,
        ]);
    }

    public function dismiss(DiscrepancyAlert $alert): JsonResponse
    {
        $this->authorizeBranch($alert->branch_id);

        $alert->update(['status' => 'dismissed']);

        return response()->json([
            'message' => 'Alert dismissed.',
            'alert' => $alert,
        ]);
    }
}
