@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Overview across all branches &middot; Today, ' . date('M d'))

@section('content')

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Today's Sales</div>
            <div class="stat-value">&#8369;{{ number_format($total_sales ?? 0, 0) }}</div>
            <span class="stat-badge green">Today</span>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Branches</div>
            <div class="stat-value">{{ $total_branches }}</div>
            <span class="stat-badge blue">Active</span>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Alerts</div>
            <div class="stat-value">{{ $pending_alerts }}</div>
            @if ($pending_alerts > 0)
                <span class="stat-badge red">Needs review</span>
            @else
                <span class="stat-badge green">All clear</span>
            @endif
        </div>
        <div class="stat-card">
            <div class="stat-label">Low Stock SKUs</div>
            <div class="stat-value">{{ $low_stock_count }}</div>
            <span class="stat-badge amber">Across branches</span>
        </div>
    </div>

    <div class="widget-grid">
        <div class="widget">
            <div class="widget-head">
                <h2>Sales &mdash; Last 7 Days</h2>
            </div>

            @php
                $chartDays = collect();
                for ($i = 6; $i >= 0; $i--) {
                    $date = \Carbon\Carbon::today()->subDays($i);
                    $match = $daily_sales->firstWhere('date', $date->format('Y-m-d'));
                    $chartDays->push([
                        'label' => substr($date->format('D'), 0, 1),
                        'total' => $match ? (float) $match->total : 0,
                    ]);
                }
                $chartMax = $chartDays->max('total');
            @endphp

            @if ($chartMax > 0)
                <div class="bar-chart">
                    @foreach ($chartDays as $day)
                        <div class="bar-col">
                            <div class="bar" style="height: {{ max(2, ($day['total'] / $chartMax) * 100) }}%"></div>
                            <div class="bar-day-label">{{ $day['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">No sales recorded in the last 7 days.</div>
            @endif
        </div>

        <div class="widget">
            <div class="widget-head">
                <h2>Branches Live</h2>
            </div>

            @forelse ($branches_with_sales as $branch)
                <div class="branch-live-row">
                    <div class="branch-live-left">
                        <span class="dot {{ $branch['has_sales'] ? 'green' : 'red' }}"></span>
                        <span>{{ $branch['name'] }}</span>
                    </div>
                    <span class="branch-live-amount">&#8369;{{ number_format($branch['today_sales'], 0) }}</span>
                </div>
            @empty
                <div class="empty-state">No branches have been added yet.</div>
            @endforelse
        </div>
    </div>

    <div class="widget">
        <div class="widget-head">
            <h2>Recent Discrepancy Alerts</h2>
            <a href="#" class="widget-link">View all &rarr;</a>
        </div>

        @if ($recent_alerts->isEmpty())
            <div class="all-clear">No alerts &mdash; all clear &#10003;</div>
        @else
            <table class="alerts-table">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Shift</th>
                        <th>Staff</th>
                        <th>Variance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent_alerts as $alert)
                        <tr>
                            <td>{{ $alert->branch->name ?? '—' }}</td>
                            <td>{{ $alert->shiftLog?->shift_start?->format('M d, g:iA') ?? '—' }}</td>
                            <td>{{ $alert->shiftLog?->user?->name ?? '—' }}</td>
                            <td class="variance-cell">{{ $alert->variance !== null ? number_format($alert->variance, 2) : '—' }}</td>
                            <td><button type="button" class="btn-review">Review</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection
