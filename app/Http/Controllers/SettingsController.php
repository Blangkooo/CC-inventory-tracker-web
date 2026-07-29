<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'varianceThresholdPct' => (float) AppSetting::get('variance_threshold_pct', 0.05),
            'varianceThresholdPhp' => (float) AppSetting::get('variance_threshold_php', 100),
            'lowStockThresholdPct' => (float) AppSetting::get('low_stock_threshold_pct', 0.25),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only the owner can change system settings.');

        $validated = $request->validate([
            'variance_threshold_pct' => ['required', 'numeric', 'min:0', 'max:1'],
            'variance_threshold_php' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold_pct' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        AppSetting::set('variance_threshold_pct', $validated['variance_threshold_pct']);
        AppSetting::set('variance_threshold_php', $validated['variance_threshold_php']);
        AppSetting::set('low_stock_threshold_pct', $validated['low_stock_threshold_pct']);

        return response()->json(['success' => true]);
    }
}
