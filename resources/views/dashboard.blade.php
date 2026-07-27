@extends('layouts.sidebar')

@section('title', 'Overview')

@section('content')
@php
    $peso = fn ($n) => '₱' . number_format((float) $n, 2);

    // ── KPI chart: stepped area across Jan–Dec ──────────────────────────
    $months   = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $series   = collect($monthly_revenue)->values();
    $peak     = max((float) $series->max(), 1);          // avoid /0 on an empty year
    $W        = 720;  $H = 190;  $step = $W / 12;

    // Build the step path: flat top per month, vertical joins between them.
    $top = '';
    foreach ($series as $i => $v) {
        $y  = $H - (((float) $v / $peak) * ($H - 20));
        $x0 = $i * $step;
        $top .= ($i === 0 ? "M {$x0} {$y}" : " L {$x0} {$y}") . ' L ' . ($x0 + $step) . " {$y}";
    }
    $areaPath = $top . " L {$W} {$H} L 0 {$H} Z";

    $monthsWithRevenue = $series->filter(fn ($v) => $v > 0)->count();
    $bestMonthIndex    = $series->search($series->max());

    // ── Trend line: daily sales over the last 7 days ────────────────────
    $daily     = collect($daily_sales);
    $dailyPeak = max((float) $daily->max('total'), 1);
    $lw = 640; $lh = 90;
    $points = $daily->count() > 1
        ? $daily->values()->map(function ($d, $i) use ($daily, $dailyPeak, $lw, $lh) {
            $x = ($i / max($daily->count() - 1, 1)) * $lw;
            $y = $lh - (((float) $d->total / $dailyPeak) * ($lh - 10));
            return round($x, 1) . ',' . round($y, 1);
          })->implode(' ')
        : null;

    // ── Alert breakdown: real severity counts ───────────────────────────
    $sevMeta = [
        'high'   => ['High',     'var(--color-sev-high)'],
        'medium' => ['Moderate', 'var(--color-sev-med)'],
        'low'    => ['Low',      'var(--color-blue)'],
    ];
    $sevTotal = collect($flag_counts)->sum();

    // ── Activity rail ───────────────────────────────────────────────────
    $tagFor = ['high' => 'tag--red', 'medium' => 'tag--amber', 'low' => 'tag--blue'];
@endphp

