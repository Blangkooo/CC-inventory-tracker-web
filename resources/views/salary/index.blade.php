@extends('layouts.sidebar')

@section('title', 'Salary')

@section('content')
<div class="mb-6">
    <div class="text-[22px] font-extrabold tracking-tight">Salary</div>
    <div class="text-[13px] text-ink-2 mt-0.5">Hourly rates and generated payslips, from clocked shift hours</div>
</div>

<div class="grid grid-cols-4 max-[880px]:grid-cols-2 card border-[1.5px] border-line overflow-hidden divide-x divide-line max-[880px]:divide-x-0 mb-5">
    <div class="p-4 text-center max-[880px]:border-b max-[880px]:border-line">
        <div class="text-[26px] font-extrabold text-accent leading-none">₱{{ number_format($kpi_monthly_payroll, 2) }}</div>
        <div class="text-[10px] font-bold uppercase tracking-[.05em] opacity-50 mt-1.5">Monthly Payroll</div>
    </div>
    <div class="p-4 text-center max-[880px]:border-b max-[880px]:border-line">
        <div class="text-[26px] font-extrabold text-accent leading-none">{{ $kpi_pending_payslips }}</div>
        <div class="text-[10px] font-bold uppercase tracking-[.05em] opacity-50 mt-1.5">Pending Payslips</div>
    </div>
    <div class="p-4 text-center">
        <div class="text-[26px] font-extrabold text-green-600 leading-none">{{ $kpi_paid_this_month }}</div>
        <div class="text-[10px] font-bold uppercase tracking-[.05em] opacity-50 mt-1.5">Paid This Month</div>
    </div>
    <div class="p-4 text-center">
        <div class="text-[26px] font-extrabold text-accent leading-none">{{ $kpi_average_rate !== null ? '₱'.number_format($kpi_average_rate, 2) : '—' }}</div>
        <div class="text-[10px] font-bold uppercase tracking-[.05em] opacity-50 mt-1.5">Average Rate</div>
    </div>
</div>

<div class="mb-4">
    <h3 class="text-[15px] font-extrabold mb-2.5">Worker Rates</h3>
    <div class="summary-table-wrap">
        @if ($workers->isEmpty())
            <div class="empty-state-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span class="empty-state-text">No workers on record yet.</span>
            </div>
        @else
            <table class="summary-table">
                <thead><tr><th>Worker</th><th>Branch</th><th>Hourly Rate</th><th></th></tr></thead>
                <tbody>
                    @foreach ($workers as $worker)
                    <tr>
                        <td class="font-bold">{{ $worker->name }}</td>
                        <td>{{ $worker->branch?->name ?? '—' }}</td>
                        <td>
                            @if (auth()->user()->isSuperAdmin())
                                {{-- Setting pay is an owner decision; managers see the rate but can't change it. --}}
                                <div class="flex items-center gap-1.5">
                                    <span>&#8369;</span>
                                    <input type="number" step="0.01" min="0" class="form-input !h-8 !w-[100px] !py-1 !text-[12px]" id="rate-{{ $worker->id }}" value="{{ $worker->profile?->hourly_rate ?? '' }}" placeholder="{{ $worker->profile?->hourly_rate ? '0.00' : 'Not set' }}">
                                    <button class="btn-sm" onclick="saveRate({{ $worker->id }})">Save</button>
                                </div>
                            @else
                                <span class="font-semibold">{{ $worker->profile?->hourly_rate ? '₱'.number_format($worker->profile->hourly_rate, 2) : '—' }}</span>
                            @endif
                        </td>
                        <td><button class="btn-sm" onclick="openGenerateModal({{ $worker->id }}, '{{ addslashes($worker->name) }}')">Generate Payslip</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div>
    <h3 class="text-[15px] font-extrabold mb-2.5">Payslip History</h3>
    <div class="summary-table-wrap">
        @if ($payslips->isEmpty())
            <div class="empty-state-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/><line x1="9" y1="11" x2="15" y2="11"/></svg>
                <span class="empty-state-text">No payslips generated yet.</span>
            </div>
        @else
            <table class="summary-table">
                <thead><tr><th>Worker</th><th>Branch</th><th>Period</th><th>Hours</th><th>Net Pay</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach ($payslips as $payslip)
                    <tr>
                        <td class="font-bold">{{ $payslip->user?->name ?? '—' }}</td>
                        <td>{{ $payslip->branch?->name ?? '—' }}</td>
                        <td>{{ $payslip->period_start->format('M d') }} &ndash; {{ $payslip->period_end->format('M d, Y') }}</td>
                        <td>{{ $payslip->total_hours }}h</td>
                        <td class="font-bold text-accent">&#8369;{{ number_format($payslip->net_pay, 2) }}</td>
                        <td><span class="{{ $payslip->status === 'paid' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($payslip->status) }}</span></td>
                        <td>
                            <div class="flex gap-1.5">
                                <a href="{{ route('salary.show', $payslip) }}" class="btn-sm">View</a>
                                @if ($payslip->status !== 'paid')
                                    <button class="btn-sm" onclick="markPaid({{ $payslip->id }})">Mark Paid</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- GENERATE PAYSLIP MODAL --}}
