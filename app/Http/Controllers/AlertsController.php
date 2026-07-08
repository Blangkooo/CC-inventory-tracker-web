<?php

namespace App\Http\Controllers;

use App\Models\DiscrepancyAlert;
use Illuminate\View\View;

class AlertsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $alerts = DiscrepancyAlert::with('branch', 'ingredient', 'shiftLog')
            ->when($user->isManager(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->latest()
            ->get();

        return view('alerts.index', [
            'alerts' => $alerts,
        ]);
    }
}
