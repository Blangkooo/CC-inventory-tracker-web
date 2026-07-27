@extends('layouts.sidebar')

@section('title', 'Dashboard')

@section('content')
@php
    $peso = fn ($n) => '₱' . number_format((float) $n, 2);

    // ── Flag summary: worst pending severity per branch ─────────────────
    $sevRank = ['low' => 1, 'medium' => 2, 'high' => 3];
    $sevTone = ['high' => 'high', 'medium' => 'med', 'low' => 'low'];
    $flagged = [];
    foreach ($recent_flags as $f) {
        $name = $f->branch->name ?? 'Unassigned';
        $sev  = $f->severity ?? 'low';
        if (! isset($flagged[$name]) || ($sevRank[$sev] ?? 0) > ($sevRank[$flagged[$name]] ?? 0)) {
            $flagged[$name] = $sev;
        }
    }

    // ── Rankings ────────────────────────────────────────────────────────
    $earners  = $top_earners->filter(fn ($b) => (float) ($b->revenue ?? 0) > 0)->values();
    $leakRows = collect($least_leakage);

    // ── KPI chart ───────────────────────────────────────────────────────
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $series = collect($monthly_revenue)->values();
    $peak   = max((float) $series->max(), 1);
    $bestIx = $series->search($series->max());

    // ── Alert severity split, drives the status chart ───────────────────
    $sevBars = [
        'High'     => (int) ($flag_counts['high'] ?? 0),
        'Moderate' => (int) ($flag_counts['medium'] ?? 0),
        'Low'      => (int) ($flag_counts['low'] ?? 0),
    ];
    $sevPeak = max(max($sevBars), 1);

    $circumference = 2 * M_PI * 42;
@endphp

