<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    use AuthorizesBranchAccess;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $staff = User::where('role', User::ROLE_STAFF)
            ->where('branch_id', $validated['branch_id'])
            ->get();

        return response()->json($staff);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'pin' => ['required', 'string', 'min:4', 'max:12'],
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $staff = User::create([
            'name' => $validated['name'],
            'pin' => $validated['pin'],
            'branch_id' => $validated['branch_id'],
            'role' => User::ROLE_STAFF,
        ]);

        return response()->json($staff, 201);
    }

    public function update(Request $request, User $staff): JsonResponse
    {
        abort_if($staff->role !== User::ROLE_STAFF, 404);

        $this->authorizeBranch($staff->branch_id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'pin' => ['sometimes', 'required', 'string', 'min:4', 'max:12'],
            'branch_id' => ['sometimes', 'required', 'exists:branches,id'],
        ]);

        if (isset($validated['branch_id'])) {
            $this->authorizeBranch((int) $validated['branch_id']);
        }

        $staff->update($validated);

        return response()->json($staff);
    }

    public function destroy(User $staff): JsonResponse
    {
        abort_if($staff->role !== User::ROLE_STAFF, 404);

        $this->authorizeBranch($staff->branch_id);

        $staff->delete();

        return response()->json([
            'message' => 'Staff deleted successfully.',
        ]);
    }
}
