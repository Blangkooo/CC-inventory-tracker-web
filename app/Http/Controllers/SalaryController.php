<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Models\ShiftLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isManager = $user->isManager();
        $branchId = $isManager ? $user->branch_id : null;

        $workers = User::with('profile')
            ->whereIn('role', [User::ROLE_STAFF, User::ROLE_MANAGER])
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        $payslips = Payslip::with('user', 'branch')
            ->when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(30)
            ->get();

        $monthlyPayslips = Payslip::when($isManager, fn ($q) => $q->where('branch_id', $branchId))
            ->where('created_at', '>=', now()->startOfMonth())
            ->get();

        $configuredRates = $workers->pluck('profile.hourly_rate')->filter(fn ($r) => $r !== null && (float) $r > 0);

        return view('salary.index', [
            'workers' => $workers,
            'payslips' => $payslips,
            'kpi_monthly_payroll' => $monthlyPayslips->sum('gross_pay'),
            'kpi_pending_payslips' => $monthlyPayslips->where('status', 'draft')->count(),
            'kpi_paid_this_month' => $monthlyPayslips->where('status', 'paid')->count(),
            'kpi_average_rate' => $configuredRates->isNotEmpty() ? $configuredRates->avg() : null,
        ]);
    }

    public function updateRate(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'hourly_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $profile = $user->profile()->firstOrCreate([]);
        $profile->update(['hourly_rate' => $validated['hourly_rate']]);

        return response()->json(['success' => true, 'profile' => $profile]);
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
        ]);

        $worker = User::with('profile')->findOrFail($validated['user_id']);
        $hourlyRate = (float) ($worker->profile?->hourly_rate ?? 0);

        if ($hourlyRate <= 0) {
            return response()->json(['message' => 'Set an hourly rate for this worker before generating a payslip.'], 422);
        }

        if (! $worker->branch_id) {
            return response()->json(['message' => 'This worker has no assigned branch.'], 422);
        }

        $periodStart = $validated['period_start'];
        $periodEnd = $validated['period_end'];

        // Only closed shifts with a recorded end time count toward paid hours —
        // an open (still-clocked-in) shift has no final duration to bill yet.
        $totalHours = ShiftLog::where('user_id', $worker->id)
            ->where('status', 'closed')
            ->whereNotNull('shift_end')
            ->whereDate('shift_start', '>=', $periodStart)
            ->whereDate('shift_start', '<=', $periodEnd)
            ->get()
            ->sum(fn ($log) => $log->shift_start->diffInMinutes($log->shift_end) / 60);

        $totalHours = round($totalHours, 2);
        $grossPay = round($totalHours * $hourlyRate, 2);
        $deductions = round((float) ($validated['deductions'] ?? 0), 2);
        $netPay = round($grossPay - $deductions, 2);

        $payslip = Payslip::create([
            'user_id' => $worker->id,
            'branch_id' => $worker->branch_id,
            'generated_by' => auth()->id(),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'hourly_rate' => $hourlyRate,
            'total_hours' => $totalHours,
            'gross_pay' => $grossPay,
            'deductions' => $deductions,
            'net_pay' => $netPay,
            'status' => 'draft',
        ]);

        return response()->json(['success' => true, 'payslip' => $payslip->load('user', 'branch')], 201);
    }

    public function show(Payslip $payslip): View
    {
        return view('salary.payslip', compact('payslip'));
    }

    public function markPaid(Payslip $payslip): JsonResponse
    {
        $payslip->update(['status' => 'paid', 'paid_at' => now()]);

        return response()->json(['success' => true, 'payslip' => $payslip]);
    }
}
