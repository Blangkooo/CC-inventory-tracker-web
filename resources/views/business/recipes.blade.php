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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipes — NITA</title>
    @include('partials._shared-styles')

    <style>
        .workspace { padding: 20px 32px; }

        .recipe-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        @media (max-width: 1100px) { .recipe-grid { grid-template-columns: 1fr; } }

        .search-row { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }

        .search-input {
            width: 280px; height: 38px; padding: 0 14px;
            background: #fff; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; color: var(--brown); font-family: var(--font);
            transition: border-color .15s ease;
        }

        .search-input::placeholder { color: rgba(92,45,27,.4); }
        .search-input:focus { outline: none; border-color: var(--terra); }

        .cat-pills { display: flex; gap: 6px; }

        .cat-pill {
            padding: 7px 18px; border-radius: 999px; font-size: 12px; font-weight: 600;
            border: 1.5px solid var(--border); background: #fff; color: var(--brown);
            cursor: pointer; transition: all .15s ease;
        }

        .cat-pill.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }

        .recipe-card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
        }

        .recipe-card__head {
            display: flex; align-items: center; gap: 8px;
            padding: 12px 16px; border-bottom: 1px solid var(--border);
        }

        .recipe-card__head svg { flex-shrink: 0; opacity: .6; }
        .recipe-card__name { font-size: 14px; font-weight: 700; }

        .recipe-table { width: 100%; border-collapse: collapse; }

        .recipe-table thead th {
            text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; opacity: .45; padding: 8px 16px;
            background: rgba(92,45,27,.03); border-bottom: 1px solid var(--border);
        }

        .recipe-table tbody td {
            padding: 8px 16px; font-size: 12px; vertical-align: top; line-height: 1.5;
            border-bottom: 1px solid rgba(92,45,27,.06);
        }

        .recipe-table tbody tr:last-child td { border-bottom: none; }

        .recipe-card__foot {
            display: flex; align-items: center; gap: 10px; justify-content: flex-end;
            padding: 10px 16px; border-top: 1px solid var(--border);
            background: rgba(92,45,27,.02);
        }

        .recipe-card__foot-label { font-size: 12px; font-weight: 600; opacity: .6; }

        .btn-edit {
            padding: 7px 20px; background: #fff; color: var(--brown);
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
            transition: all .15s ease;
        }

        .btn-edit:hover { background: var(--brown); color: var(--cream); border-color: var(--brown); }

        .modal-overlay {
            position: fixed; inset: 0; z-index: 1000;
            background: rgba(92,45,27,.45);
            display: none; align-items: center; justify-content: center;
            padding: 24px; backdrop-filter: blur(2px);
        }

        .modal-overlay.is-open { display: flex; }

        .modal-box {
            background: #fff; border-radius: 16px;
            box-shadow: 0 8px 40px rgba(92,45,27,.2);
            width: 100%; max-width: 720px; max-height: 90vh;
            display: flex; flex-direction: column; animation: modalIn .2s ease;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: translateY(20px) scale(.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid var(--border); }
        .modal-header h2 { font-size: 17px; font-weight: 800; }

        .modal-close { width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; color: var(--brown); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .15s; }
        .modal-close:hover { background: rgba(92,45,27,.08); }
        .modal-body { flex: 1; overflow-y: auto; padding: 24px; }
        .modal-body .field { margin-bottom: 16px; }
        .modal-body .field-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: .6; margin-bottom: 6px; }
        .modal-body input, .modal-body select, .modal-body textarea { width: 100%; padding: 0 14px; height: 42px; background: var(--cream); border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px; color: var(--brown); font-family: var(--font); transition: border-color .15s, box-shadow .15s; }
        .modal-body input:focus, .modal-body select:focus, .modal-body textarea:focus { outline: none; border-color: var(--terra); box-shadow: 0 0 0 3px rgba(188,97,75,.12); }
        .modal-body textarea { height: auto; min-height: 80px; padding: 10px 14px; resize: vertical; }

        .ingredient-row { display: grid; grid-template-columns: 2fr 1fr 1fr 80px 36px; gap: 8px; align-items: center; margin-bottom: 8px; }
        .ingredient-row select, .ingredient-row input { height: 38px; }
        .ingredient-row .unit-badge { font-size: 11px; font-weight: 600; opacity: .5; text-align: center; }

        .btn-remove-ingredient { width: 36px; height: 36px; border-radius: 8px; border: 1.5px solid #fecaca; background: #fef2f2; color: #991b1b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; flex-shrink: 0; }
        .btn-remove-ingredient:hover { background: #991b1b; color: #fff; border-color: #991b1b; }

        .btn-add-ingredient { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; margin-top: 4px; background: var(--cream); border: 1.5px dashed var(--border); border-radius: 10px; font-size: 12px; font-weight: 600; color: var(--brown); cursor: pointer; font-family: var(--font); transition: all .15s; }
        .btn-add-ingredient:hover { border-color: var(--terra); color: var(--terra); background: rgba(188,97,75,.06); }

        .procedures-section { margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
        .modal-footer { display: flex; align-items: center; gap: 10px; justify-content: flex-end; padding: 16px 24px; border-top: 1px solid var(--border); background: rgba(92,45,27,.02); }

        .btn-save { padding: 9px 24px; background: var(--terra); color: #fff; border: none; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: var(--font); transition: background .15s; }
        .btn-save:hover { background: #a8523e; }
        .btn-save:disabled { opacity: .5; cursor: not-allowed; }

        .btn-cancel { padding: 9px 20px; background: #fff; color: var(--brown); border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font); transition: all .15s; }
        .btn-cancel:hover { background: rgba(92,45,27,.05); }

        .btn-delete-product { padding: 9px 16px; margin-right: auto; background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font); transition: all .15s; }
        .btn-delete-product:hover { background: #991b1b; color: #fff; border-color: #991b1b; }

        .save-status { font-size: 12px; font-weight: 600; padding: 8px 14px; border-radius: 8px; display: none; }
        .save-status.is-visible { display: inline-block; }
        .save-status--success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .save-status--error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .ingredient-headers { display: grid; grid-template-columns: 2fr 1fr 1fr 80px 36px; gap: 8px; align-items: center; margin-bottom: 6px; }
        .ingredient-headers span { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: .4; }

        .size-tag { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .size-tag--regular { background: rgba(92,45,27,.08); color: var(--brown); }
        .size-tag--large { background: rgba(188,97,75,.12); color: var(--terra); }

        @media (max-width: 900px) { .search-row { flex-direction: column; align-items: stretch; } .search-input { width: 100%; } .ingredient-row { grid-template-columns: 1fr 1fr; } .ingredient-headers { display: none; } }
    </style>
</head>
<body>

<nav class="nav">
    <div class="nav__inner">
        <div class="nav__left">
            <a href="{{ url('/dashboard') }}" class="nav__logo"><img src="{{ asset('images/logo.svg') }}" alt="NITA"></a>
            <div class="nav__pills">
                <a href="{{ url('/business/recipes') }}" class="nav__pill is-active">Business</a>
                <a href="{{ url('/logistics') }}" class="nav__pill">Logistics</a>
            </div>
        </div>
        <div class="nav__right">
            <a href="{{ url('/alerts') }}" class="nav__icon" title="Alerts"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></a>
            <a href="{{ url('/alerts') }}" class="nav__icon" title="Messages" style="text-decoration:none"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></a>
            <div class="nav__sep"></div>
            <div class="nav__user">
                <div class="nav__avatar">A</div>
                <div class="nav__user-info"><div class="nav__user-name">Admin Owner</div><div class="nav__user-email">admin@nita.com</div></div>
            </div>
        </div>
    </div>
</nav>

<div class="shell">
    @include('partials._sidebar')

    <main style="padding: 0;">
        @php $currentBusinessTab = 'recipes'; @endphp
        @include('partials._business-header')

        <div style="padding: 20px 32px;">
        <div class="search-row">
            <input type="text" class="search-input" id="recipe-search" placeholder="Search product name…">
            <div class="cat-pills" id="cat-pills">
                <span class="cat-pill is-active" data-cat="">All</span>
                @foreach ($categories as $cat)
                    <span class="cat-pill" data-cat="{{ $cat }}">{{ $cat }}</span>
                @endforeach
            </div>
        </div>

        <div class="recipe-grid">
        @forelse ($products as $product)
        <div class="recipe-card" data-cat="{{ $product->category }}" data-name="{{ strtolower($product->name) }}">
            <div class="recipe-card__head">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/></svg>
                <span class="recipe-card__name">{{ $product->name }}</span>
                <span style="margin-left:auto;font-size:11px;font-weight:600;opacity:.45;text-transform:uppercase;letter-spacing:.04em">&#8369;{{ number_format($product->price ?? 0, 2) }}@if ($product->category) &middot; {{ $product->category }}@endif</span>
            </div>
            @if ($product->recipes->isEmpty())
                <div style="padding:20px 24px;font-size:13px;opacity:.4">No recipe ingredients defined yet.</div>
            @else
                @php
                    $grouped = [];
                    foreach ($product->recipes as $r) {
                        $ingId = $r->ingredient_id;
                        if (!isset($grouped[$ingId])) $grouped[$ingId] = ['ingredient' => $r->ingredient, 'regular' => null, 'large' => null, 'regular_id' => null, 'large_id' => null];
                        if ($r->size === 'regular') { $grouped[$ingId]['regular'] = $r->quantity_required; $grouped[$ingId]['regular_id'] = $r->id; }
                        else { $grouped[$ingId]['large'] = $r->quantity_required; $grouped[$ingId]['large_id'] = $r->id; }
                    }
                @endphp
                @php
                    $displayLimit = 3;
                    $groupedArr = array_values($grouped);
                    $visibleIngredients = array_slice($groupedArr, 0, $displayLimit);
                    $hiddenCount = count($groupedArr) - $displayLimit;
                @endphp
                @if (count($groupedArr) > 0)
                <table class="recipe-table">
                    <thead><tr><th>Ingredient</th><th><span class="size-tag size-tag--regular">Regular</span></th><th><span class="size-tag size-tag--large">Large</span></th><th>Unit</th></tr></thead>
                    <tbody>
                        @foreach ($visibleIngredients as $g)
                            <tr>
                                <td><strong>{{ $g['ingredient']->name ?? '—' }}</strong></td>
                                <td>{{ $g['regular'] !== null ? rtrim(rtrim(number_format($g['regular'], 3), '0'), '.') : '—' }}</td>
                                <td>{{ $g['large'] !== null ? rtrim(rtrim(number_format($g['large'], 3), '0'), '.') : '—' }}</td>
                                <td>{{ $g['ingredient']->unit ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
                @if ($hiddenCount > 0)
                    <div style="padding:6px 16px;font-size:11px;font-weight:600;color:var(--terra);background:rgba(180,83,83,.04);border-top:1px solid var(--border);">
                        +{{ $hiddenCount }} more ingredient{{ $hiddenCount > 1 ? 's' : '' }}
                    </div>
                @endif
            @endif
            <div class="recipe-card__foot">
                <span class="recipe-card__foot-label">{{ $product->recipes->count() }} ingredient{{ $product->recipes->count() !== 1 ? 's' : '' }}</span>
                <button class="btn-edit" data-product-id="{{ $product->id }}">Edit</button>
            </div>
        </div>
        @empty
            <div style="text-align:center;padding:40px;opacity:.35;font-size:14px;grid-column:1/-1">No products with recipes found.</div>
        @endforelse
        </div>

        <script>
        (function () {
            var activeCat = '';
            var search = document.getElementById('recipe-search');
            var pills = document.querySelectorAll('#cat-pills .cat-pill');
            var cards = document.querySelectorAll('.recipe-card');
            function filter() {
                var q = search.value.toLowerCase().trim();
                cards.forEach(function (card) { card.style.display = ((!activeCat || card.dataset.cat === activeCat) && (!q || card.dataset.name.includes(q))) ? '' : 'none'; });
            }
            pills.forEach(function (pill) { pill.addEventListener('click', function () { pills.forEach(function (p) { p.classList.remove('is-active'); }); this.classList.add('is-active'); activeCat = this.dataset.cat; filter(); }); });
            search.addEventListener('input', filter);
        })();
        </script>
        </div>
    </main>
</div>

<div class="modal-overlay" id="recipe-modal">
    <div class="modal-box">
        <div class="modal-header"><h2 id="modal-title">Edit Recipe</h2><button class="modal-close" onclick="closeModal()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
        <div class="modal-body">
            <input type="hidden" id="edit-product-id">
            <div class="field"><div class="field-label">Product Name</div><input type="text" id="edit-product-name" placeholder="e.g. Classic Milk Tea"></div>
            <div class="field" style="display:grid;grid-template-columns:1fr 1fr;gap:12px"><div><div class="field-label">Category</div><input type="text" id="edit-product-category" placeholder="e.g. Milk Tea"></div><div><div class="field-label">Price (₱)</div><input type="number" id="edit-product-price" step="0.01" min="0" placeholder="0.00"></div></div>
            <div style="margin-top:20px">
                <div class="field-label" style="margin-bottom:8px">Ingredients</div>
                <div class="ingredient-headers"><span>Ingredient</span><span>Regular Amt</span><span>Large Amt</span><span>Unit</span><span></span></div>
                <div id="ingredient-list"></div>
                <button class="btn-add-ingredient" onclick="addIngredientRow()"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add Ingredient</button>
            </div>
            <div class="procedures-section"><div class="field-label">Preparation Procedure</div><textarea id="edit-product-procedure" placeholder="Step-by-step preparation instructions…"></textarea></div>
            <div class="save-status" id="save-status"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-delete-product" id="btn-delete-product" onclick="deleteProduct()"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> Delete</button>
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn-save" id="btn-save" onclick="saveRecipe()">Save Changes</button>
        </div>
    </div>
</div>

<template id="ingredient-row-template">
    <div class="ingredient-row" data-ingredient-row>
        <select><option value="">Select ingredient…</option>@foreach ($allIngredients as $ing)<option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}">{{ $ing->name }}</option>@endforeach</select>
        <input type="number" step="0.001" min="0.001" placeholder="Regular qty" data-size="regular">
        <input type="number" step="0.001" min="0.001" placeholder="Large qty" data-size="large">
        <span class="unit-badge" data-unit-display></span>
        <button class="btn-remove-ingredient" onclick="removeIngredientRow(this)"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
</template>

<script>
var ALL_INGREDIENTS = @json($allIngredients->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit]));
var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
var _editingProductId = null;
var _originalRecipeIds = [];

function openModal(id) { _editingProductId = id; document.getElementById('recipe-modal').classList.add('is-open'); document.getElementById('modal-title').textContent = 'Loading…'; document.getElementById('btn-save').disabled = true; _originalRecipeIds = []; fetch('/business/recipes/product/' + id + '/data', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }).then(r => r.json()).then(d => populateModal(d)).catch(() => { var p = PRODUCTS_DATA.find(x => x.id == id); if (p) populateModal(p); else { document.getElementById('modal-title').textContent = 'Edit Recipe'; document.getElementById('btn-save').disabled = false; } }); }

function populateModal(data) {
    document.getElementById('edit-product-id').value = data.id;
    document.getElementById('edit-product-name').value = data.name || '';
    document.getElementById('edit-product-category').value = data.category || '';
    document.getElementById('edit-product-price').value = data.price || 0;
    document.getElementById('edit-product-procedure').value = data.procedure || '';
    document.getElementById('modal-title').textContent = 'Edit: ' + (data.name || 'Recipe');
    var list = document.getElementById('ingredient-list'); list.innerHTML = '';
    if (data.recipes && data.recipes.length > 0) {
        var grouped = {}; _originalRecipeIds = [];
        data.recipes.forEach(function (r) { _originalRecipeIds.push(r.id); var ig = r.ingredient_id; if (!grouped[ig]) grouped[ig] = { ingredient_id: ig, ingredient: r.ingredient, regular_id: null, large_id: null, regular: null, large: null }; if (r.size === 'regular') { grouped[ig].regular_id = r.id; grouped[ig].regular = r.quantity_required; } else { grouped[ig].large_id = r.id; grouped[ig].large = r.quantity_required; } });
        Object.keys(grouped).forEach(function (ig) { addIngredientRow(grouped[ig]); });
    }
    document.getElementById('btn-save').disabled = false;
}

function addIngredientRow(data) {
    data = data || {}; var t = document.getElementById('ingredient-row-template').content.cloneNode(true); var row = t.querySelector('[data-ingredient-row]'); var sel = row.querySelector('select'); var unit = row.querySelector('[data-unit-display]');
    if (data.ingredient_id) { sel.value = data.ingredient_id; var o = sel.options[sel.selectedIndex]; if (o) unit.textContent = o.dataset.unit || ''; }
    if (data.regular) row.querySelector('[data-size="regular"]').value = data.regular;
    if (data.large) row.querySelector('[data-size="large"]').value = data.large;
    if (data.regular_id) row.dataset.regularId = data.regular_id;
    if (data.large_id) row.dataset.largeId = data.large_id;
    sel.addEventListener('change', function () { var o = sel.options[sel.selectedIndex]; unit.textContent = o ? (o.dataset.unit || '') : ''; });
    document.getElementById('ingredient-list').appendChild(t);
}

function removeIngredientRow(btn) { var r = btn.closest('[data-ingredient-row]'); if (r) r.remove(); }

function saveRecipe() {
    var name = document.getElementById('edit-product-name').value.trim(); if (!name) { alert('Please enter a product name.'); return; }
    var btn = document.getElementById('btn-save'); btn.disabled = true;
    var status = document.getElementById('save-status'); status.className = 'save-status'; status.textContent = 'Saving…'; status.classList.add('is-visible');
    var pid = document.getElementById('edit-product-id').value; if (!pid) return;
    var requests = [fetch('/business/recipes/product/' + pid, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify({ name: document.getElementById('edit-product-name').value, category: document.getElementById('edit-product-category').value, price: parseFloat(document.getElementById('edit-product-price').value) || 0, procedure: document.getElementById('edit-product-procedure').value }) })];
    var rows = document.querySelectorAll('#ingredient-list [data-ingredient-row]'); var added = {};
    rows.forEach(function (row) { var sel = row.querySelector('select'); var iid = sel.value; if (!iid) return; var rq = parseFloat(row.querySelector('[data-size="regular"]').value); var lq = parseFloat(row.querySelector('[data-size="large"]').value); var ri = row.dataset.regularId; var li = row.dataset.largeId;
        if (rq > 0) { var k = iid + '-r'; if (!added[k]) { added[k] = true; requests.push(ri ? fetch('/business/recipes/ingredient/' + ri, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify({ quantity_required: rq }) }) : fetch('/business/recipes/product/' + pid + '/ingredient', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify({ ingredient_id: iid, size: 'regular', quantity_required: rq }) })); } }
        if (lq > 0) { var k = iid + '-l'; if (!added[k]) { added[k] = true; requests.push(li ? fetch('/business/recipes/ingredient/' + li, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify({ quantity_required: lq }) }) : fetch('/business/recipes/product/' + pid + '/ingredient', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }, body: JSON.stringify({ ingredient_id: iid, size: 'large', quantity_required: lq }) })); } }
    });
    var cur = []; rows.forEach(function (row) { if (row.dataset.regularId) cur.push(String(row.dataset.regularId)); if (row.dataset.largeId) cur.push(String(row.dataset.largeId)); });
    _originalRecipeIds.forEach(function (id) { if (cur.indexOf(String(id)) === -1) requests.push(fetch('/business/recipes/ingredient/' + id + '/delete', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN } })); });
    Promise.all(requests).then(function (res) { if (res.every(function (r) { return r.ok; })) { status.className = 'save-status is-visible save-status--success'; status.textContent = 'Saved!'; setTimeout(function () { closeModal(); location.reload(); }, 800); } else { status.className = 'save-status is-visible save-status--error'; status.textContent = 'Something went wrong.'; btn.disabled = false; } }).catch(function () { status.className = 'save-status is-visible save-status--error'; status.textContent = 'Network error.'; btn.disabled = false; });
}

function deleteProduct() { if (!confirm('Delete this product?')) return; var pid = document.getElementById('edit-product-id').value; fetch('/business/recipes/product/' + pid + '/delete', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN } }).then(r => r.json()).then(() => { closeModal(); location.reload(); }); }
function closeModal() { document.getElementById('recipe-modal').classList.remove('is-open'); _editingProductId = null; }
document.addEventListener('click', function (e) { if (e.target === document.getElementById('recipe-modal')) closeModal(); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
document.addEventListener('DOMContentLoaded', function () { document.querySelectorAll('.btn-edit').forEach(function (btn) { btn.addEventListener('click', function () { openModal(this.dataset.productId); }); }); });
var PRODUCTS_DATA = @json($productsData);
</script>

@include('partials._settings-drawer')
</body>
</html>
