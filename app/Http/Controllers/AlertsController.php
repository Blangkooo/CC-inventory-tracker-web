<?php

namespace App\Http\Controllers;

use App\Models\DiscrepancyAlert;
use App\Services\DiscrepancyValueCalculator;
use Illuminate\View\View;

class AlertsController extends Controller
{
    public function __construct(private DiscrepancyValueCalculator $valueCalculator)
    {
    }

    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $alerts = DiscrepancyAlert::with('branch', 'ingredient', 'shiftLog')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get();

        return view('alerts.index', [
            'alerts' => $alerts,
            'kpi_active' => $alerts->where('status', 'pending')->count(),
            'kpi_resolved_this_month' => $alerts->whereIn('status', ['reviewed', 'dismissed'])
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'kpi_high_severity' => $alerts->where('status', 'pending')->where('severity', 'high')->count(),
            'kpi_value_recovered' => $this->valueCalculator->estimatedValueSaved($isManager, $branchId),
            'severity_counts' => $alerts->where('status', 'pending')->countBy('severity'),
        ]);
    }
}
