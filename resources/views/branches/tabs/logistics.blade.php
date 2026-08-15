<div class="dash-main">

    @php
        $statusLabels = ['ok' => 'OK', 'low' => 'Low', 'out' => 'Out'];
        $statusBadges = ['ok' => 'green', 'low' => 'amber', 'out' => 'red'];
        $typeBadges = ['sale' => 'blue', 'restock' => 'green', 'shift_correction' => 'amber', 'initial' => 'gray'];
        $typeLabels = ['sale' => 'Sale', 'restock' => 'Restock', 'shift_correction' => 'Shift Correction', 'initial' => 'Initial'];
    @endphp

    <div class="widget">
        <div class="widget-head">
            <h2>Stock Levels</h2>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Unit</th>
                    <th>On Hand</th>
                    <th>Min Threshold</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stocks as $stock)
                    <tr>
                        <td class="cell-primary">{{ $stock->ingredient->name }}</td>
                        <td>{{ $stock->ingredient->unit }}</td>
                        <td>{{ rtrim(rtrim(number_format($stock->current_quantity, 3), '0'), '.') }}</td>
                        <td>{{ rtrim(rtrim(number_format($stock->min_threshold, 3), '0'), '.') }}</td>
                        <td><span class="badge {{ $statusBadges[$stock->stock_status] }}">{{ $statusLabels[$stock->stock_status] }}</span></td>
                        <td>{{ $stock->last_updated_at?->format('M d, Y g:iA') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2"/><path d="M3 8h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 12h6"/></svg>
                                <span class="empty-state-text">No stock records for this branch yet.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="widget">
        <div class="widget-head">
            <h2>Recent Movements</h2>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Ingredient</th>
                    <th>Type</th>
                    <th>Change</th>
                    <th>Before &rarr; After</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $m)
                    <tr>
                        <td>{{ $m->created_at->format('M d, g:iA') }}</td>
                        <td class="cell-primary">{{ $m->branchStock->ingredient->name ?? '—' }}</td>
                        <td><span class="badge {{ $typeBadges[$m->type] ?? 'gray' }}">{{ $typeLabels[$m->type] ?? ucfirst($m->type) }}</span></td>
                        <td class="{{ $m->quantity_change < 0 ? 'variance-cell' : 'cell-primary' }}">
                            {{ $m->quantity_change > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($m->quantity_change, 3), '0'), '.') }}
                        </td>
                        <td>{{ rtrim(rtrim(number_format($m->quantity_before, 3), '0'), '.') }} &rarr; {{ rtrim(rtrim(number_format($m->quantity_after, 3), '0'), '.') }}</td>
                        <td>{{ $m->user->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                <span class="empty-state-text">No stock movements recorded yet.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
