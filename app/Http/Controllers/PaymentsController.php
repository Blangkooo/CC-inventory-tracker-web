<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentsController extends Controller
{
    /** Kept in step with the `category` enum on the payments table. */
    public const CATEGORIES = [
        'rent', 'utilities', 'supplier', 'salary', 'maintenance',
        'packaging', 'utensils', 'gas', 'wages', 'other',
    ];

    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $payments = Payment::with('branch', 'recorder')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get();

        $branches = Branch::when($isManager, fn ($q) => $q->where('id', $branchId))->orderBy('name')->get();

        $totals = [
            'pending' => (float) $payments->where('status', 'pending')->sum('amount'),
            'paid' => (float) $payments->where('status', 'paid')->sum('amount'),
            'overdue' => (float) $payments->where('status', 'overdue')->sum('amount'),
        ];

        return view('payments.index', [
            'payments' => $payments,
            'branches' => $branches,
            'totals' => $totals,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        $isManager = $user->isManager();

        $validated = $request->validate([
            'branch_id' => [$isManager ? 'nullable' : 'required', 'exists:branches,id'],
            'category' => ['required', 'in:'.implode(',', self::CATEGORIES)],
            'payee' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'in:cash,bank_transfer,gcash,check,other'],
            'status' => ['required', 'in:pending,paid,overdue'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'receipt' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('receipt')) {
            $validated['receipt_photo'] = $request->file('receipt')->store('payments', 'public');
        }
        unset($validated['receipt']);

        $validated['branch_id'] = $isManager ? $user->branch_id : $validated['branch_id'];
        $validated['recorded_by'] = $user->id;
        $validated['paid_at'] = $validated['status'] === 'paid' ? now() : null;

        $payment = Payment::create($validated);

        return response()->json(['success' => true, 'payment' => $payment->load('branch', 'recorder')], 201);
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'in:'.implode(',', self::CATEGORIES)],
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

        return response()->json(['success' => true, 'payment' => $payment->load('branch', 'recorder')]);
    }

    public function markPaid(Payment $payment): JsonResponse
    {
        $payment->update(['status' => 'paid', 'paid_at' => now()]);

        return response()->json(['success' => true, 'payment' => $payment->load('branch', 'recorder')]);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(['success' => true]);
    }
}
