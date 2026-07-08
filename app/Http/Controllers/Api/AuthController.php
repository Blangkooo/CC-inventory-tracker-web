<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Roles that can authenticate via PIN + branch_id (per Ally's unified
     * login: managers covering a POS shift may also clock in by PIN).
     */
    private const PIN_ROLES = [User::ROLE_STAFF, User::ROLE_MANAGER];

    /**
     * Email + password login for super admins and managers. Returns a JWT.
     */
    public function adminLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])
            ->whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_MANAGER])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = auth('api')->login($user);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Backwards-compatible alias — the team's older clients call /auth/owner-login.
     */
    public function ownerLogin(Request $request): JsonResponse
    {
        return $this->adminLogin($request);
    }

    /**
     * PIN + branch login for on-site roles. Returns a JWT.
     */
    public function staffLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $user = User::where('branch_id', $validated['branch_id'])
            ->whereIn('role', self::PIN_ROLES)
            ->get()
            ->first(fn (User $candidate) => $candidate->pin && Hash::check($validated['pin'], $candidate->pin));

        if (! $user) {
            return response()->json([
                'message' => 'Invalid pin or branch.',
            ], 401);
        }

        $token = auth('api')->login($user);

        return response()->json([
            'token' => $token,
            'user' => $user,
            'branch' => $user->branch,
        ]);
    }

    /**
     * Unified login endpoint (Ally's /api/login) — same as staffLogin.
     */
    public function login(Request $request): JsonResponse
    {
        return $this->staffLogin($request);
    }

    public function logout(Request $request): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('branch'));
    }
}
