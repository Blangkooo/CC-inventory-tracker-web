<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    use AuthorizesBranchAccess;

    public function index(): JsonResponse
    {
        return response()->json(Branch::all());
    }

    public function show(Branch $branch): JsonResponse
    {
        $this->authorizeBranch($branch->id);

        return response()->json($branch);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $branch = Branch::create($validated);

        return response()->json($branch, 201);
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $branch->update($validated);

        return response()->json($branch);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $branch->delete();

        return response()->json([
            'message' => 'Branch deleted successfully.',
        ]);
    }
}
