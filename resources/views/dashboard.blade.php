@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    /* ══ DASHBOARD-SPECIFIC STYLES ════════════════════════════════════ */
    .main-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 16px;
    }

    @media (max-width: 1100px) {
        .main-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .main-grid { gap: 12px; }
        .group-card__section { padding: 14px 16px; }
    }

    /* ══ GROUPED CARD ══════════════════════════════════════════════════ */
    .group-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }

    .group-card__section {
        padding: 18px 20px;
    }

    .group-card__section + .group-card__section {
        border-top: 1px solid var(--border);
    }

    /* ══ STAT ITEMS ════════════════════════════════════════════════════ */
    .stats-row-inline {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0;
    }

    .stat-item {
        padding: 20px 24px;
        text-align: center;
    }

    .stat-item + .stat-item {
        border-left: 1px solid var(--border);
    }

    .stat-item__label { font-size: 13px; font-weight: 600; opacity: .6; margin-bottom: 8px; }
    .stat-item__value { font-size: 42px; font-weight: 800; line-height: 1.1; margin-bottom: 8px; color: var(--terra); }
    .stat-item__trend { font-size: 12px; font-weight: 600; }
    .stat-item__trend.up { color: #16a34a; }
    .stat-item__trend.down { color: #dc2626; }
    .stat-item__trend .vs { opacity: .5; font-weight: 400; }

    /* ══ CHART CARD ════════════════════════════════════════════════════ */
    .chart-card { padding: 18px 20px; }
    .chart-card__title { font-size: 13px; font-weight: 700; margin-bottom: 4px; }
    .chart-card__total { font-size: 32px; font-weight: 800; margin-bottom: 2px; }
    .chart-card__trend { font-size: 11px; font-weight: 600; color: #16a34a; margin-bottom: 14px; }
    .chart-card__trend .vs { opacity: .5; font-weight: 400; }

    .bar-chart { display: flex; align-items: flex-end; gap: 10px; height: 90px; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .bar {
        width: 100%; border-radius: 4px 4px 0 0;
        background: linear-gradient(to top, var(--terra-dk), var(--terra-lt));
        min-height: 4px;
    }
    .bar-col__label { font-size: 9px; font-weight: 600; opacity: .5; white-space: nowrap; }

    /* ══ FLAG SUMMARY ══════════════════════════════════════════════════ */
    .flag-section__head {
        display: flex; align-items: center; gap: 16px;
        margin-bottom: 12px;
    }
    .flag-section__title { font-size: 13px; font-weight: 700; }

    .flag-legend { display: flex; gap: 14px; }
    .flag-legend__item { display: flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 500; opacity: .6; }
    .flag-legend__dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

    .flag-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px 12px; }
    .flag-row {
        display: flex; align-items: center; gap: 8px;
        padding: 5px 6px; border-radius: 6px; font-size: 12px; font-weight: 500;
    }
    .flag-pip { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }

    /* ══ PERFORMANCE HEADER ════════════════════════════════════════════ */
    .perf-header {
        display: flex; align-items: baseline; gap: 10px;
        margin: 20px 0 14px;
    }
    .perf-header__title { font-size: 18px; font-weight: 800; }
    .perf-header__sep { color: var(--border); font-weight: 300; }
    .perf-header__month { font-size: 18px; font-weight: 800; color: var(--terra); }

    /* ══ PERF ROW ══════════════════════════════════════════════════════ */
    .perf-row-inline {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0;
    }

    .perf-item { padding: 16px 18px; }
    .perf-item + .perf-item { border-left: 1px solid var(--border); }

    .perf-item__label { font-size: 11px; font-weight: 600; opacity: .55; margin-bottom: 6px; }
    .perf-item__value { font-size: 28px; font-weight: 800; line-height: 1.1; margin-bottom: 4px; }
    .perf-item__trend { font-size: 11px; font-weight: 600; }
    .perf-item__trend.up { color: #16a34a; }
    .perf-item__trend.down { color: #dc2626; }
    .perf-item__trend .vs { opacity: .5; font-weight: 400; }

    .earner-list { list-style: none; }
    .earner-list__item {
        padding: 3px 0; font-size: 12px; font-weight: 500;
        display: flex; align-items: center; gap: 5px;
    }
    .earner-list__num { font-weight: 700; opacity: .35; min-width: 14px; }

    .goal-wrap { display: flex; flex-direction: column; align-items: center; text-align: center; }
    .goal-circle {
        width: 80px; height: 80px; border-radius: 50%;
        position: relative; margin-bottom: 8px;
        background: conic-gradient(
            var(--terra) 0deg,
            var(--terra) calc(var(--pct, 0) * 3.6deg),
            var(--cream-2) calc(var(--pct, 0) * 3.6deg),
            var(--cream-2) 360deg
        );
    }
    .goal-circle::after {
        content: ''; position: absolute; inset: 12px; border-radius: 50%; background: #fff;
    }
    .goal-circle__label {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 800; z-index: 1;
    }
    .goal-target { font-size: 10px; opacity: .5; font-weight: 500; }

    /* ══ KPI PERFORMANCE ═══════════════════════════════════════════════ */
    .kpi-card__head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 4px;
    }
    .kpi-card__title { font-size: 13px; font-weight: 700; }
    .kpi-card__year { font-size: 12px; font-weight: 500; opacity: .4; margin-left: 6px; }
    .kpi-card__trend { font-size: 11px; font-weight: 600; color: #16a34a; margin-bottom: 12px; }
    .kpi-card__trend .vs { opacity: .5; font-weight: 400; }

    .kpi-dropdown {
        padding: 4px 10px; border-radius: 6px;
        border: 1.5px solid var(--border); background: #fff;
        font-size: 11px; font-weight: 600; color: var(--brown);
        font-family: var(--font); cursor: pointer;
    }

    .kpi-bars { display: flex; align-items: flex-end; gap: 5px; height: 120px; }
    .kpi-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; }
    .kpi-bar {
        width: 100%; border-radius: 3px 3px 0 0;
        background: linear-gradient(to top, var(--terra-dk), var(--terra-lt));
        min-height: 2px; margin-top: auto;
    }
    .kpi-bar-col__label { font-size: 8px; font-weight: 600; opacity: .4; white-space: nowrap; }

    @media (max-width: 1100px) {
        .stats-row-inline, .perf-row-inline { grid-template-columns: 1fr 1fr; }
        .flag-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 880px) {
        .stats-row-inline, .perf-row-inline { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .stat-item__value { font-size: 28px; }
        .perf-item__value { font-size: 22px; }
        .chart-card__total { font-size: 24px; }
        .flag-legend { flex-wrap: wrap; gap: 8px; }
    }
</style>

@php
    $fmt = fn($n) => $n >= 1_000_000
        ? '&#8369;' . number_format($n / 1_000_000, 1) . 'M'
        : ($n >= 1_000 ? '&#8369;' . number_format($n / 1_000, 1) . 'k' : '&#8369;' . number_format($n));

    $pctChange = function ($current, $previous) {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 0);
    };

    $sevMap   = [];
    $sevOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
    foreach ($recent_flags as $f) {
        $bid = $f->branch_id;
        if (!isset($sevMap[$bid]) || ($sevOrder[$f->severity] ?? 0) > ($sevOrder[$sevMap[$bid]] ?? 0)) {
            $sevMap[$bid] = $f->severity;
        }
    }
    $sevPipColors = ['high' => '#dc2626', 'medium' => '#f97316', 'low' => '#eab308'];

    $revenuePct  = $pctChange($annual_revenue, $lastMonthRevenue);
    $alertsPct   = $pctChange($pending_alerts, $lastMonthAlerts);
    $lowStockPct = $pctChange($low_stock_count, $lastMonthLowStock);

    $maxMonthly = max(array_values($monthly_sales ?: [1]));

    $revenueGoal    = max($annual_revenue * 1.2, 100_000);
    $revenueGoalPct = min(100, ($annual_revenue / $revenueGoal) * 100);

    $savedGoal    = max($value_saved * 1.6, 10_000);
    $savedGoalPct = min(100, ($value_saved / $savedGoal) * 100);

    $maxBranchSale = max(array_merge($branches_with_sales->pluck('today_sales')->toArray(), [1]));
@endphp

<div class="main-grid">
    {{-- ═══ LEFT COLUMN ═══ --}}
    <div class="left-col">

        {{-- ── STATS ROW ── --}}
        <div class="group-card" style="margin-bottom:16px;">
            <div class="group-card__section" style="padding:0;">
                <div class="stats-row-inline">
                    <div class="stat-item">
                        <div class="stat-item__label">Total Branches</div>
                        <div class="stat-item__value">{{ $total_branches }}</div>
                        @if ($lastMonthRevenue > 0)
                            <div class="stat-item__trend {{ $revenuePct >= 0 ? 'up' : 'down' }}">
                                {{ abs($revenuePct) }}%
                                {{ $revenuePct >= 0 ? '&#8593;' : '&#8595;' }}
                                <span class="vs">vs last month</span>
                            </div>
                        @endif
                    </div>
                    <div class="stat-item">
                        <div class="stat-item__label">Pending Alerts</div>
                        <div class="stat-item__value">{{ $pending_alerts }}</div>
                        <div class="stat-item__trend {{ $alertsPct <= 0 ? 'up' : 'down' }}">
                            {{ abs($alertsPct) }}%
                            {{ $alertsPct <= 0 ? '&#8593;' : '&#8595;' }}
                            <span class="vs">vs last month</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-item__label">Low Stock Items</div>
                        <div class="stat-item__value">{{ $low_stock_count }}</div>
                        @if ($lastMonthLowStock > 0)
                            <div class="stat-item__trend {{ $lowStockPct <= 0 ? 'up' : 'down' }}">
                                {{ abs($lowStockPct) }}%
                                {{ $lowStockPct <= 0 ? '&#8593;' : '&#8595;' }}
                                <span class="vs">vs last month</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── FLAG SUMMARY ── --}}
        <div class="group-card" style="margin-bottom:16px;">
            <div class="group-card__section">
                <div class="flag-section__head">
                    <span class="flag-section__title">Recent Flag Summary</span>
                    <div class="flag-legend">
                        <span class="flag-legend__item"><span class="flag-legend__dot" style="background:#eab308"></span>Low Importance</span>
                        <span class="flag-legend__item"><span class="flag-legend__dot" style="background:#f97316"></span>Moderate Importance</span>
                        <span class="flag-legend__item"><span class="flag-legend__dot" style="background:#dc2626"></span>High Importance</span>
                    </div>
                </div>
                @if ($branches_with_sales->isEmpty())
                    <div style="font-size:12px;opacity:.35;text-align:center;padding:8px 0;">No flag data yet.</div>
                @else
                    <div class="flag-grid">
                        @foreach ($branches_with_sales as $b)
                            @php
                                $flagMatch = $recent_flags->first(fn($f) => $f->branch?->name === $b['name']);
                                $branchSev = $flagMatch ? ($sevMap[$flagMatch->branch_id] ?? null) : null;
                                $pip = $branchSev ? ($sevPipColors[$branchSev] ?? '#eab308') : 'rgba(28,25,23,.15)';
                            @endphp
                            <div class="flag-row">
                                <span class="flag-pip" style="background:{{ $pip }}"></span>
                                <span>{{ $b['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ── PERFORMANCE SUMMARY ── --}}
        <div class="perf-header">
            <span class="perf-header__title">Performance Summary</span>
            <span class="perf-header__sep">|</span>
            <span class="perf-header__month">{{ now()->format('F') }}</span>
        </div>

        {{-- Revenue + Top Earner + Goal --}}
        <div class="group-card" style="margin-bottom:16px;">
            <div class="group-card__section" style="padding:0;">
                <div class="perf-row-inline">
                    <div class="perf-item">
                        <div class="perf-item__label">Total Revenue</div>
                        <div class="perf-item__value">{!! $fmt($annual_revenue) !!}</div>
                        @if ($lastMonthRevenue > 0)
                            <div class="perf-item__trend up">
                                {{ abs($revenuePct) }}%
                                {{ $revenuePct >= 0 ? '&#8593;' : '&#8595;' }}
                                <span class="vs">vs last month</span>
                            </div>
                        @else
                            <div class="perf-item__trend" style="opacity:.4">No prior data</div>
                        @endif
                    </div>
                    <div class="perf-item">
                        <div class="perf-item__label">Top Earner</div>
                        <ol class="earner-list" style="margin-top:4px;">
                            @forelse ($top_earners as $i => $branch)
                                <li class="earner-list__item">
                                    <span class="earner-list__num">{{ $i + 1 }}.</span>
                                    {{ $branch->name }}
                                </li>
                            @empty
                                <li style="font-size:11px;opacity:.35;padding:4px 0;">No data yet</li>
                            @endforelse
                        </ol>
                    </div>
                    <div class="perf-item">
                        <div class="perf-item__label">Goal</div>
                        <div class="goal-wrap" style="margin-top:4px;">
                            <div class="goal-circle" style="--pct: {{ round($revenueGoalPct) }}">
                                <span class="goal-circle__label">{{ round($revenueGoalPct) }}%</span>
                            </div>
                            <div class="goal-target">{!! $fmt($revenueGoal) !!}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Value Saved + Least Leakage + Goal --}}
        <div class="group-card">
            <div class="group-card__section" style="padding:0;">
                <div class="perf-row-inline">
                    <div class="perf-item">
                        <div class="perf-item__label">Overall Value Saved</div>
                        <div class="perf-item__value">{!! $fmt($value_saved) !!}</div>
                        <div class="perf-item__trend {{ $leakage_pct > 0 ? 'down' : 'up' }}">
                            {{ number_format($leakage_pct, 0) }}%
                            {{ $leakage_pct > 0 ? '&#8595;' : '&#8593;' }}
                            <span class="vs">leakage vs last month</span>
                        </div>
                    </div>
                    <div class="perf-item">
                        <div class="perf-item__label">Least Leakage</div>
                        <ol class="earner-list" style="margin-top:4px;">
                            @forelse ($least_leakage as $i => $item)
                                <li class="earner-list__item">
                                    <span class="earner-list__num">{{ $i + 1 }}.</span>
                                    {{ $item['name'] }}
                                </li>
                            @empty
                                <li style="font-size:11px;opacity:.35;padding:4px 0;">No data yet</li>
                            @endforelse
                        </ol>
                    </div>
                    <div class="perf-item">
                        <div class="perf-item__label">Goal</div>
                        <div class="goal-wrap" style="margin-top:4px;">
                            <div class="goal-circle" style="--pct: {{ round($savedGoalPct) }}">
                                <span class="goal-circle__label">{{ round($savedGoalPct) }}%</span>
                            </div>
                            <div class="goal-target">{!! $fmt($savedGoal) !!} in saved value</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ RIGHT COLUMN ═══ --}}
    <div class="right-col">

        {{-- Employee Status Chart --}}
        <div class="group-card" style="margin-bottom:20px;">
            <div class="group-card__section chart-card">
                <div class="chart-card__title">Employee Status</div>
                <div class="chart-card__total">{{ $branches_with_sales->where('has_sales', true)->count() }}</div>
                <div class="chart-card__trend">
                    {{ round(($branches_with_sales->where('has_sales', true)->count() / max($total_branches, 1)) * 100) }}%
                    <span class="vs">active today</span>
                </div>
                <div class="bar-chart">
                    @forelse ($branches_with_sales->take(5) as $i => $b)
                        @php $h = $maxBranchSale > 0 ? max(8, ($b['today_sales'] / $maxBranchSale) * 100) : 8; @endphp
                        <div class="bar-col">
                            <div class="bar" style="height: {{ $h }}%"></div>
                            <div class="bar-col__label">{{ \Illuminate\Support\Str::limit($b['name'], 8) }}</div>
                        </div>
                    @empty
                        <div style="font-size:11px;opacity:.35;text-align:center;width:100%;padding:20px 0;">No branch data</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- KPI Performance --}}
        <div class="group-card">
            <div class="group-card__section">
                <div class="kpi-card__head">
                    <div>
                        <span class="kpi-card__title">KPI Performance</span>
                        <span class="kpi-card__year">{{ now()->year }}</span>
                    </div>
                    <select class="kpi-dropdown">
                        <option>Monthly</option>
                        <option>Weekly</option>
                    </select>
                </div>
                <div class="kpi-card__trend">
                    {{ abs($revenuePct) }}%
                    {{ $revenuePct >= 0 ? '&#8593;' : '&#8595;' }}
                    <span class="vs">vs last month</span>
                </div>
                <div class="kpi-bars">
                    @php
                        $monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        $currentMonth = now()->month;
                    @endphp
                    @for ($m = 1; $m <= 12; $m++)
                        @php
                            $val = $monthly_sales[$m] ?? 0;
                            $h = $maxMonthly > 0 ? max(3, ($val / $maxMonthly) * 100) : 3;
                            $isFuture = $m > $currentMonth;
                        @endphp
                        <div class="kpi-bar-col">
                            <div class="kpi-bar" style="height: {{ $isFuture ? 0 : $h }}%; {{ $isFuture ? 'opacity:.15' : '' }}"></div>
                            <div class="kpi-bar-col__label">{{ $monthLabels[$m - 1] }}</div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
