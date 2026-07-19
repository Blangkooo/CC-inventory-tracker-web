<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkersController extends Controller
{
    private const MANAGED_ROLES = [User::ROLE_STAFF, User::ROLE_MANAGER];

    /**
     * Profile fields that belong in the worker_profiles table.
     */
    private const PROFILE_FIELDS = [
        'phone', 'address', 'birthday',
        'senior_high', 'college',
        'partner_contact', 'mother_contact',
        'skills', 'notes',
        'work_schedule', 'performance_metrics',
        'rating',
    ];

    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser->isSuperAdmin() && ! $authUser->isManager()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'unique:users,email'],
            'pin'       => ['required', 'string', 'min:4', 'max:8'],
            'branch_id' => ['required', 'exists:branches,id'],
            'role'      => ['sometimes', 'in:staff,manager'],
        ]);

        if ($authUser->isManager() && $authUser->branch_id !== (int) $validated['branch_id']) {
            return response()->json(['message' => 'You can only add workers to your own branch.'], 403);
        }

        $staff = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'] ?? null,
            'pin'       => $validated['pin'],
            'role'      => $validated['role'] ?? User::ROLE_STAFF,
            'branch_id' => $validated['branch_id'],
        ]);

        // Auto-create an empty profile so the relationship always resolves
        $staff->profile()->create([]);

        return response()->json([
            'message' => 'Worker added successfully.',
            'staff'   => [
                'id'         => $staff->id,
                'name'       => $staff->name,
                'email'      => $staff->email,
                'role'       => $staff->role,
                'branch_id'  => $staff->branch_id,
                'created_at' => $staff->created_at,
            ],
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if (! in_array($user->role, self::MANAGED_ROLES)) {
            return response()->json(['message' => 'Worker not found.'], 404);
        }

        $authUser = $request->user();

        if (! $authUser->isSuperAdmin() && ! $authUser->isManager()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($authUser->isManager() && $authUser->branch_id !== $user->branch_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'pin'       => ['sometimes', 'string', 'min:4', 'max:8'],
            'branch_id' => ['sometimes', 'exists:branches,id'],
            'role'      => ['sometimes', 'in:staff,manager'],
        ]);

        if (isset($validated['branch_id'])
            && $authUser->isManager()
            && $authUser->branch_id !== (int) $validated['branch_id']) {
            return response()->json(['message' => 'You can only assign workers to your own branch.'], 403);
        }

        $data = [];
        foreach (['name', 'email', 'branch_id', 'role'] as $field) {
            if (isset($validated[$field])) {
                $data[$field] = $validated[$field];
            }
        }
        if (isset($validated['pin'])) {
            $data['pin'] = $validated['pin'];
        }

        $user->update($data);

        return response()->json([
            'message' => 'Worker updated successfully.',
            'staff'   => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'branch_id' => $user->branch_id,
            ],
        ]);
    }

    /**
     * Update a worker's profile fields (phone, address, skills, etc.).
     */
    public function updateProfile(Request $request, User $user): JsonResponse
    {
        if (! in_array($user->role, self::MANAGED_ROLES)) {
            return response()->json(['message' => 'Worker not found.'], 404);
        }

        $authUser = $request->user();

        if (! $authUser->isSuperAdmin() && ! $authUser->isManager()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($authUser->isManager() && $authUser->branch_id !== $user->branch_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $rules = [];
        foreach (self::PROFILE_FIELDS as $field) {
            $rules[$field] = ['sometimes', 'nullable'];
        }
        // JSON fields should validate as arrays when provided
        $rules['skills'] = ['sometimes', 'nullable', 'array'];
        $rules['skills.*'] = ['string', 'max:100'];
        $rules['work_schedule'] = ['sometimes', 'nullable', 'array'];
        $rules['performance_metrics'] = ['sometimes', 'nullable', 'array'];
        $rules['performance_metrics.*'] = ['string', 'max:200'];
        $rules['rating'] = ['sometimes', 'nullable', 'numeric', 'min:0', 'max:5'];

        $validated = $request->validate($rules);

        // Get or auto-create the profile
        $profile = $user->profile()->firstOrCreate([]);

        $profileData = [];
        foreach (self::PROFILE_FIELDS as $field) {
            if ($request->filled($field)) {
                $profileData[$field] = $validated[$field];
            }
        }

        $profile->update($profileData);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => $profile->fresh(),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if (! in_array($user->role, self::MANAGED_ROLES)) {
            return response()->json(['message' => 'Worker not found.'], 404);
        }

        $authUser = $request->user();

        if (! $authUser->isSuperAdmin() && ! $authUser->isManager()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($authUser->isManager() && $authUser->branch_id !== $user->branch_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($user->id === $authUser->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Worker deleted successfully.']);
    }
}
