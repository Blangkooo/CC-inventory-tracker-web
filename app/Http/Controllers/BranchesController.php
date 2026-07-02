<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\View\View;

class BranchesController extends Controller
{
    public function index(): View
    {
        $branches = Branch::with([
            'transactions' => fn ($q) => $q->whereDate('created_at', today()),
            'users' => fn ($q) => $q->where('role', 'staff'),
        ])->get();

        return view('branches.index', [
            'branches' => $branches,
        ]);
    }
}
