<?php

use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthOnboardingController;
use Illuminate\Support\Facades\Route;

// ── Public Auth Routes ───────────────────────────────────────────────

Route::post('/auth/owner-login', [AuthController::class, 'ownerLogin'])->middleware('throttle:login');
Route::post('/auth/staff-login', [AuthController::class, 'staffLogin'])->middleware('throttle:pin-login');

// Unified login endpoint — accepts pin+branch_id for staff
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:pin-login')->name('api.login');

// ── Onboarding / Registration API routes ─────────────────────────────

Route::post('/auth/login', [AuthOnboardingController::class, 'apiLogin'])->middleware('web');
Route::post('/auth/register/step-1', [AuthOnboardingController::class, 'apiRegisterStep1']);
Route::post('/auth/register/step-2', [AuthOnboardingController::class, 'apiRegisterStep2']);
Route::post('/auth/register/manager/step-2', [AuthOnboardingController::class, 'apiRegisterManagerStep2']);
Route::post('/auth/register/confirm', [AuthOnboardingController::class, 'apiRegisterConfirm']);

// TEMPORARY: no-auth route for local testing. Remove before production.
Route::post('/test-transaction', [TransactionController::class, 'store']);
Route::post('/test-receipt-scan', [ReceiptController::class, 'scan']);

// ── Authenticated Sanctum Routes ─────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ──────────────────────────────────────────────────────────
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/me', [AuthController::class, 'me']);

    // ── Staff Management ──────────────────────────────────────────────
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::get('/staff/{staff}', [StaffController::class, 'show']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);

    // ── Branches Management ───────────────────────────────────────────
    Route::get('/branches', [BranchController::class, 'index']);
    Route::post('/branches', [BranchController::class, 'store']);
    Route::get('/branches/{branch}', [BranchController::class, 'show']);
    Route::put('/branches/{branch}', [BranchController::class, 'update']);

    // ── Transactions ──────────────────────────────────────────────────
    Route::post('/transactions', [TransactionController::class, 'store']);

    // ── Receipts ──────────────────────────────────────────────────────
    Route::post('/receipts/scan', [ReceiptController::class, 'scan']);
    Route::get('/receipts', [ReceiptController::class, 'index']);
    Route::get('/receipts/summary', [ReceiptController::class, 'summary']);

    // ── Dashboard Meta ────────────────────────────────────────────────
    Route::get('/dashboard', function () {
        return response()->json([
            'total_branches' => App\Models\Branch::count(),
            'total_products' => App\Models\Product::count(),
            'total_ingredients' => App\Models\Ingredient::count(),
            'pending_alerts' => App\Models\DiscrepancyAlert::where('status', 'pending')->count(),
        ]);
    });

    // ── Branch Stock ──────────────────────────────────────────────────
    Route::get('/branches/{branch}/stock', function (App\Models\Branch $branch) {
        return response()->json(
            App\Models\BranchStock::with('ingredient')
                ->where('branch_id', $branch->id)
                ->get()
                ->map(fn ($s) => [
                    'ingredient' => $s->ingredient->name,
                    'unit' => $s->ingredient->unit,
                    'current_quantity' => $s->current_quantity,
                    'min_threshold' => $s->min_threshold,
                    'status' => $s->stock_status,
                ])
        );
    });
});
