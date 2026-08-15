@extends('layouts.sidebar')

@section('title', 'Inventory Levels')
@section('subtitle', 'Live stock across all branches &middot; updated per transaction')

@section('content')

    <div class="toolbar">
        <select id="branch-filter" class="select-filter">
            <option value="all">All Branches</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="summary-cards">
        <div class="summary-card green">
            <div class="summary-count">{{ $ok_count }}</div>
            <div class="summary-label">OK</div>
        </div>
        <div class="summary-card amber">
            <div class="summary-count">{{ $low_count }}</div>
            <div class="summary-label">Low</div>
        </div>
        <div class="summary-card red">
            <div class="summary-count">{{ $out_count }}</div>
            <div class="summary-label">Out</div>
        </div>
    </div>

    @php
        $statusLabels = ['ok' => 'OK', 'low' => 'Low', 'out' => 'Out'];
        $statusBadges = ['ok' => 'green', 'low' => 'amber', 'out' => 'red'];
    @endphp

    <div class="card-panel">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Unit</th>
                    <th>Branch</th>
                    <th>On Hand</th>
                    <th>Min Threshold</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody id="stock-tbody">
                @forelse ($stocks as $stock)
                    <tr data-branch="{{ $stock->branch_id }}">
                        <td class="cell-primary">{{ $stock->ingredient->name }}</td>
                        <td>{{ $stock->ingredient->unit }}</td>
                        <td>{{ $stock->branch->name }}</td>
                        <td>{{ rtrim(rtrim(number_format($stock->current_quantity, 3), '0'), '.') }}</td>
                        <td>{{ rtrim(rtrim(number_format($stock->min_threshold, 3), '0'), '.') }}</td>
                        <td><span class="badge {{ $statusBadges[$stock->stock_status] }}">{{ $statusLabels[$stock->stock_status] }}</span></td>
                        <td>{{ $stock->last_updated_at?->format('M d, Y g:iA') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2"/><path d="M3 8h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 12h6"/></svg>
                                <span class="empty-state-text">No stock records found.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('branch-filter')?.addEventListener('change', function () {
            const value = this.value;
            document.querySelectorAll('#stock-tbody tr[data-branch]').forEach(function (row) {
                row.style.display = (value === 'all' || row.dataset.branch === value) ? '' : 'none';
            });
        });
    </script>

@endsection
