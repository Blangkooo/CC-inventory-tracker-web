<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class RecipesController extends Controller
{
    public function index(): View
    {
        $products = Product::with('recipes.ingredient')->get();
        $categories = Product::distinct()->pluck('category')->filter();

        return view('recipes.index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
