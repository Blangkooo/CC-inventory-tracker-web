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
    Route::get('/recipes', [RecipesController::class, 'index'])->name('recipes');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/branches', [BranchesController::class, 'index'])->name('branches');
    Route::get('/branches/{branch}', [BranchesController::class, 'show'])->whereNumber('branch')->name('branches.show');
    Route::get('/alerts', [AlertsController::class, 'index'])->name('alerts');

    // ── Business Pages (static structural placeholders) ───────────────
    Route::get('/business/recipes', function () {
        return view('business.recipes');
    })->name('business.recipes');

    Route::get('/business/summary', function () {
        return view('business.summary');
    })->name('business.summary');

    // ── Settings Drawer Page ──────────────────────────────────────────
    Route::get('/logistics', function () {
        return view('logistics.index');
    })->name('logistics');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings');
});

// ── Logout ───────────────────────────────────────────────────────────

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');
