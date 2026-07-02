<?php

use App\Http\Controllers\AlertsController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RecipesController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/recipes', [RecipesController::class, 'index'])->name('recipes');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/branches', [BranchesController::class, 'index'])->name('branches');
    Route::get('/alerts', [AlertsController::class, 'index'])->name('alerts');
});

// Temporary test route — remove before shipping.
Route::get('/test-data', function () {
    return [
        'branches' => App\Models\Branch::all(),
        'products_with_ingredients' => App\Models\Product::with('ingredients')->get(),
        'branch_stock' => App\Models\BranchStock::with('ingredient', 'branch')->get(),
    ];
});
