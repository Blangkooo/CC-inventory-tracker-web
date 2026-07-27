@extends('layouts.sidebar')

@section('title', 'Recipes & Formulas')
@section('subtitle', 'Ingredient-to-product mappings &middot; controls auto-deduction')

@section('content')
<style>
    /* ── MODAL ── */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 1000;
        background: rgba(92,45,27,.45);
        display: none; align-items: center; justify-content: center;
        padding: 24px;
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
    }

    .modal-overlay.is-open { display: flex; }

    .modal-box {
        background: #fff; border-radius: 16px;
        box-shadow: 0 8px 40px rgba(92,45,27,.2);
        width: 100%; max-width: 720px; max-height: 90vh;
        display: flex; flex-direction: column;
        animation: modalIn .2s ease;
    }

    @keyframes modalIn {
        from { opacity: 0; transform: translateY(20px) scale(.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 24px; border-bottom: 1px solid var(--border);
    }

    .modal-header h2 { font-size: 17px; font-weight: 800; }

    .modal-close {
        width: 32px; height: 32px; border-radius: 8px;
        border: none; background: transparent; color: var(--brown);
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: background .15s;
    }

    .modal-close:hover { background: rgba(92,45,27,.08); }

    .modal-body { flex: 1; overflow-y: auto; padding: 24px; }
    .modal-body .field { margin-bottom: 16px; }

    .modal-body .field-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; opacity: .6; margin-bottom: 6px;
    }

    .modal-body input,
    .modal-body select,
    .modal-body textarea {
        width: 100%; padding: 0 14px;
        height: 42px;
        background: var(--cream); border: 1.5px solid var(--border);
        border-radius: 10px; font-size: 13px; color: var(--brown);
        font-family: var(--font);
        transition: border-color .15s, box-shadow .15s;
    }

    .modal-body input:focus,
    .modal-body select:focus,
    .modal-body textarea:focus {
        outline: none; border-color: var(--terra);
        box-shadow: 0 0 0 3px rgba(188,97,75,.12);
    }

    .modal-body textarea { height: auto; min-height: 80px; padding: 10px 14px; resize: vertical; }

    .ingredient-row {
        display: grid; grid-template-columns: 2fr 1fr 1fr 80px 36px;
        gap: 8px; align-items: center; margin-bottom: 8px;
    }

    .ingredient-row select,
    .ingredient-row input { height: 38px; }

    .ingredient-row .unit-badge {
        font-size: 11px; font-weight: 600; opacity: .5;
        text-align: center;
    }

    .btn-remove-ingredient {
        width: 36px; height: 36px; border-radius: 8px;
        border: 1.5px solid #fecaca; background: #fef2f2;
        color: #991b1b; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all .15s; flex-shrink: 0;
    }

    .btn-remove-ingredient:hover { background: #991b1b; color: #fff; border-color: #991b1b; }

    .btn-add-ingredient {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; margin-top: 4px;
        background: var(--cream); border: 1.5px dashed var(--border);
        border-radius: 10px; font-size: 12px; font-weight: 600;
        color: var(--brown); cursor: pointer; font-family: var(--font);
        transition: all .15s;
    }

    .btn-add-ingredient:hover { border-color: var(--terra); color: var(--terra); background: rgba(188,97,75,.06); }

    .modal-footer {
        display: flex; align-items: center; gap: 10px; justify-content: flex-end;
        padding: 16px 24px; border-top: 1px solid var(--border);
        background: rgba(92,45,27,.02);
    }

    .btn-save {
        padding: 9px 24px; background: var(--terra); color: #fff;
        border: none; border-radius: 10px;
        font-size: 13px; font-weight: 700; cursor: pointer; font-family: var(--font);
        transition: background .15s;
    }

    .btn-save:hover { background: #a8523e; }
    .btn-save:disabled { opacity: .5; cursor: not-allowed; }

    .btn-cancel {
        padding: 9px 20px; background: #fff; color: var(--brown);
        border: 1.5px solid var(--border); border-radius: 10px;
        font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font);
        transition: all .15s;
    }

    .btn-cancel:hover { background: rgba(92,45,27,.05); }

    .btn-delete-product {
        padding: 9px 16px; margin-right: auto;
        background: #fef2f2; color: #991b1b;
        border: 1.5px solid #fecaca; border-radius: 10px;
        font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
        transition: all .15s;
    }

    .btn-delete-product:hover { background: #991b1b; color: #fff; border-color: #991b1b; }

    .save-status {
        font-size: 12px; font-weight: 600;
        padding: 8px 14px; border-radius: 8px;
        display: none;
    }

    .save-status.is-visible { display: inline-block; }
    .save-status--success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .save-status--error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

    .ingredient-headers {
        display: grid; grid-template-columns: 2fr 1fr 1fr 80px 36px;
        gap: 8px; align-items: center; margin-bottom: 6px;
    }

    .ingredient-headers span {
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; opacity: .4;
    }

    .procedures-section { margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }

    @media (max-width: 768px) {
        .ingredient-row { grid-template-columns: 1fr 1fr; }
        .ingredient-headers { display: none; }
    }
</style>

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
                    <td class="cell-primary">&#8369;{{ number_format($product->price ?? 0, 2) }}</td>
                    <td>{{ $product->updated_at->format('M d, Y') }}</td>
                    <td><button type="button" class="btn-pill btn-pill-sm btn-edit" data-product-id="{{ $product->id }}">Edit</button></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-state">No products yet. Click New Product to add one.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ── EDIT MODAL ── --}}
<div class="modal-overlay" id="recipe-modal">
    <div class="modal-box">
        <div class="modal-header">
            <h2 id="modal-title">Edit Recipe</h2>
            <button class="modal-close" onclick="closeModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="modal-body">
            <input type="hidden" id="edit-product-id">

            <div class="field">
                <div class="field-label">Product Name</div>
                <input type="text" id="edit-product-name" placeholder="e.g. Classic Milk Tea">
            </div>

            <div class="field" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <div class="field-label">Category</div>
                    <input type="text" id="edit-product-category" placeholder="e.g. Milk Tea">
                </div>
                <div>
                    <div class="field-label">Price (₱)</div>
                    <input type="number" id="edit-product-price" step="0.01" min="0" placeholder="0.00">
                </div>
            </div>

            <div style="margin-top:20px">
                <div class="field-label" style="margin-bottom:8px">Ingredients</div>

                <div class="ingredient-headers">
                    <span>Ingredient</span>
                    <span>Regular Amt</span>
                    <span>Large Amt</span>
                    <span>Unit</span>
                    <span></span>
                </div>

                <div id="ingredient-list"></div>

                <button class="btn-add-ingredient" onclick="addIngredientRow()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Ingredient
                </button>
            </div>

            <div class="procedures-section">
                <div class="field-label">Preparation Procedure</div>
                <textarea id="edit-product-procedure" placeholder="Step-by-step preparation instructions…"></textarea>
            </div>

            <div class="save-status" id="save-status"></div>
        </div>

        <div class="modal-footer">
            <button class="btn-delete-product" id="btn-delete-product" onclick="deleteProduct()">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
                Delete
            </button>
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn-save" id="btn-save" onclick="saveRecipe()">Save Changes</button>
        </div>
    </div>
</div>

<template id="ingredient-row-template">
    <div class="ingredient-row" data-ingredient-row>
        <select>
            <option value="">Select ingredient…</option>
            @foreach ($allIngredients as $ing)
                <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}">{{ $ing->name }}</option>
            @endforeach
        </select>
        <input type="number" step="0.001" min="0.001" placeholder="Regular qty" data-size="regular">
        <input type="number" step="0.001" min="0.001" placeholder="Large qty" data-size="large">
        <span class="unit-badge" data-unit-display></span>
        <button class="btn-remove-ingredient" onclick="removeIngredientRow(this)" title="Remove ingredient">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
</template>

<script>
// ── All ingredients lookup ──
var ALL_INGREDIENTS = @json($allIngredients->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit]));
var CSRF_TOKEN = '{{ csrf_token() }}';
var _editingProductId = null;

