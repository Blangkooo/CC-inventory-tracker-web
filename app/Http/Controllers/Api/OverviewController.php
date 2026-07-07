<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class OverviewController extends Controller
{
    use AuthorizesBranchAccess;

    public function overview(): JsonResponse
    {
        return response()->json([
            'total_branches' => Branch::count(),
            'total_products' => Product::count(),
            'total_ingredients' => Ingredient::count(),
            'pending_alerts' => DiscrepancyAlert::where('status', 'pending')->count(),
        ]);
    }

    public function branchStock(Branch $branch): JsonResponse
    {
        $this->authorizeBranch($branch->id);

        return response()->json(
            BranchStock::with('ingredient')
                ->where('branch_id', $branch->id)
                ->get()
                ->map(fn ($s) => [
                    'ingredient' => $s->ingredient->name,
                    'unit' => $s->ingredient->unit,
                    'current_quantity' => $s->current_quantity,
                    'min_threshold' => $s->min_threshold,
                    'status' => $s->stock_status,
                ])
        );
    }
}
