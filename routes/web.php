<?php

use App\Http\Controllers\AlertsController;
use App\Http\Controllers\AuthOnboardingController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RecipesController;
use App\Http\Controllers\SettingsController;
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

    // ── Analytics Page ──────────────────────────────────────────────────
    Route::get('/analytics', function () {
        $user = auth()->user();
        $isManager = $user->isManager();
        $selectedBranchId = request()->query('branch_id') ? (int) request()->query('branch_id') : null;

        // Get branches for the business tabs
        $branches = \App\Models\Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')
            ->get();

        // Scope helper: apply branch filter if a branch is selected
        $branchScope = function ($query) use ($isManager, $user, $selectedBranchId) {
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            } elseif ($isManager && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
        };

        // Get recent transactions
        $recentTransactions = \App\Models\Transaction::with('product', 'branch', 'user')
            ->when(true, $branchScope)
            ->latest()
            ->take(5)
            ->get();

        // Get active alerts
        $activeAlerts = \App\Models\DiscrepancyAlert::with('branch', 'ingredient')
            ->where('status', 'pending')
            ->when(true, $branchScope)
            ->latest()
            ->take(5)
            ->get();

        // Get current leakage data from shift stock counts
        $leakageRows = \App\Models\ShiftStockCount::with('ingredient', 'shiftLog.user')
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

        // Build current leakage summary (ingredient name + amount)
        $currentLeakage = $leakageRows->groupBy(fn($row) => $row->ingredient->name ?? 'Unknown')
            ->map(fn($rows, $name) => [
                'name' => $name,
                'amount' => abs($rows->sum('variance')),
                'unit' => $rows->first()->ingredient->unit ?? '',
            ])
            ->values()
            ->take(5);

        // Get monthly sales for the current year
        $monthlySales = \App\Models\Transaction::selectRaw("strftime('%m', created_at) as month, SUM(total_amount) as total")
            ->whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Historical data (same as monthly for now)
        $historicalData = $monthlySales;

        // Calculate profit margin (simplified - would need cost data for accuracy)
        $totalRevenue = \App\Models\Transaction::whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->sum('total_amount');
        $profitMargin = $totalRevenue > 0 ? 20 : 0; // Placeholder - would need cost data

        // Calculate performance trend
        $lastMonthRevenue = \App\Models\Transaction::whereMonth('created_at', now()->subMonth())
            ->whereYear('created_at', now()->subMonth()->year)
            ->when(true, $branchScope)
            ->sum('total_amount');
        $performanceTrend = $lastMonthRevenue > 0 
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : ($totalRevenue > 0 ? 100 : 0);

        // Get total orders count
        $totalOrders = \App\Models\Transaction::whereYear('created_at', now()->year)
            ->when(true, $branchScope)
            ->count();
        $orderTrend = $lastMonthRevenue > 0 ? 10 : 0; // Placeholder

        // Get inventory items for the inventory view
        $stocks = \App\Models\BranchStock::with('ingredient', 'branch', 'movements')
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
            $initial = $movements->where('type', \App\Models\StockMovement::TYPE_INITIAL)->sum('quantity_change');
            $restocks = $movements->where('type', \App\Models\StockMovement::TYPE_RESTOCK)->sum('quantity_change');
            $sales = abs($movements->where('type', \App\Models\StockMovement::TYPE_SALE)->sum('quantity_change'));

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

        return view('analytics.index', [
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
        ]);
    })->name('analytics');
    Route::get('/recipes', [RecipesController::class, 'index'])->name('recipes');

    // ── Recipes CRUD (AJAX endpoints) ─────────────────────────────────
    Route::get('/business/recipes/product/{product}/data', [RecipesController::class, 'getProductData'])
        ->name('business.recipes.product.data');
    Route::put('/business/recipes/product/{product}', [RecipesController::class, 'updateProduct'])
        ->name('business.recipes.product.update');
    Route::post('/business/recipes/product/{product}/ingredient', [RecipesController::class, 'addIngredient'])
        ->name('business.recipes.product.ingredient');
    Route::put('/business/recipes/ingredient/{recipe}', [RecipesController::class, 'updateIngredient'])
        ->name('business.recipes.ingredient.update');
    Route::post('/business/recipes/ingredient/{recipe}/delete', [RecipesController::class, 'removeIngredient'])
        ->name('business.recipes.ingredient.delete');
    Route::post('/business/recipes/product/{product}/delete', [RecipesController::class, 'deleteProduct'])
        ->name('business.recipes.product.delete');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/branches', [BranchesController::class, 'index'])->name('branches');
    Route::post('/branches', [BranchesController::class, 'store'])->name('branches.store');
    Route::get('/branches/{branch}', [BranchesController::class, 'show'])->whereNumber('branch')->name('branches.show');
    Route::get('/alerts', [AlertsController::class, 'index'])->name('alerts');

    // ── Reports Page ────────────────────────────────────────────────────
    Route::get('/reports', function () {
        $user = auth()->user();
        $isManager = $user->isManager();
        $selectedBranchId = request()->query('branch_id') ? (int) request()->query('branch_id') : null;

        // Get branches for the business tabs
        $branches = \App\Models\Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')
            ->get();

        $branchScope = function ($query) use ($isManager, $user, $selectedBranchId) {
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            } elseif ($isManager && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
        };

        // Get recent flags (last 7 days)
        $recentFlags = \App\Models\DiscrepancyAlert::with('branch', 'ingredient')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subDays(7))
            ->when(true, $branchScope)
            ->latest()
            ->get();

        // Get previous flags (older than 7 days or resolved)
        $previousFlags = \App\Models\DiscrepancyAlert::with('branch', 'ingredient')
            ->where(function ($q) use ($user, $isManager) {
                $q->where('status', '!=', 'pending')
                  ->orWhere('created_at', '<', now()->subDays(7));
            })
            ->when(true, $branchScope)
            ->latest()
            ->take(10)
            ->get();

        return view('reports.index', [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'recentFlags' => $recentFlags,
            'previousFlags' => $previousFlags,
        ]);
    })->name('reports');



    // ── Workers Page (Staff & Manager listing) ──────────────────────────
    Route::get('/business/workers', function () {
        $user = auth()->user();

        // Authorization: staff cannot access the workers management page.
        if (! $user->isSuperAdmin() && ! $user->isManager()) {
            abort(403, 'You do not have permission to manage workers.');
        }

        $isManager = $user->isManager();
        $selectedBranchId = request()->query('branch_id') ? (int) request()->query('branch_id') : null;

        $workers = \App\Models\User::whereIn('role', $isManager
                ? [\App\Models\User::ROLE_STAFF]           // managers see only staff
                : [\App\Models\User::ROLE_STAFF, \App\Models\User::ROLE_MANAGER]  // super_admins see all
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

        // Dedicated query to find workers with an open shift (avoids loading all shift logs)
        $openShiftUserIds = \App\Models\ShiftLog::where('status', 'open')
            ->whereIn('user_id', $workers->pluck('id'))
            ->pluck('user_id')
            ->unique()
            ->toArray();

        return view('business.workers', [
            'branches'          => \App\Models\Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))->orderBy('name')->get(),
            'workers'           => $workers,
            'openShiftUserIds'  => $openShiftUserIds,
            'selectedBranchId'  => $selectedBranchId,
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
        $selectedBranchId = request()->query('branch_id') ? (int) request()->query('branch_id') : null;

        $branchScope = function ($query) use ($isManager, $user, $selectedBranchId) {
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            } elseif ($isManager && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
        };

        // Stock summary with movements for estimated-amount calculation
        $stocks = \App\Models\BranchStock::with('ingredient', 'branch', 'movements')
            ->when(true, $branchScope)
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
            ->when(true, function ($q) use ($isManager, $user, $selectedBranchId) {
                $q->whereHas('branchStock', function ($sq) use ($isManager, $user, $selectedBranchId) {
                    if ($selectedBranchId) {
                        $sq->where('branch_id', $selectedBranchId);
                    } elseif ($isManager && $user->branch_id) {
                        $sq->where('branch_id', $user->branch_id);
                    }
                });
            })
            ->latest()
            ->take(20)
            ->get();

        // Active discrepancy alerts
        $activeAlerts = \App\Models\DiscrepancyAlert::with('branch', 'ingredient')
            ->where('status', 'open')
            ->when(true, $branchScope)
            ->latest()
            ->get();

        // Recent transactions (last 10)
        $recentTransactions = \App\Models\Transaction::with('product', 'branch')
            ->when(true, $branchScope)
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
            'selectedBranchId'   => $selectedBranchId,
            'stockItems'         => $stockItems,
            'stocks'             => $stocks,
            'totalStockItems'    => $totalStockItems,
            'recentMovements'    => $recentMovements,
            'activeAlerts'       => $activeAlerts,
            'recentTransactions' => $recentTransactions,
        ]);
    })->name('logistics');

    // ── Legal page (Document verification) ────────────────────────────
    Route::get('/business/verification', function () {
        $user = auth()->user();
        $isManager = $user->isManager();

        $branches = \App\Models\Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')
            ->get();

        return view('business.verification', [
            'branches' => $branches,
        ]);
    })->name('business.verification');

    // ── API Documentation (protected — requires auth) ─────────────────
    Route::get('/api-docs', function () {
        $path = public_path('_api-docs.html');
        if (! file_exists($path)) {
            abort(404, 'API documentation not found.');
        }
        return response()->file($path);
    })->name('api.docs');

    // ── Calendar Page ──────────────────────────────────────────────────
    Route::get('/calendar', function () {
        $user = auth()->user();
        $isManager = $user->isManager();

        // Get branches for the meeting form
        $branches = \App\Models\Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')
            ->get();

        // Get meetings for the current month
        $meetings = \App\Models\Meeting::with(['branch', 'creator'])
            ->when($isManager && $user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Get meetings for the current week
        $weekMeetings = \App\Models\Meeting::with(['branch', 'creator'])
            ->when($isManager && $user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Get upcoming meetings
        $upcomingMeetings = \App\Models\Meeting::with(['branch', 'creator'])
            ->when($isManager && $user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Get days with events for calendar highlighting
        $eventDays = $meetings->pluck('date')->map(fn($d) => $d->day)->unique()->toArray();

        return view('calendar.index', [
            'branches' => $branches,
            'meetings' => $meetings,
            'weekMeetings' => $weekMeetings,
            'upcomingMeetings' => $upcomingMeetings,
            'eventDays' => $eventDays,
        ]);
    })->name('calendar');

    // ── Meeting AJAX Endpoints ────────────────────────────────────────
    Route::post('/calendar/meetings', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'string', 'max:10'],
            'end_time' => ['required', 'string', 'max:10'],
            'meeting_type' => ['nullable', 'in:meeting,task,event'],
            'location' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'scheduled';

        $meeting = \App\Models\Meeting::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Meeting created successfully.',
            'data' => $meeting->load(['branch', 'creator']),
        ], 201);
    })->name('calendar.meetings.store');

    Route::delete('/calendar/meetings/{meeting}', function (\App\Models\Meeting $meeting) {
        $meeting->delete();

        return response()->json([
            'status' => true,
            'message' => 'Meeting deleted successfully.',
        ]);
    })->name('calendar.meetings.delete');

    Route::get('/payments', function () {
        return view('payments.index');
    })->name('payments');

    Route::get('/help', function () {
        return view('help.index');
    })->name('help');

    Route::get('/about', function () {
        return view('about.index');
    })->name('about');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    // ── AJAX Branch Data Endpoints ────────────────────────────────────
    Route::get('/ajax/analytics', [\App\Http\Controllers\BranchDataController::class, 'analytics'])->name('ajax.analytics');
    Route::get('/ajax/reports', [\App\Http\Controllers\BranchDataController::class, 'reports'])->name('ajax.reports');
    Route::get('/ajax/workers', [\App\Http\Controllers\BranchDataController::class, 'workers'])->name('ajax.workers');
    Route::get('/ajax/summary', [\App\Http\Controllers\BranchDataController::class, 'summary'])->name('ajax.summary');
    Route::get('/ajax/logistics', [\App\Http\Controllers\BranchDataController::class, 'logistics'])->name('ajax.logistics');
    Route::get('/ajax/branches', [\App\Http\Controllers\BranchDataController::class, 'branches'])->name('ajax.branches');

    // ── Ingredient Management ───────────────────────────────────────────
    Route::get('/ingredients', [IngredientController::class, 'index'])->name('ingredients');
    Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
    Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update');
    Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy'])->name('ingredients.destroy');
});

// ── Logout ───────────────────────────────────────────────────────────

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');