{{-- Search + primary action --}}
<div class="mb-4 flex items-center gap-3">
    <label class="search-bar">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" placeholder="Search…" autocomplete="off">
        <kbd>⌘K</kbd>
    </label>
    <a href="{{ route('alerts') }}" class="btn-dark ml-auto">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export
    </a>
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-[2fr_1fr]">

    {{-- ══════════ Left column ══════════ --}}
    <div class="flex flex-col gap-4">

        {{-- Stat tiles --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="tile">
                <div class="tile__label">Total Branches</div>
                <div class="tile__figure">{{ $total_branches }}</div>
                <div class="mt-2.5"><span class="delta-note ml-0">{{ $branches_with_sales->where('has_sales', true)->count() }} trading today</span></div>
            </div>
            <div class="tile">
                <div class="tile__label">Active Shifts</div>
                <div class="tile__figure">{{ $ongoing_shifts->count() }}</div>
                <div class="mt-2.5"><span class="delta-note ml-0">clocked in right now</span></div>
            </div>
            <div class="tile">
                <div class="tile__label">Pending Alerts</div>
                <div class="tile__figure">{{ $pending_alerts }}</div>
                <div class="mt-2.5">
                    @if ($pending_alerts > 0)
                        <span class="delta delta--down">Needs review</span>
                    @else
                        <span class="delta delta--up">All clear</span>
                    @endif
                    <span class="delta-note">{{ $low_stock_count }} low stock</span>
                </div>
            </div>
        </div>

        {{-- Revenue performance --}}
        <div class="tile">
            <div class="tile__head">
                <span class="tile__title">Revenue Performance</span>
                <span class="pill-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ now()->year }}
                </span>
            </div>

            <div class="text-[30px] font-bold tracking-[-.02em]">{{ $peso($annual_revenue) }}</div>
            <div class="mt-2">
                <span class="delta delta--up">{{ $monthsWithRevenue }}/12 months</span>
                <span class="delta-note">with recorded sales</span>
            </div>

            <svg viewBox="0 0 {{ $W }} {{ $H }}" class="mt-5 w-full" preserveAspectRatio="none" style="height:190px">
                <defs>
                    <linearGradient id="kpiFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%"   stop-color="var(--color-accent)" stop-opacity=".28"/>
                        <stop offset="100%" stop-color="var(--color-accent)" stop-opacity=".02"/>
                    </linearGradient>
                </defs>
                <path d="{{ $areaPath }}" fill="url(#kpiFill)"/>
                <path d="{{ $top }}" fill="none" stroke="var(--color-accent)" stroke-width="2" vector-effect="non-scaling-stroke"/>
            </svg>
            <div class="mt-1.5 flex text-[11px] text-ink-3">
                @foreach ($months as $i => $m)
                    <span class="flex-1 text-center {{ $i === $bestMonthIndex && $peak > 1 ? 'font-bold text-accent' : '' }}">{{ $m }}</span>
                @endforeach
            </div>
        </div>

        {{-- Bottom row: trend + alert breakdown --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <div class="tile">
                <div class="tile__head">
                    <span class="tile__title">Sales Trend</span>
                    <span class="pill-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Last 7 days
                    </span>
                </div>

                <div class="text-[26px] font-bold tracking-[-.02em]">{{ $peso($total_sales) }}</div>
                <div class="mt-1.5"><span class="delta-note ml-0">today's sales</span></div>

                @if ($points)
                    <svg viewBox="0 0 {{ $lw }} {{ $lh }}" class="mt-4 w-full" preserveAspectRatio="none" style="height:90px">
                        <polyline points="{{ $points }}" fill="none" stroke="var(--color-accent)" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                    </svg>
                @else
                    <div class="empty-state !py-8">Not enough days recorded to plot a trend.</div>
                @endif

                <div class="mt-3 flex justify-between border-t border-line pt-3 text-[11px] text-ink-3">
                    <span>{{ now()->subDays(7)->format('M d, Y') }}</span>
                    <span>{{ $peso($daily->sum('total')) }} over 7 days</span>
                </div>
            </div>

            <div class="tile">
                <div class="tile__head"><span class="tile__title">Alert Breakdown</span></div>

                @if ($sevTotal === 0)
                    <div class="all-clear">No pending alerts.</div>
                @else
                    <div class="density">
                        @foreach ($sevMeta as $key => [$label, $color])
                            @php $count = (int) ($flag_counts[$key] ?? 0); @endphp
                            @if ($count > 0)
                                <div class="density__seg" style="flex:{{ $count }};color:{{ $color }}"></div>
                            @endif
                        @endforeach
                    </div>
                    <div class="density__scale"><span>0%</span><span>100%</span></div>

                    <div class="mt-4 grid grid-cols-3 gap-2 border-t border-line pt-4">
                        @foreach ($sevMeta as $key => [$label, $color])
                            @php
                                $count = (int) ($flag_counts[$key] ?? 0);
                                $pct   = $sevTotal > 0 ? round($count / $sevTotal * 100) : 0;
                            @endphp
                            <div>
                                <div class="legend-stat__top">
                                    <span class="legend-stat__swatch" style="background:{{ $color }}"></span>
                                    {{ $label }}
                                </div>
                                <div class="mt-1">
                                    <span class="legend-stat__value">{{ $count }}</span>
                                    <span class="legend-stat__pct">{{ $pct }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════ Right rail: Activity ══════════ --}}
    <div class="tile flex flex-col">
        <div class="tile__head">
            <span class="tile__title">Activity</span>
            <span class="flex items-center gap-2">
                <a href="{{ route('alerts') }}" class="icon-btn" title="Search alerts">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </a>
                <a href="{{ route('alerts') }}" class="pill-btn">See All</a>
            </span>
        </div>

        {{-- Month + day strip --}}
        <div class="mb-3 flex items-center justify-between px-1">
            <span class="text-ink-3">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
            </span>
            <span class="text-[13px] font-semibold">{{ now()->format('F Y') }}</span>
            <span class="text-ink-3">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
        </div>

        <div class="daystrip mb-4">
            @foreach (range(-2, 2) as $offset)
                @php $d = now()->addDays($offset); @endphp
                <span class="daystrip__day {{ $offset === 0 ? 'is-today' : '' }}">
                    <span class="daystrip__num">{{ $d->format('d') }}</span>
                    <span class="daystrip__dow">{{ $d->format('D') }}</span>
                </span>
            @endforeach
        </div>

        {{-- Tabs --}}
        <div class="utabs mb-3">
            <span class="utab is-active">Alerts</span>
            <a href="{{ route('business.workers') }}" class="utab">Shifts</a>
        </div>

        {{-- Alert cards --}}
        <div class="flex flex-col gap-3">
            @forelse ($recent_flags->take(4) as $flag)
                <a href="{{ route('alerts') }}" class="rounded-xl border border-line p-3.5 transition-colors hover:bg-surface-2">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="tag {{ $tagFor[$flag->severity] ?? 'tag--accent' }}">{{ ucfirst($flag->severity) }} importance</span>
                        <span class="text-ink-3">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                    </div>
                    <div class="text-[13.5px] font-bold">{{ $flag->ingredient->name ?? 'Stock variance' }}</div>
                    <div class="mt-0.5 text-[11.5px] text-ink-3">
                        {{ $flag->branch->name ?? '—' }} · {{ $flag->created_at->format('M d, g:iA') }}
                    </div>
                    <div class="mt-2.5 flex items-center justify-between">
                        <span class="text-[11.5px] text-ink-2">
                            Short by <span class="font-bold text-accent-2">{{ $flag->variance !== null ? number_format(abs($flag->variance), 0) : '—' }}{{ $flag->ingredient->unit ?? '' }}</span>
                        </span>
                        @if ($flag->shiftLog?->user)
                            <span class="avatar-stack">
                                <span class="avatar-stack__item bg-accent">{{ mb_substr($flag->shiftLog->user->name, 0, 1) }}</span>
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="all-clear">Nothing flagged — all branches reconciled.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
