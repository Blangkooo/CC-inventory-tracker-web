@extends('layouts.sidebar')

@section('title', 'Dashboard')

@section('styles')
    /* ═══ METRICS ═══ */
    .metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }

    .m-card {
        border-radius: var(--radius); padding: 22px; position: relative; overflow: hidden;
        color: #fff; box-shadow: var(--shadow-md);
    }
    .m-card.grad-1 { background: linear-gradient(135deg, #e17055, #fd79a8); }
    .m-card.grad-2 { background: linear-gradient(135deg, #6c5ce7, #a29bfe); }
    .m-card.grad-3 { background: linear-gradient(135deg, #00b894, #55efc4); }
    .m-card.grad-4 { background: linear-gradient(135deg, #fdcb6e, #e17055); }

    .m-card__icon {
        width: 42px; height: 42px; border-radius: 12px;
        background: rgba(255,255,255,.2); display: flex;
        align-items: center; justify-content: center; margin-bottom: 14px;
    }
    .m-card__label { font-size: 11px; font-weight: 600; opacity: .85; text-transform: uppercase; letter-spacing: .04em; }
    .m-card__value { font-size: 28px; font-weight: 900; margin-top: 4px; letter-spacing: -.02em; }
    .m-card__sub { font-size: 11px; opacity: .75; margin-top: 6px; font-weight: 500; }
    .m-card__decor {
        position: absolute; right: -20px; bottom: -20px; width: 100px; height: 100px;
        border-radius: 50%; background: rgba(255,255,255,.08);
    }
    .m-card__decor2 {
        position: absolute; right: 30px; bottom: 30px; width: 60px; height: 60px;
        border-radius: 50%; background: rgba(255,255,255,.05);
    }

    /* ═══ GRID LAYOUTS ═══ */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
    .grid-3-1 { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px; }

    /* ═══ BRANCH STATUS ═══ */
    .branch-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .branch-item {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 12px; border-radius: 10px; font-size: 13px; font-weight: 500;
        background: rgba(0,0,0,.015); transition: background .1s;
    }
    .branch-item:hover { background: rgba(0,0,0,.03); }
    .branch-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

    /* ═══ SHIFTS ═══ */
    .shift-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 0; border-bottom: 1px solid var(--border);
    }
    .shift-item:last-child { border-bottom: none; }
    .shift-dot { width: 8px; height: 8px; border-radius: 50%; background: #00b894; flex-shrink: 0; animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }
    .shift-info { font-size: 12px; line-height: 1.5; }
    .shift-info strong { font-weight: 700; }
    .shift-info span { color: var(--text-2); }

    /* ═══ RANKINGS ═══ */
    .rank-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 0; border-bottom: 1px solid var(--border);
        font-size: 13px;
    }
    .rank-row:last-child { border-bottom: none; }
    .rank-left { display: flex; align-items: center; gap: 10px; }
    .rank-num {
        width: 24px; height: 24px; border-radius: 8px;
        background: var(--accent-light); color: var(--accent);
        font-size: 10px; font-weight: 800; display: flex;
        align-items: center; justify-content: center;
    }
    .val-green { font-weight: 700; color: #00b894; }
    .val-red { font-weight: 700; color: #d63031; }

    /* ═══ ALERT TABLE ═══ */
    .alert-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .alert-table thead th {
        text-align: left; padding: 10px 16px; font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .04em; color: var(--text-2);
        background: rgba(0,0,0,.02); border-bottom: 1px solid var(--border);
    }
    .alert-table td { padding: 12px 16px; border-bottom: 1px solid var(--border); }
    .alert-table tr:last-child td { border-bottom: none; }

    .sev-badge {
        padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase;
    }
    .sev-high { background: rgba(214,48,49,.08); color: #d63031; }
    .sev-medium { background: rgba(225,112,85,.08); color: #e17055; }
    .sev-low { background: rgba(108,92,231,.08); color: #6c5ce7; }

    /* ═══ ALERT BREAKDOWN ═══ */
    .breakdown-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 0; border-bottom: 1px solid var(--border);
    }
    .breakdown-row:last-child { border-bottom: none; }
    .breakdown-row span:last-child { font-size: 20px; font-weight: 900; }

    /* ═══ QUICK STATS ROW ═══ */
    .quick-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
    .qs-card {
        background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow);
        border: 1px solid var(--border); padding: 18px 20px;
        display: flex; align-items: center; gap: 14px;
    }
    .qs-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .qs-icon.purple { background: rgba(108,92,231,.08); color: #6c5ce7; }
    .qs-icon.teal { background: rgba(0,184,148,.08); color: #00b894; }
    .qs-icon.rose { background: rgba(253,121,168,.08); color: #fd79a8; }
    .qs-value { font-size: 22px; font-weight: 900; }
    .qs-label { font-size: 11px; color: var(--text-2); font-weight: 600; }

    @media (max-width: 1100px) {
        .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        .grid-2, .grid-3-1 { grid-template-columns: 1fr; }
        .branch-grid { grid-template-columns: repeat(2, 1fr); }
        .quick-stats { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .metrics-grid { grid-template-columns: 1fr; }
    }
@endsection

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
@endphp

<div class="content-header">
    <h1 class="content-title">Dashboard</h1>
    <span class="content-date">{{ now()->format('l, F j, Y') }}</span>
</div>

{{-- Gradient Metric Cards --}}
<div class="metrics-grid">
    <div class="m-card grad-1">
        <div class="m-card__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="m-card__label">Annual Revenue</div>
        <div class="m-card__value">{!! $fmt($annual_revenue) !!}</div>
        <div class="m-card__sub">Today: {!! $fmt($total_sales) !!}</div>
        <div class="m-card__decor"></div><div class="m-card__decor2"></div>
    </div>
    <div class="m-card grad-2">
        <div class="m-card__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div class="m-card__label">Value Saved</div>
        <div class="m-card__value">{!! $fmt($value_saved) !!}</div>
        <div class="m-card__sub">From reviewed alerts</div>
        <div class="m-card__decor"></div><div class="m-card__decor2"></div>
    </div>
    <div class="m-card grad-3">
        <div class="m-card__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div class="m-card__label">Leakage Rate</div>
        <div class="m-card__value">{{ number_format($leakage_pct, 1) }}%</div>
        <div class="m-card__sub">Based on shift variances</div>
        <div class="m-card__decor"></div><div class="m-card__decor2"></div>
    </div>
    <div class="m-card grad-4">
        <div class="m-card__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <div class="m-card__label">Pending Alerts</div>
        <div class="m-card__value">{{ $pending_alerts }}</div>
        <div class="m-card__sub">{{ $low_stock_count }} low stock items</div>
        <div class="m-card__decor"></div><div class="m-card__decor2"></div>
    </div>
</div>

{{-- Quick Stats --}}
<div class="quick-stats">
    <div class="qs-card">
        <div class="qs-icon purple">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/></svg>
        </div>
        <div>
            <div class="qs-value">{{ $total_branches }}</div>
            <div class="qs-label">Total Branches</div>
        </div>
    </div>
    <div class="qs-card">
        <div class="qs-icon teal">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
            <div class="qs-value">{{ $ongoing_shifts->count() }}</div>
            <div class="qs-label">Active Shifts</div>
        </div>
    </div>
    <div class="qs-card">
        <div class="qs-icon rose">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <div>
            <div class="qs-value">{{ $low_stock_count }}</div>
            <div class="qs-label">Low Stock Items</div>
        </div>
    </div>
</div>

{{-- Branch Status + Open Shifts --}}
<div class="grid-3-1">
    <div class="card">
        <div class="card__head">
            <span class="card__title">Branch Status — Today</span>
            <span style="font-size:12px;font-weight:600;color:var(--text-2);">{{ $total_branches }} branches</span>
        </div>
        <div class="card__body">
            @if ($branches_with_sales->isEmpty())
                <div class="empty-state">No branch data yet.</div>
            @else
                <div class="branch-grid">
                    @foreach ($branches_with_sales as $b)
                        @php
                            $flagMatch = $recent_flags->first(fn($f) => $f->branch?->name === $b['name']);
                            $branchSev = $flagMatch ? ($sevMap[$flagMatch->branch_id] ?? null) : null;
                            $pip = $branchSev ? ($sevColors[$branchSev]) : ($b['has_sales'] ? '#00b894' : 'rgba(0,0,0,.12)');
                        @endphp
                        <div class="branch-item">
                            <span class="branch-dot" style="background:{{ $pip }}"></span>
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
                <div class="shift-item">
                    <span class="shift-dot"></span>
                    <div class="shift-info">
                        <strong>{{ $shift->branch->name ?? '—' }}</strong><br>
                        <span>{{ $shift->user->name ?? '—' }} · {{ $shift->shift_start->format('g:i A') }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state">No active shifts</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Rankings --}}
<div class="grid-2">
    <div class="card">
        <div class="card__head"><span class="card__title">Top Earners — {{ now()->year }}</span></div>
        <div class="card__body">
            @forelse ($top_earners as $i => $branch)
                <div class="rank-row">
                    <div class="rank-left">
                        <span class="rank-num">{{ $i + 1 }}</span>
                        <span style="font-weight:500">{{ $branch->name }}</span>
                    </div>
                    <span class="val-green">&#8369;{{ number_format($branch->revenue ?? 0) }}</span>
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
                <div class="rank-row">
                    <div class="rank-left">
                        <span class="rank-num">{{ $i + 1 }}</span>
                        <span style="font-weight:500">{{ $item['name'] }}</span>
                    </div>
                    <span class="{{ $item['leak'] > 0 ? 'val-red' : 'val-green' }}">
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
<div class="card" style="margin-bottom:24px;">
    <div class="card__head">
        <span class="card__title">Recent Pending Alerts</span>
        <a href="{{ route('alerts') }}" class="card__link">View All &rarr;</a>
    </div>
    <div style="overflow-x:auto;">
        <table class="alert-table">
            <thead>
                <tr><th>Branch</th><th>Ingredient</th><th>Severity</th><th>Variance</th><th>Date</th></tr>
            </thead>
            <tbody>
                @foreach ($recent_flags as $flag)
                <tr>
                    <td style="font-weight:600">{{ $flag->branch->name ?? '—' }}</td>
                    <td>{{ $flag->ingredient->name ?? '—' }}</td>
                    <td><span class="sev-badge sev-{{ $flag->severity }}">{{ ucfirst($flag->severity) }}</span></td>
                    <td class="val-red">{{ $flag->variance !== null ? '−' . number_format(abs($flag->variance), 2) : '—' }}</td>
                    <td style="color:var(--text-2)">{{ $flag->created_at->format('M d, g:iA') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Alert Breakdown --}}
<div class="card" style="max-width:380px;">
    <div class="card__head"><span class="card__title">Alert Breakdown</span></div>
    <div class="card__body">
        @foreach (['high' => '#d63031', 'medium' => '#e17055', 'low' => '#6c5ce7'] as $sev => $color)
            <div class="breakdown-row">
                <span style="font-size:13px;font-weight:500">{{ ucfirst($sev) }}</span>
                <span style="color:{{ $color }}">{{ $flag_counts[$sev] ?? 0 }}</span>
            </div>
        @endforeach
    </div>
</div>
@endsection
