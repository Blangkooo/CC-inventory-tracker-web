@extends('layouts.app')

@section('title', $branch->name)
@section('subtitle', ($branch->location ? $branch->location . ' &middot; ' : '') . ucfirst($branch->status))

@section('content')

    <div class="master-detail">
        <div class="widget">
            <div class="widget-head">
                <h2>Businesses</h2>
            </div>
            <div class="biz-list">
                @foreach ($all_branches as $b)
                    <a href="{{ route('branches.show', $b) }}?tab={{ $tab }}"
                       class="{{ $b->id === $branch->id ? 'active' : '' }}">
                        <span class="dot {{ $b->status === 'active' ? 'green' : 'red' }}"></span>
                        {{ $b->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <div>
            <div class="detail-header">
                <h2>{{ $branch->name }}</h2>
                <span class="badge {{ $branch->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($branch->status) }}</span>
                <span class="detail-sub">{{ $branch->location ?? '' }}</span>
            </div>

            <div class="filter-tabs">
                <a href="{{ route('branches.show', $branch) }}?tab=analytics" class="tab {{ $tab === 'analytics' ? 'active' : '' }}">Analytics</a>
                <a href="{{ route('branches.show', $branch) }}?tab=recipe" class="tab {{ $tab === 'recipe' ? 'active' : '' }}">Recipe</a>
                <a href="{{ route('branches.show', $branch) }}?tab=workers" class="tab {{ $tab === 'workers' ? 'active' : '' }}">Workers</a>
                <a href="{{ route('branches.show', $branch) }}?tab=logistics" class="tab {{ $tab === 'logistics' ? 'active' : '' }}">Logistics</a>
            </div>

            @include('branches.tabs.' . $tab)
        </div>
    </div>

@endsection
