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
        ]);

        $recipes = Recipe::with('ingredient')
            ->where('product_id', $validated['product_id'])
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
            'ingredient_id' => [
                'required',
                'exists:ingredients,id',
                Rule::unique('recipes')->where('product_id', $request->product_id),
            ],
            'quantity_required' => ['required', 'numeric', 'min:0.001'],
        ]);

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
