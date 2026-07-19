<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\View\View;

class BusinessRecipesController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))
            ->orderBy('name')
            ->get();

        $categories = Product::distinct()->pluck('category')->filter()->values();

        $products = Product::with('recipes.ingredient')
            ->orderBy('name')
            ->get();

        return view('business.recipes', [
            'branches'   => $branches,
            'categories' => $categories,
            'products'   => $products,
        ]);
    }
}
