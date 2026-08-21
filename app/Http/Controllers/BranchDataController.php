<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\ShiftStockCount;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BranchDataController extends Controller
{
    use AuthorizesBranchAccess;

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
     * GET /reports/flags/{alert}/pdf
     */
    public function flagPdf(DiscrepancyAlert $alert): Response
    {
        $this->authorizeBranch($alert->branch_id);

        $alert->load('branch', 'ingredient', 'shiftLog.user');

        $pdf = Pdf::loadView('reports.flag-pdf', [
            'alert' => $alert,
            'generatedBy' => auth()->user()->name,
            'generatedAt' => now()->format('M d, Y g:i A'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('flag-report-'.$alert->id.'-'.now()->format('Y-m-d').'.pdf');
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
