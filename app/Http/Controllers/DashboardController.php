<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\Product;
use App\Models\ShiftStockCount;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $expectedTotal = (float) ShiftStockCount::when($isManager, fn ($q) => $q->whereHas(
            'shiftLog', fn ($q2) => $q2->where('branch_id', $branchId)
        ))->sum('closing_quantity_expected');

        // ── Last-month comparisons ─────────────────────────────────────
        $lastMonthRevenue = Transaction::whereMonth('created_at', now()->subMonth())
            ->whereYear('created_at', now()->subMonth()->year)
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $lastMonthAlerts = DiscrepancyAlert::where('status', 'pending')
            ->whereMonth('created_at', now()->subMonth())
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        $lastMonthLowStock = BranchStock::where('current_quantity', '<=', DB::raw('min_threshold'))
            ->where('min_threshold', '>', 0)
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        // ── Monthly sales for KPI chart (Jan–Dec of current year) ─────
        $monthlySales = Transaction::selectRaw("strftime('%m', created_at) as month, SUM(total_amount) as total")
            ->whereYear('created_at', now()->year)
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return view('dashboard', [
            'total_branches' => Branch::count(),
            'pending_alerts' => DiscrepancyAlert::where('status', 'pending')
                ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
                ->count(),
            'low_stock_count' => BranchStock::where('current_quantity', '<=', DB::raw('min_threshold'))
                ->where('min_threshold', '>', 0)
                ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
                ->count(),
            'total_sales' => Transaction::whereDate('created_at', today())
                ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
                ->sum('total_amount'),
            'branches_with_sales' => Branch::with(['transactions' => fn ($q) => $q->whereDate('created_at', today())])
                ->when($isManager, fn ($q) => $q->where('id', $branchId))
                ->get()
                ->map(fn ($b) => [
                    'name' => $b->name,
                    'today_sales' => $b->transactions->sum('total_amount'),
                    'has_sales' => $b->transactions->count() > 0,
                ]),
            'annual_revenue' => Transaction::whereYear('created_at', now()->year)
                ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
                ->sum('total_amount'),
            'leakage_pct' => $expectedTotal > 0
                ? (float) ShiftStockCount::where('variance', '<', 0)
                    ->when($isManager, fn ($q) => $q->whereHas('shiftLog', fn ($q2) => $q2->where('branch_id', $branchId)))
                    ->sum(DB::raw('ABS(variance)')) / $expectedTotal * 100
                : 0,
            'value_saved' => $this->estimatedValueSaved($isManager, $branchId),

            'recent_flags' => DiscrepancyAlert::with('branch', 'ingredient', 'shiftLog.user')
                ->where('status', 'pending')
                ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
                ->latest()
                ->take(6)
                ->get(),

            'top_earners' => Branch::withSum('transactions as revenue', 'total_amount')
                ->when($isManager, fn ($q) => $q->where('id', $branchId))
                ->orderByDesc('revenue')
                ->take(5)
                ->get(),
            'least_leakage' => Branch::when($isManager, fn ($q) => $q->where('id', $branchId))
                ->get()
                ->map(fn ($b) => [
                    'name' => $b->name,
                    'leak' => (float) ShiftStockCount::whereHas('shiftLog', fn ($q) => $q->where('branch_id', $b->id))
                        ->where('variance', '<', 0)
                        ->sum(DB::raw('ABS(variance)')),
                ])->sortBy('leak')->values(),

            // ── New data for redesigned UI ─────────────────────────────
            'monthly_sales' => $monthlySales,
            'lastMonthRevenue' => $lastMonthRevenue,
            'lastMonthAlerts' => $lastMonthAlerts,
            'lastMonthLowStock' => $lastMonthLowStock,
        ]);
    }

    /**
     * Estimated peso value of leakage that was caught and acted on
     * (alerts marked reviewed/dismissed). No ingredient cost data exists,
     * so each ingredient's unit value is pro-rated from product prices:
     * price / total recipe quantity, averaged across products using it.
     */
    private function estimatedValueSaved(bool $isManager, ?int $branchId): float
    {
        $unitValues = [];

        Product::with('recipes')->get()->each(function ($product) use (&$unitValues) {
            $totalQty = $product->recipes->sum('quantity_required');

            if ($totalQty <= 0) {
                return;
            }

            foreach ($product->recipes as $recipe) {
                $unitValues[$recipe->ingredient_id][] = (float) $product->price / $totalQty;
            }
        });

        $avgUnitValue = array_map(fn ($values) => array_sum($values) / count($values), $unitValues);

        return DiscrepancyAlert::whereIn('status', ['reviewed', 'dismissed'])
            ->whereNotNull('ingredient_id')
            ->whereNotNull('variance')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->sum(fn ($alert) => abs((float) $alert->variance) * ($avgUnitValue[$alert->ingredient_id] ?? 0));
    }
}
