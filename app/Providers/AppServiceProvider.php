<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Rate Limiting for Login Endpoints ────────────────────────
        // Strict 5 attempts per minute per IP + email/pin combo
        RateLimiter::for('login', function (Request $request) {
            $key = 'login:' . $request->ip() . '|' . ($request->input('email') ?? $request->input('pin') ?? 'unknown');

            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again in a minute.',
                ], 429);
            });
        });

        // Separate limiter for PIN-based staff login
        RateLimiter::for('pin-login', function (Request $request) {
            $key = 'pin-login:' . $request->ip() . '|' . ($request->input('pin') ?? 'unknown') . '|' . ($request->input('branch_id') ?? 'unknown');

            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Too many PIN attempts. Please try again in a minute.',
                ], 429);
            });
        });
    }
}
