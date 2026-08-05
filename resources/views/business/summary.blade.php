<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Summary — NITA</title>
    @include('partials._shared-styles')

    <style>
        .workspace { padding: 20px 32px; }

        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            padding: 20px;
        }

        .card h3 {
            font-size: 13px; font-weight: 700; margin-bottom: 14px; padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .activity-log { list-style: none; }
        .activity-log li { display: flex; justify-content: space-between; align-items: baseline; padding: 8px 0; font-size: 13px; border-bottom: 1px solid rgba(92,45,27,.06); }
        .activity-log li:last-child { border-bottom: none; }
        .activity-log .items-list { font-size: 11px; opacity: .6; padding: 0; border-bottom: none; }
        .activity-log .total-row { font-weight: 800; font-size: 14px; padding-top: 12px; border-top: 2px solid var(--brown); }

        .leakage-row { display: flex; justify-content: space-between; padding: 9px 0; font-size: 13px; border-bottom: 1px solid rgba(92,45,27,.06); }
        .leakage-row:last-child { border-bottom: none; }
        .qty.red { color: #dc2626; font-weight: 700; }

        .profit-card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); padding: 24px 20px; margin-bottom: 16px; text-align: center; }
        .profit-card h3 { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; opacity: .5; margin-bottom: 10px; }
        .profit-value { font-size: 38px; font-weight: 800; color: #16a34a; }
        .profit-value .arrow { font-size: 22px; }

        .chart-card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); padding: 20px; margin-bottom: 16px; }
        .chart-card h3 { font-size: 13px; font-weight: 700; margin-bottom: 14px; }
        .chart-canvas { width: 100%; height: 140px; position: relative; overflow: hidden; }
        .chart-svg { width: 100%; height: 100%; }
        .chart-milestones { display: flex; justify-content: space-between; font-size: 10px; opacity: .5; margin-top: 6px; }

        @media (max-width: 900px) { .summary-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<nav class="nav">
    <div class="nav__inner">
        <div class="nav__left">
            <a href="{{ url('/dashboard') }}" class="nav__logo"><img src="{{ asset('images/logo.svg') }}" alt="NITA"></a>
            <div class="nav__pills">
                <a href="{{ url('/business/recipes') }}" class="nav__pill is-active">Business</a>
                <a href="{{ url('/logistics') }}" class="nav__pill">Logistics</a>
            </div>
        </div>
        <div class="nav__right">
            <a href="{{ url('/alerts') }}" class="nav__icon" title="Alerts">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </a>
            <a href="{{ url('/alerts') }}" class="nav__icon" title="Messages" style="text-decoration:none">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </a>
            <div class="nav__sep"></div>
            <div class="nav__user">
                <div class="nav__avatar">A</div>
                <div class="nav__user-info">
                    <div class="nav__user-name">Admin Owner</div>
                    <div class="nav__user-email">admin@nita.com</div>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="shell">
    @include('partials._sidebar')

    <main style="padding: 0;">
        @php $currentBusinessTab = 'summary'; @endphp
        @include('partials._business-header')

        <div style="padding: 20px 32px;">
        @php $isOwner = true; @endphp

        <div class="summary-grid">
            <div class="left-col">
                <div class="card">
                    <h3>Recent Transactions — {{ $activeBranch?->name ?? 'All' }}</h3>
                    @if ($recentTransactions->isEmpty())
                        <p style="font-size:13px;opacity:.4;padding:8px 0">No transactions recorded yet.</p>
                    @else
                        <ul class="activity-log">
                            @foreach ($recentTransactions as $i => $tx)
                                <li>
                                    <span><strong>Txn #{{ $tx->id }}</strong></span>
                                    <span>&#8369;{{ number_format($tx->total_amount, 2) }}</span>
                                </li>
                                <li class="items-list">
                                    <span>{{ $tx->product?->name ?? 'Unknown Item' }} — {{ $tx->created_at->format('M d, g:iA') }} · {{ $tx->user?->name ?? '—' }}</span>
                                </li>
                            @endforeach
                            <li class="total-row">
                                <span>TOTAL:</span>
                                <span>&#8369;{{ number_format($recentTransactions->sum('total_amount'), 2) }}</span>
                            </li>
                        </ul>
                    @endif
                </div>

                <div class="card">
                    <h3>Leakage Log (Negative Variance)</h3>
                    @if ($leakageRows->isEmpty())
                        <p style="font-size:13px;opacity:.4;padding:8px 0">No leakage records found.</p>
                    @else
                        @foreach ($leakageRows as $row)
                            <div class="leakage-row">
                                <span>{{ $row->ingredient?->name ?? 'Unknown' }}</span>
                                <span class="qty red">{{ number_format($row->variance, 2) }} {{ $row->ingredient?->unit ?? '' }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="right-col">
                <div class="profit-card">
                    <h3>Annual Revenue ({{ now()->year }})</h3>
                    <div class="profit-value">
                        &#8369;{{ $totalRevenue >= 1_000_000
                            ? number_format($totalRevenue / 1_000_000, 2) . 'M'
                            : ($totalRevenue >= 1_000 ? number_format($totalRevenue / 1_000, 1) . 'k' : number_format($totalRevenue, 2)) }}
                        @if ($totalRevenue > 0)<span class="arrow">&uarr;</span>@endif
                    </div>
                </div>

                @php
                    $maxSales = $monthlySales->max() ?: 1;
                    $svgW = 300; $svgH = 110; $pad = 10;
                    $pts = collect(range(1, 12))->map(function ($m) use ($monthlySales, $maxSales, $svgW, $svgH, $pad) {
                        $x = $pad + ($m - 1) * (($svgW - $pad * 2) / 11);
                        $val = $monthlySales->get($m, 0);
                        $y = $svgH - $pad - ($val / $maxSales) * ($svgH - $pad * 2);
                        return "$x,$y";
                    })->implode(' ');
                    $polyClose = $pts . " $svgW,$svgH 0,$svgH";
                @endphp

                <div class="chart-card">
                    <h3>Monthly Sales — {{ now()->year }}</h3>
                    <div class="chart-canvas">
                        <svg class="chart-svg" viewBox="0 0 {{ $svgW }} {{ $svgH }}" preserveAspectRatio="none">
                            <polygon points="{{ $polyClose }}" fill="rgba(188,97,75,.08)"/>
                            <polyline points="{{ $pts }}" fill="none" stroke="#BC614B" stroke-width="2.5" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="chart-milestones">
                        @foreach (['Jan','Mar','May','Jul','Sep','Nov'] as $ml)<span>{{ $ml }}</span>@endforeach
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>
</div>

@include('partials._settings-drawer')
</body>
</html>
