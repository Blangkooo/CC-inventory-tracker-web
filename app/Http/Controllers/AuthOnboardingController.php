<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthOnboardingController extends Controller
{
    // ── Views ──────────────────────────────────────────────────────────

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegisterStep1(): View
    {
        return view('auth.register-step-1');
    }

    public function showRegisterStep2(): View
    {
        // Role-based routing handled client-side JS in step-1
        // Fallback to owner step-2
        return view('auth.register-owner-step-2');
    }

    public function showRegisterStep3(): View
    {
        // Fallback to owner step-3
        return view('auth.register-owner-step-3');
    }

    public function showOwnerStep2(): View
    {
        return view('auth.register-owner-step-2');
    }

    public function showOwnerStep3(): View
    {
        return view('auth.register-owner-step-3');
    }

    public function showManagerStep2(): View
    {
        return view('auth.register-manager-step-2');
    }

    public function showManagerStep3(): View
    {
        return view('auth.register-manager-step-3');
    }

    // ── API Placeholder Handlers ──────────────────────────────────────

    public function apiLogin(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // If the request expects JSON (API client), return token-based response
        if ($request->expectsJson()) {
            $user = User::where('email', $credentials['email'])->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials.'], 401);
            }

            $user->tokens()->delete();
            $token = $user->createToken('api-token');

            return response()->json([
                'token' => $token->plainTextToken,
                'user' => $user,
            ]);
        }

        // Browser form submission — authenticate via session and redirect
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->route('login')
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    public function apiRegisterStep1(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'contact_number' => ['required', 'string'],
            'role' => ['required', 'string', 'in:owner,manager'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Step 1 personal information is valid.',
            'data' => $request->only(['full_name', 'email', 'contact_number', 'role']),
        ]);
    }

    public function apiRegisterStep2(Request $request): JsonResponse
    {
        $request->validate([
            'businesses' => ['required', 'array', 'min:1'],
            'businesses.*.business_name' => ['required', 'string'],
            'businesses.*.type_of_business' => ['required', 'string'],
            'businesses.*.business_registration' => ['required', 'string'],
            'businesses.*.business_permit' => ['required', 'string'],
            'businesses.*.location' => ['required', 'string'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Multi-location business payload received.',
            'data' => [
                'businesses' => $request->input('businesses'),
            ],
        ]);
    }

    public function apiRegisterManagerStep2(Request $request): JsonResponse
    {
        $request->validate([
            'business_name' => ['required', 'string'],
            'branch_location' => ['required', 'string'],
            'business_owner' => ['required', 'string'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Branch manager info received.',
            'data' => $request->only(['business_name', 'branch_location', 'business_owner']),
        ]);
    }

    public function apiRegisterConfirm(Request $request): JsonResponse
    {
        $request->validate([
            'owner_id' => ['required', 'string'],
            'permit_validity' => ['required', 'boolean'],
            'terms_accepted' => ['required', 'boolean'],
            'legal_papers_submitted' => ['required', 'boolean'],
            'legal_papers_secondary_submitted' => ['required', 'boolean'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Onboarding document trackers initialized successfully.',
            'data' => [
                'trackers' => [
                    'owner_id' => 'verified',
                    'permit_validity' => $request->input('permit_validity') ? 'valid' : 'pending',
                    'terms_of_service' => $request->input('terms_accepted') ? 'accepted' : 'pending',
                    'legal_papers' => $request->input('legal_papers_submitted') ? 'submitted' : 'pending',
                    'legal_papers_secondary' => $request->input('legal_papers_secondary_submitted') ? 'submitted' : 'pending',
                ],
            ],
        ]);
    }
}
