<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'uuid'       => 'required|uuid',
            'branch_id'  => 'required|integer|exists:branches,id',
            'user_id'    => 'required|integer|exists:users,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $existing = \App\Models\Transaction::where('uuid', $request->uuid)->first();
        if ($existing) {
            return response()->json($existing, 200);
        }

        $transaction = \App\Models\Transaction::create([
            'uuid'       => $request->uuid,
            'branch_id'  => $request->branch_id,
            'user_id'    => $request->user_id,
            'product_id' => $request->product_id,
            'quantity'   => $request->quantity,
            'synced'     => true,
        ]);

        $recipes = \App\Models\Recipe::where('product_id', $request->product_id)->get();

        foreach ($recipes as $recipe) {
            $deduction = $recipe->quantity * $request->quantity;

            \App\Models\StockLevel::where('branch_id', $request->branch_id)
                ->where('ingredient_name', $recipe->ingredient_name)
                ->decrement('quantity', $deduction);

            \App\Models\StockLevel::where('branch_id', $request->branch_id)
                ->where('ingredient_name', $recipe->ingredient_name)
                ->update(['updated_at' => now()]);
        }

        return response()->json($transaction, 201);
    }
}
