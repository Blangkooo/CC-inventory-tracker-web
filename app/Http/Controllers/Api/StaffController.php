<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\StaffStoreRequest;
use App\Http\Requests\StaffUpdateRequest;
use App\Http\Resources\StaffResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StaffController extends Controller
{
    use AuthorizesBranchAccess;

    /**
     * Roles that are managed via the Staff CRUD endpoints.
     */
    private const MANAGED_ROLES = [User::ROLE_STAFF, User::ROLE_MANAGER];

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['sometimes', 'exists:branches,id'],
        ]);

        if (isset($validated['branch_id'])) {
            $this->authorizeBranch((int) $validated['branch_id']);
        } elseif (! $request->user()->isSuperAdmin()) {
            // Only super admins may list staff across all branches.
            abort(403, 'Forbidden: pass your own branch_id.');
        }

        $staff = User::whereIn('role', self::MANAGED_ROLES)
            ->when(isset($validated['branch_id']), fn ($q) => $q->where('branch_id', $validated['branch_id']))
            ->with('branch')
            ->latest()
            ->paginate(20);

        return StaffResource::collection($staff);
    }

    public function store(StaffStoreRequest $request): JsonResponse
    {
        $this->authorizeBranch((int) $request->branch_id);

        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email,
            // Plain value on purpose — the User model's 'hashed' cast hashes it once.
            'pin' => $request->pin,
            'role' => $request->input('role', User::ROLE_STAFF),
            'branch_id' => $request->branch_id,
        ]);

        return response()->json([
            'message' => 'Staff member created successfully.',
            'staff' => new StaffResource($staff->load('branch')),
        ], 201);
    }

    public function show(User $staff): JsonResponse
    {
        if (! in_array($staff->role, self::MANAGED_ROLES)) {
            return response()->json(['message' => 'Staff member not found.'], 404);
        }

        $this->authorizeBranch($staff->branch_id);

        return response()->json(new StaffResource($staff->load('branch')));
    }

    public function update(StaffUpdateRequest $request, User $staff): JsonResponse
    {
        if (! in_array($staff->role, self::MANAGED_ROLES)) {
            return response()->json(['message' => 'Staff member not found.'], 404);
        }

        $this->authorizeBranch($staff->branch_id);

        $data = $request->only(['name', 'email', 'branch_id']);

        if (isset($data['branch_id'])) {
            $this->authorizeBranch((int) $data['branch_id']);
        }

        if ($request->filled('pin')) {
            $data['pin'] = $request->pin; // hashed by the model cast
        }

        $staff->update($data);

        return response()->json([
            'message' => 'Staff member updated successfully.',
            'staff' => new StaffResource($staff->fresh()->load('branch')),
        ]);
    }

    public function destroy(User $staff): JsonResponse
    {
        if (! in_array($staff->role, self::MANAGED_ROLES)) {
            return response()->json(['message' => 'Staff member not found.'], 404);
        }

        $this->authorizeBranch($staff->branch_id);

        $staff->delete();

        return response()->json([
            'message' => 'Staff member deleted successfully.',
        ]);
    }
}
