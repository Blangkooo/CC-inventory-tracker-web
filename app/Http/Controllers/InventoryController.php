<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();

        $stocks = BranchStock::with('ingredient', 'branch')
            ->when($isManager, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->get();

        return view('inventory.index', [
            'stocks' => $stocks,
            'ok_count' => $stocks->filter(fn ($s) => $s->stock_status === 'ok')->count(),
            'low_count' => $stocks->filter(fn ($s) => $s->stock_status === 'low')->count(),
            'out_count' => $stocks->filter(fn ($s) => $s->stock_status === 'out')->count(),
            'branches' => Branch::when($isManager, fn ($q) => $q->where('id', $user->branch_id))->get(),
        ]);
    }
}
