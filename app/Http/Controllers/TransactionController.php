<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\BranchStock;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'client_uuid' => ['required', 'uuid', 'unique:transactions,client_uuid'],
        ]);

        $product = Product::with('recipes.ingredient')->findOrFail($validated['product_id']);
        $quantity = (int) $validated['quantity'];
        $branchId = (int) $validated['branch_id'];
        $totalAmount = $product->price * $quantity;

        // Resolve the acting user: the Sanctum token holder when authenticated,
        // otherwise fall back to a staff user at the branch (keeps the test route working).
        $userId = $request->user()?->id
            ?? User::where('branch_id', $branchId)->where('role', 'staff')->value('id')
            ?? User::query()->value('id');

        try {
            $result = DB::transaction(function () use ($product, $quantity, $branchId, $totalAmount, $userId, $validated) {
                // a. Check every ingredient has enough stock before deducting anything.
                foreach ($product->recipes as $recipe) {
                    $stock = BranchStock::where('branch_id', $branchId)
                        ->where('ingredient_id', $recipe->ingredient_id)
                        ->lockForUpdate()
                        ->first();

                    $needed = $recipe->quantity_required * $quantity;

                    if (! $stock || $stock->current_quantity < $needed) {
                        $ingredientName = $recipe->ingredient?->name ?? 'ingredient #' . $recipe->ingredient_id;
                        $have = $stock?->current_quantity ?? 0;

                        // Abort the DB transaction and bubble up a 422 payload.
                        throw new InsufficientStockException(
                            "Insufficient stock for {$ingredientName}. Need {$needed}, have {$have}"
                        );
                    }
                }

                // b. All checks passed — deduct stock.
                foreach ($product->recipes as $recipe) {
                    $needed = $recipe->quantity_required * $quantity;

                    BranchStock::where('branch_id', $branchId)
                        ->where('ingredient_id', $recipe->ingredient_id)
                        ->update([
                            'current_quantity' => DB::raw("current_quantity - {$needed}"),
                            'last_updated_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                // c. Record the transaction.
                $transaction = Transaction::create([
                    'client_uuid' => $validated['client_uuid'],
                    'branch_id' => $branchId,
                    'user_id' => $userId,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'total_amount' => $totalAmount,
                    'sync_status' => 'synced',
                    'synced_at' => now(),
                ]);

                // d. Return the transaction plus the branch's updated stock levels.
                $updatedStock = BranchStock::with('ingredient')
                    ->where('branch_id', $branchId)
                    ->get();

                return [$transaction, $updatedStock];
            });
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        [$transaction, $updatedStock] = $result;

        return response()->json([
            'transaction' => $transaction->load('product', 'branch', 'user'),
            'branch_stock' => $updatedStock,
        ], 201);
    }
}
