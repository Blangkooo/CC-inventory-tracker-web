@extends('layouts.sidebar')

@section('title', 'Dashboard')

@section('content')
@php
    $peso = fn ($n) => '₱' . number_format((float) $n, 2);

    $roleName = auth()->user()->isSuperAdmin() ? 'Owner' : (auth()->user()->isManager() ? 'Manager' : 'Staff');

    // Severity → dot colour, matching the legend on the flag summary.
    $sevTone  = ['high' => 'high', 'medium' => 'med', 'low' => 'low'];

    // One entry per branch carrying its worst pending severity.
    $sevRank  = ['low' => 1, 'medium' => 2, 'high' => 3];
    $flagged  = [];
    foreach ($recent_flags as $f) {
        $name = $f->branch->name ?? 'Unassigned';
        $sev  = $f->severity ?? 'low';
        if (! isset($flagged[$name]) || ($sevRank[$sev] ?? 0) > ($sevRank[$flagged[$name]] ?? 0)) {
            $flagged[$name] = $sev;
        }
    }

    // Top earners, tallest bar first, used for both the bars and the share pie.
    $earners      = $top_earners->filter(fn ($b) => (float) ($b->revenue ?? 0) > 0)->values();
    $maxRevenue   = (float) ($earners->max('revenue') ?? 0);
    $topEarner    = $earners->first();
    $topShare     = $annual_revenue > 0 && $topEarner ? ((float) $topEarner->revenue / $annual_revenue) : 0;

    // Least leakage: already sorted ascending, so the first entry is cleanest.
    $leakRows     = collect($least_leakage);
    $maxLeak      = (float) ($leakRows->max('leak') ?? 0);
    $cleanest     = $leakRows->first();

    // Donut shows how many branches are running clean, which is real data —
    // there is no "value saved vs lost" split to plot.
    $cleanCount   = $leakRows->where('leak', '<=', 0)->count();
    $cleanShare   = $leakRows->count() > 0 ? $cleanCount / $leakRows->count() : 0;

    // Worst-leaking branch drives the Leakage History breakdown.
    $worstLeak    = $leakRows->sortByDesc('leak')->first();
    $worstFlags   = $recent_flags
        ->filter(fn ($f) => ($f->branch->name ?? null) === ($worstLeak['name'] ?? null))
        ->take(3);

    $circumference = 2 * M_PI * 42;   // r = 42 on the donut/pie circles
@endphp

{{-- Search + primary action --}}
<div class="mb-5 flex items-center gap-3">
    <label class="search-bar">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" placeholder="Search…" autocomplete="off">
        <kbd>⌘K</kbd>
    </label>
    <span class="ml-auto text-xs font-medium text-ink-2">{{ now()->format('l, F j, Y') }}</span>
    <a href="{{ route('alerts') }}" class="btn-dark">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export
    </a>
</div>

{{-- ══ Recent Flag Summary ══ --}}
<div class="panel mb-6">
    <div class="panel__head">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
        <span class="panel__title">Recent Flag Summary</span>
        <span class="panel__aside legend">
            <span class="dot-item dot-item--low">Low Importance</span>
            <span class="dot-item dot-item--med">Moderate Importance</span>
            <span class="dot-item dot-item--high">High Importance</span>
        </span>
    </div>

    @if (empty($flagged))
        <div class="all-clear">No pending flags — every branch is reconciled.</div>
    @else
        <div class="grid grid-cols-1 gap-x-6 gap-y-2.5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($flagged as $branchName => $sev)
                <a href="{{ route('alerts') }}" class="dot-item dot-item--{{ $sevTone[$sev] ?? 'low' }} hover:text-accent">
                    {{ $branchName }}
                </a>
            @endforeach
        </div>
    @endif
</div>

