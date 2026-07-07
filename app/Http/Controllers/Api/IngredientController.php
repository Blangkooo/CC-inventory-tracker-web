<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IngredientController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Ingredient::all());
    }

    public function show(Ingredient $ingredient): JsonResponse
    {
        return response()->json($ingredient);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(['g', 'kg', 'ml', 'l', 'pcs'])],
        ]);

        $ingredient = Ingredient::create($validated);

        return response()->json($ingredient, 201);
    }

    public function update(Request $request, Ingredient $ingredient): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'unit' => ['sometimes', 'required', Rule::in(['g', 'kg', 'ml', 'l', 'pcs'])],
        ]);

        $ingredient->update($validated);

        return response()->json($ingredient);
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        $ingredient->delete();

        return response()->json([
            'message' => 'Ingredient deleted successfully.',
        ]);
    }
}
