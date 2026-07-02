<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'total_branches' => Branch::count(),
            'pending_alerts' => DiscrepancyAlert::where('status', 'pending')->count(),
            'low_stock_count' => BranchStock::where('current_quantity', '<=', DB::raw('min_threshold'))
                ->where('min_threshold', '>', 0)
                ->count(),
            'total_sales' => Transaction::whereDate('created_at', today())->sum('total_amount'),
            'daily_sales' => Transaction::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'branches_with_sales' => Branch::with(['transactions' => fn ($q) => $q->whereDate('created_at', today())])
                ->get()
                ->map(fn ($b) => [
                    'name' => $b->name,
                    'today_sales' => $b->transactions->sum('total_amount'),
                    'has_sales' => $b->transactions->count() > 0,
                ]),
            'recent_alerts' => DiscrepancyAlert::with('branch', 'ingredient', 'shiftLog.user')
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'recent_transactions' => Transaction::with('product', 'branch', 'user')->latest()->take(10)->get(),
        ]);
    }
}
