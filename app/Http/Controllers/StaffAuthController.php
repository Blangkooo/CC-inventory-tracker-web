<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffAuthController extends Controller
{
    private const PIN_ROLES = [User::ROLE_STAFF, User::ROLE_MANAGER];

    public function showLogin(): View
    {
        return view('staff.login', [
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'pin' => ['required', 'string'],
        ]);

        $user = User::where('branch_id', $validated['branch_id'])
            ->whereIn('role', self::PIN_ROLES)
            ->get()
            ->first(fn (User $candidate) => $candidate->pin && Hash::check($validated['pin'], $candidate->pin));

        if (! $user) {
            return back()
                ->withInput(['branch_id' => $validated['branch_id']])
                ->withErrors(['pin' => 'Invalid Worker ID for this branch.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('staff.dashboard');
    }
}
