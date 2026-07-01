<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function ownerLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])
            ->where('role', 'owner')
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('owner-token');

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }

    public function staffLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $user = User::where('branch_id', $validated['branch_id'])
            ->where('role', 'staff')
            ->get()
            ->first(fn (User $candidate) => $candidate->pin && Hash::check($validated['pin'], $candidate->pin));

        if (! $user) {
            return response()->json([
                'message' => 'Invalid pin or branch.',
            ], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('staff-token');

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
            'branch' => $user->branch,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('branch'));
    }
}
