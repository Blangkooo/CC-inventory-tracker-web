<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\ShiftLog;
use App\Models\ShiftStockCount;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    use AuthorizesBranchAccess;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $shiftLogs = ShiftLog::with('user')
            ->where('branch_id', $validated['branch_id'])
            ->latest('shift_start')
            ->paginate(20);

        return response()->json($shiftLogs);
    }

    public function show(ShiftLog $shiftLog): JsonResponse
    {
        $this->authorizeBranch($shiftLog->branch_id);

        return response()->json($shiftLog->load('user', 'stockCounts.ingredient'));
    }

    public function open(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opening_counts' => ['required', 'array', 'min:1'],
            'opening_counts.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'opening_counts.*.opening_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $user = $request->user();

        if (! $user->branch_id) {
            return response()->json([
                'message' => 'User is not assigned to a branch.',
            ], 422);
        }

        $existingOpenShift = ShiftLog::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existingOpenShift) {
            return response()->json([
                'message' => 'You already have an open shift.',
                'shift_log' => $existingOpenShift,
            ], 422);
        }

        return DB::transaction(function () use ($validated, $user) {
            $shiftLog = ShiftLog::create([
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'shift_start' => now(),
                'status' => 'open',
            ]);

            foreach ($validated['opening_counts'] as $count) {
                ShiftStockCount::create([
                    'shift_log_id' => $shiftLog->id,
                    'ingredient_id' => $count['ingredient_id'],
                    'opening_quantity' => $count['opening_quantity'],
                ]);
            }

            return response()->json([
                'message' => 'Shift opened successfully.',
                'shift_log' => $shiftLog->load('stockCounts.ingredient'),
            ], 201);
        });
    }

    public function close(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shift_log_id' => ['required', 'exists:shift_logs,id'],
            'closing_counts' => ['required', 'array', 'min:1'],
            'closing_counts.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'closing_counts.*.closing_quantity_actual' => ['required', 'numeric', 'min:0'],
        ]);

        $shiftLog = ShiftLog::findOrFail($validated['shift_log_id']);

        $this->authorizeBranch($shiftLog->branch_id);

        if ($shiftLog->status === 'closed') {
            return response()->json([
                'message' => 'Shift is already closed.',
            ], 422);
        }

        return DB::transaction(function () use ($validated, $shiftLog, $request) {
            $alerts = [];

            foreach ($validated['closing_counts'] as $count) {
                $stockCount = ShiftStockCount::where('shift_log_id', $shiftLog->id)
                    ->where('ingredient_id', $count['ingredient_id'])
                    ->first();

                if (! $stockCount) {
                    continue;
                }

                $branchStock = BranchStock::where('branch_id', $shiftLog->branch_id)
                    ->where('ingredient_id', $count['ingredient_id'])
                    ->first();

                $expected = $branchStock->current_quantity ?? $stockCount->opening_quantity;
                $actual = $count['closing_quantity_actual'];
                $variance = $actual - $expected;

                $stockCount->update([
                    'closing_quantity_expected' => $expected,
                    'closing_quantity_actual' => $actual,
                    'variance' => $variance,
                ]);

                if (abs($variance) > 0) {
                    $severity = 'low';
                    if ((float) $expected !== 0.0) {
                        $pct = abs($variance) / abs($expected);
                        $severity = $pct >= 0.1 ? 'high' : ($pct >= 0.05 ? 'medium' : 'low');
                    } else {
                        $severity = 'high';
                    }

                    $alerts[] = DiscrepancyAlert::create([
                        'branch_id' => $shiftLog->branch_id,
                        'type' => 'shift_variance',
                        'severity' => $severity,
                        'ingredient_id' => $count['ingredient_id'],
                        'shift_log_id' => $shiftLog->id,
                        'expected_value' => $expected,
                        'actual_value' => $actual,
                        'variance' => $variance,
                        'details' => "Shift-end variance of {$variance} detected for ingredient #{$count['ingredient_id']} during shift #{$shiftLog->id}.",
                        'status' => 'pending',
                    ]);
                }

                if ($branchStock) {
                    $before = $branchStock->current_quantity;

                    $branchStock->update([
                        'current_quantity' => $actual,
                        'last_updated_at' => now(),
                    ]);

                    StockMovement::create([
                        'branch_stock_id' => $branchStock->id,
                        'type' => StockMovement::TYPE_SHIFT_CORRECTION,
                        'quantity_change' => $actual - $before,
                        'quantity_before' => $before,
                        'quantity_after' => $actual,
                        'reference_type' => ShiftLog::class,
                        'reference_id' => $shiftLog->id,
                        'user_id' => $request->user()?->id,
                        'notes' => "Physical count at shift close (variance {$variance}).",
                    ]);
                }
            }

            $shiftLog->update([
                'shift_end' => now(),
                'status' => 'closed',
            ]);

            return response()->json([
                'message' => 'Shift closed successfully.',
                'shift_log' => $shiftLog->load('stockCounts.ingredient'),
                'discrepancy_alerts' => $alerts,
            ]);
        });
    }
}
