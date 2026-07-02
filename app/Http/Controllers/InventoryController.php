<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $stocks = BranchStock::with('ingredient', 'branch')->get();

        return view('inventory.index', [
            'stocks' => $stocks,
            'ok_count' => $stocks->filter(fn ($s) => $s->stock_status === 'ok')->count(),
            'low_count' => $stocks->filter(fn ($s) => $s->stock_status === 'low')->count(),
            'out_count' => $stocks->filter(fn ($s) => $s->stock_status === 'out')->count(),
            'branches' => Branch::all(),
        ]);
    }
}