{{-- ══ Performance Summary ══ --}}
<div class="section-title mb-4">
    <span class="section-title__main">Performance Summary</span>
    <span class="section-title__pipe">|</span>
    <span class="section-title__sub">{{ now()->format('F') }}</span>
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

    {{-- ── Left column ── --}}
    <div class="flex flex-col gap-4">

        {{-- Top Earner --}}
        <div class="panel">
            <div class="panel__head">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                <span class="panel__title">Top Earner</span>
                <span class="panel__aside figure-value">{{ $peso($annual_revenue) }}</span>
            </div>

            @if ($earners->isEmpty())
                <div class="empty-state">No revenue recorded yet.</div>
            @else
                <div class="grid grid-cols-2 items-center gap-4">
                    {{-- Bars: tallest branch highlighted --}}
                    <div>
                        @if ($topEarner)
                            <div class="chart-callout mb-2">
                                <div class="chart-callout__value text-green">{{ $peso($topEarner->revenue) }}</div>
                                <div class="chart-callout__label">{{ $topEarner->name }}</div>
                            </div>
                        @endif
                        <div class="flex h-[110px] items-end gap-1.5">
                            @foreach ($earners as $i => $b)
                                @php $h = $maxRevenue > 0 ? max(6, ((float) $b->revenue / $maxRevenue) * 100) : 6; @endphp
                                <div class="flex-1 rounded-t-[3px] {{ $i === 0 ? 'bg-accent' : 'bg-cream' }}"
                                     style="height: {{ $h }}%"
                                     title="{{ $b->name }} — {{ $peso($b->revenue) }}"></div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Share of total revenue --}}
                    <div class="flex flex-col items-center gap-2 border-l border-line pl-4">
                        <svg viewBox="0 0 100 100" class="h-[104px] w-[104px] -rotate-90">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-cream)" stroke-width="16"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-accent)" stroke-width="16"
                                    stroke-dasharray="{{ $topShare * $circumference }} {{ $circumference }}"/>
                        </svg>
                        <div class="figure-label">Total Revenue</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Least Leakage --}}
        <div class="panel">
            <div class="panel__head">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/></svg>
                <span class="panel__title">Least Leakage</span>
                <span class="panel__aside figure-value">{{ $peso($value_saved) }}</span>
            </div>

            @if ($leakRows->isEmpty())
                <div class="empty-state">No branches to compare yet.</div>
            @else
                <div class="grid grid-cols-2 items-center gap-4">
                    <div>
                        @if ($cleanest)
                            <div class="chart-callout mb-2">
                                <div class="chart-callout__value text-accent">
                                    {{ $cleanest['leak'] > 0 ? number_format($cleanest['leak'], 2) . 'u lost' : 'No loss' }}
                                </div>
                                <div class="chart-callout__label">{{ $cleanest['name'] }} — cleanest</div>
                            </div>
                        @endif
                        <div class="flex h-[110px] items-end gap-1.5">
                            @foreach ($leakRows->take(6) as $i => $row)
                                @php $h = $maxLeak > 0 ? max(6, ($row['leak'] / $maxLeak) * 100) : 6; @endphp
                                <div class="flex-1 rounded-t-[3px] {{ $i === 0 ? 'bg-accent' : 'bg-cream' }}"
                                     style="height: {{ $h }}%"
                                     title="{{ $row['name'] }} — {{ number_format($row['leak'], 2) }}u"></div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-2 border-l border-line pl-4">
                        <svg viewBox="0 0 100 100" class="h-[104px] w-[104px] -rotate-90">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-cream)" stroke-width="13"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-accent)" stroke-width="13"
                                    stroke-dasharray="{{ $cleanShare * $circumference }} {{ $circumference }}"/>
                        </svg>
                        <div class="figure-label">
                            Total Value Saved
                            <span class="block text-ink-3">{{ $cleanCount }}/{{ $leakRows->count() }} branches clean</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Right column ── --}}
    <div class="flex flex-col gap-4">

        {{-- Leakage History --}}
        <div class="panel flex-1">
            <div class="panel__head">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <span class="panel__title">Leakage History</span>
            </div>

            <div class="flex items-center justify-center gap-5">
                <svg viewBox="0 0 100 100" class="h-[112px] w-[112px] -rotate-90">
                    <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-cream)" stroke-width="16"/>
                    <circle cx="50" cy="50" r="42" fill="none" stroke="var(--color-accent)" stroke-width="16"
                            stroke-dasharray="{{ min($leakage_pct, 100) / 100 * $circumference }} {{ $circumference }}"/>
                </svg>
                <div class="text-[26px] font-extrabold">{{ number_format($leakage_pct, 1) }}%</div>
            </div>

            @if ($worstLeak && $worstFlags->isNotEmpty())
                <div class="mt-4 border-t border-line pt-3">
                    <div class="mb-2 text-[15px] font-extrabold">{{ $worstLeak['name'] }}</div>
                    @foreach ($worstFlags as $f)
                        <div class="flex items-baseline justify-between py-0.5 text-[13px]">
                            <span>{{ $f->ingredient->name ?? '—' }}</span>
                            <span class="font-semibold text-accent">
                                {{ $f->variance !== null ? number_format(abs($f->variance), 0) : '—' }}
                                {{ $f->ingredient->unit ?? '' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Overall Leakage --}}
        <div class="panel">
            <div class="panel__head">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/></svg>
                <span class="panel__title">Overall Leakage</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-[38px] font-extrabold {{ $leakage_pct > 10 ? 'text-accent-2' : 'text-green' }}">
                    {{ number_format($leakage_pct, 1) }}%
                </span>
                <span class="text-[11px] leading-tight text-ink-3">
                    of expected stock<br>unaccounted for
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ══ Recent Pending Alerts ══ --}}
@if ($recent_flags->isNotEmpty())
<div class="card mt-6">
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
                @php $sevBadge = ['high' => 'badge-red', 'medium' => 'badge-amber', 'low' => 'badge-blue']; @endphp
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
@endsection
