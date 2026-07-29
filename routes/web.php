<?php

use App\Http\Controllers\AlertsController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthOnboardingController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HiringController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LegalPapersController;
use App\Http\Controllers\NoticesController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\RecipesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SalaryController;
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

    // ── Static Pages ──────────────────────────────────────────────────
    Route::get('/help-center', fn () => view('pages.help-center'))->name('help-center');
    Route::get('/about', fn () => view('pages.about'))->name('about');

    // ── Mail/Messages ─────────────────────────────────────────────────
    Route::get('/mail', [NoticesController::class, 'index'])->name('notices.index');
    Route::post('/mail', [NoticesController::class, 'store'])->name('notices.store');
    Route::delete('/mail/{notice}', [NoticesController::class, 'destroy'])->name('notices.destroy');

    /*
     * Everything below is closed to staff. These screens read across the whole
     * branch (pay rates, expense ledgers, revenue) and the controllers treat
     * "not a manager" as "may see every branch" — so without this gate a staff
     * account would see more than a manager, not less.
     */
    Route::middleware('role:super_admin,manager')->group(function () {
        // ── Calendar ──────────────────────────────────────────────────
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.events.store');
        Route::put('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.events.update');
        Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.events.destroy');

        // ── Salary ────────────────────────────────────────────────────
        Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
        Route::post('/salary/payslips', [SalaryController::class, 'generate'])->name('salary.generate');
        Route::get('/salary/payslips/{payslip}', [SalaryController::class, 'show'])->name('salary.show');
        Route::post('/salary/payslips/{payslip}/mark-paid', [SalaryController::class, 'markPaid'])->name('salary.mark-paid');

        // ── Payments ──────────────────────────────────────────────────
        Route::get('/payments', [PaymentsController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store');
        Route::put('/payments/{payment}', [PaymentsController::class, 'update'])->name('payments.update');
        Route::post('/payments/{payment}/mark-paid', [PaymentsController::class, 'markPaid'])->name('payments.mark-paid');
        Route::delete('/payments/{payment}', [PaymentsController::class, 'destroy'])->name('payments.destroy');

        // ── Hiring ────────────────────────────────────────────────────
        Route::get('/hiring', [HiringController::class, 'index'])->name('hiring.index');
        Route::post('/hiring/openings', [HiringController::class, 'storeOpening'])->name('hiring.openings.store');
        Route::put('/hiring/openings/{opening}', [HiringController::class, 'updateOpening'])->name('hiring.openings.update');
        Route::delete('/hiring/openings/{opening}', [HiringController::class, 'destroyOpening'])->name('hiring.openings.destroy');
        Route::post('/hiring/openings/{opening}/applicants', [HiringController::class, 'storeApplicant'])->name('hiring.applicants.store');
        Route::put('/hiring/applicants/{applicant}/status', [HiringController::class, 'updateApplicantStatus'])->name('hiring.applicants.status');
        Route::delete('/hiring/applicants/{applicant}', [HiringController::class, 'destroyApplicant'])->name('hiring.applicants.destroy');

        // ── Legal Papers ──────────────────────────────────────────────
        Route::get('/legal-papers', [LegalPapersController::class, 'index'])->name('legal-papers.index');
        Route::get('/legal-papers/{document}/download', [LegalPapersController::class, 'download'])->name('legal-papers.download');
    });

    /*
     * Owner-only: setting what a worker is paid, and adding or removing the
     * company's legal records, are ownership decisions rather than day-to-day
     * branch management.
     */
    Route::middleware('role:super_admin')->group(function () {
        Route::put('/salary/workers/{user}/rate', [SalaryController::class, 'updateRate'])->name('salary.rate.update');

        Route::post('/legal-papers', [LegalPapersController::class, 'store'])->name('legal-papers.store');
        Route::put('/legal-papers/{document}', [LegalPapersController::class, 'update'])->name('legal-papers.update');
        Route::delete('/legal-papers/{document}', [LegalPapersController::class, 'destroy'])->name('legal-papers.destroy');
    });

    // ── Analytics & Reports (revenue figures — not for staff) ───────────
    Route::middleware('role:super_admin,manager')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/{type}', [ReportsController::class, 'show'])
            ->whereIn('type', ['sales', 'inventory', 'payroll', 'purchases'])
            ->name('reports.show');
        Route::get('/reports/{type}/export', [ReportsController::class, 'export'])
            ->whereIn('type', ['sales', 'inventory', 'payroll', 'purchases'])
            ->name('reports.export');
        Route::get('/reports/{type}/pdf', [ReportsController::class, 'exportPdf'])
            ->whereIn('type', ['sales', 'inventory', 'payroll', 'purchases'])
            ->name('reports.pdf');
    });

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

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

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