// ── Open modal ──
function openModal(productId) {
    _editingProductId = productId;
    document.getElementById('recipe-modal').classList.add('is-open');
    document.getElementById('modal-title').textContent = 'Loading…';
    document.getElementById('btn-save').disabled = true;
    fetchProductData(productId);
}

// ── Fetch product data ──
function fetchProductData(productId) {
    fetch('/business/recipes/product/' + productId + '/data', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) { populateModal(data); })
    .catch(function () { populateFromCard(productId); });
}

// ── Populate from page data ──
function populateFromCard(productId) {
    var product = PRODUCTS_DATA.find(function (p) { return p.id == productId; });
    if (product) populateModal(product);
    else {
        document.getElementById('modal-title').textContent = 'Edit Recipe';
        document.getElementById('btn-save').disabled = false;
    }
}

// ── Populate modal form ──
function populateModal(data) {
    document.getElementById('edit-product-id').value = data.id;
    document.getElementById('edit-product-name').value = data.name || '';
    document.getElementById('edit-product-category').value = data.category || '';
    document.getElementById('edit-product-price').value = data.price || 0;
    document.getElementById('edit-product-procedure').value = data.procedure || '';
    document.getElementById('modal-title').textContent = 'Edit: ' + (data.name || 'Recipe');

    var list = document.getElementById('ingredient-list');
    list.innerHTML = '';

    if (data.recipes && data.recipes.length > 0) {
        var grouped = {};
        data.recipes.forEach(function (r) {
            var ingId = r.ingredient_id;
            if (!grouped[ingId]) {
                grouped[ingId] = {
                    ingredient_id: ingId,
                    ingredient: r.ingredient,
                    regular_id: null, large_id: null,
                    regular: null, large: null
                };
            }
            if (r.size === 'regular') {
                grouped[ingId].regular_id = r.id;
                grouped[ingId].regular = r.quantity_required;
            } else {
                grouped[ingId].large_id = r.id;
                grouped[ingId].large = r.quantity_required;
            }
        });

        Object.keys(grouped).forEach(function (ingId) {
            addIngredientRow(grouped[ingId]);
        });
    }

    document.getElementById('btn-save').disabled = false;
}

