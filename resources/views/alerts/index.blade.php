@extends('layouts.sidebar')

@section('title', 'Discrepancy Alerts')
@section('subtitle', 'Stock mismatches and shift variances requiring review')

@section('content')

    @php
        $severityBadges = ['high' => 'red', 'medium' => 'amber', 'low' => 'blue'];
        $statusBadges = ['pending' => 'amber', 'reviewed' => 'green', 'dismissed' => 'gray'];
    @endphp

    <div class="filter-tabs status-tabs">
        <button type="button" class="tab active" data-status="all">All</button>
        <button type="button" class="tab" data-status="pending">Pending</button>
        <button type="button" class="tab" data-status="reviewed">Reviewed</button>
        <button type="button" class="tab" data-status="dismissed">Dismissed</button>
    </div>

    <div class="toolbar">
        <select id="severity-filter" class="select-filter">
            <option value="all">All Severities</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
        <div></div>
    </div>

    @if ($alerts->isEmpty())
        <div class="card-panel">
            <div class="all-clear">No alerts &mdash; all clear &#10003;</div>
        </div>
    @else
        <div class="card-panel">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Ingredient</th>
                        <th>Expected</th>
                        <th>Actual</th>
                        <th>Variance</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="alerts-tbody">
                    @foreach ($alerts as $alert)
                        <tr data-status="{{ $alert->status }}" data-severity="{{ $alert->severity }}">
                            <td class="cell-primary">{{ $alert->branch->name ?? '—' }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $alert->type)) }}</td>
                            <td><span class="badge {{ $severityBadges[$alert->severity] ?? 'gray' }}">{{ ucfirst($alert->severity) }}</span></td>
                            <td>{{ $alert->ingredient->name ?? '—' }}</td>
                            <td>{{ $alert->expected_value !== null ? number_format($alert->expected_value, 2) : '—' }}</td>
                            <td>{{ $alert->actual_value !== null ? number_format($alert->actual_value, 2) : '—' }}</td>
                            <td class="variance-cell">{{ $alert->variance !== null ? '−' . number_format(abs($alert->variance), 2) : '—' }}</td>
                            <td><span class="badge {{ $statusBadges[$alert->status] ?? 'gray' }}">{{ ucfirst($alert->status) }}</span></td>
                            <td>{{ $alert->created_at->format('M d, Y g:iA') }}</td>
                            <td>
                                @if ($alert->status === 'pending')
                                    <button type="button" class="btn-pill">Review</button>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <script>
        (function () {
            const statusTabs = document.querySelectorAll('.status-tabs .tab');
            const severitySelect = document.getElementById('severity-filter');
            const rows = document.querySelectorAll('#alerts-tbody tr[data-status]');
            let activeStatus = 'all';

            function applyFilters() {
                const severity = severitySelect?.value || 'all';
                rows.forEach(function (row) {
                    const matchesStatus = activeStatus === 'all' || row.dataset.status === activeStatus;
                    const matchesSeverity = severity === 'all' || row.dataset.severity === severity;
                    row.style.display = (matchesStatus && matchesSeverity) ? '' : 'none';
                });
            }

            statusTabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    statusTabs.forEach(function (t) { t.classList.remove('active'); });
                    tab.classList.add('active');
                    activeStatus = tab.dataset.status;
                    applyFilters();
                });
            });

            severitySelect?.addEventListener('change', applyFilters);
        })();
    </script>

@endsection