<div class="modal-overlay" id="generateModal">
    <div class="modal-box">
        <h2 class="text-lg font-extrabold mb-1" id="generateModalTitle">Generate Payslip</h2>
        <div class="text-xs text-ink-2 mb-5">Sums closed, clocked shift hours within the period at the worker's hourly rate.</div>
        <form id="generateForm" onsubmit="generatePayslip(event)">
            <input type="hidden" id="genUserId">
            <div class="flex gap-3">
                <div class="form-group flex-1"><div class="form-label">Period Start *</div><input type="date" class="form-input" id="genStart" required></div>
                <div class="form-group flex-1"><div class="form-label">Period End *</div><input type="date" class="form-input" id="genEnd" required></div>
            </div>
            <div class="form-group"><div class="form-label">Deductions <span class="opacity-50 font-normal">(optional flat amount)</span></div><input type="number" step="0.01" min="0" class="form-input" id="genDeductions" placeholder="0.00"></div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-save" id="generateSubmitBtn">Generate</button>
            </div>
        </form>
    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function closeModal() { document.getElementById('generateModal').classList.remove('is-open'); }

async function saveRate(userId) {
    const rate = document.getElementById(`rate-${userId}`).value;
    const res = await fetch(`{{ url('/salary/workers') }}/${userId}/rate`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify({ hourly_rate: rate }) });
    if (!res.ok) { const data = await res.json(); alert(data.message || 'Error saving rate.'); }
}

function openGenerateModal(userId, name) {
    document.getElementById('generateForm').reset();
    document.getElementById('genUserId').value = userId;
    document.getElementById('generateModalTitle').textContent = `Generate Payslip — ${name}`;
    document.getElementById('generateModal').classList.add('is-open');
}

async function generatePayslip(e) {
    e.preventDefault();
    const btn = document.getElementById('generateSubmitBtn');
    btn.disabled = true; btn.textContent = 'Generating…';
    const body = {
        user_id: document.getElementById('genUserId').value,
        period_start: document.getElementById('genStart').value,
        period_end: document.getElementById('genEnd').value,
        deductions: document.getElementById('genDeductions').value || 0,
    };
    const res = await fetch('{{ route('salary.generate') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(body) });
    const data = await res.json();
    if (res.ok) location.reload();
    else { alert(data.message || 'Error generating payslip.'); btn.disabled = false; btn.textContent = 'Generate'; }
}

async function markPaid(id) {
    const res = await fetch(`{{ url('/salary/payslips') }}/${id}/mark-paid`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    if (res.ok) location.reload(); else alert('Error marking as paid.');
}

document.querySelectorAll('.modal-overlay').forEach(el => el.addEventListener('click', e => { if (e.target === el) el.classList.remove('is-open'); }));
</script>
@endsection
