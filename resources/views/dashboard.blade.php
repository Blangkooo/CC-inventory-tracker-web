@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Welcome back, ' . auth()->user()->name . '!')

@section('content')

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Total Branches</div>
            <div class="stat-value">{{ $total_branches }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Products Tracked</div>
            <div class="stat-value">{{ $total_products }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Ingredients Monitored</div>
            <div class="stat-value">{{ $total_ingredients }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Alerts</div>
            <div class="stat-value">{{ $total_alerts }}</div>
        </div>
    </div>

    <div class="widget-grid">
        <div class="widget">
            <h2>Recipe formulas</h2>

            @forelse ($products as $product)
                <div class="recipe-item">
                    <div class="recipe-head">
                        <span class="recipe-name">{{ $product->name }}</span>
                        <span class="recipe-price">&#8369;{{ number_format($product->price, 2) }}</span>
                    </div>

                    @if ($product->ingredients->isEmpty())
                        <div class="empty-state" style="padding: 8px 0;">No ingredients linked to this product yet.</div>
                    @else
                        <div class="ingredient-tags">
                            @foreach ($product->ingredients as $ingredient)
                                <span class="ingredient-tag">{{ $ingredient->name }} &mdash; {{ rtrim(rtrim(number_format($ingredient->pivot->quantity_required, 3), '0'), '.') }}{{ $ingredient->unit }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state">No products have been added yet.</div>
            @endforelse
        </div>

        <div class="widget">
            <h2>Branch stock</h2>

            @forelse ($branch_stocks as $stock)
                @php
                    $initial = 500;
                    $percent = $initial > 0 ? min(100, ($stock->current_quantity / $initial) * 100) : 0;
                    $colorClass = $percent > 50 ? 'green' : ($percent >= 20 ? 'yellow' : 'red');
                    $recipe = $stock->ingredient->recipes->first();
                    $ordersRemaining = $recipe && $recipe->quantity_required > 0
                        ? floor($stock->current_quantity / $recipe->quantity_required)
                        : null;
                @endphp
                <div class="stock-item">
                    <div class="stock-head">
                        <span class="stock-name">{{ $stock->ingredient->name }} ({{ $stock->branch->name }})</span>
                        <span>{{ rtrim(rtrim(number_format($stock->current_quantity, 3), '0'), '.') }}{{ $stock->ingredient->unit }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill {{ $colorClass }}" style="width: {{ $percent }}%"></div>
                    </div>
                    <div class="stock-meta">
                        @if ($ordersRemaining !== null)
                            Est. {{ $ordersRemaining }} order{{ $ordersRemaining == 1 ? '' : 's' }} remaining
                        @else
                            No recipe linked &mdash; can't estimate orders remaining
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">No branch stock recorded yet.</div>
            @endforelse
        </div>
    </div>

    <div class="widget">
        <h2>Branches</h2>

        @forelse ($branches as $branch)
            <div class="branch-row">
                <div class="branch-info">
                    <div class="branch-name">{{ $branch->name }}</div>
                    <div class="branch-location">{{ $branch->location ?? 'No location set' }}</div>
                </div>
                <span class="badge {{ $branch->status === 'active' ? 'active' : 'inactive' }}">
                    {{ ucfirst($branch->status) }}
                </span>
            </div>
        @empty
            <div class="empty-state">No branches have been added yet.</div>
        @endforelse
    </div>

@endsection
