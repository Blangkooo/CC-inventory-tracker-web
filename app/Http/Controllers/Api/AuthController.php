<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'pin'       => 'required|string',
        ]);

        $user = \App\Models\User::where('branch_id', $request->branch_id)->get()
            ->first(fn($u) => \Illuminate\Support\Facades\Hash::check($request->pin, $u->pin_hash));

        if (! $user) {
            return response()->json(['message' => 'Invalid PIN.'], 401);
        }

        $token = $user->createToken('pos-device')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role],
        ]);
    }
}
