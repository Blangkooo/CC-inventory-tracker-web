<?php

namespace App\Services;

use App\Models\DiscrepancyAlert;
use App\Models\Receipt;
use App\Models\Transaction;
use Carbon\Carbon;

class ReconciliationService
{
    // Match criteria: same branch, similar amount (within 1 peso tolerance), within 24 hours
    public function reconcile(Receipt $receipt): Receipt
    {
        if (!$receipt->parsed_total_amount) {
            $receipt->update(['reconciliation_status' => 'unreadable']);
            return $receipt;
        }

        $tolerance = 1.00; // 1 peso tolerance for rounding differences
        $timeWindow = 24; // hours to look back for matching transactions

        $matchedTransaction = Transaction::where('branch_id', $receipt->branch_id)
            ->whereBetween('total_amount', [
                $receipt->parsed_total_amount - $tolerance,
                $receipt->parsed_total_amount + $tolerance,
            ])
            ->where('created_at', '>=', Carbon::now()->subHours($timeWindow))
            ->whereDoesntHave('receipt')
            ->latest()
            ->first();

        if ($matchedTransaction) {
            $receipt->update([
                'matched_transaction_id' => $matchedTransaction->id,
                'reconciliation_status' => 'matched',
            ]);
        } else {
            $receipt->update([
                'reconciliation_status' => 'mismatched',
            ]);

            DiscrepancyAlert::create([
                'branch_id' => $receipt->branch_id,
                'type' => 'stock_mismatch',
                'severity' => 'high',
                'ingredient_id' => null,
                'shift_log_id' => null,
                'expected_value' => $receipt->parsed_total_amount,
                'actual_value' => 0,
                'variance' => $receipt->parsed_total_amount,
                'details' => 'Receipt scanned for ₱' . number_format($receipt->parsed_total_amount, 2) . ' at Branch ID ' . $receipt->branch_id . ' but no matching POS transaction found. Possible unrecorded sale.',
                'status' => 'pending',
            ]);
        }

        return $receipt;
    }
}
