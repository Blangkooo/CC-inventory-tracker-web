<?php

namespace App\Http\Controllers;

use App\Models\DiscrepancyAlert;
use Illuminate\View\View;

class AlertsController extends Controller
{
    public function index(): View
    {
        $alerts = DiscrepancyAlert::with('branch', 'ingredient', 'shiftLog')->latest()->get();

        return view('alerts.index', [
            'alerts' => $alerts,
        ]);
    }
}
