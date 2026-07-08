<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShiftLogController extends Controller
{
    const VARIANCE_THRESHOLD = 5;

    public function start(Request $request)
    {
        $request->validate([
            'branch_id'     => 'required|integer|exists:branches,id',
            'user_id'       => 'required|integer|exists:users,id',
            'opening_stock' => 'required|numeric|min:0',
            'time_in'       => 'required|date',
        ]);

        $log = \App\Models\ShiftLog::create([
            'branch_id'     => $request->branch_id,
            'user_id'       => $request->user_id,
            'opening_stock' => $request->opening_stock,
            'time_in'       => $request->time_in,
            'status'        => 'open',
        ]);

        return response()->json($log, 201);
    }

    public function end(Request $request, $id)
    {
        $request->validate([
            'closing_stock' => 'required|numeric|min:0',
            'time_out'      => 'required|date',
        ]);

        $log = \App\Models\ShiftLog::findOrFail($id);

        if ($log->status === 'closed') {
            return response()->json(['message' => 'Shift already closed.'], 409);
        }

        $deductions = \App\Models\Transaction::where('branch_id', $log->branch_id)
            ->whereBetween('created_at', [$log->time_in, $request->time_out])
            ->with('product.recipes')
            ->get()
            ->sum(fn($tx) => $tx->product->recipes->sum(fn($r) => $r->quantity * $tx->quantity));

        $expectedClosing = $log->opening_stock - $deductions;
        $variance        = $request->closing_stock - $expectedClosing;
        $flagged         = abs($variance) > self::VARIANCE_THRESHOLD;

        $log->update([
            'closing_stock' => $request->closing_stock,
            'time_out'      => $request->time_out,
            'variance'      => $variance,
            'flagged'       => $flagged,
            'status'        => 'closed',
        ]);

        if ($flagged) {
            \App\Models\Alert::create([
                'branch_id'    => $log->branch_id,
                'shift_log_id' => $log->id,
                'type'         => 'variance',
                'message'      => "Shift #{$log->id} variance of {$variance} exceeds threshold.",
                'status'       => 'unread',
            ]);
        }

        return response()->json($log->fresh(), 200);
    }
}
