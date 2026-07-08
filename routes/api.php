<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OverviewController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\ShiftLogController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\AuthOnboardingController;
use Illuminate\Support\Facades\Route;

// ── Public Auth Routes (JWT) ─────────────────────────────────────────

Route::post('/auth/admin-login', [AuthController::class, 'adminLogin'])->middleware('throttle:login');
Route::post('/auth/owner-login', [AuthController::class, 'ownerLogin'])->middleware('throttle:login'); // legacy alias
Route::post('/auth/staff-login', [AuthController::class, 'staffLogin'])->middleware('throttle:pin-login');

// Unified login endpoint — accepts pin+branch_id for staff/managers
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:pin-login')->name('api.login');

// ── Onboarding / Registration API routes ─────────────────────────────

Route::post('/auth/register/step-1', [AuthOnboardingController::class, 'apiRegisterStep1']);
Route::post('/auth/register/step-2', [AuthOnboardingController::class, 'apiRegisterStep2']);
Route::post('/auth/register/manager/step-2', [AuthOnboardingController::class, 'apiRegisterManagerStep2']);
Route::post('/auth/register/confirm', [AuthOnboardingController::class, 'apiRegisterConfirm']);

// ── Authenticated Routes (JWT guard) ─────────────────────────────────

Route::middleware('auth:api')->group(function () {
    // Any authenticated role.
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/me', [AuthController::class, 'me']); // legacy alias

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    // Operational endpoints — all 3 roles; branch-scoped inside the controller.
    Route::post('/transactions', [TransactionController::class, 'store']);

    Route::post('/receipts/scan', [ReceiptController::class, 'scan']);
    Route::get('/receipts', [ReceiptController::class, 'index']);
    Route::get('/receipts/summary', [ReceiptController::class, 'summary']);
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::get('/ingredients', [IngredientController::class, 'index']);
    Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show']);

    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);

    // Supervisory: manager (own branch) + super_admin (global).
    Route::middleware('role:super_admin,manager')->group(function () {
        Route::get('/staff', [StaffController::class, 'index']);
        Route::post('/staff', [StaffController::class, 'store']);
        Route::get('/staff/{staff}', [StaffController::class, 'show']);
        Route::put('/staff/{staff}', [StaffController::class, 'update']);
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);

        Route::get('/reports/sales', [ReportController::class, 'sales']);
        Route::get('/reports/inventory', [ReportController::class, 'inventory']);

        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);

        Route::get('/shifts', [ShiftController::class, 'index']);
        Route::get('/shifts/{shiftLog}', [ShiftController::class, 'show']);

        Route::get('/alerts', [AlertController::class, 'index']);
        Route::get('/alerts/{alert}', [AlertController::class, 'show']);
        Route::put('/alerts/{alert}/review', [AlertController::class, 'review']);
        Route::put('/alerts/{alert}/dismiss', [AlertController::class, 'dismiss']);

        Route::get('/branches/{branch}/stock', [OverviewController::class, 'branchStock']);
        Route::post('/stock', [StockController::class, 'store']);
        Route::post('/stock/restock', [StockController::class, 'restock']);
        Route::get('/stock/low-stock', [StockController::class, 'lowStock']);
        Route::get('/stock/{branchStock}/movements', [StockController::class, 'movements']);

        Route::get('/branches/{branch}', [BranchController::class, 'show']);

        // Dashboard analytics (Ally's endpoints — branch-filterable).
        Route::get('/dashboard/kpis', [DashboardController::class, 'kpis']);
        Route::get('/dashboard/sales-summary', [DashboardController::class, 'salesSummary']);
        Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);
    });

    // POS shift lifecycle — staff on the floor + manager covering a shift.
    Route::middleware('role:staff,manager')->group(function () {
        Route::post('/shifts/open', [ShiftController::class, 'open']);
        Route::post('/shifts/close', [ShiftController::class, 'close']);

        // Thin start/end aliases (Ally's shape) over the same shift_logs table.
        Route::post('/shift-logs/start', [ShiftLogController::class, 'start']);
        Route::post('/shift-logs/{shiftLog}/end', [ShiftLogController::class, 'end']);
    });

    // Global / structural — super_admin only.
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/dashboard', [OverviewController::class, 'overview']);

        Route::get('/branches', [BranchController::class, 'index']);
        Route::post('/branches', [BranchController::class, 'store']);
        Route::put('/branches/{branch}', [BranchController::class, 'update']);
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy']);

        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        Route::post('/ingredients', [IngredientController::class, 'store']);
        Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update']);
        Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy']);

        Route::post('/recipes', [RecipeController::class, 'store']);
        Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);
        Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);
    });
});
