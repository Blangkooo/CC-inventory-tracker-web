<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecipeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'size' => ['sometimes', Rule::in([Recipe::SIZE_REGULAR, Recipe::SIZE_LARGE])],
        ]);

        $recipes = Recipe::with('ingredient')
            ->where('product_id', $validated['product_id'])
            ->when(isset($validated['size']), fn ($q) => $q->where('size', $validated['size']))
            ->get();

        return response()->json($recipes);
    }

    public function show(Recipe $recipe): JsonResponse
    {
        return response()->json($recipe->load('product', 'ingredient'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'size' => ['sometimes', Rule::in([Recipe::SIZE_REGULAR, Recipe::SIZE_LARGE])],
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'quantity_required' => ['required', 'numeric', 'min:0.001'],
        ]);

        $validated['size'] ??= Recipe::SIZE_REGULAR;

        $exists = Recipe::where('product_id', $validated['product_id'])
            ->where('ingredient_id', $validated['ingredient_id'])
            ->where('size', $validated['size'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This ingredient is already in the recipe for this size.',
                'errors' => ['ingredient_id' => ['This ingredient is already in the recipe for this size.']],
            ], 422);
        }

        $recipe = Recipe::create($validated);

        return response()->json($recipe->load('ingredient'), 201);
    }

    public function update(Request $request, Recipe $recipe): JsonResponse
    {
        $validated = $request->validate([
            'quantity_required' => ['required', 'numeric', 'min:0.001'],
        ]);

        $recipe->update($validated);

        return response()->json($recipe->load('ingredient'));
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        $recipe->delete();

        return response()->json([
            'message' => 'Recipe entry deleted successfully.',
        ]);
    }
}
