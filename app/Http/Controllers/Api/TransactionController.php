<?php

namespace App\Http\Controllers\Api;

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
        $unit_price = $product->priceForSize($validated['size']);
        $total_amount = $unit_price * $validated['quantity'];

        return DB::transaction(function () use ($validated, $product, $unit_price, $total_amount, $request) {
            // DEDUCT STOCK — per the team's confirmed negative-stock policy,
            // a short ingredient no longer blocks the sale: it deducts past
            // zero, the response carries a warning, and the existing
            // low/out-of-stock alert loop below flags it on the dashboard.
            // lockForUpdate() still holds a row lock per ingredient so two
            // concurrent checkouts can't read stale stock and race.
            $deductions = [];
            $stockWarnings = [];

            foreach ($product->recipes as $recipe) {
                $stock = BranchStock::where('branch_id', $validated['branch_id'])
                    ->where('ingredient_id', $recipe->ingredient_id)
                    ->lockForUpdate()
                    ->first();
                $needed = $recipe->quantity_required * $validated['quantity'];
                $before = $stock->current_quantity ?? 0;
                $after = $before - $needed;

                if ($stock) {
                    $stock->update(['current_quantity' => $after, 'last_updated_at' => now()]);
                } else {
                    // No stock row yet for this branch+ingredient — create one
                    // at the (likely negative) resulting balance rather than
                    // silently skipping the deduction.
                    $stock = BranchStock::create([
                        'branch_id' => $validated['branch_id'],
                        'ingredient_id' => $recipe->ingredient_id,
                        'current_quantity' => $after,
                        'min_threshold' => 0,
                        'last_updated_at' => now(),
                    ]);
                }

                if ($after < 0) {
                    $stockWarnings[] = [
                        'ingredient_id' => $recipe->ingredient_id,
                        'ingredient_name' => $recipe->ingredient->name,
                        'short_by' => round(abs($after), 3),
                        'unit' => $recipe->ingredient->unit,
                    ];
                }

                $deductions[] = [
                    'branch_stock_id' => $stock->id,
                    'quantity_change' => -$needed,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                ];
            }

            // CHECK IF NOW LOW/OUT/NEGATIVE - create discrepancy alert if needed
            foreach ($product->recipes as $recipe) {
                $stock = BranchStock::where('branch_id', $validated['branch_id'])
                    ->where('ingredient_id', $recipe->ingredient_id)
                    ->first();
                if ($stock && $stock->current_quantity <= $stock->min_threshold) {
                    // Reorder link (Trinity doc): point straight at the cheapest/primary
                    // supplier so whoever reads the alert can act without hunting through
                    // the Supplier Directory first.
                    $supplier = $recipe->ingredient->primarySupplier();
                    $reorderNote = $supplier
                        ? ' Reorder from '.$supplier->name.($supplier->contact_number ? ' ('.$supplier->contact_number.')' : '').'.'
                        : '';

                    DiscrepancyAlert::create([
                        'branch_id' => $validated['branch_id'],
                        'type' => 'stock_mismatch',
                        'severity' => $stock->current_quantity <= 0 ? 'high' : 'medium',
                        'ingredient_id' => $recipe->ingredient_id,
                        'expected_value' => $stock->min_threshold,
                        'actual_value' => $stock->current_quantity,
                        'variance' => $stock->current_quantity - $stock->min_threshold,
                        'details' => ($stock->current_quantity < 0
                            ? $recipe->ingredient->name.' went negative ('.$stock->current_quantity.' '.$recipe->ingredient->unit.') at Branch ID '.$validated['branch_id']
                            : $recipe->ingredient->name.' is '.$stock->stock_status.' at Branch ID '.$validated['branch_id']
                        ).$reorderNote,
                        'status' => 'pending',
                    ]);
                }
            }

            // CREATE TRANSACTION — always recorded; the POS flow is never blocked by stock.
            $transaction = Transaction::create([
                'client_uuid' => $validated['client_uuid'],
                'branch_id' => $validated['branch_id'],
                'user_id' => $request->user()?->id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'unit_price' => $unit_price,
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
                'message' => $stockWarnings
                    ? 'Transaction recorded. Stock went negative for '.count($stockWarnings).' ingredient(s) — flagged on the dashboard.'
                    : 'Transaction recorded and stock updated',
                'transaction' => $transaction->load('product', 'branch'),
                'updated_stock' => $updatedStock,
                'stock_warnings' => $stockWarnings,
            ], 201);
        });
    }
}
