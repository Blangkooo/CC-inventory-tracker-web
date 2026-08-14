@extends('layouts.sidebar')

@section('title', 'Payslip')

@section('content')
<div class="mb-6">
    <a href="{{ route('salary.index') }}" class="text-[12px] text-ink-2 no-underline hover:text-accent">&larr; Salary</a>
    <div class="text-[22px] font-extrabold tracking-tight mt-1">Payslip</div>
</div>

<div class="ncard max-w-[560px]">
    <div class="flex items-center justify-between mb-5 pb-4 border-b border-line">
        <div>
            <div class="text-lg font-extrabold">{{ $payslip->user?->name ?? '—' }}</div>
            <div class="text-xs text-ink-2 mt-0.5">{{ $payslip->branch?->name ?? '—' }}</div>
        </div>
        <span class="{{ $payslip->status === 'paid' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($payslip->status) }}</span>
    </div>

    <div class="text-[12px] text-ink-2 mb-4">
        Pay period: <strong>{{ $payslip->period_start->format('M d, Y') }} &ndash; {{ $payslip->period_end->format('M d, Y') }}</strong>
        @if ($payslip->paid_at)
            <br>Paid on: <strong>{{ $payslip->paid_at->format('M d, Y') }}</strong>
        @endif
    </div>

    <div class="flex flex-col gap-2 text-[13px]">
        <div class="flex justify-between py-1.5 border-b border-[rgba(92,45,27,.06)]"><span>Hourly Rate</span><span class="font-semibold">&#8369;{{ number_format($payslip->hourly_rate, 2) }}</span></div>
        <div class="flex justify-between py-1.5 border-b border-[rgba(92,45,27,.06)]"><span>Total Hours</span><span class="font-semibold">{{ $payslip->total_hours }}h</span></div>
        <div class="flex justify-between py-1.5 border-b border-[rgba(92,45,27,.06)]"><span>Gross Pay</span><span class="font-semibold">&#8369;{{ number_format($payslip->gross_pay, 2) }}</span></div>
        <div class="flex justify-between py-1.5 border-b border-[rgba(92,45,27,.06)]"><span>Deductions</span><span class="font-semibold text-red-600">&minus;&#8369;{{ number_format($payslip->deductions, 2) }}</span></div>
        <div class="flex justify-between pt-3 font-extrabold text-base"><span>Net Pay</span><span class="text-accent">&#8369;{{ number_format($payslip->net_pay, 2) }}</span></div>
    </div>
</div>
@endsection
