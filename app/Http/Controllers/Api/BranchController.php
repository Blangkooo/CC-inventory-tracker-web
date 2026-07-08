<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchFormRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $branches = Branch::withCount('users')->latest()->get();

        return BranchResource::collection($branches);
    }

    public function store(BranchFormRequest $request): JsonResponse
    {
        $branch = Branch::create($request->only(['name', 'location', 'status']));

        return response()->json([
            'message' => 'Branch created successfully.',
            'branch' => new BranchResource($branch),
        ], 201);
    }

    public function show(Branch $branch): BranchResource
    {
        return new BranchResource($branch->loadCount('users'));
    }

    public function update(BranchFormRequest $request, Branch $branch): JsonResponse
    {
        $branch->update($request->only(['name', 'location', 'status']));

        return response()->json([
            'message' => 'Branch updated successfully.',
            'branch' => new BranchResource($branch->fresh()->loadCount('users')),
        ]);
    }
}
