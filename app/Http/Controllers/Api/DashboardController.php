<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
}
