<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShiftLogController extends Controller
{
    const VARIANCE_THRESHOLD = 5;

    public function store(Request $request)
    {
        $request->validate([
            'branch_id'     => 'required|integer|exists:branches,id',
            'user_id'       => 'required|integer|exists:users,id',
            'opening_stock' => 'required|numeric|min:0',
            'closing_stock' => 'required|numeric|min:0',
            'time_in'       => 'required|date',
            'time_out'      => 'required|date|after:time_in',
        ]);

        // Sum all ingredient deductions from transactions in this shift window
        $deductions = \App\Models\Transaction::where('branch_id', $request->branch_id)
            ->whereBetween('created_at', [$request->time_in, $request->time_out])
            ->with('product.recipes')
            ->get()
            ->sum(function ($tx) {
                return $tx->product->recipes->sum(fn($r) => $r->quantity * $tx->quantity);
            });

        $expectedClosing = $request->opening_stock - $deductions;
        $variance        = $request->closing_stock - $expectedClosing;
        $flagged         = abs($variance) > self::VARIANCE_THRESHOLD;

        $log = \App\Models\ShiftLog::create([
            'branch_id'     => $request->branch_id,
            'user_id'       => $request->user_id,
            'opening_stock' => $request->opening_stock,
            'closing_stock' => $request->closing_stock,
            'time_in'       => $request->time_in,
            'time_out'      => $request->time_out,
            'variance'      => $variance,
            'flagged'       => $flagged,
        ]);

        return response()->json($log, 201);
    }
}
