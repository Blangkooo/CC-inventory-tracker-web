<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use AuthorizesBranchAccess;

    public function kpis(Request $request)
    {
        $branchId = $request->branch_id;
        $date     = $request->date ?? now()->toDateString();

        $txQuery = \App\Models\Transaction::whereDate('created_at', $date);
        if ($branchId) $txQuery->where('branch_id', $branchId);

        $totalSales     = (clone $txQuery)->count();
        $totalRevenue   = (clone $txQuery)->sum('total_amount');
        // "Flagged shifts" = shifts that produced a variance alert that day.
        $flaggedShifts  = \App\Models\DiscrepancyAlert::whereDate('created_at', $date)
                            ->where('type', 'shift_variance')
                            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                            ->distinct('shift_log_id')->count('shift_log_id');
        $openAlerts     = \App\Models\DiscrepancyAlert::where('status', 'pending')
                            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                            ->count();

        return response()->json([
            'date'           => $date,
            'total_sales'    => $totalSales,
            'total_revenue'  => round($totalRevenue, 2),
            'flagged_shifts' => $flaggedShifts,
            'open_alerts'    => $openAlerts,
        ]);
    }

    public function salesSummary(Request $request)
    {
        $branchId = $request->branch_id;
        $from     = $request->from ?? now()->startOfWeek()->toDateString();
        $to       = $request->to   ?? now()->toDateString();

        $summary = \App\Models\Transaction::selectRaw('DATE(transactions.created_at) as date, COUNT(*) as sales, SUM(products.price * transactions.quantity) as revenue')
            ->join('products', 'products.id', '=', 'transactions.product_id')
            ->whereBetween(\Illuminate\Support\Facades\DB::raw('DATE(transactions.created_at)'), [$from, $to])
            ->when($branchId, fn($q) => $q->where('transactions.branch_id', $branchId))
            ->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(transactions.created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json($summary);
    }

    public function topProducts(Request $request)
    {
        $branchId = $request->branch_id;
        $limit    = $request->limit ?? 5;
        $date     = $request->date ?? now()->toDateString();

        $products = \App\Models\Transaction::selectRaw('product_id, SUM(quantity) as units_sold, SUM(products.price * transactions.quantity) as revenue')
            ->join('products', 'products.id', '=', 'transactions.product_id')
            ->whereDate('transactions.created_at', $date)
            ->when($branchId, fn($q) => $q->where('transactions.branch_id', $branchId))
            ->groupBy('product_id', 'products.name', 'products.price')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->with('product')
            ->get()
            ->map(fn($row) => [
                'product_id'  => $row->product_id,
                'name'        => $row->product->name ?? null,
                'units_sold'  => $row->units_sold,
                'revenue'     => round($row->revenue, 2),
            ]);

        return response()->json($products);
    }

    /**
     * Revenue for every month of a given year, plus the same twelve months
     * one year earlier for a year-over-year comparison. Always exactly
     * twelve values per series (zero-filled), so a chart never has to guess
     * at a missing month.
     */
    public function trends(Request $request)
    {
        $branchId = $request->branch_id;
        if ($branchId) {
            $this->authorizeBranch((int) $branchId);
        }

        $year = (int) ($request->year ?? now()->year);

        $monthlyRevenue = fn (int $y) => \App\Models\Transaction::selectRaw('MONTH(created_at) as m, SUM(total_amount) as total')
            ->whereYear('created_at', $y)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->groupBy('m')
            ->pluck('total', 'm');

        $thisYear = $monthlyRevenue($year);
        $lastYear = $monthlyRevenue($year - 1);

        $series = fn ($totals) => collect(range(1, 12))
            ->map(fn ($m) => round((float) ($totals[$m] ?? 0), 2))
            ->values();

        return response()->json([
            'year' => $year,
            'monthly_revenue' => $series($thisYear),
            'monthly_revenue_prior_year' => $series($lastYear),
            'yearly_total' => round($thisYear->sum(), 2),
            'yearly_total_prior_year' => round($lastYear->sum(), 2),
        ]);
    }

    /**
     * Stock leakage (negative variance between expected and actual counts)
     * over a date range, both as a total and broken down per branch — the
     * same underlying figure the web dashboard's "Overall Value Saved" /
     * "Least Leakage" cards read from, exposed here for the API/mobile side.
     */
    public function leakage(Request $request)
    {
        $branchId = $request->branch_id;
        if ($branchId) {
            $this->authorizeBranch((int) $branchId);
        }

        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to = $request->to ?? now()->toDateString();

        $baseQuery = fn () => \App\Models\ShiftStockCount::where('variance', '<', 0)
            ->whereHas('shiftLog', fn ($q) => $q->whereBetween('shift_start', [$from, $to])
                ->when($branchId, fn ($q2) => $q2->where('branch_id', $branchId)));

        $totalLeakage = (float) $baseQuery()->sum(\Illuminate\Support\Facades\DB::raw('ABS(variance)'));

        $byBranch = \App\Models\Branch::when($branchId, fn ($q) => $q->where('id', $branchId))
            ->get()
            ->map(fn ($b) => [
                'branch_id' => $b->id,
                'branch_name' => $b->name,
                'leakage' => (float) \App\Models\ShiftStockCount::where('variance', '<', 0)
                    ->whereHas('shiftLog', fn ($q) => $q->where('branch_id', $b->id)
                        ->whereBetween('shift_start', [$from, $to]))
                    ->sum(\Illuminate\Support\Facades\DB::raw('ABS(variance)')),
            ])
            ->sortBy('leakage')
            ->values();

        return response()->json([
            'from' => $from,
            'to' => $to,
            'total_leakage' => round($totalLeakage, 3),
            'by_branch' => $byBranch,
        ]);
    }
}
