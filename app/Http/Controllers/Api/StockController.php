<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    use AuthorizesBranchAccess;

    /**
     * Create the initial stock entry for a branch+ingredient pair.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'ingredient_id' => [
                'required',
                'exists:ingredients,id',
                Rule::unique('branch_stock')->where('branch_id', $request->branch_id),
            ],
            'current_quantity' => ['required', 'numeric', 'min:0'],
            'min_threshold' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $stock = DB::transaction(function () use ($validated, $request) {
            $stock = BranchStock::create($validated + ['last_updated_at' => now()]);

            StockMovement::create([
                'branch_stock_id' => $stock->id,
                'type' => StockMovement::TYPE_INITIAL,
                'quantity_change' => $stock->current_quantity,
                'quantity_before' => 0,
                'quantity_after' => $stock->current_quantity,
                'user_id' => $request->user()->id,
                'notes' => 'Initial stock entry.',
            ]);

            return $stock;
        });

        return response()->json($stock->load('ingredient'), 201);
    }

    /**
     * Manual restock — adds a quantity to whatever is already on hand
     * (as opposed to setting an absolute value) and logs the movement.
     */
    public function restock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $stock = BranchStock::where('branch_id', $validated['branch_id'])
            ->where('ingredient_id', $validated['ingredient_id'])
            ->firstOrFail();

        $stock = DB::transaction(function () use ($stock, $validated, $request) {
            $before = $stock->current_quantity;
            $after = $before + $validated['quantity'];

            $stock->update([
                'current_quantity' => $after,
                'last_updated_at' => now(),
            ]);

            StockMovement::create([
                'branch_stock_id' => $stock->id,
                'type' => StockMovement::TYPE_RESTOCK,
                'quantity_change' => $validated['quantity'],
                'quantity_before' => $before,
                'quantity_after' => $after,
                'user_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            return $stock;
        });

        return response()->json($stock->load('ingredient'));
    }

    /**
     * Chronological history of every change to a branch+ingredient's stock level.
     */
    public function movements(BranchStock $branchStock): JsonResponse
    {
        $this->authorizeBranch($branchStock->branch_id);

        $movements = $branchStock->movements()
            ->with('user')
            ->latest()
            ->paginate(20);

        return response()->json($movements);
    }

    /**
     * All branch_stock rows currently at or below their min_threshold.
     */
    public function lowStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $items = BranchStock::with('ingredient')
            ->where('branch_id', $validated['branch_id'])
            ->whereColumn('current_quantity', '<=', 'min_threshold')
            ->get();

        return response()->json($items);
    }
}
