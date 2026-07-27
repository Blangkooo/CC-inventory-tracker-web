@extends('layouts.sidebar')

@section('title', 'Dashboard')

@section('content')
@php
    $fmt = fn($n) => $n >= 1_000_000
        ? '₱' . number_format($n / 1_000_000, 1) . 'M'
        : ($n >= 1_000 ? '₱' . number_format($n / 1_000, 1) . 'k' : '₱' . number_format($n));

    $sevMap = [];
    $sevOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
    foreach ($recent_flags as $f) {
        $bid = $f->branch_id;
        if (!isset($sevMap[$bid]) || ($sevOrder[$f->severity] ?? 0) > ($sevOrder[$sevMap[$bid]] ?? 0)) {
            $sevMap[$bid] = $f->severity;
        }
    }
    $sevColors = ['high' => '#d63031', 'medium' => '#e17055', 'low' => '#fdcb6e'];
    $sevBadge  = ['high' => 'badge-red', 'medium' => 'badge-amber', 'low' => 'badge-blue'];

    $metrics = [
        ['from-accent to-pink',   'Annual Revenue', $fmt($annual_revenue),                 'Today: ' . $fmt($total_sales)],
        ['from-blue to-[#a29bfe]', 'Value Saved',    $fmt($value_saved),                    'From reviewed alerts'],
        ['from-green to-[#55efc4]', 'Leakage Rate',  number_format($leakage_pct, 1) . '%',  'Based on shift variances'],
        ['from-orange to-accent', 'Pending Alerts', $pending_alerts,                        $low_stock_count . ' low stock items'],
    ];
@endphp

<div class="content-header">
    <h1 class="content-title">Dashboard</h1>
    <span class="content-date">{{ now()->format('l, F j, Y') }}</span>
</div>

{{-- Gradient Metric Cards --}}
<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
    @foreach ($metrics as $i => [$gradient, $label, $value, $sub])
        <div class="relative overflow-hidden rounded-card p-[22px] text-white shadow-card-md bg-linear-to-br {{ $gradient }}">
            <div class="mb-3.5 flex h-[42px] w-[42px] items-center justify-center rounded-xl bg-white/20">
                @switch($i)
                    @case(0)
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        @break
                    @case(1)
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        @break
                    @case(2)
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        @break
                    @default
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                @endswitch
            </div>
            <div class="text-[11px] font-semibold uppercase tracking-[.04em] opacity-85">{{ $label }}</div>
            <div class="mt-1 text-[28px] font-black tracking-[-.02em]">{!! $value !!}</div>
            <div class="mt-1.5 text-[11px] font-medium opacity-75">{!! $sub !!}</div>
            <div class="pointer-events-none absolute -right-5 -bottom-5 h-25 w-25 rounded-full bg-white/8"></div>
            <div class="pointer-events-none absolute right-[30px] bottom-[30px] h-15 w-15 rounded-full bg-white/5"></div>
        </div>
    @endforeach
</div>

{{-- Quick Stats --}}
@php
    $quickStats = [
        ['bg-blue/8 text-blue',   $total_branches,           'Total Branches', '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/>'],
        ['bg-green/8 text-green', $ongoing_shifts->count(),  'Active Shifts',  '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>'],
        ['bg-pink/8 text-pink',   $low_stock_count,          'Low Stock Items', '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>'],
    ];
@endphp
<div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
    @foreach ($quickStats as [$iconClass, $value, $label, $path])
        <div class="flex items-center gap-3.5 rounded-card border border-line bg-card px-5 py-[18px] shadow-card">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $iconClass }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">{!! $path !!}</svg>
            </div>
            <div>
                <div class="text-[22px] font-black">{{ $value }}</div>
                <div class="text-[11px] font-semibold text-ink-2">{{ $label }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- Branch Status + Open Shifts --}}
