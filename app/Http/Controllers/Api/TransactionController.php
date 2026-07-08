<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    use AuthorizesBranchAccess;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $transactions = Transaction::with('product', 'user')
            ->where('branch_id', $validated['branch_id'])
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $this->authorizeBranch($transaction->branch_id);

        return response()->json($transaction->load('product', 'branch', 'user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
            'size' => ['sometimes', Rule::in([Recipe::SIZE_REGULAR, Recipe::SIZE_LARGE])],
            'client_uuid' => 'required|string|unique:transactions,client_uuid',
        ]);
        $validated['size'] ??= Recipe::SIZE_REGULAR;

        $this->authorizeBranch((int) $validated['branch_id']);

        $product = Product::with(['recipes' => fn ($q) => $q->where('size', $validated['size'])->with('ingredient')])
            ->findOrFail($validated['product_id']);
        $total_amount = $product->price * $validated['quantity'];

        try {
            return DB::transaction(function () use ($validated, $product, $total_amount, $request) {
                // CHECK STOCK — lockForUpdate() holds a row lock per ingredient for
                // the rest of this DB transaction, so a second concurrent checkout
                // for the same ingredient can't read stale stock and over-deduct.
                foreach ($product->recipes as $recipe) {
                    $stock = BranchStock::where('branch_id', $validated['branch_id'])
                        ->where('ingredient_id', $recipe->ingredient_id)
                        ->lockForUpdate()
                        ->first();
                    $needed = $recipe->quantity_required * $validated['quantity'];

                    if (! $stock || $stock->current_quantity < $needed) {
                        throw new InsufficientStockException(
                            'Insufficient stock for '.$recipe->ingredient->name,
                            $needed.$recipe->ingredient->unit,
                            ($stock->current_quantity ?? 0).$recipe->ingredient->unit,
                        );
                    }
                }

                // DEDUCT STOCK — also capture before/after per ingredient so the
                // sale's movement history can be logged once the transaction exists.
                $deductions = [];
                foreach ($product->recipes as $recipe) {
                    $deduction = $recipe->quantity_required * $validated['quantity'];
                    $stock = BranchStock::where('branch_id', $validated['branch_id'])
                        ->where('ingredient_id', $recipe->ingredient_id)
                        ->first();

                    $before = $stock->current_quantity;
                    $after = $before - $deduction;

                    $stock->update([
                        'current_quantity' => $after,
                        'last_updated_at' => now(),
                    ]);

                    $deductions[] = [
                        'branch_stock_id' => $stock->id,
                        'quantity_change' => -$deduction,
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                    ];
                }

                // CHECK IF NOW LOW/OUT - create discrepancy alert if needed
                foreach ($product->recipes as $recipe) {
                    $stock = BranchStock::where('branch_id', $validated['branch_id'])
                        ->where('ingredient_id', $recipe->ingredient_id)
                        ->first();
                    if ($stock && $stock->current_quantity <= $stock->min_threshold) {
                        DiscrepancyAlert::create([
                            'branch_id' => $validated['branch_id'],
                            'type' => 'stock_mismatch',
                            'severity' => $stock->current_quantity <= 0 ? 'high' : 'medium',
                            'ingredient_id' => $recipe->ingredient_id,
                            'expected_value' => $stock->min_threshold,
                            'actual_value' => $stock->current_quantity,
                            'variance' => $stock->current_quantity - $stock->min_threshold,
                            'details' => $recipe->ingredient->name.' is '.$stock->stock_status.' at Branch ID '.$validated['branch_id'],
                            'status' => 'pending',
                        ]);
                    }
                }

                // CREATE TRANSACTION
                $transaction = Transaction::create([
                    'client_uuid' => $validated['client_uuid'],
                    'branch_id' => $validated['branch_id'],
                    'user_id' => $request->user()?->id,
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                    'total_amount' => $total_amount,
                    'sync_status' => 'synced',
                    'synced_at' => now(),
                    'created_offline_at' => now(),
                ]);

                // LOG STOCK MOVEMENTS for this sale, now that the transaction exists to reference.
                foreach ($deductions as $deduction) {
                    StockMovement::create($deduction + [
                        'type' => StockMovement::TYPE_SALE,
                        'reference_type' => Transaction::class,
                        'reference_id' => $transaction->id,
                        'user_id' => $request->user()?->id,
                    ]);
                }

                // RETURN WITH UPDATED STOCK
                $updatedStock = BranchStock::with('ingredient')
                    ->where('branch_id', $validated['branch_id'])
                    ->get();

                return response()->json([
                    'message' => 'Transaction recorded and stock updated',
                    'transaction' => $transaction->load('product', 'branch'),
                    'updated_stock' => $updatedStock,
                ], 201);
            });
        } catch (InsufficientStockException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'needed' => $e->needed,
                'available' => $e->available,
            ], 422);
        }
    }
}
