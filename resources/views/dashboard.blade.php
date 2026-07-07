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

    <div class="dash-grid">
        <div class="dash-main">

            <div class="widget">
                <div class="widget-head">
                    <h2>Flags Summary</h2>
                    <a href="{{ route('alerts') }}" class="widget-link">View all &rarr;</a>
                </div>

                @if ($recent_flags->isEmpty())
                    <div class="all-clear">No pending flags &mdash; all clear &#10003;</div>
                @else
                    <div class="flag-pills" style="margin-bottom: 14px;">
                        @foreach (['high' => 'red', 'medium' => 'amber', 'low' => 'blue'] as $severity => $color)
                            @if (($flag_counts[$severity] ?? 0) > 0)
                                <span class="badge {{ $color }}">{{ $flag_counts[$severity] }} {{ ucfirst($severity) }}</span>
                            @endif
                        @endforeach
                    </div>

                    <table class="alerts-table">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Ingredient</th>
                                <th>Type</th>
                                <th>Variance</th>
                                <th>Severity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent_flags as $flag)
                                <tr>
                                    <td>{{ $flag->branch->name ?? '—' }}</td>
                                    <td>{{ $flag->ingredient->name ?? '—' }}</td>
                                    <td>{{ str_replace('_', ' ', ucfirst($flag->type)) }}</td>
                                    <td class="variance-cell">{{ $flag->variance !== null ? number_format($flag->variance, 2) : '—' }}</td>
                                    <td>
                                        @php $sevColors = ['high' => 'red', 'medium' => 'amber', 'low' => 'blue']; @endphp
                                        <span class="badge {{ $sevColors[$flag->severity] ?? 'gray' }}">{{ ucfirst($flag->severity) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="kpi-grid">
                <div class="stat-card">
                    <div class="stat-label">Annual Revenue (total)</div>
                    <div class="stat-value">&#8369;{{ number_format($annual_revenue, 0) }}</div>
                    <span class="stat-badge blue">{{ now()->year }}</span>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Percentage of Leakage (overall)</div>
                    <div class="stat-value">{{ number_format($leakage_pct, 1) }}%</div>
                    <span class="stat-badge red">Leakage</span>
                    <span class="stat-note">Est. across all stock counts.</span>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Value Saved</div>
                    <div class="stat-value">&#8369;{{ number_format($value_saved, 0) }}</div>
                    <span class="stat-badge green">Caught</span>
                    <span class="stat-note">Leakage caught &amp; reviewed, est.</span>
                </div>
            </div>

            <div class="two-up">
                <div class="widget">
                    <div class="widget-head">
                        <h2>Top Earner</h2>
                    </div>
                    @forelse ($top_earners as $i => $earner)
                        <div class="branch-live-row">
                            <div class="branch-live-left">
                                <span class="rank-num">{{ $i + 1 }}</span>
                                <span>{{ $earner->name }}</span>
                            </div>
                            <span class="branch-live-amount">&#8369;{{ number_format($earner->revenue ?? 0, 0) }}</span>
                        </div>
                    @empty
                        <div class="empty-state">No branches yet.</div>
                    @endforelse
                </div>

                <div class="widget">
                    <div class="widget-head">
                        <h2>Least Leakage</h2>
                    </div>
                    @forelse ($least_leakage as $i => $row)
                        <div class="branch-live-row">
                            <div class="branch-live-left">
                                <span class="rank-num">{{ $i + 1 }}</span>
                                <span>{{ $row['name'] }}</span>
                                @if ($i === 0)
                                    <span class="dot green"></span>
                                @endif
                            </div>
                            <span class="branch-live-amount">-{{ rtrim(rtrim(number_format($row['leak'], 2), '0'), '.') ?: '0' }} units</span>
                        </div>
                    @empty
                        <div class="empty-state">No branches yet.</div>
                    @endforelse
                </div>
            </div>

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

        </div>

        <div class="dash-aside">

            <div class="widget">
                <div class="calendar-head">
                    <span class="cal-month">{{ now()->format('F Y') }}</span>
                </div>
                @php
                    $startOfMonth = now()->startOfMonth();
                    $leadBlanks = $startOfMonth->dayOfWeek; // 0 = Sunday
                    $daysInMonth = $startOfMonth->daysInMonth;
                    $today = now()->day;
                @endphp
                <div class="calendar-grid">
                    @foreach (['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $dow)
                        <span class="cal-dow">{{ $dow }}</span>
                    @endforeach
                    @for ($i = 0; $i < $leadBlanks; $i++)
                        <span class="cal-day muted"></span>
                    @endfor
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        <span class="cal-day {{ $day === $today ? 'today' : '' }}">{{ $day }}</span>
                    @endfor
                </div>
            </div>

            <div class="widget">
                <div class="widget-head">
                    <h2>Ongoing Shifts</h2>
                </div>
                @forelse ($ongoing_shifts as $shift)
                    <div class="branch-live-row">
                        <div class="branch-live-left">
                            <span class="dot green"></span>
                            <div>
                                <div>{{ $shift->user->name ?? '—' }}</div>
                                <div class="detail-sub" style="font-weight: 400;">{{ $shift->branch->name ?? '—' }}</div>
                            </div>
                        </div>
                        <span class="branch-live-amount" style="font-size: 12px;">{{ $shift->shift_start?->format('g:iA') ?? '—' }}</span>
                    </div>
                @empty
                    <div class="empty-state">No shifts in progress.</div>
                @endforelse
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
    </div>

@endsection
