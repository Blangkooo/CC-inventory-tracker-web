<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\ShiftLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin start/end endpoints over shift_logs (Ally's API shape, adapted to the
 * live schema: shift_start/shift_end/status). Per-ingredient opening/closing
 * counts + variance detection live in ShiftController (/shifts/open|close),
 * which raises DiscrepancyAlerts and corrects branch stock.
 */
class ShiftLogController extends Controller
{
    use AuthorizesBranchAccess;

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'time_in' => ['sometimes', 'date'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $log = ShiftLog::create([
            'branch_id' => $validated['branch_id'],
            'user_id' => $request->user()->id,
            'shift_start' => $validated['time_in'] ?? now(),
            'status' => 'open',
        ]);

        return response()->json($log, 201);
    }

    public function end(Request $request, ShiftLog $shiftLog): JsonResponse
    {
        $validated = $request->validate([
            'time_out' => ['sometimes', 'date'],
        ]);

        $this->authorizeBranch($shiftLog->branch_id);

        if ($shiftLog->status === 'closed') {
            return response()->json(['message' => 'Shift already closed.'], 409);
        }

        $shiftLog->update([
            'shift_end' => $validated['time_out'] ?? now(),
            'status' => 'closed',
        ]);

        return response()->json($shiftLog->fresh());
    }
}
