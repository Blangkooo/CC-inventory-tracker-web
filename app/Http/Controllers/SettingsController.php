<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'varianceThresholdPct' => (float) AppSetting::get('variance_threshold_pct', 0.05),
            'varianceThresholdPhp' => (float) AppSetting::get('variance_threshold_php', 100),
            'lowStockThresholdPct' => (float) AppSetting::get('low_stock_threshold_pct', 0.25),
            'retentionMonths' => (int) AppSetting::get('receipt_retention_months', 24),
            'paymentCategories' => PaymentsController::categories(),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only the owner can change system settings.');

        $validated = $request->validate([
            'variance_threshold_pct' => ['required', 'numeric', 'min:0', 'max:1'],
            'variance_threshold_php' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold_pct' => ['required', 'numeric', 'min:0', 'max:1'],
            'receipt_retention_months' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        AppSetting::set('variance_threshold_pct', $validated['variance_threshold_pct']);
        AppSetting::set('variance_threshold_php', $validated['variance_threshold_php']);
        AppSetting::set('low_stock_threshold_pct', $validated['low_stock_threshold_pct']);
        AppSetting::set('receipt_retention_months', $validated['receipt_retention_months']);

        return response()->json(['success' => true]);
    }

    public function addPaymentCategory(Request $request): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only the owner can change system settings.');

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:30', 'regex:/^[a-z0-9_]+$/'],
        ]);

        $categories = PaymentsController::categories();
        if (! in_array($validated['category'], $categories, true)) {
            $categories[] = $validated['category'];
            AppSetting::set('payment_categories', json_encode($categories));
        }

        return response()->json(['success' => true, 'categories' => PaymentsController::categories()]);
    }

    public function removePaymentCategory(Request $request, string $category): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only the owner can change system settings.');
        abort_if($category === 'other', 422, "'other' is the fallback category and can't be removed.");

        $categories = array_values(array_diff(PaymentsController::categories(), [$category]));
        AppSetting::set('payment_categories', json_encode($categories));

        return response()->json(['success' => true, 'categories' => PaymentsController::categories()]);
    }
}
