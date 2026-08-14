@extends('layouts.sidebar')

@section('title', 'Pricing Simulator')

@section('content')
@php
    $marginColor = fn ($pct) => $pct >= 60 ? 'text-green' : ($pct >= 40 ? 'text-accent' : 'text-accent-2');
@endphp

<div class="mb-6">
    <div class="text-[22px] font-extrabold tracking-tight">Pricing Simulator</div>
    <div class="text-[13px] text-ink-2 mt-0.5">Real ingredient costs, live margins, and what-if price modelling</div>
    <div class="inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-[10px] bg-[rgba(0,184,148,.06)] border border-[rgba(0,184,148,.2)] text-xs font-semibold text-green">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        Target: 65% gross margin (₱50 cost &rarr; ~₱143 suggested price)
    </div>
</div>

@if ($products->isEmpty() || $products->every(fn ($p) => collect($p['sizes'])->sum('total_cost') == 0))
    <div class="card p-8 text-center text-[13px] text-ink-3">
        No ingredient cost data yet. Link suppliers to ingredients in the
        <a href="{{ route('suppliers.index') }}" class="text-accent font-bold no-underline hover:underline">Supplier Directory</a> first.
    </div>
@else
    <div class="grid grid-cols-[repeat(auto-fill,minmax(360px,1fr))] gap-4">
        @foreach ($products as $p)
            <div class="card p-5 {{ $p['is_active'] ? '' : 'opacity-60' }}" data-product-id="{{ $p['id'] }}">
                <div class="flex items-start justify-between mb-3.5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[15px] font-extrabold">{{ $p['name'] }}</span>
                            @unless ($p['is_active'])
                                <span class="badge-gray" title="Discontinued — no longer sold.">Not Available</span>
                            @endunless
                        </div>
                        <div class="text-[11px] text-ink-3 mt-0.5">{{ $p['category'] ?? 'Uncategorized' }}</div>
                    </div>
                    <div class="text-lg font-extrabold text-accent">₱{{ number_format($p['price'], 2) }}</div>
                </div>

                @forelse ($p['sizes'] as $sizeData)
                    <div class="mb-4 pb-4 border-b-[1.5px] border-dashed border-line last:mb-0 last:pb-0 last:border-b-0">
                        <div class="text-[11px] font-bold uppercase tracking-[.04em] text-ink-3 mb-2">{{ ucfirst($sizeData['size']) }}</div>

                        @if (!empty($sizeData['ingredients']))
                            <div class="mb-3">
                                @foreach ($sizeData['ingredients'] as $ing)
                                    <div class="flex items-center justify-between py-[5px] text-xs border-b border-line last:border-b-0">
                                        <span class="text-ink-2">{{ $ing['name'] }} ({{ $ing['qty'] }}{{ $ing['unit'] }})</span>
                                        <span class="font-bold" data-line-cost="{{ $ing['id'] }}">₱{{ number_format($ing['line_cost'], 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex gap-3 pt-3 border-t-[1.5px] border-line">
                            <div class="flex-1 text-center">
                                <div class="text-base font-extrabold">₱{{ number_format($sizeData['total_cost'], 2) }}</div>
                                <div class="text-[10px] font-semibold text-ink-3 uppercase tracking-[.04em]">Total Cost</div>
                            </div>
                            <div class="flex-1 text-center">
                                <div class="text-base font-extrabold {{ $marginColor($sizeData['margin_pct']) }}">{{ $sizeData['margin_pct'] }}%</div>
                                <div class="text-[10px] font-semibold text-ink-3 uppercase tracking-[.04em]">Margin</div>
                            </div>
                            <div class="flex-1 text-center">
                                <div class="text-base font-extrabold">₱{{ number_format($sizeData['suggested_price'], 2) }}</div>
                                <div class="text-[10px] font-semibold text-ink-3 uppercase tracking-[.04em]">Suggested (65%)</div>
                            </div>
                        </div>

                        {{-- A serving costing many times its selling price is almost always a
                             unit mismatch (a per-kilo price saved against a per-gram
                             ingredient), so point at the data rather than just showing the
                             alarming percentage. --}}
                        @if ($p['price'] > 0 && $sizeData['total_cost'] > $p['price'] * 2)
                            <div class="mt-2.5 flex items-start gap-2 px-3 py-2 rounded-lg bg-[rgba(245,166,35,.1)] border border-[rgba(245,166,35,.28)] text-[11px] font-semibold text-[#b45309]">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="mt-px shrink-0"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                <span>Cost exceeds the selling price. Check the supplier's unit cost against the recipe's unit ({{ $sizeData['ingredients'][0]['unit'] ?? '' }}).</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-3 text-center text-xs text-ink-3">No recipe defined yet.</div>
                @endforelse
            </div>
        @endforeach
    </div>
@endif

{{-- ══ What-If Simulator ══ --}}
<div class="mt-10 pt-8 border-t-2 border-line">
    <div class="text-lg font-extrabold">What-If Simulator</div>
    <div class="text-[13px] text-ink-2 mb-5">Drag the slider to change an ingredient's cost — every affected margin recalculates live.</div>

    @if ($simulatable->isEmpty())
        <div class="card p-8 text-center text-[13px] text-ink-3">
            No ingredient has a supplier unit cost yet, so there is nothing to simulate.
        </div>
    @else
        <div class="card p-5 mb-5">
            <div class="flex flex-wrap gap-5 items-end">
                <div class="flex flex-col gap-1.5 min-w-[220px]">
                    <span class="text-[11px] font-bold uppercase tracking-[.04em] text-ink-3">Ingredient</span>
                    <select class="form-input" id="simIngredient">
                        @foreach ($simulatable as $ing)
                            <option value="{{ $ing['id'] }}" data-unit-cost="{{ $ing['unit_cost'] }}" data-unit="{{ $ing['unit'] }}">
                                {{ $ing['name'] }} (₱{{ number_format($ing['unit_cost'], 2) }}/{{ $ing['unit'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[280px] flex flex-col gap-1.5">
                    <div class="flex items-baseline justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-[.04em] text-ink-3">Cost Change</span>
                        <span class="text-[13px] font-extrabold" id="simDeltaLabel">0%</span>
                    </div>
                    <input type="range" id="simSlider" min="-50" max="200" step="1" value="0" class="w-full accent-[var(--color-accent)] cursor-pointer">
                    <div class="flex justify-between text-[10px] text-ink-3">
                        <span>&minus;50%</span><span>0%</span><span>+200%</span>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 w-[160px]">
                    <span class="text-[11px] font-bold uppercase tracking-[.04em] text-ink-3">New Unit Cost</span>
                    <input type="number" step="0.01" min="0" class="form-input font-bold" id="simCost">
                </div>

                <button type="button" class="btn-secondary h-[42px]" onclick="resetSim()">Reset</button>
            </div>
        </div>

        <div id="simSummary" class="mb-4"></div>
        <div id="simResults" class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-3"></div>
    @endif
</div>

<script>
const productsData = @json($products);

const $sel    = document.getElementById('simIngredient');
const $slider = document.getElementById('simSlider');
const $cost   = document.getElementById('simCost');
const $delta  = document.getElementById('simDeltaLabel');

const peso = n => '₱' + Number(n).toFixed(2);
const baseCost = () => parseFloat($sel.selectedOptions[0].dataset.unitCost) || 0;

/** Margin bucket → text colour, matching the server-rendered cards. */
function marginClass(pct) {
    if (pct >= 60) return 'text-green';
    if (pct >= 40) return 'text-accent';
    return 'text-accent-2';
}

/** Slider is the source of truth; the number box mirrors it and vice versa. */
function syncFromSlider() {
    const pct = parseInt($slider.value, 10);
    $delta.textContent = (pct > 0 ? '+' : '') + pct + '%';
    $delta.className = 'text-[13px] font-extrabold ' + (pct > 0 ? 'text-accent-2' : pct < 0 ? 'text-green' : '');
    $cost.value = (baseCost() * (1 + pct / 100)).toFixed(2);
    recalc();
}

function syncFromCost() {
    const base = baseCost();
    const val = parseFloat($cost.value);
    if (base > 0 && !isNaN(val)) {
        const pct = Math.round(((val - base) / base) * 100);
        $slider.value = Math.max(-50, Math.min(200, pct));
        $delta.textContent = (pct > 0 ? '+' : '') + pct + '%';
        $delta.className = 'text-[13px] font-extrabold ' + (pct > 0 ? 'text-accent-2' : pct < 0 ? 'text-green' : '');
    }
    recalc();
}

function resetSim() {
    $slider.value = 0;
    syncFromSlider();
}

function recalc() {
    const ingredientId = parseInt($sel.value, 10);
    const newCost = parseFloat($cost.value);
    const affected = [];

    if (isNaN(newCost) || newCost < 0) return;

    productsData.forEach(product => {
        product.sizes.forEach(sizeData => {
            let newTotal = 0, uses = false;

            sizeData.ingredients.forEach(ing => {
                if (ing.id === ingredientId) {
                    uses = true;
                    newTotal += newCost * ing.qty;
                } else {
                    newTotal += ing.line_cost;
                }
            });

            if (!uses) return;

            const oldTotal = sizeData.total_cost;
            newTotal = Math.round(newTotal * 100) / 100;
            const price = product.price;

            affected.push({
                name: `${product.name} (${sizeData.size.charAt(0).toUpperCase()}${sizeData.size.slice(1)})`,
                price, oldTotal, newTotal,
                oldMargin: price > 0 ? ((price - oldTotal) / price) * 100 : 0,
                newMargin: price > 0 ? ((price - newTotal) / price) * 100 : 0,
                suggested: newTotal > 0 ? newTotal / 0.35 : 0,
            });
        });
    });

    const $summary = document.getElementById('simSummary');
    const $results = document.getElementById('simResults');

    if (!affected.length) {
        $summary.innerHTML = '';
        $results.innerHTML = '<div class="card p-6 text-center text-[13px] text-ink-3">No product uses this ingredient.</div>';
        return;
    }

    const wentNegative = affected.filter(a => a.newMargin < 40).length;
    $summary.innerHTML = `
        <div class="text-[13px] text-ink-2">
            <strong>${affected.length}</strong> item${affected.length === 1 ? '' : 's'} affected
            ${wentNegative ? `· <span class="text-accent-2 font-bold">${wentNegative} below the 40% margin floor</span>` : '· <span class="text-green font-bold">all still above the 40% floor</span>'}
        </div>`;

    $results.innerHTML = affected.map(a => {
        const diff = a.newTotal - a.oldTotal;
        const up = diff > 0.005, down = diff < -0.005;
        return `
        <div class="card p-4">
            <div class="text-[13px] font-extrabold mb-2.5">${a.name}</div>
            <div class="flex justify-between text-xs py-1 border-b border-line"><span class="text-ink-2">Selling Price</span><span class="font-semibold">${peso(a.price)}</span></div>
            <div class="flex justify-between text-xs py-1 border-b border-line"><span class="text-ink-2">Cost</span><span class="font-semibold">${peso(a.oldTotal)} &rarr; ${peso(a.newTotal)}</span></div>
            <div class="flex justify-between text-xs py-1 border-b border-line"><span class="text-ink-2">Margin</span><span class="font-semibold">${a.oldMargin.toFixed(1)}% &rarr; <span class="${marginClass(a.newMargin)} font-extrabold">${a.newMargin.toFixed(1)}%</span></span></div>
            <div class="flex justify-between text-xs py-1"><span class="text-ink-2">Suggested (65%)</span><span class="font-semibold">${peso(a.suggested)}</span></div>
            ${(up || down) ? `<div class="mt-2.5 text-[11px] font-bold ${up ? 'text-accent-2' : 'text-green'}">${up ? '▲' : '▼'} ${peso(Math.abs(diff))} cost ${up ? 'increase' : 'decrease'}</div>` : ''}
        </div>`;
    }).join('');
}

if ($sel) {
    $sel.addEventListener('change', resetSim);
    $slider.addEventListener('input', syncFromSlider);
    $cost.addEventListener('input', syncFromCost);
    resetSim();
}
</script>
@endsection
