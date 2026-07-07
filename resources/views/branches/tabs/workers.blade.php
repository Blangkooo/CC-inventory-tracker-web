<div class="dash-main">

    <div class="toolbar" style="margin-bottom: 0;">
        <div></div>
        <button type="button" class="btn-primary">Add Worker</button>
    </div>

    <div class="card-panel">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workers as $worker)
                    <tr>
                        <td class="cell-primary">{{ $worker->name }}</td>
                        <td>{{ $worker->email ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $worker->role === \App\Models\User::ROLE_MANAGER ? 'blue' : 'gray' }}">
                                {{ ucfirst($worker->role) }}
                            </span>
                        </td>
                        <td>{{ $worker->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">No workers assigned to this branch yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
