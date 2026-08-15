<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\Product;
use App\Models\ShiftStockCount;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        return view('analytics.index', $this->buildData($request));
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json($this->buildData($request));
    }

    public function exportComparison(Request $request): StreamedResponse
    {
        abort_if($request->user()->isManager(), 403, 'Branch comparison is not available for branch-scoped accounts.');

        $rows = $this->branchComparison();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Branch', 'Revenue This Year (PHP)', 'Orders This Year']);
            foreach ($rows as $row) {
                fputcsv($out, [$row['name'], number_format($row['revenue'], 2, '.', ''), $row['orders']]);
            }
            fclose($out);
        }, 'branch-comparison-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function buildData(Request $request): array
    {
        $user = $request->user();
        $isManager = $user->isManager();
        $selectedBranchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')
            ->get();

        $branchScope = function ($query) use ($isManager, $user, $selectedBranchId) {
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            } elseif ($isManager && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
        };

        $recentTransactions = Transaction::with('product', 'branch', 'user')
            ->when(true, $branchScope)
            ->latest()
            ->take(5)
            ->get();

        $activeAlerts = DiscrepancyAlert::with('branch', 'ingredient')
            ->where('status', 'pending')
            ->when(true, $branchScope)
            ->latest()
            ->take(5)
            ->get();

        $leakageRows = ShiftStockCount::with('ingredient', 'shiftLog.user')
            ->where('variance', '<', 0)
            ->when(true, function ($q) use ($isManager, $user, $selectedBranchId) {
                $q->whereHas('shiftLog', function ($sq) use ($isManager, $user, $selectedBranchId) {
                    if ($selectedBranchId) {
                        $sq->where('branch_id', $selectedBranchId);
                    } elseif ($isManager && $user->branch_id) {
                        $sq->where('branch_id', $user->branch_id);
                    }
                });
            })
            ->latest()
            ->get();

        $currentLeakage = $leakageRows->groupBy(fn ($row) => $row->ingredient->name ?? 'Unknown')
            ->map(fn ($rows, $name) => [
                'name' => $name,
                'amount' => abs($rows->sum('variance')),
                'unit' => $rows->first()->ingredient->unit ?? '',
            ])
            ->values()
            ->take(5);

        // MONTH() is MySQL-only; SQLite (the test suite) needs strftime instead.
        $monthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';

        $monthlySales = Transaction::selectRaw("{$monthExpr} as month, SUM(total_amount) as total")
            ->whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $historicalData = $monthlySales;

        $totalRevenue = Transaction::whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->sum('total_amount');

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

        $thisMonthOrders = Transaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->count();

        $lastMonthOrders = Transaction::whereMonth('created_at', now()->subMonth())
            ->whereYear('created_at', now()->subMonth()->year)
            ->when(true, $branchScope)
            ->count();

        $orderTrend = $lastMonthOrders > 0
            ? round((($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100)
            : ($thisMonthOrders > 0 ? 100 : 0);

        $profitMargin = $this->weightedProfitMargin($branchScope);

        $stocks = BranchStock::with('ingredient', 'branch', 'movements')
            ->when(true, function ($q) use ($isManager, $user, $selectedBranchId) {
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

            return (object) [
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

        return [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'recentTransactions' => $recentTransactions,
            'activeAlerts' => $activeAlerts,
            'currentLeakage' => $currentLeakage,
            'monthlySales' => $monthlySales,
            'historicalData' => $historicalData,
            'profitMargin' => $profitMargin,
            'performanceTrend' => $performanceTrend,
            'totalOrders' => $totalOrders,
            'orderTrend' => $orderTrend,
            'inventoryItems' => $inventoryItems,
            // Branch-vs-branch comparison only makes sense for an account that can
            // see more than one branch — a manager's $branches is always just their own.
            'branchComparison' => $isManager ? collect() : $this->branchComparison(),
        ];
    }

    /**
     * Revenue + order count per branch, this year, for the super_admin-only
     * comparison table and its CSV export.
     */
    private function branchComparison(): \Illuminate\Support\Collection
    {
        $revenueByBranch = Transaction::selectRaw('branch_id, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->whereYear('created_at', now()->year)
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        return Branch::orderBy('name')->get()->map(function ($branch) use ($revenueByBranch) {
            $row = $revenueByBranch->get($branch->id);

            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'revenue' => (float) ($row->revenue ?? 0),
                'orders' => (int) ($row->orders ?? 0),
            ];
        })->sortByDesc('revenue')->values();
    }

    /**
     * Revenue-weighted average margin (price vs. recipe ingredient cost), across
     * products sold this year that have both a price and a costed recipe. Null
     * when no sold product has recipe/supplier cost data to compute a real figure.
     */
    private function weightedProfitMargin(callable $branchScope): ?float
    {
        $revenueByProduct = Transaction::selectRaw('product_id, SUM(total_amount) as revenue')
            ->whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->groupBy('product_id')
            ->pluck('revenue', 'product_id');

        if ($revenueByProduct->isEmpty()) {
            return null;
        }

        $products = Product::with('recipes.ingredient.suppliers')
            ->whereIn('id', $revenueByProduct->keys())
            ->get()
            ->keyBy('id');

        $weightedMarginSum = 0.0;
        $weightedRevenueTotal = 0.0;

        foreach ($revenueByProduct as $productId => $revenue) {
            $product = $products->get($productId);
            $price = (float) ($product->price ?? 0);
            if (! $product || $price <= 0 || $product->recipes->isEmpty()) {
                continue;
            }

            $costBySize = $product->recipes->groupBy('size')->map(fn ($lines) => $lines->sum(function ($recipe) {
                $unitCost = $recipe->ingredient->suppliers->firstWhere('pivot.is_primary', true)?->pivot?->unit_cost ?? 0;

                return $unitCost * (float) $recipe->quantity_required;
            }));

            $avgCost = $costBySize->avg();
            if ($avgCost <= 0) {
                continue;
            }

            $marginPct = (($price - $avgCost) / $price) * 100;
            $weightedMarginSum += $marginPct * $revenue;
            $weightedRevenueTotal += $revenue;
        }

        return $weightedRevenueTotal > 0 ? round($weightedMarginSum / $weightedRevenueTotal, 1) : null;
    }
}
