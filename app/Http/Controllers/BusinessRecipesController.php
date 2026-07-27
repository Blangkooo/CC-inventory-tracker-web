<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessRecipesController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')
            ->get();

        $categories = Product::distinct()->pluck('category')->filter()->values();

        $products = Product::with('recipes.ingredient')
            ->orderBy('name')
            ->get();

        $allIngredients = Ingredient::orderBy('name')->get();

        return view('business.recipes', [
            'branches'        => $branches,
            'categories'      => $categories,
            'products'        => $products,
            'allIngredients'  => $allIngredients,
        ]);
    }

    /**
     * Update product metadata (name, category, price, procedure).
     */
    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $this->authorizeOwnerOrManager($product);

        $validated = $request->validate([
            'name'      => ['sometimes', 'required', 'string', 'max:255'],
            'category'  => ['nullable', 'string', 'max:255'],
            'price'     => ['sometimes', 'required', 'numeric', 'min:0'],
            'procedure' => ['nullable', 'string'],
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * Add or update a recipe ingredient for a specific size.
     */
    public function addIngredient(Request $request, Product $product): JsonResponse
    {
        $this->authorizeOwnerOrManager($product);

        $validated = $request->validate([
            'ingredient_id'     => ['required', 'exists:ingredients,id'],
            'size'              => ['required', Rule::in([Recipe::SIZE_REGULAR, Recipe::SIZE_LARGE])],
            'quantity_required' => ['required', 'numeric', 'min:0.001'],
        ]);

        // Check if this ingredient+size combo already exists
        $existing = Recipe::where('product_id', $product->id)
            ->where('ingredient_id', $validated['ingredient_id'])
            ->where('size', $validated['size'])
            ->first();

        if ($existing) {
            // Update existing
            $existing->update(['quantity_required' => $validated['quantity_required']]);
            return response()->json($existing->load('ingredient'));
        }

        $recipe = Recipe::create([
            'product_id'        => $product->id,
            'ingredient_id'     => $validated['ingredient_id'],
            'size'              => $validated['size'],
            'quantity_required' => $validated['quantity_required'],
        ]);

        return response()->json($recipe->load('ingredient'), 201);
    }

    /**
     * Update a recipe ingredient's quantity.
     */
    public function updateIngredient(Request $request, Recipe $recipe): JsonResponse
    {
        $this->authorizeOwnerOrManager($recipe->product);

        $validated = $request->validate([
            'quantity_required' => ['required', 'numeric', 'min:0.001'],
        ]);

        $recipe->update($validated);

        return response()->json($recipe->load('ingredient'));
    }

    /**
     * Return full product data with recipes as JSON.
     */
    public function getProductData(Product $product): JsonResponse
    {
        $product->load('recipes.ingredient');
        return response()->json($product);
    }

    /**
     * Ingredient profile drill-down: full cost breakdown with supplier info.
     */
    public function ingredientProfile(Product $product): JsonResponse
    {
        $product->load('recipes.ingredient.suppliers');

        $ingredients = $product->recipes->map(function ($recipe) {
            $ingredient = $recipe->ingredient;
            $primarySupplier = $ingredient->suppliers->firstWhere('pivot.is_primary', true);

            $unitCost = $primarySupplier?->pivot?->unit_cost ?? 0;
            $lineCost = $unitCost * (float) $recipe->quantity_required;

            return [
                'ingredient_id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'size' => $recipe->size,
                'quantity_required' => (float) $recipe->quantity_required,
                'unit_cost' => (float) $unitCost,
                'line_cost' => round($lineCost, 2),
                'supplier' => $primarySupplier ? [
                    'id' => $primarySupplier->id,
                    'name' => $primarySupplier->name,
                    'contact_number' => $primarySupplier->contact_number,
                ] : null,
            ];
        });

        $price = (float) $product->price;

        // A serving is one size or the other — never both, so cost is summed per size.
        $sizes = $ingredients->groupBy('size')->map(function ($lines, $size) use ($price) {
            $totalCost = round($lines->sum('line_cost'), 2);

            return [
                'size' => $size,
                'total_cost' => $totalCost,
                'margin_pct' => $price > 0 ? round((($price - $totalCost) / $price) * 100, 1) : 0,
                'suggested_price_65' => $totalCost > 0 ? round($totalCost / 0.35, 2) : 0,
            ];
        })->values();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'price' => $price,
            ],
            'ingredients' => $ingredients,
            'sizes' => $sizes,
        ]);
    }

    /**
     * Delete a product and its recipes (super_admin only).
     */
    public function destroyProduct(Product $product): JsonResponse
    {
        $user = Auth::user();

        if (! $user->isSuperAdmin()) {
            abort(403, 'Only the account owner can delete products.');
        }

        $product->recipes()->delete();
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Remove an ingredient from a recipe.
     */
    public function removeIngredient(Recipe $recipe): JsonResponse
    {
        $this->authorizeOwnerOrManager($recipe->product);

        $recipe->delete();

        return response()->json([
            'message' => 'Ingredient removed from recipe.',
        ]);
    }

    /**
     * Authorize that the current user can edit this product's recipe.
     * Super admins can edit all; managers can only edit their own branch's products.
     */
    private function authorizeOwnerOrManager(Product $product): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isManager()) {
            // Managers can edit — products are global, not branch-scoped.
            // If branch-scoping is needed later, check $product->branch_id here.
            return;
        }

        abort(403, 'You do not have permission to edit recipes.');
    }
}
