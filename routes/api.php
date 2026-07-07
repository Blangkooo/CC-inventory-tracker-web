<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ShiftLogController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products',              [ProductController::class,     'index']);
    Route::get('/recipes',               [RecipeController::class,      'index']);
    Route::post('/transactions',         [TransactionController::class, 'store']);

    Route::post('/shift-logs/start',     [ShiftLogController::class,    'start']);
    Route::post('/shift-logs/{id}/end',  [ShiftLogController::class,    'end']);

    Route::get('/alerts',                [AlertController::class,       'index']);
    Route::patch('/alerts/{id}',         [AlertController::class,       'update']);

    Route::get('/dashboard/kpis',        [DashboardController::class,   'kpis']);
    Route::get('/dashboard/sales-summary', [DashboardController::class, 'salesSummary']);
    Route::get('/dashboard/top-products',  [DashboardController::class, 'topProducts']);
});
