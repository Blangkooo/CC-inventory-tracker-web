@extends('layouts.sidebar')

@section('title', 'Pricing Simulator')

@section('styles')
    .page-header { margin-bottom: 24px; }
    .page-title { font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
    .page-subtitle { font-size: 13px; color: var(--text-2); margin-top: 2px; }

    .margin-rule {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 16px; background: rgba(0,184,148,.06); border: 1px solid rgba(0,184,148,.2);
        border-radius: 10px; font-size: 12px; font-weight: 600; color: var(--green); margin-top: 12px;
    }

    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 16px; }

    .product-card {
        background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
        padding: 20px; box-shadow: var(--shadow); transition: all .15s;
    }
    .product-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

    .pc-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 14px; }
    .pc-name { font-size: 15px; font-weight: 800; }
    .pc-category { font-size: 11px; color: var(--text-3); margin-top: 2px; }
    .pc-price { font-size: 18px; font-weight: 800; color: var(--accent); }

    .pc-costs { margin-bottom: 12px; }
    .pc-cost-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 5px 0; font-size: 12px; border-bottom: 1px solid var(--border);
    }
    .pc-cost-row:last-child { border-bottom: none; }
    .pc-cost-row__name { color: var(--text-2); }
    .pc-cost-row__value { font-weight: 700; }

    .pc-summary {
        display: flex; gap: 12px; padding-top: 12px; border-top: 1.5px solid var(--border);
    }
    .pc-metric { flex: 1; text-align: center; }
    .pc-metric__value { font-size: 16px; font-weight: 800; }
    .pc-metric__label { font-size: 10px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: .04em; }

    .pc-size-block { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1.5px dashed var(--border); }
    .pc-size-block:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .pc-size-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        color: var(--text-3); margin-bottom: 8px;
    }

    .margin-good .pc-metric__value { color: var(--green); }
    .margin-warn .pc-metric__value { color: #e17055; }
    .margin-bad .pc-metric__value { color: #d63031; }

    .sim-section { margin-top: 40px; padding-top: 32px; border-top: 2px solid var(--border); }
    .sim-title { font-size: 18px; font-weight: 800; margin-bottom: 6px; }
    .sim-subtitle { font-size: 13px; color: var(--text-2); margin-bottom: 20px; }

    .sim-controls { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 24px; }
    .sim-field { display: flex; flex-direction: column; gap: 4px; }
    .sim-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-3); }
    .sim-input {
        height: 40px; padding: 0 12px; border: 1.5px solid var(--border); border-radius: 10px;
        font-size: 13px; color: var(--text); font-family: var(--font); width: 160px; background: var(--card);
    }
    .sim-input:focus { outline: none; border-color: var(--accent); }

    .sim-btn {
        height: 40px; padding: 0 20px; background: var(--accent); color: #fff;
        border: none; border-radius: 10px; font-size: 13px; font-weight: 700;
        font-family: var(--font); cursor: pointer; transition: all .15s;
    }
    .sim-btn:hover { background: #d35e47; transform: translateY(-1px); }

    .sim-results { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
    .sim-card {
        background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; box-shadow: var(--shadow);
    }
    .sim-card__name { font-weight: 700; font-size: 13px; margin-bottom: 8px; }
    .sim-card__row { display: flex; justify-content: space-between; font-size: 12px; padding: 3px 0; }
    .sim-card__row span:last-child { font-weight: 700; }
    .sim-card__delta { font-size: 11px; font-weight: 700; margin-top: 6px; padding: 4px 8px; border-radius: 8px; display: inline-block; }
    .delta-up { background: rgba(214,48,49,.08); color: #d63031; }
    .delta-down { background: rgba(0,184,148,.08); color: var(--green); }

    @media (max-width: 768px) {
        .products-grid { grid-template-columns: 1fr; }
    }
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">Pricing Simulator</div>
    <div class="page-subtitle">See real ingredient costs, margins, and simulate price changes</div>
    <div class="margin-rule">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        Target: 65% gross margin (₱50 cost → ~₱143 suggested price)
    </div>
</div>

@if ($products->isEmpty() || $products->every(fn ($p) => collect($p['sizes'])->sum('total_cost') == 0))
    <div class="empty-state">
        <p>No ingredient cost data yet. Link suppliers to ingredients in the <a href="{{ route('suppliers.index') }}" style="color:var(--accent);font-weight:700;">Supplier Directory</a> first.</p>
    </div>
@else
    <div class="products-grid">
        @foreach ($products as $p)
            <div class="product-card">
                <div class="pc-header">
                    <div><div class="pc-name">{{ $p['name'] }}</div><div class="pc-category">{{ $p['category'] ?? 'Uncategorized' }}</div></div>
                    <div class="pc-price">₱{{ number_format($p['price'], 2) }}</div>
                </div>
                @forelse ($p['sizes'] as $sizeData)
                    @php $marginClass = $sizeData['margin_pct'] >= 60 ? 'margin-good' : ($sizeData['margin_pct'] >= 40 ? 'margin-warn' : 'margin-bad'); @endphp
                    <div class="pc-size-block">
                        <div class="pc-size-label">{{ ucfirst($sizeData['size']) }}</div>
                        @if (!empty($sizeData['ingredients']))
                        <div class="pc-costs">
                            @foreach ($sizeData['ingredients'] as $ing)
                            <div class="pc-cost-row"><span class="pc-cost-row__name">{{ $ing['name'] }} ({{ $ing['qty'] }}{{ $ing['unit'] }})</span><span class="pc-cost-row__value">₱{{ number_format($ing['line_cost'], 2) }}</span></div>
                            @endforeach
                        </div>
                        @endif
                        <div class="pc-summary {{ $marginClass }}">
                            <div class="pc-metric"><div class="pc-metric__value">₱{{ number_format($sizeData['total_cost'], 2) }}</div><div class="pc-metric__label">Total Cost</div></div>
                            <div class="pc-metric"><div class="pc-metric__value">{{ $sizeData['margin_pct'] }}%</div><div class="pc-metric__label">Margin</div></div>
                            <div class="pc-metric"><div class="pc-metric__value">₱{{ number_format($sizeData['suggested_price'], 2) }}</div><div class="pc-metric__label">Suggested (65%)</div></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="padding:12px 0;">No recipe defined yet.</div>
                @endforelse
            </div>
        @endforeach
    </div>
@endif

<div class="sim-section">
    <div class="sim-title">What-If Simulator</div>
    <div class="sim-subtitle">Adjust an ingredient cost and see how it affects all products using it</div>
    <div class="sim-controls">
        <div class="sim-field">
            <span class="sim-label">Ingredient</span>
            <select class="sim-input" id="simIngredient" style="width:200px;">
                @foreach (\App\Models\Ingredient::orderBy('name')->get() as $ing)
                <option value="{{ $ing->id }}" data-name="{{ $ing->name }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                @endforeach
            </select>
        </div>
        <div class="sim-field">
            <span class="sim-label">New Unit Cost (₱)</span>
            <input type="number" step="0.01" class="sim-input" id="simCost" placeholder="e.g. 95.00" style="width:120px;">
        </div>
        <button class="sim-btn" onclick="runSimulation()">Simulate</button>
    </div>
    <div class="sim-results" id="simResults"></div>
</div>

<script>
const productsData = @json($products);
function runSimulation() {
    const ingredientId = parseInt(document.getElementById('simIngredient').value);
    const ingredientName = document.getElementById('simIngredient').selectedOptions[0].dataset.name;
    const newCost = parseFloat(document.getElementById('simCost').value);
    if (!newCost || newCost <= 0) { alert('Enter a valid cost.'); return; }
    const affected = [];
    productsData.forEach(product => {
        product.sizes.forEach(sizeData => {
            const ings = sizeData.ingredients;
            let oldTotal = sizeData.total_cost, newTotal = 0, found = false;
            ings.forEach(ing => {
                if (ing.name === ingredientName) { found = true; newTotal += newCost * ing.qty; }
                else { newTotal += ing.line_cost; }
            });
            if (!found) return;
            newTotal = Math.round(newTotal * 100) / 100;
            const oldMargin = product.price > 0 ? ((product.price - oldTotal) / product.price * 100).toFixed(1) : 0;
            const newMargin = product.price > 0 ? ((product.price - newTotal) / product.price * 100).toFixed(1) : 0;
            const costDelta = newTotal - oldTotal;
            const label = `${product.name} (${sizeData.size.charAt(0).toUpperCase()}${sizeData.size.slice(1)})`;
            affected.push({ name: label, price: product.price, oldTotal, newTotal, oldMargin, newMargin, costDelta });
        });
    });
    const container = document.getElementById('simResults');
    if (!affected.length) { container.innerHTML = '<div class="empty-state">No products use this ingredient.</div>'; return; }
    container.innerHTML = affected.map(a => `<div class="sim-card"><div class="sim-card__name">${a.name}</div><div class="sim-card__row"><span>Selling Price</span><span>₱${a.price.toFixed(2)}</span></div><div class="sim-card__row"><span>Old Cost</span><span>₱${a.oldTotal.toFixed(2)}</span></div><div class="sim-card__row"><span>New Cost</span><span>₱${a.newTotal.toFixed(2)}</span></div><div class="sim-card__row"><span>Old Margin</span><span>${a.oldMargin}%</span></div><div class="sim-card__row"><span>New Margin</span><span>${a.newMargin}%</span></div><span class="sim-card__delta ${a.costDelta>0?'delta-up':'delta-down'}">${a.costDelta>0?'▲':'▼'} ₱${Math.abs(a.costDelta).toFixed(2)} cost ${a.costDelta>0?'increase':'decrease'}</span></div>`).join('');
}
</script>
@endsection