// ── Add ingredient row ──
function addIngredientRow(data) {
    data = data || {};
    var template = document.getElementById('ingredient-row-template');
    var clone = template.content.cloneNode(true);
    var row = clone.querySelector('[data-ingredient-row]');
    var select = row.querySelector('select');
    var regInput = row.querySelector('[data-size="regular"]');
    var lrgInput = row.querySelector('[data-size="large"]');
    var unitDisplay = row.querySelector('[data-unit-display]');

    if (data.ingredient_id) {
        select.value = data.ingredient_id;
        if (select.selectedIndex >= 0) {
            unitDisplay.textContent = select.options[select.selectedIndex].dataset.unit || '';
        }
    }
    if (data.regular) regInput.value = data.regular;
    if (data.large) lrgInput.value = data.large;
    if (data.regular_id) row.dataset.regularId = data.regular_id;
    if (data.large_id) row.dataset.largeId = data.large_id;

    select.addEventListener('change', function () {
        var opt = select.options[select.selectedIndex];
        unitDisplay.textContent = opt ? (opt.dataset.unit || '') : '';
    });

    document.getElementById('ingredient-list').appendChild(clone);
}

// ── Remove ingredient row ──
function removeIngredientRow(btn) {
    var row = btn.closest('[data-ingredient-row]');
    if (row) row.remove();
}

// ── Save recipe ──
function saveRecipe() {
    var name = document.getElementById('edit-product-name').value.trim();
    if (!name) {
        alert('Please enter a product name.');
        document.getElementById('edit-product-name').focus();
        return;
    }

    var btn = document.getElementById('btn-save');
    btn.disabled = true;
    var status = document.getElementById('save-status');
    status.className = 'save-status';
    status.textContent = 'Saving…';
    status.classList.add('is-visible');

    var productId = document.getElementById('edit-product-id').value;
    if (!productId) return;

    var productData = {
        name: document.getElementById('edit-product-name').value,
        category: document.getElementById('edit-product-category').value,
        price: parseFloat(document.getElementById('edit-product-price').value) || 0,
        procedure: document.getElementById('edit-product-procedure').value,
    };

    var requests = [
        fetch('/business/recipes/product/' + productId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify(productData)
        })
    ];

    var rows = document.querySelectorAll('#ingredient-list [data-ingredient-row]');
    var addedIngredients = {};

    rows.forEach(function (row) {
        var select = row.querySelector('select');
        var ingredientId = select.value;
        if (!ingredientId) return;

        var regularQty = parseFloat(row.querySelector('[data-size="regular"]').value);
        var largeQty = parseFloat(row.querySelector('[data-size="large"]').value);
        var regularId = row.dataset.regularId;
        var largeId = row.dataset.largeId;

        if (regularQty && regularQty > 0) {
            var key = ingredientId + '-regular';
            if (!addedIngredients[key]) {
                addedIngredients[key] = true;
                if (regularId) {
                    requests.push(
                        fetch('/business/recipes/ingredient/' + regularId, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
                            body: JSON.stringify({ quantity_required: regularQty })
                        })
                    );
                } else {
                    requests.push(
                        fetch('/business/recipes/product/' + productId + '/ingredient', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
                            body: JSON.stringify({ ingredient_id: ingredientId, size: 'regular', quantity_required: regularQty })
                        })
                    );
                }
            }
        }

        if (largeQty && largeQty > 0) {
            var key = ingredientId + '-large';
            if (!addedIngredients[key]) {
                addedIngredients[key] = true;
                if (largeId) {
                    requests.push(
                        fetch('/business/recipes/ingredient/' + largeId, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
                            body: JSON.stringify({ quantity_required: largeQty })
                        })
                    );
                } else {
                    requests.push(
                        fetch('/business/recipes/product/' + productId + '/ingredient', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
                            body: JSON.stringify({ ingredient_id: ingredientId, size: 'large', quantity_required: largeQty })
                        })
                    );
                }
            }
        }
    });

    Promise.all(requests)
    .then(function (responses) {
        var allOk = responses.every(function (r) { return r.ok; });
        if (allOk) {
            status.className = 'save-status is-visible save-status--success';
            status.textContent = 'Saved successfully!';
            setTimeout(function () {
                closeModal();
                window.location.reload();
            }, 800);
        } else {
            status.className = 'save-status is-visible save-status--error';
            status.textContent = 'Something went wrong. Check your inputs and try again.';
            btn.disabled = false;
        }
    })
    .catch(function () {
        status.className = 'save-status is-visible save-status--error';
        status.textContent = 'Network error. Please try again.';
        btn.disabled = false;
    });
}

// ── Delete product ──
function deleteProduct() {
    if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) return;
    var productId = document.getElementById('edit-product-id').value;
    if (!productId) return;

    var btn = document.getElementById('btn-delete-product');
    btn.disabled = true;

    fetch('/business/recipes/product/' + productId + '/delete', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
    })
    .then(function (r) { return r.json(); })
    .then(function () {
        closeModal();
        window.location.reload();
    })
    .catch(function () {
        btn.disabled = false;
        alert('Failed to delete product.');
    });
}

// ── Close modal ──
function closeModal() {
    document.getElementById('recipe-modal').classList.remove('is-open');
    _editingProductId = null;
}

document.addEventListener('click', function (e) {
    if (e.target === document.getElementById('recipe-modal')) closeModal();
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
});

// ── Wire up Edit buttons ──
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var productId = this.dataset.productId;
            if (productId) openModal(productId);
        });
    });
});

@php
    $productsData = $products->map(function ($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'category' => $p->category,
            'price' => $p->price,
            'procedure' => $p->procedure ?? '',
            'recipes' => $p->recipes->map(function ($r) {
                return [
                    'id' => $r->id,
                    'ingredient_id' => $r->ingredient_id,
                    'size' => $r->size,
                    'quantity_required' => $r->quantity_required,
                    'ingredient' => $r->ingredient ? [
                        'id' => $r->ingredient_id,
                        'name' => $r->ingredient->name,
                        'unit' => $r->ingredient->unit,
                    ] : null,
                ];
            })->values(),
        ];
    })->values();
@endphp
// ── Embedded product data ──
var PRODUCTS_DATA = @json($productsData);
</script>

@endsection
