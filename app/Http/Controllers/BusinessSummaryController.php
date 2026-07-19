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
        $branchId = $isManager ? $user->branch_id : null;

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $branchId))
            ->orderBy('name')
            ->get();

        $activeBranch = $branches->first();

        // Recent transactions (last 10)
        $recentTransactions = Transaction::with('product', 'user')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(10)
            ->get();

        // Total revenue this year
        $totalRevenue = Transaction::whereYear('created_at', now()->year)
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // Leakage rows (negative variance from discrepancy alerts or stock movements)
        $leakageRows = DiscrepancyAlert::with('ingredient')
            ->where('variance', '<', 0)
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(10)
            ->get();

        // Monthly sales for the current year
        // Fetch all, then group in PHP for SQLite/MySQL compatibility
        $monthlyTransactions = Transaction::whereYear('created_at', now()->year)
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $monthlySales = $monthlyTransactions
            ->groupBy(fn ($t) => (int) $t->created_at->format('n'))
            ->map(fn ($g) => $g->sum('total_amount'));

        // Fill in missing months with 0 (months 1-12)
        $filled = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => (float) ($monthlySales->get($m, 0))]);

        return view('business.summary', [
            'branches'           => $branches,
            'activeBranch'       => $activeBranch,
            'totalRevenue'       => max(0, (float) $totalRevenue),
            'recentTransactions' => $recentTransactions,
            'leakageRows'        => $leakageRows,
            'monthlySales'       => $filled,
        ]);
    }
}
