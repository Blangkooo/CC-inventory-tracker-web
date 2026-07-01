<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/owner-login', [AuthController::class, 'ownerLogin']);
Route::post('/auth/staff-login', [AuthController::class, 'staffLogin']);

// TEMPORARY: no-auth route for local testing of the transaction flow. Remove before production.
Route::post('/test-transaction', [TransactionController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::post('/transactions', [TransactionController::class, 'store']);

    Route::get('/dashboard', function () {
        return response()->json([
            'total_branches' => App\Models\Branch::count(),
            'total_products' => App\Models\Product::count(),
            'total_ingredients' => App\Models\Ingredient::count(),
            'pending_alerts' => App\Models\DiscrepancyAlert::where('status', 'pending')->count(),
        ]);
    });

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
