<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\ShiftStockCount;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchDataController extends Controller
{
    /**
     * Helper: resolve the branch scope closure based on selected branch or manager restrictions.
     */
    private function branchScope(?int $selectedBranchId, bool $isManager, ?int $managerBranchId): \Closure
    {
        return function ($query) use ($selectedBranchId, $isManager, $managerBranchId) {
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            } elseif ($isManager && $managerBranchId) {
                $query->where('branch_id', $managerBranchId);
            }
        };
    }

    /**
     * GET /ajax/analytics?branch_id=X
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $isManager = $user->isManager();
        $selectedBranchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;
        $branchScope = $this->branchScope($selectedBranchId, $isManager, $user->branch_id);

        $recentTransactions = Transaction::with('product', 'branch', 'user')
            ->when(true, $branchScope)
            ->latest()->take(5)->get();

        $activeAlerts = DiscrepancyAlert::with('branch', 'ingredient')
            ->where('status', 'pending')
            ->when(true, $branchScope)
            ->latest()->take(5)->get();

        $leakageRows = ShiftStockCount::with('ingredient', 'shiftLog.user')
            ->where('variance', '<', 0)
            ->when(true, function ($q) use ($selectedBranchId, $isManager, $user) {
                $q->whereHas('shiftLog', function ($sq) use ($selectedBranchId, $isManager, $user) {
                    if ($selectedBranchId) {
                        $sq->where('branch_id', $selectedBranchId);
                    } elseif ($isManager && $user->branch_id) {
                        $sq->where('branch_id', $user->branch_id);
                    }
                });
            })
            ->latest()->get();

        $currentLeakage = $leakageRows->groupBy(fn ($row) => $row->ingredient->name ?? 'Unknown')
            ->map(fn ($rows, $name) => [
                'name' => $name,
                'amount' => abs($rows->sum('variance')),
                'unit' => $rows->first()->ingredient->unit ?? '',
            ])
            ->values()->take(5);

        $monthlySales = Transaction::selectRaw("DATE_FORMAT(created_at, '%m') as month, SUM(total_amount) as total")
            ->whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $totalRevenue = Transaction::whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->sum('total_amount');
        $profitMargin = $totalRevenue > 0 ? 20 : 0;

        $lastMonthRevenue = Transaction::whereMonth('created_at', now()->subMonth())
            ->whereYear('created_at', now()->subMonth()->year)
            ->when(true, $branchScope)
            ->sum('total_amount');
        $performanceTrend = $lastMonthRevenue > 0
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : ($totalRevenue > 0 ? 100 : 0);

        $totalOrders = Transaction::whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->count();
        $orderTrend = $lastMonthRevenue > 0 ? 10 : 0;

        $stocks = BranchStock::with('ingredient', 'branch', 'movements')
            ->when(true, function ($q) use ($selectedBranchId, $isManager, $user) {
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                } elseif ($isManager && $user->branch_id) {
                    $q->where('branch_id', $user->branch_id);
                }
            })
            ->get();

        $inventoryItems = $stocks->map(function ($stock) {
            $movements = $stock->movements;
            $initial = $movements->where('type', StockMovement::TYPE_INITIAL)->sum('quantity_change');
            $restocks = $movements->where('type', StockMovement::TYPE_RESTOCK)->sum('quantity_change');
            $sales = abs($movements->where('type', StockMovement::TYPE_SALE)->sum('quantity_change'));
            $estimated = ($initial + $restocks - $sales);
            if ($estimated <= 0 && $stock->current_quantity > 0) {
                $estimated = $stock->current_quantity;
            }
            return [
                'id' => $stock->id,
                'item_name' => $stock->ingredient?->name ?? 'Unknown Ingredient',
                'unit' => $stock->ingredient?->unit ?? '',
                'branch_id' => $stock->branch_id,
                'branch_name' => $stock->branch?->name ?? 'Unknown',
                'estimated_amount' => max(0, $estimated),
                'on_site_amount' => $stock->current_quantity,
                'min_threshold' => $stock->min_threshold,
                'status' => $stock->stock_status,
            ];
        })->sortBy('branch_name')->values();

        return response()->json([
            'recentTransactions' => $recentTransactions,
            'activeAlerts' => $activeAlerts,
            'currentLeakage' => $currentLeakage,
            'monthlySales' => $monthlySales,
            'historicalData' => $monthlySales,
            'profitMargin' => $profitMargin,
            'performanceTrend' => $performanceTrend,
            'totalOrders' => $totalOrders,
            'orderTrend' => $orderTrend,
            'inventoryItems' => $inventoryItems,
        ]);
    }

    /**
     * GET /ajax/reports?branch_id=X
     */
    public function reports(Request $request): JsonResponse
    {
        $user = $request->user();
        $isManager = $user->isManager();
        $selectedBranchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;
        $branchScope = $this->branchScope($selectedBranchId, $isManager, $user->branch_id);

        $recentFlags = DiscrepancyAlert::with('branch', 'ingredient')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subDays(7))
            ->when(true, $branchScope)
            ->latest()->get();

        $previousFlags = DiscrepancyAlert::with('branch', 'ingredient')
            ->where(function ($q) {
                $q->where('status', '!=', 'pending')
                    ->orWhere('created_at', '<', now()->subDays(7));
            })
            ->when(true, $branchScope)
            ->latest()->take(10)->get();

        return response()->json([
            'recentFlags' => $recentFlags,
            'previousFlags' => $previousFlags,
        ]);
    }

    /**
     * GET /ajax/workers?branch_id=X
     */
    public function workers(Request $request): JsonResponse
    {
        $user = $request->user();
        $isManager = $user->isManager();
        $selectedBranchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;

        $workers = User::whereIn('role', $isManager
                ? [User::ROLE_STAFF]
                : [User::ROLE_STAFF, User::ROLE_MANAGER]
            )
            ->with('branch', 'profile')
            ->when(true, function ($q) use ($isManager, $user, $selectedBranchId) {
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                } elseif ($isManager) {
                    $q->where('branch_id', $user->branch_id);
                }
            })
            ->orderBy('name')
            ->get();

        $openShiftUserIds = \App\Models\ShiftLog::where('status', 'open')
            ->whereIn('user_id', $workers->pluck('id'))
            ->pluck('user_id')
            ->unique()
            ->toArray();

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')->get();

        $totalEmployees = $workers->count();

        return response()->json([
            'workers' => $workers,
            'branches' => $branches,
            'openShiftUserIds' => $openShiftUserIds,
            'totalEmployees' => $totalEmployees,
        ]);
    }

    /**
     * GET /ajax/summary?branch_id=X
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $isManager = $user->isManager();
        $selectedBranchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;
        $branchId = $selectedBranchId ?: ($isManager ? $user->branch_id : null);

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')->get();

        $activeBranch = $selectedBranchId
            ? $branches->firstWhere('id', $selectedBranchId)
            : $branches->first();

        $branchScope = function ($query) use ($branchId) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
        };

        $recentTransactions = Transaction::with('product', 'user')
            ->when(true, $branchScope)
            ->latest()->take(10)->get();

        $totalRevenue = Transaction::whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->sum('total_amount');

        $leakageRows = \App\Models\DiscrepancyAlert::with('ingredient')
            ->where('variance', '<', 0)
            ->when(true, $branchScope)
            ->latest()->take(10)->get();

        $monthlyTransactions = Transaction::whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->get();

        $monthlySales = $monthlyTransactions
            ->groupBy(fn ($t) => (int) $t->created_at->format('n'))
            ->map(fn ($g) => $g->sum('total_amount'));

        $filled = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => (float) ($monthlySales->get($m, 0))]);

        // Compute branch dots HTML
        $dotsHtml = '';
        foreach ($branches as $branch) {
            $loc = $branch->location ?? $branch->name;
            $city = trim(explode(',', $loc)[0]);
            $ini = collect(explode(' ', $city))->map(fn ($w) => strtoupper($w[0]))->take(2)->implode('');
            $isActive = $selectedBranchId == $branch->id;
            $activeClass = $isActive ? 'is-active' : '';
            $dotsHtml .= "<a href=\"#\" class=\"branch-dot-sm {$activeClass}\" onclick=\"switchSummaryBranch({$branch->id});return false;\" title=\"{$branch->name} — {$branch->location}\">{$ini}</a>";
        }

        return response()->json([
            'activeBranch' => $activeBranch,
            'totalRevenue' => max(0, (float) $totalRevenue),
            'recentTransactions' => $recentTransactions,
            'leakageRows' => $leakageRows,
            'monthlySales' => $filled,
            'dotsHtml' => $dotsHtml,
        ]);
    }

    /**
     * GET /ajax/logistics?branch_id=X
     */
    public function logistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $isManager = $user->isManager();
        $selectedBranchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;
        $branchScope = $this->branchScope($selectedBranchId, $isManager, $user->branch_id);

        $stocks = BranchStock::with('ingredient', 'branch', 'movements')
            ->when(true, $branchScope)
            ->get();

        $stockItems = $stocks->map(function ($stock) {
            $movements = $stock->movements;
            $initial = $movements->where('type', StockMovement::TYPE_INITIAL)->sum('quantity_change');
            $restocks = $movements->where('type', StockMovement::TYPE_RESTOCK)->sum('quantity_change');
            $sales = abs($movements->where('type', StockMovement::TYPE_SALE)->sum('quantity_change'));
            $estimated = ($initial + $restocks - $sales);
            if ($estimated <= 0 && $stock->current_quantity > 0) {
                $estimated = $stock->current_quantity;
            }
            return [
                'id' => $stock->id,
                'item_name' => $stock->ingredient?->name ?? 'Unknown Ingredient',
                'unit' => $stock->ingredient?->unit ?? '',
                'branch_id' => $stock->branch_id,
                'branch_name' => $stock->branch?->name ?? 'Unknown',
                'estimated_amount' => max(0, $estimated),
                'on_site_amount' => $stock->current_quantity,
                'min_threshold' => $stock->min_threshold,
                'status' => $stock->stock_status,
            ];
        })->sortBy('branch_name')->values();

        $activeAlerts = DiscrepancyAlert::with('branch', 'ingredient')
            ->where('status', 'open')
            ->when(true, $branchScope)
            ->latest()->get();

        $recentTransactions = Transaction::with('product', 'branch')
            ->when(true, $branchScope)
            ->latest()->take(10)->get();

        $totalStockItems = $stockItems->count();

        return response()->json([
            'stockItems' => $stockItems,
            'activeAlerts' => $activeAlerts,
            'recentTransactions' => $recentTransactions,
            'totalStockItems' => $totalStockItems,
        ]);
    }

    /**
     * GET /ajax/branches?branch_id=X
     * Returns branch info and recipes for the branches index page.
     */
    public function branches(Request $request): JsonResponse
    {
        $selectedBranchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;

        $branch = Branch::find($selectedBranchId);
        if (!$branch) {
            $branch = Branch::orderBy('name')->first();
        }

        $products = \App\Models\Product::with('recipes.ingredient')
            ->orderBy('name')
            ->take(3)
            ->get();

        $productsData = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category,
                'price' => $p->price,
                'procedure' => $p->procedure,
                'recipes' => $p->recipes->map(function ($r) {
                    return [
                        'ingredient_name' => $r->ingredient->name ?? 'Unknown',
                        'unit' => $r->ingredient->unit ?? '',
                        'quantity' => $r->quantity_required,
                        'size' => $r->size,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'branch' => $branch,
            'products' => $productsData,
        ]);
    }
}
