<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $products = Product::with('recipes.ingredient.suppliers')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $lines = $product->recipes->map(function ($recipe) {
                    $ingredient = $recipe->ingredient;
                    $primary = $ingredient->suppliers->firstWhere('pivot.is_primary', true);
                    $unitCost = (float) ($primary?->pivot?->unit_cost ?? 0);
                    $lineCost = $unitCost * (float) $recipe->quantity_required;

                    return [
                        'name' => $ingredient->name,
                        'unit' => $ingredient->unit,
                        'size' => $recipe->size,
                        'qty' => (float) $recipe->quantity_required,
                        'unit_cost' => $unitCost,
                        'line_cost' => round($lineCost, 2),
                        'supplier' => $primary?->name,
                    ];
                });

                $price = (float) $product->price;

                // A serving is one size or the other — never both, so cost is summed per size.
                $sizes = $lines->groupBy('size')->map(function ($ingredients, $size) use ($price) {
                    $totalCost = round($ingredients->sum('line_cost'), 2);

                    return [
                        'size' => $size,
                        'total_cost' => $totalCost,
                        'margin_pct' => $price > 0 ? round((($price - $totalCost) / $price) * 100, 1) : 0,
                        'suggested_price' => $totalCost > 0 ? round($totalCost / 0.35, 2) : 0,
                        'ingredients' => $ingredients->values(),
                    ];
                })->values();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'price' => $price,
                    'sizes' => $sizes,
                ];
            });

        return view('pricing.index', ['products' => $products]);
    }

    public function simulate(): JsonResponse
    {
        $products = Product::with('recipes.ingredient.suppliers')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $lines = $product->recipes->map(function ($recipe) {
                    $ingredient = $recipe->ingredient;
                    $primary = $ingredient->suppliers->firstWhere('pivot.is_primary', true);
                    $unitCost = (float) ($primary?->pivot?->unit_cost ?? 0);
                    $lineCost = $unitCost * (float) $recipe->quantity_required;

                    return [
                        'id' => $ingredient->id,
                        'name' => $ingredient->name,
                        'unit' => $ingredient->unit,
                        'size' => $recipe->size,
                        'qty' => (float) $recipe->quantity_required,
                        'unit_cost' => $unitCost,
                        'line_cost' => round($lineCost, 2),
                    ];
                });

                $price = (float) $product->price;

                // A serving is one size or the other — never both, so cost is summed per size.
                $sizes = $lines->groupBy('size')->map(function ($ingredients, $size) {
                    return [
                        'size' => $size,
                        'total_cost' => round($ingredients->sum('line_cost'), 2),
                        'ingredients' => $ingredients->values(),
                    ];
                })->values();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $price,
                    'sizes' => $sizes,
                ];
            });

        return response()->json($products);
    }
}
