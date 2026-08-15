<?php

namespace App\Services;

use App\Models\DiscrepancyAlert;
use App\Models\Product;

class DiscrepancyValueCalculator
{
    /**
     * Estimated peso value of leakage that was caught and acted on
     * (alerts marked reviewed/dismissed). No ingredient cost data exists,
     * so each ingredient's unit value is pro-rated from product prices:
     * price / total recipe quantity, averaged across products using it.
     */
    public function estimatedValueSaved(bool $isManager, ?int $branchId): float
    {
        $unitValues = [];

        Product::with('recipes')->get()->each(function ($product) use (&$unitValues) {
            $totalQty = $product->recipes->sum('quantity_required');

            if ($totalQty <= 0) {
                return;
            }

            foreach ($product->recipes as $recipe) {
                $unitValues[$recipe->ingredient_id][] = (float) $product->price / $totalQty;
            }
        });

        $avgUnitValue = array_map(fn ($values) => array_sum($values) / count($values), $unitValues);

        return DiscrepancyAlert::whereIn('status', ['reviewed', 'dismissed'])
            ->whereNotNull('ingredient_id')
            ->whereNotNull('variance')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->sum(fn ($alert) => abs((float) $alert->variance) * ($avgUnitValue[$alert->ingredient_id] ?? 0));
    }
}
