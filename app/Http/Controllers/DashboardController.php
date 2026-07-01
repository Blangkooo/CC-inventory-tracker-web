<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\DiscrepancyAlert;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'total_branches' => Branch::count(),
            'total_products' => Product::count(),
            'total_ingredients' => Ingredient::count(),
            'total_alerts' => DiscrepancyAlert::where('status', 'pending')->count(),
            'branches' => Branch::all(),
            'products' => Product::with('ingredients')->get(),
            'branch_stocks' => BranchStock::with('ingredient', 'branch')->get(),
            'recent_transactions' => Transaction::with('product', 'branch', 'user')->latest()->take(10)->get(),
        ]);
    }
}
