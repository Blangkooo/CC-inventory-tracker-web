<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ShiftStockCount;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchesController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $branches = Branch::with([
            'transactions' => fn ($q) => $q->whereDate('created_at', today()),
            'users' => fn ($q) => $q->where('role', 'staff'),
        ])
            ->when($user->isManager(), fn ($q) => $q->where('id', $user->branch_id))
            ->get();

        return view('branches.index', [
            'branches' => $branches,
        ]);
    }

    public function show(Branch $branch, Request $request): View
    {
        $user = auth()->user();
        abort_if($user->isManager() && $branch->id !== $user->branch_id, 403);

        $tab = in_array($request->query('tab'), ['analytics', 'recipe', 'workers', 'logistics'], true)
            ? $request->query('tab')
            : 'analytics';

        $data = match ($tab) {
            'analytics' => [
                'recent_transactions' => $branch->transactions()->with('product', 'user')->latest()->take(10)->get(),
                'daily_sales' => $branch->transactions()
                    ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
                    ->whereDate('created_at', '>=', now()->subDays(7))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
                'total_revenue' => $branch->transactions()->sum('total_amount'),
                'leakage_rows' => ShiftStockCount::with('ingredient', 'shiftLog.user')
                    ->whereHas('shiftLog', fn ($q) => $q->where('branch_id', $branch->id))
                    ->where('variance', '<', 0)
                    ->latest()
                    ->take(8)
                    ->get(),
                'monthly_sales' => $branch->transactions()
                    ->whereYear('created_at', now()->year)
                    ->get()
                    ->groupBy(fn ($t) => $t->created_at->format('Y-m'))
                    ->map(fn ($g) => $g->sum('total_amount'))
                    ->sortKeys(),
            ],
            'recipe' => [
                'products' => Product::with('recipes.ingredient')->get(),
            ],
            'workers' => [
                'workers' => $branch->users()->orderBy('name')->get(),
            ],
            'logistics' => [
                'stocks' => $branch->stocks()->with('ingredient')->get(),
                'movements' => StockMovement::with('branchStock.ingredient', 'user')
                    ->whereHas('branchStock', fn ($q) => $q->where('branch_id', $branch->id))
                    ->latest()
                    ->take(20)
                    ->get(),
            ],
        };

        return view('branches.show', $data + [
            'branch' => $branch,
            'tab' => $tab,
            'all_branches' => Branch::orderBy('name')->get(['id', 'name', 'status']),
        ]);
    }
}
