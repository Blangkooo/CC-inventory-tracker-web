<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Product, BranchStock, Transaction, DiscrepancyAlert};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
            'client_uuid' => 'required|string|unique:transactions,client_uuid',
        ]);

        $product = Product::with('recipes.ingredient')->findOrFail($validated['product_id']);
        $total_amount = $product->price * $validated['quantity'];

        return DB::transaction(function () use ($validated, $product, $total_amount, $request) {
            // CHECK STOCK
            foreach ($product->recipes as $recipe) {
                $stock = BranchStock::where('branch_id', $validated['branch_id'])
                                   ->where('ingredient_id', $recipe->ingredient_id)
                                   ->first();
                $needed = $recipe->quantity_required * $validated['quantity'];

                if (!$stock || $stock->current_quantity < $needed) {
                    return response()->json([
                        'error' => 'Insufficient stock for ' . $recipe->ingredient->name,
                        'needed' => $needed . $recipe->ingredient->unit,
                        'available' => ($stock->current_quantity ?? 0) . $recipe->ingredient->unit,
                    ], 422);
                }
            }

            // DEDUCT STOCK
            foreach ($product->recipes as $recipe) {
                $deduction = $recipe->quantity_required * $validated['quantity'];
                BranchStock::where('branch_id', $validated['branch_id'])
                           ->where('ingredient_id', $recipe->ingredient_id)
                           ->decrement('current_quantity', $deduction);
                BranchStock::where('branch_id', $validated['branch_id'])
                           ->where('ingredient_id', $recipe->ingredient_id)
                           ->update(['last_updated_at' => now()]);
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
                        'details' => $recipe->ingredient->name . ' is ' . $stock->stock_status . ' at Branch ID ' . $validated['branch_id'],
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
    }
}
