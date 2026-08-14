<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesBranchAccess;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentsController as WebPaymentsController;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Operational cost logging (rent, utilities, packaging, utensils, gas,
 * wages, etc.). Category list is owned by the web controller so the two
 * surfaces can't drift out of sync with the `payments.category` enum.
 */
class PaymentsController extends Controller
{
    use AuthorizesBranchAccess;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        $payments = Payment::with('branch', 'recorder')
            ->where('branch_id', $validated['branch_id'])
            ->latest()
            ->get();

        return response()->json([
            'payments' => $payments,
            'totals' => [
                'pending' => (float) $payments->where('status', 'pending')->sum('amount'),
                'paid' => (float) $payments->where('status', 'paid')->sum('amount'),
                'overdue' => (float) $payments->where('status', 'overdue')->sum('amount'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'category' => ['required', 'in:'.implode(',', WebPaymentsController::categories())],
            'payee' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'in:cash,bank_transfer,gcash,check,other'],
            'status' => ['required', 'in:pending,paid,overdue'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'receipt' => ['nullable', 'image', 'max:5120'],
        ]);

        $this->authorizeBranch((int) $validated['branch_id']);

        if ($request->hasFile('receipt')) {
            $validated['receipt_photo'] = $request->file('receipt')->store('payments', 'public');
        }
        unset($validated['receipt']);

        $validated['recorded_by'] = $request->user()->id;
        $validated['paid_at'] = $validated['status'] === 'paid' ? now() : null;

        $payment = Payment::create($validated);

        return response()->json(['message' => 'Payment recorded successfully.', 'payment' => $payment->load('branch', 'recorder')], 201);
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        $this->authorizeBranch($payment->branch_id);

        $validated = $request->validate([
            'category' => ['required', 'in:'.implode(',', WebPaymentsController::categories())],
            'payee' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'in:cash,bank_transfer,gcash,check,other'],
            'status' => ['required', 'in:pending,paid,overdue'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'paid' && $payment->status !== 'paid') {
            $validated['paid_at'] = now();
        } elseif ($validated['status'] !== 'paid') {
            $validated['paid_at'] = null;
        }

        $payment->update($validated);

        return response()->json(['message' => 'Payment updated successfully.', 'payment' => $payment->fresh()->load('branch', 'recorder')]);
    }

    public function markPaid(Payment $payment): JsonResponse
    {
        $this->authorizeBranch($payment->branch_id);

        $payment->update(['status' => 'paid', 'paid_at' => now()]);

        return response()->json(['message' => 'Payment marked as paid.', 'payment' => $payment->fresh()->load('branch', 'recorder')]);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $this->authorizeBranch($payment->branch_id);

        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully.']);
    }
}
