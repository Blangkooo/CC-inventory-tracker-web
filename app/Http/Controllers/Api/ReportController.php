<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use AuthorizesBranchAccess;

    public function sales(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'period' => ['required', 'in:daily,weekly,monthly'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $start = match ($validated['period']) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
        };

        $transactions = Transaction::with('product')
            ->where('branch_id', $validated['branch_id'])
            ->where('created_at', '>=', $start)
            ->get();

        $byProduct = $transactions->groupBy('product_id')->map(function ($group) {
            $product = $group->first()->product;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity_sold' => $group->sum('quantity'),
                'total_sales' => $group->sum('total_amount'),
            ];
        })->values();

        return response()->json([
            'branch_id' => (int) $validated['branch_id'],
            'period' => $validated['period'],
            'from' => $start,
            'to' => now(),
            'total_transactions' => $transactions->count(),
            'total_sales' => $transactions->sum('total_amount'),
            'average_order_value' => $transactions->count() > 0
                ? round((float) $transactions->avg('total_amount'), 2)
                : 0,
            'by_product' => $byProduct,
        ]);
    }

    public function inventory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $items = BranchStock::with('ingredient')
            ->where('branch_id', $validated['branch_id'])
            ->get()
            ->map(fn (BranchStock $stock) => [
                'ingredient_id' => $stock->ingredient_id,
                'ingredient' => $stock->ingredient->name,
                'unit' => $stock->ingredient->unit,
                'current_quantity' => $stock->current_quantity,
                'min_threshold' => $stock->min_threshold,
                'status' => $stock->stock_status,
            ]);

        return response()->json([
            'branch_id' => (int) $validated['branch_id'],
            'total_ingredients' => $items->count(),
            'low_stock_count' => $items->where('status', 'low')->count(),
            'out_of_stock_count' => $items->where('status', 'out')->count(),
            'items' => $items->values(),
        ]);
    }
}
