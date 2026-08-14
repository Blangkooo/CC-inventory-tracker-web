@extends('layouts.sidebar')

@section('title', 'About NITA')

@section('content')
<div class="mb-6">
    <div class="text-[22px] font-extrabold tracking-tight">About NITA</div>
    <div class="text-[13px] text-ink-2 mt-0.5">Inventory and operations tracking for multi-branch food businesses</div>
</div>

<div class="card p-6 max-w-[640px]">
    <p class="text-[13px] leading-relaxed text-ink">
        NITA helps coffee shop and food business owners run day-to-day operations across branches —
        inventory levels, recipes and pricing, worker schedules and payroll, supplier purchases,
        and the discrepancy alerts that catch stock leakage before it adds up.
    </p>
    <div class="mt-5 pt-5 border-t border-line">
        <div class="text-[11px] font-bold uppercase tracking-[.06em] opacity-50 mb-2">Modules</div>
        <div class="flex flex-wrap gap-1.5">
            @foreach (['Dashboard','Calendar','Analytics','Reports','Payments','Salary','Employees','Hiring','Legal Papers','Business','Logistics','Suppliers','Pricing','Alerts','Branches'] as $mod)
                <span class="tag tag--accent">{{ $mod }}</span>
            @endforeach
        </div>
    </div>
</div>
@endsection
