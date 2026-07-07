<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $totalRevenue = \App\Models\Transaction::join('products', 'products.id', '=', 'transactions.product_id')
            ->whereDate('transactions.created_at', $today)
            ->sum(\Illuminate\Support\Facades\DB::raw('products.price * transactions.quantity'));

        $totalSales = \App\Models\Transaction::whereDate('created_at', $today)->count();

        $flaggedShifts = \App\Models\ShiftLog::where('flagged', true)
            ->whereDate('created_at', $today)->count();

        $openAlerts = \App\Models\Alert::where('status', 'unread')->count();

        $topProducts = \App\Models\Transaction::selectRaw('product_id, SUM(quantity) as units_sold, SUM(products.price * transactions.quantity) as revenue')
            ->join('products', 'products.id', '=', 'transactions.product_id')
            ->whereDate('transactions.created_at', $today)
            ->groupBy('product_id', 'products.name', 'products.price')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'product_id' => $row->product_id,
                'name'       => \App\Models\Product::find($row->product_id)?->name,
                'units_sold' => $row->units_sold,
                'revenue'    => round($row->revenue, 2),
            ]);

        $recentAlerts = \App\Models\Alert::with('branch')
            ->where('status', 'unread')
            ->latest()
            ->limit(10)
            ->get();

        $salesSummary = \App\Models\Transaction::selectRaw('DATE(created_at) as date, COUNT(*) as sales, SUM(products.price * transactions.quantity) as revenue')
            ->join('products', 'products.id', '=', 'transactions.product_id')
            ->whereBetween(\Illuminate\Support\Facades\DB::raw('DATE(transactions.created_at)'), [now()->subDays(6)->toDateString(), $today])
            ->groupBy(\Illuminate\Support\Facades\DB::raw('DATE(transactions.created_at)'))
            ->orderBy('date')
            ->get();

        return view('dashboard', compact(
            'totalRevenue', 'totalSales', 'flaggedShifts',
            'openAlerts', 'topProducts', 'recentAlerts', 'salesSummary'
        ));
    }
}
