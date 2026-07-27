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

// ── Staff / Worker PIN Login (Branch + Worker ID) ────────────────────
Route::get('/staff/login', [\App\Http\Controllers\StaffAuthController::class, 'showLogin'])->name('staff.login');
Route::post('/staff/login', [\App\Http\Controllers\StaffAuthController::class, 'login'])->name('staff.login.post');

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

        if (Auth::user()->isStaff()) {
            return redirect()->intended(route('staff.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    return back()
        ->withInput(request()->only('email'))
        ->withErrors(['email' => 'These credentials do not match our records.']);
})->name('login.post');

// ── Authenticated Pages ──────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Staff Dashboard (self-service: clock in/out, verify stock) ─────
    Route::get('/staff/dashboard', [\App\Http\Controllers\StaffDashboardController::class, 'index'])
        ->name('staff.dashboard');
    Route::post('/staff/clock-in', [\App\Http\Controllers\StaffDashboardController::class, 'clockIn'])
        ->name('staff.clock-in');
    Route::post('/staff/clock-out', [\App\Http\Controllers\StaffDashboardController::class, 'clockOut'])
        ->name('staff.clock-out');
    Route::post('/staff/verify-stock', [\App\Http\Controllers\StaffDashboardController::class, 'verifyStock'])
        ->name('staff.verify-stock');
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
        $user = auth()->user();

        // Authorization: staff cannot access the workers management page.
        if (! $user->isSuperAdmin() && ! $user->isManager()) {
            abort(403, 'You do not have permission to manage workers.');
        }

        $isManager = $user->isManager();

        $workers = \App\Models\User::whereIn('role', $isManager
                ? [\App\Models\User::ROLE_STAFF]           // managers see only staff
                : [\App\Models\User::ROLE_STAFF, \App\Models\User::ROLE_MANAGER]  // super_admins see all
            )
            ->with('branch', 'profile')
            ->when($isManager, fn ($q) => $q->where('branch_id', $user->branch_id))
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
        ]);
    })->name('business.workers');

    // ── Recipe CRUD (AJAX endpoints — session auth) ────────────────────
    Route::get('/business/recipes/product/{product}/data', [\App\Http\Controllers\BusinessRecipesController::class, 'getProductData'])
        ->name('business.recipes.product.data');
    Route::get('/business/recipes/product/{product}/profile', [\App\Http\Controllers\BusinessRecipesController::class, 'ingredientProfile'])
        ->name('business.recipes.product.profile');
    Route::put('/business/recipes/product/{product}', [\App\Http\Controllers\BusinessRecipesController::class, 'updateProduct'])
        ->name('business.recipes.product.update');
    Route::post('/business/recipes/product/{product}/ingredient', [\App\Http\Controllers\BusinessRecipesController::class, 'addIngredient'])
        ->name('business.recipes.ingredient.add');
    Route::put('/business/recipes/ingredient/{recipe}', [\App\Http\Controllers\BusinessRecipesController::class, 'updateIngredient'])
        ->name('business.recipes.ingredient.update');
    Route::post('/business/recipes/ingredient/{recipe}/delete', [\App\Http\Controllers\BusinessRecipesController::class, 'removeIngredient'])
        ->name('business.recipes.ingredient.remove');
    Route::post('/business/recipes/product/{product}/delete', [\App\Http\Controllers\BusinessRecipesController::class, 'destroyProduct'])
        ->name('business.recipes.product.delete');

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

    // ── Pricing Simulator ─────────────────────────────────────────────
    Route::get('/pricing', [\App\Http\Controllers\PricingController::class, 'index'])
        ->name('pricing.index');
    Route::get('/pricing/simulate', [\App\Http\Controllers\PricingController::class, 'simulate'])
        ->name('pricing.simulate');

    // ── Supplier Directory ──────────────────────────────────────────────
    Route::get('/suppliers', [\App\Http\Controllers\SupplierController::class, 'index'])
        ->name('suppliers.index');
    Route::post('/suppliers', [\App\Http\Controllers\SupplierController::class, 'store'])
        ->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'show'])
        ->name('suppliers.show');
    Route::put('/suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'update'])
        ->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'destroy'])
        ->name('suppliers.destroy');
    Route::post('/suppliers/{supplier}/ingredients', [\App\Http\Controllers\SupplierController::class, 'linkIngredient'])
        ->name('suppliers.link-ingredient');
    Route::delete('/suppliers/{supplier}/ingredients/{ingredient}', [\App\Http\Controllers\SupplierController::class, 'unlinkIngredient'])
        ->name('suppliers.unlink-ingredient');
    Route::post('/suppliers/{supplier}/purchases', [\App\Http\Controllers\SupplierController::class, 'addPurchase'])
        ->name('suppliers.add-purchase');
});

// ── Logout ───────────────────────────────────────────────────────────

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');
