@extends('layouts.sidebar')

@section('title', 'Analytics')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="text-[22px] font-extrabold tracking-tight">Analytics</div>
        <div class="text-[13px] text-ink-2 mt-0.5">Sales trends, top products, branch performance, and labor — last 30 days</div>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-[1.35fr_1fr]">

    <div class="flex flex-col gap-4">

        {{-- Sales trend --}}
        <div class="ncard">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-[15px] font-extrabold">Sales Trend <span class="ml-1 text-[12px] font-medium text-ink-3">last 30 days</span></span>
            </div>
            <div class="mt-4 flex h-[130px] items-end gap-1">
                @forelse ($salesSeries as $row)
                    <div class="gbar flex-1" style="height: {{ max(4, ($row['total'] / $peakSales) * 120) }}px" title="{{ $row['date'] }} — ₱{{ number_format($row['total'], 2) }}"></div>
                @empty
                    <div class="all-clear">No sales recorded in this window.</div>
                @endforelse
            </div>
        </div>

        {{-- Top products --}}
        <div class="ncard">
            <span class="text-[15px] font-extrabold">Top Products by Revenue</span>
            @if ($topProducts->isEmpty())
                <div class="all-clear mt-2">No product sales recorded yet.</div>
            @else
                <div class="mt-3 flex flex-col gap-2.5">
                    @foreach ($topProducts as $p)
                        <div class="flex items-center gap-3">
                            <span class="w-[110px] shrink-0 text-[12.5px] font-semibold truncate">{{ $p['name'] }}</span>
                            <div class="flex-1 h-[10px] rounded-full bg-[var(--color-surface-2)] overflow-hidden">
                                <div class="h-full gbar !rounded-full" style="width: {{ max(4, ($p['revenue'] / $peakProduct) * 100) }}%"></div>
                            </div>
                            <span class="w-[90px] shrink-0 text-right text-[12.5px] font-bold text-accent">₱{{ number_format($p['revenue'], 2) }}</span>
                            <span class="w-[60px] shrink-0 text-right text-[11px] text-ink-3">{{ $p['units'] }} sold</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Leakage trend --}}
        <div class="ncard">
            <span class="text-[15px] font-extrabold">Leakage Trend <span class="ml-1 text-[12px] font-medium text-ink-3">last 30 days</span></span>
            <div class="mt-4 flex h-[110px] items-end gap-1">
                @forelse ($leakageTrend as $row)
                    <div class="gbar--soft flex-1" style="height: {{ max(4, ($row['total'] / $peakLeakage) * 100) }}px" title="{{ $row['date'] }} — {{ number_format($row['total'], 2) }} units lost"></div>
                @empty
                    <div class="all-clear">No leakage recorded — every counted shift matched expected stock.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-4">

        {{-- Branch revenue comparison --}}
        <div class="ncard">
            <span class="text-[15px] font-extrabold">Branch Revenue{{ $isManager ? '' : ' Comparison' }}</span>
            @if ($branchRevenue->isEmpty())
                <div class="all-clear mt-2">No branches to compare yet.</div>
            @else
                <div class="rank-list mt-3">
                    @foreach ($branchRevenue as $b)
                        <div class="rank-list__item">
                            <span class="flex-1">{{ $b['name'] }}</span>
                            <span class="font-semibold text-accent">₱{{ number_format($b['revenue'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Labor hours --}}
        <div class="ncard">
            <span class="text-[15px] font-extrabold">Labor Hours <span class="ml-1 text-[12px] font-medium text-ink-3">{{ now()->format('F') }}</span></span>
            @if ($laborHours->isEmpty())
                <div class="all-clear mt-2">No closed shifts recorded this month.</div>
            @else
                <div class="mt-3 flex flex-col gap-2.5">
                    @foreach ($laborHours as $row)
                        <div class="flex items-center gap-3">
                            <span class="w-[100px] shrink-0 text-[12.5px] font-semibold truncate">{{ $row['name'] }}</span>
                            <div class="flex-1 h-[10px] rounded-full bg-[var(--color-surface-2)] overflow-hidden">
                                <div class="h-full gbar--soft !rounded-full" style="width: {{ max(4, ($row['hours'] / $peakLabor) * 100) }}%"></div>
                            </div>
                            <span class="w-[70px] shrink-0 text-right text-[12.5px] font-bold">{{ $row['hours'] }}h</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
