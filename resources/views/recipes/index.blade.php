@extends('layouts.app')

@section('title', 'Recipes & Formulas')
@section('subtitle', 'Ingredient-to-product mappings &middot; controls auto-deduction')

@section('content')

    <div class="toolbar">
        <input type="text" id="product-search" class="search-input" placeholder="Search products&hellip;">
        <button type="button" class="btn-primary">New Product</button>
    </div>

    <div class="filter-tabs">
        <button type="button" class="tab active" data-category="all">All Products ({{ $products->count() }})</button>
        @foreach ($categories as $category)
            <button type="button" class="tab" data-category="{{ $category }}">
                {{ $category }} ({{ $products->where('category', $category)->count() }})
            </button>
        @endforeach
    </div>

    <div class="card-panel">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Ingredients</th>
                    <th>Price</th>
                    <th>Last Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="products-tbody">
                @forelse ($products as $product)
                    <tr data-category="{{ $product->category }}" data-name="{{ strtolower($product->name) }}">
                        <td class="cell-primary">{{ $product->name }}</td>
                        <td>{{ $product->category ?? '—' }}</td>
                        <td>{{ $product->recipes->count() }} ingredients</td>
                        <td class="cell-primary">&#8369;{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->updated_at->format('M d, Y') }}</td>
                        <td><button type="button" class="btn-pill btn-pill-sm">Edit</button></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">No products yet. Click New Product to add one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        (function () {
            const search = document.getElementById('product-search');
            const tabs = document.querySelectorAll('.filter-tabs .tab');
            const rows = document.querySelectorAll('#products-tbody tr[data-name]');
            let activeCategory = 'all';

            function applyFilters() {
                const term = (search?.value || '').toLowerCase();
                rows.forEach(function (row) {
                    const matchesCategory = activeCategory === 'all' || row.dataset.category === activeCategory;
                    const matchesSearch = row.dataset.name.includes(term);
                    row.style.display = (matchesCategory && matchesSearch) ? '' : 'none';
                });
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) { t.classList.remove('active'); });
                    tab.classList.add('active');
                    activeCategory = tab.dataset.category;
                    applyFilters();
                });
            });

            search?.addEventListener('input', applyFilters);
        })();
    </script>

@endsection
