<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Show the settings page. Was a dead redirect to a `#settings` dashboard
     * panel that was never built; this is now a real page.
     */
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

    /**
     * Update the authenticated user's profile (name & email).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => [
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors'  => ['current_password' => ['The current password does not match our records.']],
            ], 422);
        }

        $user->update(['password' => $validated['new_password']]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
