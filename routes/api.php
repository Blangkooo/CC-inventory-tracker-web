<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ShiftLogController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products',       [ProductController::class,     'index']);
    Route::get('/recipes',        [RecipeController::class,      'index']);
    Route::post('/transactions',  [TransactionController::class, 'store']);
    Route::post('/shift-logs',    [ShiftLogController::class,    'store']);
});
