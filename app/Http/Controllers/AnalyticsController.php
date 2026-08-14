<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ShiftLog;
use App\Models\ShiftStockCount;
use App\Models\Transaction;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $salesTotals = Transaction::selectRaw('DATE(created_at) as d, SUM(total_amount) as total')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->groupBy('d')
            ->pluck('total', 'd');

        $salesSeries = collect(range(0, 29))->map(function ($i) use ($salesTotals) {
            $date = now()->subDays(29 - $i)->format('Y-m-d');

            return ['date' => $date, 'total' => (float) ($salesTotals[$date] ?? 0)];
        });

        $topProducts = Transaction::with('product')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('product_id, SUM(total_amount) as revenue, SUM(quantity) as units')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->take(8)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->product?->name ?? 'Unknown product',
                'revenue' => (float) $row->revenue,
                'units' => (int) $row->units,
            ]);

        $branchRevenue = Branch::withSum('transactions as revenue', 'total_amount')
            ->when($isManager, fn ($q) => $q->where('id', $branchId))
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($b) => ['name' => $b->name, 'revenue' => (float) ($b->revenue ?? 0)]);

        $laborHours = ShiftLog::with('user')
            ->where('status', 'closed')
            ->whereNotNull('shift_end')
            ->where('shift_start', '>=', now()->startOfMonth())
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->groupBy('user_id')
            ->map(fn ($logs) => [
                'name' => $logs->first()->user?->name ?? 'Unknown worker',
                'hours' => round($logs->sum(fn ($l) => $l->shift_start->diffInMinutes($l->shift_end) / 60), 1),
            ])
            ->sortByDesc('hours')
            ->values()
            ->take(8);

        $leakageTrend = ShiftStockCount::where('variance', '<', 0)
            ->whereHas('shiftLog', fn ($q) => $q->where('shift_start', '>=', now()->subDays(29))
                ->when($isManager, fn ($q2) => $q2->where('branch_id', $branchId)))
            ->with('shiftLog')
            ->get()
            ->groupBy(fn ($row) => $row->shiftLog->shift_start->format('Y-m-d'))
            ->map(fn ($rows, $date) => ['date' => $date, 'total' => (float) $rows->sum(fn ($r) => abs($r->variance))])
            ->sortKeys()
            ->values();

        $peakSales = max($salesSeries->max('total'), 1);
        $peakLeakage = max($leakageTrend->max('total') ?: 0, 1);
        $peakLabor = max($laborHours->max('hours') ?: 0, 1);
        $peakProduct = max($topProducts->max('revenue') ?: 0, 1);

        return view('analytics.index', compact(
            'salesSeries', 'topProducts', 'branchRevenue', 'laborHours', 'leakageTrend',
            'peakSales', 'peakLeakage', 'peakLabor', 'peakProduct', 'isManager'
        ));
    }
}