{{-- ══ Headline figures ══ --}}
<div class="ncard ncard--split mb-4 grid-cols-1 sm:grid-cols-3">
    <div>
        <div class="nstat__label">Total Branches</div>
        <div class="nstat__value">{{ $total_branches }}</div>
        <span class="trend__note ml-0">{{ $branches_with_sales->where('has_sales', true)->count() }} trading today</span>
    </div>
    <div>
        <div class="nstat__label">Pending Alerts</div>
        <div class="nstat__value">{{ $pending_alerts }}</div>
        @if ($delta_alerts)
            {{-- Fewer new alerts than last month is the good direction. --}}
            <span class="trend trend--{{ $delta_alerts['direction'] === 'up' ? 'down' : 'up' }}">
                {{ $delta_alerts['pct'] }}%{{ $delta_alerts['direction'] === 'up' ? '↑' : '↓' }}
            </span>
            <span class="trend__note">vs last month</span>
        @else
            <span class="trend__note ml-0">no prior month to compare</span>
        @endif
    </div>
    <div>
        <div class="nstat__label">Low Stock Items</div>
        <div class="nstat__value">{{ $low_stock_count }}</div>
        <span class="trend__note ml-0">at or below threshold</span>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-[1.35fr_1fr]">

    {{-- ══════════ Left column ══════════ --}}
    <div class="flex flex-col gap-4">

        {{-- Recent Flag Summary --}}
        <div class="ncard">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <span class="text-[15px] font-extrabold text-accent">Recent Flag Summary</span>
                <span class="legend">
                    <span class="dot-item dot-item--low">Low Importance</span>
                    <span class="dot-item dot-item--med">Moderate Importance</span>
                    <span class="dot-item dot-item--high">High Importance</span>
                </span>
            </div>

            @if (empty($flagged))
                <div class="all-clear">No pending flags — every branch is reconciled.</div>
            @else
                <div class="grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($flagged as $branchName => $sev)
                        <a href="{{ route('alerts') }}"
                           class="flex items-center gap-2 text-[13px] font-semibold hover:text-accent">
                            {{ $branchName }}
                            <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full"
                                  style="background: var(--color-sev-{{ $sevTone[$sev] ?? 'low' }})"></span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="nsection">
            Performance Summary <span class="nsection__pipe">|</span> {{ now()->format('F') }}
        </div>

        {{-- Total Revenue --}}
        <div class="ncard">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-[1.1fr_1fr_auto]">
                <div>
                    <div class="nstat__label">Total Revenue</div>
                    <div class="nstat__value">{{ $peso($annual_revenue) }}</div>
                    @if ($delta_revenue)
                        <span class="trend trend--{{ $delta_revenue['direction'] }}">
                            {{ $delta_revenue['pct'] }}%{{ $delta_revenue['direction'] === 'up' ? '↑' : '↓' }}
                        </span>
                        <span class="trend__note">vs last month</span>
                    @else
                        <span class="trend__note ml-0">{{ $peso($revenue_this_month) }} this month</span>
                    @endif
                </div>

                <div class="md:border-l md:border-line md:pl-4">
                    <div class="nstat__label mb-1.5">Top Earner</div>
                    @if ($earners->isEmpty())
                        <div class="text-[12.5px] text-ink-3">No revenue recorded yet.</div>
                    @else
                        <div class="rank-list">
                            @foreach ($earners as $b)
                                <div class="rank-list__item">
                                    <span class="flex-1">{{ $b->name }}</span>
                                    <span class="font-semibold text-accent">{{ $peso($b->revenue) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex items-center md:border-l md:border-line md:pl-4">
                    <div class="goal">
                        <div class="nstat__label">Share</div>
                        @php $topShare = $annual_revenue > 0 && $earners->isNotEmpty() ? (float) $earners->first()->revenue / $annual_revenue : 0; @endphp
                        <svg viewBox="0 0 100 100" class="h-[86px] w-[86px] -rotate-90">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-cream)" stroke-width="16"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-accent)" stroke-width="16"
                                    stroke-dasharray="{{ $topShare * $circumference }} {{ $circumference }}"/>
                        </svg>
                        <div class="goal__pct">{{ round($topShare * 100) }}%</div>
                        <div class="goal__target">from {{ $earners->first()->name ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Overall Value Saved --}}
        <div class="ncard">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-[1.1fr_1fr_auto]">
                <div>
                    <div class="nstat__label">Overall Value Saved</div>
                    <div class="nstat__value">{{ $peso($value_saved) }}</div>
                    @if ($delta_leakage)
                        {{-- Less leakage month-over-month is the good direction. --}}
                        <span class="trend trend--{{ $delta_leakage['direction'] === 'up' ? 'down' : 'up' }}">
                            {{ $delta_leakage['pct'] }}%{{ $delta_leakage['direction'] === 'up' ? '↑' : '↓' }}
                        </span>
                        <span class="trend__note">leakage vs last month</span>
                    @else
                        <span class="trend__note ml-0">from reviewed alerts</span>
                    @endif
                </div>

                <div class="md:border-l md:border-line md:pl-4">
                    <div class="nstat__label mb-1.5">Least Leakage</div>
                    @if ($leakRows->isEmpty())
                        <div class="text-[12.5px] text-ink-3">No branches to compare yet.</div>
                    @else
                        <div class="rank-list">
                            @foreach ($leakRows->take(6) as $row)
                                <div class="rank-list__item">
                                    <span class="flex-1">{{ $row['name'] }}</span>
                                    <span class="font-semibold {{ $row['leak'] > 0 ? 'text-accent-2' : 'text-green' }}">
                                        {{ $row['leak'] > 0 ? number_format($row['leak'], 0) . 'u' : 'Clean' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex items-center md:border-l md:border-line md:pl-4">
                    @php
                        $cleanCount = $leakRows->where('leak', '<=', 0)->count();
                        $cleanShare = $leakRows->count() > 0 ? $cleanCount / $leakRows->count() : 0;
                    @endphp
                    <div class="goal">
                        <div class="nstat__label">Clean</div>
                        <svg viewBox="0 0 100 100" class="h-[86px] w-[86px] -rotate-90">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-cream)" stroke-width="16"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-accent)" stroke-width="16"
                                    stroke-dasharray="{{ $cleanShare * $circumference }} {{ $circumference }}"/>
                        </svg>
                        <div class="goal__pct">{{ round($cleanShare * 100) }}%</div>
                        <div class="goal__target">{{ $cleanCount }}/{{ $leakRows->count() }} branches</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ Right column ══════════ --}}
    <div class="flex flex-col gap-4">

        {{-- Alert Status --}}
        <div class="ncard">
            <div class="flex items-start justify-between">
                <span class="text-[15px] font-extrabold">Alert Status</span>
                <div class="text-right">
                    <div class="text-[30px] font-extrabold leading-none text-accent">{{ array_sum($sevBars) }}</div>
                    <div class="text-[10px] leading-tight text-ink-3">pending across<br>all branches</div>
                </div>
            </div>

            @if (array_sum($sevBars) === 0)
                <div class="all-clear">No pending alerts.</div>
            @else
                <div class="mt-4 flex h-[130px] items-end gap-4 px-2">
                    @foreach ($sevBars as $label => $count)
                        <div class="flex flex-1 flex-col items-center gap-2">
                            <div class="gbar w-full" style="height: {{ max(4, ($count / $sevPeak) * 110) }}px" title="{{ $count }}"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-4 px-2 text-center text-[11.5px] font-semibold text-ink-2">
                    @foreach ($sevBars as $label => $count)
                        <span class="flex-1">{{ $label }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- KPI Performance --}}
        <div class="ncard flex-1">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-[15px] font-extrabold">
                    KPI Performance <span class="ml-1 text-[12px] font-medium text-ink-3">{{ now()->year }}</span>
                </span>
                <span class="pill-btn">Monthly</span>
            </div>

            @if ($delta_revenue)
                <div class="trend trend--{{ $delta_revenue['direction'] }} text-[22px]">
                    {{ $delta_revenue['pct'] }}%{{ $delta_revenue['direction'] === 'up' ? '↑' : '↓' }}
                </div>
                <div class="trend__note ml-0">vs last month</div>
            @else
                <div class="text-[22px] font-extrabold text-accent">{{ $peso($revenue_this_month) }}</div>
                <div class="trend__note ml-0">this month · no prior month to compare</div>
            @endif

            <div class="mt-4 flex h-[150px] items-end gap-1">
                @foreach ($series as $i => $v)
                    <div class="gbar {{ $i === $bestIx && $peak > 1 ? '' : 'gbar--soft' }} flex-1"
                         style="height: {{ max(4, ((float) $v / $peak) * 140) }}px"
                         title="{{ $months[$i] }} — {{ $peso($v) }}"></div>
                @endforeach
            </div>
            <div class="mt-1.5 flex text-[10px] text-ink-3">
                @foreach ($months as $i => $m)
                    <span class="flex-1 text-center {{ $i === $bestIx && $peak > 1 ? 'font-bold text-accent' : '' }}">{{ $m }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