<div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-[2fr_1fr]">
    <div class="card">
        <div class="card__head">
            <span class="card__title">Branch Status — Today</span>
            <span class="text-xs font-semibold text-ink-2">{{ $total_branches }} branches</span>
        </div>
        <div class="card__body">
            @if ($branches_with_sales->isEmpty())
                <div class="empty-state">No branch data yet.</div>
            @else
                <div class="grid grid-cols-2 gap-2 lg:grid-cols-3">
                    @foreach ($branches_with_sales as $b)
                        @php
                            $flagMatch = $recent_flags->first(fn($f) => $f->branch?->name === $b['name']);
                            $branchSev = $flagMatch ? ($sevMap[$flagMatch->branch_id] ?? null) : null;
                            $pip = $branchSev ? ($sevColors[$branchSev]) : ($b['has_sales'] ? '#00b894' : 'rgba(0,0,0,.12)');
                        @endphp
                        <div class="flex items-center gap-2 rounded-[10px] bg-black/[.015] px-3 py-2.5 text-[13px] font-medium transition-colors hover:bg-black/[.03]">
                            <span class="h-2 w-2 shrink-0 rounded-full" style="background:{{ $pip }}"></span>
                            <span>{{ $b['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card__head"><span class="card__title">Open Shifts</span></div>
        <div class="card__body">
            @forelse ($ongoing_shifts as $shift)
                <div class="flex items-center gap-3 border-b border-line py-2.5 last:border-b-0">
                    <span class="h-2 w-2 shrink-0 animate-pulse rounded-full bg-green"></span>
                    <div class="text-xs/relaxed">
                        <strong class="font-bold">{{ $shift->branch->name ?? '—' }}</strong><br>
                        <span class="text-ink-2">{{ $shift->user->name ?? '—' }} · {{ $shift->shift_start->format('g:i A') }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state">No active shifts</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Rankings --}}
<div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="card">
        <div class="card__head"><span class="card__title">Top Earners — {{ now()->year }}</span></div>
        <div class="card__body">
            @forelse ($top_earners as $i => $branch)
                <div class="flex items-center justify-between border-b border-line py-2.5 text-[13px] last:border-b-0">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-accent-light text-[10px] font-extrabold text-accent">{{ $i + 1 }}</span>
                        <span class="font-medium">{{ $branch->name }}</span>
                    </div>
                    <span class="font-bold text-green">&#8369;{{ number_format($branch->revenue ?? 0) }}</span>
                </div>
            @empty
                <div class="empty-state">No transaction data yet.</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card__head"><span class="card__title">Least Leakage (Units)</span></div>
        <div class="card__body">
            @forelse ($least_leakage->take(6) as $i => $item)
                <div class="flex items-center justify-between border-b border-line py-2.5 text-[13px] last:border-b-0">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-accent-light text-[10px] font-extrabold text-accent">{{ $i + 1 }}</span>
                        <span class="font-medium">{{ $item['name'] }}</span>
                    </div>
                    <span class="font-bold {{ $item['leak'] > 0 ? 'text-accent-2' : 'text-green' }}">
                        {{ $item['leak'] > 0 ? '−' . number_format($item['leak'], 2) . 'u' : 'Clean' }}
                    </span>
                </div>
            @empty
                <div class="empty-state">No leakage data yet.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Recent Alerts --}}
@if ($recent_flags->isNotEmpty())
<div class="card mb-6">
    <div class="card__head">
        <span class="card__title">Recent Pending Alerts</span>
        <a href="{{ route('alerts') }}" class="card__link">View All &rarr;</a>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr><th>Branch</th><th>Ingredient</th><th>Severity</th><th>Variance</th><th>Date</th></tr>
            </thead>
            <tbody>
                @foreach ($recent_flags as $flag)
                <tr>
                    <td class="cell-primary">{{ $flag->branch->name ?? '—' }}</td>
                    <td>{{ $flag->ingredient->name ?? '—' }}</td>
                    <td><span class="badge {{ $sevBadge[$flag->severity] ?? 'badge-gray' }}">{{ ucfirst($flag->severity) }}</span></td>
                    <td class="variance-cell">{{ $flag->variance !== null ? '−' . number_format(abs($flag->variance), 2) : '—' }}</td>
                    <td class="text-ink-2">{{ $flag->created_at->format('M d, g:iA') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Alert Breakdown --}}
<div class="card max-w-[380px]">
    <div class="card__head"><span class="card__title">Alert Breakdown</span></div>
    <div class="card__body">
        @foreach (['high' => 'text-accent-2', 'medium' => 'text-accent', 'low' => 'text-blue'] as $sev => $textColor)
            <div class="flex items-center justify-between border-b border-line py-3 last:border-b-0">
                <span class="text-[13px] font-medium">{{ ucfirst($sev) }}</span>
                <span class="text-xl font-black {{ $textColor }}">{{ $flag_counts[$sev] ?? 0 }}</span>
            </div>
        @endforeach
    </div>
</div>
@endsection
