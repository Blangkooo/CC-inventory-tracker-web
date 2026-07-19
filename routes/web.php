<?php

use App\Http\Controllers\AlertsController;
use App\Http\Controllers\AuthOnboardingController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RecipesController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ── Public: Auth / Onboarding Views (no middleware) ──────────────────

Route::get('/', [AuthOnboardingController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthOnboardingController::class, 'showLogin']);
Route::get('/auth/login', [AuthOnboardingController::class, 'showLogin']);
Route::get('/auth/register/step-1', [AuthOnboardingController::class, 'showRegisterStep1']);
Route::get('/auth/register/step-2', [AuthOnboardingController::class, 'showRegisterStep2']);
Route::get('/auth/register/step-3', [AuthOnboardingController::class, 'showRegisterStep3']);

// ── Path A: Business Owner Onboarding ────────────────────────────────
Route::get('/auth/register/owner/step-2', [AuthOnboardingController::class, 'showOwnerStep2']);
Route::get('/auth/register/owner/step-3', [AuthOnboardingController::class, 'showOwnerStep3']);

// ── Path B: Branch Manager Onboarding ────────────────────────────────
Route::get('/auth/register/manager/step-2', [AuthOnboardingController::class, 'showManagerStep2']);
Route::get('/auth/register/manager/step-3', [AuthOnboardingController::class, 'showManagerStep3']);

// ── Session-based Login POST ─────────────────────────────────────────

