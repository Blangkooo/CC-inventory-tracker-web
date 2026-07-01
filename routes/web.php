<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});

// Temporary test route — remove before shipping.
Route::get('/test-data', function () {
    return [
        'branches' => App\Models\Branch::all(),
        'products_with_ingredients' => App\Models\Product::with('ingredients')->get(),
        'branch_stock' => App\Models\BranchStock::with('ingredient', 'branch')->get(),
    ];
});
