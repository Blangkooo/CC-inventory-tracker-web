<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Recompute every active product/size's cost and margin under a set of
     * hypothetical ingredient price adjustments. Nothing is persisted —
     * this is a what-if calculation only. Only sizes that actually use one
     * of the adjusted ingredients are returned.
     */
    public function simulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'adjustments' => ['required', 'array', 'min:1'],
            'adjustments.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'adjustments.*.new_unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $adjustedCosts = collect($validated['adjustments'])->pluck('new_unit_cost', 'ingredient_id');

        $products = Product::with('recipes.ingredient.suppliers')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $results = [];
        $netMarginImpact = 0.0;

        foreach ($products as $product) {
            $price = (float) $product->price;

            foreach ($product->recipes->groupBy('size') as $size => $recipes) {
                $oldCost = 0.0;
                $newCost = 0.0;
                $affected = false;
                $lines = [];

                foreach ($recipes as $recipe) {
                    $ingredient = $recipe->ingredient;
                    $primary = $ingredient->suppliers->firstWhere('pivot.is_primary', true);
                    $currentUnitCost = (float) ($primary?->pivot?->unit_cost ?? 0);
                    $qty = (float) $recipe->quantity_required;

                    $newUnitCost = $currentUnitCost;
                    if ($adjustedCosts->has($ingredient->id)) {
                        $newUnitCost = (float) $adjustedCosts->get($ingredient->id);
                        $affected = true;
                    }

                    $oldLineCost = $currentUnitCost * $qty;
                    $newLineCost = $newUnitCost * $qty;
                    $oldCost += $oldLineCost;
                    $newCost += $newLineCost;

                    $lines[] = [
                        'ingredient_id' => $ingredient->id,
                        'ingredient_name' => $ingredient->name,
                        'qty' => $qty,
                        'unit' => $ingredient->unit,
                        'old_unit_cost' => round($currentUnitCost, 2),
                        'new_unit_cost' => round($newUnitCost, 2),
                        'old_line_cost' => round($oldLineCost, 2),
                        'new_line_cost' => round($newLineCost, 2),
                    ];
                }

                if (! $affected) {
                    continue;
                }

                $costDelta = round($newCost - $oldCost, 2);
                $netMarginImpact -= $costDelta; // a cost increase eats directly into profit per unit

                $results[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'size' => $size,
                    'price' => $price,
                    'old_cost' => round($oldCost, 2),
                    'new_cost' => round($newCost, 2),
                    'cost_delta' => $costDelta,
                    'old_margin_pct' => $price > 0 ? round((($price - $oldCost) / $price) * 100, 1) : 0,
                    'new_margin_pct' => $price > 0 ? round((($price - $newCost) / $price) * 100, 1) : 0,
                    'suggested_price_65pct_margin' => $newCost > 0 ? round($newCost / 0.35, 2) : 0,
                    'ingredients' => $lines,
                ];
            }
        }

        return response()->json([
            'adjustments' => $validated['adjustments'],
            'affected_count' => count($results),
            'net_gain_loss_per_unit_sold' => round($netMarginImpact, 2),
            'results' => $results,
        ]);
    }
}
