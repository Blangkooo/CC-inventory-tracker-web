<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\ShiftLog;
use App\Models\ShiftStockCount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $branch = Branch::find($branchId);

        $openShift = ShiftLog::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('shift_start')
            ->first();

        $hasVerifiedStock = $openShift
            && ShiftStockCount::where('shift_log_id', $openShift->id)->exists();

        $totalStaff = User::where('branch_id', $branchId)->where('role', User::ROLE_STAFF)->count();
        $clockedIn = ShiftLog::where('branch_id', $branchId)
            ->where('status', 'open')
            ->whereHas('user', fn ($q) => $q->where('role', User::ROLE_STAFF))
            ->distinct('user_id')
            ->count('user_id');

        $transactionsToday = Transaction::join('products', 'products.id', '=', 'transactions.product_id')
            ->where('transactions.branch_id', $branchId)
            ->whereDate('transactions.created_at', today())
            ->selectRaw('products.category, COUNT(*) as count')
            ->groupBy('products.category')
            ->pluck('count', 'category');

        $stock = BranchStock::with('ingredient')
            ->where('branch_id', $branchId)
            ->get();
        $lowStock = $stock->filter(fn ($s) => $s->min_threshold > 0 && $s->current_quantity <= $s->min_threshold);

        return view('staff.dashboard', [
            'staffUser' => $user,
            'branch' => $branch,
            'openShift' => $openShift,
            'hasVerifiedStock' => $hasVerifiedStock,
            'totalStaff' => $totalStaff,
            'clockedIn' => $clockedIn,
            'transactionsToday' => $transactionsToday,
            'totalIngredients' => $stock->count(),
            'lowStockIngredients' => $lowStock->values(),
        ]);
    }

    /**
     * Self-service clock-in — opens a shift for the logged-in staff member.
     */
    public function clockIn(Request $request): RedirectResponse
    {
        $user = $request->user();

        $existing = ShiftLog::where('user_id', $user->id)->where('status', 'open')->first();
        if (! $existing) {
            ShiftLog::create([
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'shift_start' => now(),
                'status' => 'open',
            ]);
        }

        return redirect()->route('staff.dashboard')->with('status', 'Shift opened.');
    }

    /**
     * Self-service clock-out — closes the logged-in staff member's open shift.
     */
    public function clockOut(Request $request): RedirectResponse
    {
        $user = $request->user();

        $openShift = ShiftLog::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('shift_start')
            ->first();

        if ($openShift) {
            $openShift->update(['shift_end' => now(), 'status' => 'closed']);
        }

        return redirect()->route('login')->with('status', 'Clocked out. See you next shift!');
    }

    /**
     * Marks the "Verify Stock" pre-opening task done by recording a stock
     * count snapshot for every branch ingredient against the open shift.
     */
    public function verifyStock(Request $request): RedirectResponse
    {
        $user = $request->user();

        $openShift = ShiftLog::where('user_id', $user->id)->where('status', 'open')->latest('shift_start')->first();
        if (! $openShift) {
            $openShift = ShiftLog::create([
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'shift_start' => now(),
                'status' => 'open',
            ]);
        }

        $stock = BranchStock::where('branch_id', $user->branch_id)->get();
        foreach ($stock as $s) {
            ShiftStockCount::updateOrCreate(
                ['shift_log_id' => $openShift->id, 'ingredient_id' => $s->ingredient_id],
                ['opening_quantity' => $s->current_quantity]
            );
        }

        return redirect()->route('staff.dashboard')->with('status', 'Stock verified for this shift.');
    }
}
