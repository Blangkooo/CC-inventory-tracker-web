<div class="dash-main">

    <div class="toolbar" style="margin-bottom: 0;">
        <div style="font-size:13px;font-weight:600;opacity:.6;">
            {{ $workers->count() }} {{ Str::plural('worker', $workers->count()) }}
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ url('/business/workers') }}" class="btn-pill">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:4px;">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Manage All Workers
            </a>
        </div>
    </div>

    <div class="card-panel">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Single query for all open shifts (avoids N+1)
                    $openShiftUserIds = \App\Models\ShiftLog::where('status', 'open')
                        ->whereIn('user_id', $workers->pluck('id'))
                        ->pluck('user_id')
                        ->unique()
                        ->toArray();
                @endphp
                @forelse ($workers as $worker)
                    @php $isOnShift = in_array($worker->id, $openShiftUserIds); @endphp
                    <tr>
                        <td class="cell-primary">
                            <a href="{{ url('/business/workers') }}?worker={{ $worker->id }}" style="color:var(--brown);text-decoration:none;">
                                {{ $worker->name }}
                            </a>
                        </td>
                        <td>{{ $worker->email ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $worker->role === \App\Models\User::ROLE_MANAGER ? 'blue' : 'gray' }}">
                                {{ $worker->role === \App\Models\User::ROLE_MANAGER ? 'Manager' : 'Staff' }}
                            </span>
                        </td>
                        <td>{{ $worker->branch?->name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $isOnShift ? 'green' : 'gray' }}">
                                {{ $isOnShift ? 'On Shift' : 'Off Duty' }}
                            </span>
                        </td>
                        <td>{{ $worker->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span class="empty-state-text">No workers assigned to this branch yet.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
