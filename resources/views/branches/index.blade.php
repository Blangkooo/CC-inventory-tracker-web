@extends('layouts.app')

@section('title', 'Branches')
@section('subtitle', 'All franchise locations')

@section('content')

    <div class="toolbar">
        <div></div>
        <button type="button" class="btn-primary">Add Branch</button>
    </div>

    <div class="card-panel">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Branch Name</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Today's Sales</th>
                    <th>Staff Count</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($branches as $branch)
                    <tr>
                        <td class="cell-primary">{{ $branch->name }}</td>
                        <td>{{ $branch->location ?? '—' }}</td>
                        <td><span class="badge {{ $branch->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($branch->status) }}</span></td>
                        <td class="cell-primary">&#8369;{{ number_format($branch->transactions->sum('total_amount'), 2) }}</td>
                        <td>{{ $branch->users->count() }}</td>
                        <td><button type="button" class="btn-pill">View</button></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">No branches yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
