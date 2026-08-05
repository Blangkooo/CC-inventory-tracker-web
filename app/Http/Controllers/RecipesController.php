<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecipesController extends Controller
{
    public function index(): View
    {
        $products = Product::with('recipes.ingredient')->get();
        $categories = Product::distinct()->pluck('category')->filter();
        $allIngredients = Ingredient::orderBy('name')->get();

        return view('recipes.index', [
            'products'        => $products,
            'categories'       => $categories,
            'allIngredients'   => $allIngredients,
        ]);
    }

    /**
     * GET /business/recipes/product/{product}/data
     */
    public function getProductData(Product $product): JsonResponse
    {
        $product->load('recipes.ingredient');

        return response()->json($product);
    }

    /**
     * PUT /business/recipes/product/{product}
     */
    public function updateProduct(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $this->authorizeProductAction($request);

        $validated = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'price'    => ['sometimes', 'numeric', 'min:0'],
            'procedure' => ['sometimes', 'nullable', 'string'],
        ]);

        $product->update($validated);

        if ($request->expectsJson()) {
            return response()->json($product->fresh('recipes.ingredient'));
        }

        return back()->with('success', 'Product updated successfully.');
    }

    /**
     * POST /business/recipes/product/{product}/ingredient
     */
    public function addIngredient(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $this->authorizeProductAction($request);

        $validated = $request->validate([
            'ingredient_id'     => ['required', 'exists:ingredients,id'],
            'size'              => ['required', 'string', 'in:regular,large'],
            'quantity_required' => ['required', 'numeric', 'min:0'],
        ]);

        // Update existing recipe or create new one
        $recipe = Recipe::updateOrCreate(
            [
                'product_id'    => $product->id,
                'ingredient_id' => $validated['ingredient_id'],
                'size'          => $validated['size'],
            ],
            [
                'quantity_required' => $validated['quantity_required'],
            ]
        );

        $recipe->load('ingredient');

        $status = $recipe->wasRecentlyCreated ? 201 : 200;

        if ($request->expectsJson()) {
            return response()->json($recipe, $status);
        }

        return back()->with('success', 'Ingredient added/updated in recipe.');
    }

    /**
     * PUT /business/recipes/ingredient/{recipe}
     */
    public function updateIngredient(Request $request, Recipe $recipe): JsonResponse|RedirectResponse
    {
        $this->authorizeProductAction($request);

        $validated = $request->validate([
            'quantity_required' => ['required', 'numeric', 'min:0'],
        ]);

        $recipe->update($validated);

        if ($request->expectsJson()) {
            return response()->json($recipe->fresh('ingredient'));
        }

        return back()->with('success', 'Ingredient quantity updated.');
    }

    /**
     * POST /business/recipes/ingredient/{recipe}/delete
     */
    public function removeIngredient(Request $request, Recipe $recipe): JsonResponse|RedirectResponse
    {
        $this->authorizeProductAction($request);

        $recipe->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Ingredient removed from recipe.']);
        }

        return back()->with('success', 'Ingredient removed from recipe.');
    }

    /**
     * POST /business/recipes/product/{product}/delete
     */
    public function deleteProduct(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Only owner (super admin) can delete products
        if (! $user->isSuperAdmin()) {
            if ($request->expectsJson()) {
                abort(403, 'Only business owners can delete products.');
            }
            abort(403, 'Only business owners can delete products.');
        }

        // Delete associated recipes first
        $product->recipes()->delete();
        $product->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Product deleted successfully.']);
        }

        return redirect()->route('recipes')->with('success', 'Product deleted successfully.');
    }

    /**
     * Authorize that the user is owner or manager (not staff).
     */
    private function authorizeProductAction(Request $request): void
    {
        $user = $request->user();

        if ($user->isStaff()) {
            abort(403, 'You do not have permission to manage recipes.');
        }
    }
}
