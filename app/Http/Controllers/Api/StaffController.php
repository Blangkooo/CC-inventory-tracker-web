<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffStoreRequest;
use App\Http\Requests\StaffUpdateRequest;
use App\Http\Resources\StaffResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Roles that are managed via the Staff CRUD endpoints.
     */
    private const MANAGED_ROLES = ['staff', 'manager'];

    public function index(): AnonymousResourceCollection
    {
        $staff = User::whereIn('role', self::MANAGED_ROLES)
            ->with('branch')
            ->latest()
            ->paginate(20);

        return StaffResource::collection($staff);
    }

    public function store(StaffStoreRequest $request): JsonResponse
    {
        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'pin' => Hash::make($request->pin),
            'role' => 'manager',
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

        return response()->json(new StaffResource($staff->load('branch')));
    }

    public function update(StaffUpdateRequest $request, User $staff): JsonResponse
    {
        if (! in_array($staff->role, self::MANAGED_ROLES)) {
            return response()->json(['message' => 'Staff member not found.'], 404);
        }

        $data = $request->only(['name', 'email', 'branch_id']);

        if ($request->filled('pin')) {
            $data['pin'] = Hash::make($request->pin);
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

        $staff->tokens()->delete();
        $staff->delete();

        return response()->json([
            'message' => 'Staff member deleted successfully.',
        ]);
    }
}