Route::post('/login', function () {
    $credentials = request()->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (Auth::attempt($credentials, request()->boolean('remember'))) {
        request()->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()
        ->withInput(request()->only('email'))
        ->withErrors(['email' => 'These credentials do not match our records.']);
})->name('login.post');

// ── Authenticated Pages ──────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/recipes', [RecipesController::class, 'index'])->name('recipes');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/branches', [BranchesController::class, 'index'])->name('branches');
    Route::get('/branches/{branch}', [BranchesController::class, 'show'])->whereNumber('branch')->name('branches.show');
    Route::get('/alerts', [AlertsController::class, 'index'])->name('alerts');

    // ── Business Pages (static structural placeholders) ───────────────
    Route::get('/business/recipes', \App\Http\Controllers\BusinessRecipesController::class)
        ->name('business.recipes');

    Route::get('/business/summary', \App\Http\Controllers\BusinessSummaryController::class)
        ->name('business.summary');

    // ── Workers Page (Staff & Manager listing) ──────────────────────────
    Route::get('/business/workers', function () {
        $workers = \App\Models\User::whereIn('role', [\App\Models\User::ROLE_STAFF, \App\Models\User::ROLE_MANAGER])
            ->with('branch', 'profile')
            ->orderBy('name')
            ->get();

        // Dedicated query to find workers with an open shift (avoids loading all shift logs)
        $openShiftUserIds = \App\Models\ShiftLog::where('status', 'open')
            ->whereIn('user_id', $workers->pluck('id'))
            ->pluck('user_id')
            ->unique()
            ->toArray();

        return view('business.workers', [
            'branches'          => \App\Models\Branch::orderBy('name')->get(),
            'workers'           => $workers,
            'openShiftUserIds'  => $openShiftUserIds,
        ]);
    })->name('business.workers');

    // ── Workers CRUD (AJAX endpoints) ───────────────────────────────────
    Route::post('/business/workers', [\App\Http\Controllers\WorkersController::class, 'store'])
        ->name('business.workers.store');
    Route::put('/business/workers/{user}', [\App\Http\Controllers\WorkersController::class, 'update'])
        ->name('business.workers.update');
    Route::delete('/business/workers/{user}', [\App\Http\Controllers\WorkersController::class, 'destroy'])
        ->name('business.workers.destroy');

    // Alias for form-method spoofing (DELETE)
    Route::post('/business/workers/{user}/delete', [\App\Http\Controllers\WorkersController::class, 'destroy'])
        ->name('business.workers.delete');

    // ── Worker Profile CRUD ─────────────────────────────────────────────
    Route::put('/business/workers/{user}/profile', [\App\Http\Controllers\WorkersController::class, 'updateProfile'])
        ->name('business.workers.profile');

    // ── Attendance / Clock-In-Out ───────────────────────────────────────
    Route::post('/business/workers/{user}/clock-in', [\App\Http\Controllers\AttendanceController::class, 'clockIn'])
        ->name('business.workers.clock-in');
    Route::post('/business/workers/{user}/clock-out', [\App\Http\Controllers\AttendanceController::class, 'clockOut'])
        ->name('business.workers.clock-out');
    Route::get('/business/workers/{user}/attendance', [\App\Http\Controllers\AttendanceController::class, 'history'])
        ->name('business.workers.attendance');

    // ── Worker Activity (transactions, shifts, discrepancies) ─────────────
    Route::get('/business/workers/{user}/activity', \App\Http\Controllers\ActivityController::class)
        ->name('business.workers.activity');

    // ── Logistics Page (Summary + Flags) ────────────────────────────
    Route::get('/logistics', function () {
        $user = auth()->user();
        $isManager = $user->isManager();

        // Stock summary with movements for estimated-amount calculation
        $stocks = \App\Models\BranchStock::with('ingredient', 'branch', 'movements')
            ->when($isManager, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->get();

        // Build per-item inventory rows
        $stockItems = $stocks->map(function ($stock) {
            $movements = $stock->movements;

            $initial  = $movements->where('type', \App\Models\StockMovement::TYPE_INITIAL)->sum('quantity_change');
            $restocks = $movements->where('type', \App\Models\StockMovement::TYPE_RESTOCK)->sum('quantity_change');
            $sales    = abs($movements->where('type', \App\Models\StockMovement::TYPE_SALE)->sum('quantity_change'));

            $estimated = ($initial + $restocks - $sales);
            // Fallback if no movements exist yet
            if ($estimated <= 0 && $stock->current_quantity > 0) {
                $estimated = $stock->current_quantity;
            }

            return (object) [
                'id'               => $stock->id,
                'item_name'        => $stock->ingredient?->name ?? 'Unknown Ingredient',
                'unit'             => $stock->ingredient?->unit ?? '',
                'branch_id'        => $stock->branch_id,
                'branch_name'      => $stock->branch?->name ?? 'Unknown',
                'estimated_amount' => max(0, $estimated),
                'on_site_amount'   => $stock->current_quantity,
                'min_threshold'    => $stock->min_threshold,
                'status'           => $stock->stock_status,
            ];
        })->sortBy('branch_name')->values();

        // Recent stock movements (last 20)
        $recentMovements = \App\Models\StockMovement::with('branchStock.ingredient', 'branchStock.branch', 'user')
            ->when($isManager, fn ($q) => $q->whereHas('branchStock', fn ($q) => $q->where('branch_id', $user->branch_id)))
            ->latest()
            ->take(20)
            ->get();

        // Active discrepancy alerts
        $activeAlerts = \App\Models\DiscrepancyAlert::with('branch', 'ingredient')
            ->where('status', 'open')
            ->when($isManager, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->latest()
            ->get();

        // Recent transactions (last 10)
        $recentTransactions = \App\Models\Transaction::with('product', 'branch')
            ->when($isManager, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->latest()
            ->take(10)
            ->get();

        // Branches (for filtering)
        $branches = \App\Models\Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))->orderBy('name')->get();

        $tab = in_array(request()->query('tab'), ['summary', 'flags'], true)
            ? request()->query('tab')
            : 'summary';

        $totalStockItems = $stockItems->count();

        return view('logistics.index', [
            'tab'                => $tab,
            'branches'           => $branches,
            'stockItems'         => $stockItems,
            'stocks'             => $stocks,
            'totalStockItems'    => $totalStockItems,
            'recentMovements'    => $recentMovements,
            'activeAlerts'       => $activeAlerts,
            'recentTransactions' => $recentTransactions,
        ]);
    })->name('logistics');

    // ── Verification page (sub-tab in business/* views) ──────────────
    Route::get('/business/verification', function () {
        return view('business.verification');
    })->name('business.verification');

    // ── API Documentation (protected — requires auth) ─────────────────
    Route::get('/api-docs', function () {
        $path = public_path('_api-docs.html');
        if (! file_exists($path)) {
            abort(404, 'API documentation not found.');
        }
        return response()->file($path);
    })->name('api.docs');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings');
});

// ── Logout ───────────────────────────────────────────────────────────

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');
