<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DiscrepancyAlert;
use App\Models\Transaction;
use Illuminate\View\View;

class BusinessSummaryController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $selectedBranchId = request()->query('branch_id') ? (int) request()->query('branch_id') : null;
        $branchId = $selectedBranchId ?: ($isManager ? $user->branch_id : null);

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')
            ->get();

        $activeBranch = $selectedBranchId
            ? $branches->firstWhere('id', $selectedBranchId)
            : $branches->first();

        $branchScope = function ($query) use ($isManager, $user, $branchId) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
        };

        // Recent transactions (last 10)
        $recentTransactions = Transaction::with('product', 'user')
            ->when(true, $branchScope)
            ->latest()
            ->take(10)
            ->get();

        // Total revenue this year
        $totalRevenue = Transaction::whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->sum('total_amount');

        // Leakage rows (negative variance from discrepancy alerts or stock movements)
        $leakageRows = DiscrepancyAlert::with('ingredient')
            ->where('variance', '<', 0)
            ->when(true, $branchScope)
            ->latest()
            ->take(10)
            ->get();

        // Monthly sales for the current year
        // Fetch all, then group in PHP for SQLite/MySQL compatibility
        $monthlyTransactions = Transaction::whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->get();

        $monthlySales = $monthlyTransactions
            ->groupBy(fn ($t) => (int) $t->created_at->format('n'))
            ->map(fn ($g) => $g->sum('total_amount'));

        // Fill in missing months with 0 (months 1-12)
        $filled = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => (float) ($monthlySales->get($m, 0))]);

        return view('business.summary', [
            'branches'           => $branches,
            'selectedBranchId'   => $selectedBranchId,
            'activeBranch'       => $activeBranch,
            'totalRevenue'       => max(0, (float) $totalRevenue),
            'recentTransactions' => $recentTransactions,
            'leakageRows'        => $leakageRows,
            'monthlySales'       => $filled,
        ]);
    }
}
