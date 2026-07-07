<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Tracker — Owner Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f4f4f5; color: #18181b; }
        header { background: #18181b; color: #fff; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; }
        header h1 { font-size: 1.1rem; font-weight: 600; }
        header span { font-size: 0.85rem; color: #a1a1aa; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .kpi { background: #fff; border-radius: 10px; padding: 1.25rem 1.5rem; border: 1px solid #e4e4e7; }
        .kpi-label { font-size: 0.78rem; color: #71717a; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .5rem; }
        .kpi-value { font-size: 2rem; font-weight: 700; }
        .kpi-value.danger { color: #dc2626; }
        .kpi-value.warning { color: #d97706; }
        .kpi-value.success { color: #16a34a; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem; }
        @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
        .card { background: #fff; border-radius: 10px; border: 1px solid #e4e4e7; padding: 1.25rem 1.5rem; }
        .card h2 { font-size: 0.9rem; font-weight: 600; margin-bottom: 1rem; color: #3f3f46; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        th { text-align: left; color: #71717a; font-weight: 500; padding: 0.4rem 0.5rem; border-bottom: 1px solid #e4e4e7; }
        td { padding: 0.5rem 0.5rem; border-bottom: 1px solid #f4f4f5; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 500; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warn { background: #fef9c3; color: #854d0e; }
        .chart-bar-wrap { display: flex; align-items: flex-end; gap: 6px; height: 100px; margin-top: .5rem; }
        .chart-bar-col { display: flex; flex-direction: column; align-items: center; flex: 1; }
        .chart-bar { background: #6366f1; border-radius: 4px 4px 0 0; width: 100%; }
        .chart-label { font-size: 0.65rem; color: #71717a; margin-top: 4px; }
        .empty { color: #a1a1aa; font-size: 0.85rem; text-align: center; padding: 1rem 0; }
    </style>
</head>
<body>

<header>
    <h1>Inventory Tracker — Owner Dashboard</h1>
    <span>{{ now()->format('F j, Y') }}</span>
</header>

<div class="container">

    {{-- KPIs --}}
    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">Revenue today</div>
            <div class="kpi-value success">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Transactions today</div>
            <div class="kpi-value">{{ $totalSales }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Flagged shifts</div>
            <div class="kpi-value {{ $flaggedShifts > 0 ? 'danger' : '' }}">{{ $flaggedShifts }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Open alerts</div>
            <div class="kpi-value {{ $openAlerts > 0 ? 'warning' : '' }}">{{ $openAlerts }}</div>
        </div>
    </div>

    <div class="grid-2">

        {{-- Sales chart (last 7 days) --}}
        <div class="card">
            <h2>Sales — last 7 days</h2>
            @if($salesSummary->count())
                @php $maxRev = $salesSummary->max('revenue') ?: 1; @endphp
                <div class="chart-bar-wrap">
                    @foreach($salesSummary as $day)
                        <div class="chart-bar-col">
                            <div class="chart-bar" style="height: {{ round(($day->revenue / $maxRev) * 100) }}px" title="₱{{ number_format($day->revenue,2) }}"></div>
                            <div class="chart-label">{{ \Carbon\Carbon::parse($day->date)->format('D') }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">No sales data yet.</div>
            @endif
        </div>

        {{-- Top products --}}
        <div class="card">
            <h2>Top products today</h2>
            @if($topProducts->count())
                <table>
                    <thead>
                        <tr><th>Product</th><th>Units</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $p)
                        <tr>
                            <td>{{ $p['name'] }}</td>
                            <td>{{ $p['units_sold'] }}</td>
                            <td>₱{{ number_format($p['revenue'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">No sales today yet.</div>
            @endif
        </div>

    </div>

    {{-- Alerts --}}
    <div class="card">
        <h2>Open alerts</h2>
        @if($recentAlerts->count())
            <table>
                <thead>
                    <tr><th>Branch</th><th>Type</th><th>Message</th><th>Time</th></tr>
                </thead>
                <tbody>
                    @foreach($recentAlerts as $alert)
                    <tr>
                        <td>{{ $alert->branch->name ?? '—' }}</td>
                        <td><span class="badge badge-danger">{{ $alert->type }}</span></td>
                        <td>{{ $alert->message }}</td>
                        <td>{{ $alert->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No open alerts.</div>
        @endif
    </div>

</div>
</body>
</html>
