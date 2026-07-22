<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\View\View;

class RecipesController extends Controller
{
    public function index(): View
    {
        $products = Product::with('recipes.ingredient')->get();
        $categories = Product::distinct()->pluck('category')->filter();
        $allIngredients = Ingredient::orderBy('name')->get();

        return view('recipes.index', [
            'products'        => $products,
            'categories'       => $categories,
            'allIngredients'   => $allIngredients,
        ]);
    }
}
