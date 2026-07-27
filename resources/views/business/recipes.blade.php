@php
    // ── Placeholder fallbacks ─────────────────────────────────────────
    if (!isset($branches) || $branches->isEmpty()) {
        $branches = collect([
            (object)['id'=>1,'name'=>'QC Main Branch'],
            (object)['id'=>2,'name'=>'Makati Outlet'],
            (object)['id'=>3,'name'=>'BGC Branch'],
            (object)['id'=>4,'name'=>'Cebu City Branch'],
        ]);
    }
    if (!isset($categories) || $categories->isEmpty()) {
        $categories = collect(['Drinks','Goods','Sets']);
    }
    if (!isset($allIngredients) || $allIngredients->isEmpty()) {
        $allIngredients = collect([
            (object)['id'=>1,'name'=>'Flour','unit'=>'g'],
            (object)['id'=>2,'name'=>'Sugar','unit'=>'g'],
            (object)['id'=>3,'name'=>'Butter','unit'=>'g'],
            (object)['id'=>4,'name'=>'Milk','unit'=>'ml'],
            (object)['id'=>5,'name'=>'Chocolate Syrup','unit'=>'ml'],
        ]);
    }

    if (!isset($products) || $products->isEmpty()) {
        $makeProd = fn($id,$name,$cat,$ings) => (object)[
            'id'=>$id, 'name'=>$name, 'category'=>$cat, 'price'=>0, 'procedure'=>'',
            'recipes'=>collect(array_map(fn($i)=>(object)[
                'id'=>0,
                'ingredient_id'=>0,
                'ingredient'=>(object)['id'=>0,'name'=>$i[0],'unit'=>$i[2]],
                'size'=>(object)['regular'=>$i[1],'large'=>$i[3]],
            ], $ings)),
        ];
        $products = collect([
            $makeProd(1,'Black Forest Milk Tea','Drinks',[
                ['Black Tea Base','200ml','ml','300ml'],
                ['Whole Milk','60ml','ml','90ml'],
                ['Chocolate Syrup','30ml','ml','45ml'],
                ['Cherry Syrup','15ml','ml','22ml'],
                ['Flavor Powder','25g','g','38g'],
                ['Sugar','20g','g','30g'],
                ['Ice','1 cup','cup','1.5 cups'],
            ]),
            $makeProd(2,'Classic Milk Tea','Drinks',[
                ['Black Tea Base','200ml','ml','300ml'],
                ['Whole Milk','80ml','ml','120ml'],
                ['Brown Sugar Syrup','25ml','ml','40ml'],
                ['Tapioca Pearls','50g','g','80g'],
                ['Ice','1 cup','cup','1.5 cups'],
            ]),
            $makeProd(3,'Taro Milk Tea','Drinks',[
                ['Taro Powder','30g','g','45g'],
                ['Whole Milk','100ml','ml','150ml'],
                ['Sugar Syrup','20ml','ml','30ml'],
                ['Ice','1 cup','cup','1.5 cups'],
            ]),
            $makeProd(4,'Cheese Dog Set','Sets',[
                ['Hotdog Bun','1 pc','pc','2 pc'],
                ['Hotdog','1 pc','pc','2 pc'],
                ['Cheese Sauce','30g','g','60g'],
                ['Ketchup','10g','g','20g'],
            ]),
        ]);
    }

    // ── Pre-compute JSON-safe product data for the edit modal ──────
    // (Must be AFTER the fallback block so placeholders are included.)
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
@extends('layouts.sidebar')

@section('title', 'Recipes')

@section('styles')
        /* ── LAYOUT ── */
        .workspace {
            max-width: 1400px; margin: 0 auto; padding: 24px 32px;
            display: flex; gap: 20px;
        }

        /* ── BRANCH SIDEBAR ── */
        .branch-bar {
            width: 108px; flex-shrink: 0;
            background: var(--terra);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            display: flex; flex-direction: column;
            align-items: center; padding: 14px 10px; gap: 10px;
        }

        .biz-badge {
            width: 100%; background: #fff; border-radius: 10px;
            border: 1px solid rgba(92,45,27,.2);
            padding: 10px 8px; text-align: center;
        }

        .biz-badge__icon { margin-bottom: 6px; }
        .biz-badge__name { font-size: 11px; font-weight: 700; line-height: 1.2; }
        .biz-badge__sub { font-size: 9px; font-weight: 600; opacity: .5; text-transform: uppercase; letter-spacing: .03em; margin-top: 2px; }

        .branch-divider { width: 100%; height: 1px; background: rgba(255,255,255,.2); }
        .branch-dots { display: flex; flex-direction: column; align-items: center; gap: 8px; width: 100%; }

        .branch-dot {
            width: 42px; height: 42px; border-radius: 50%;
            background: rgba(250, 249, 247,.85); border: 1.5px solid rgba(92,45,27,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700; color: var(--brown);
            cursor: pointer; text-decoration: none; transition: all .15s ease;
            flex-shrink: 0;
        }

        .branch-dot:hover,
        .branch-dot.is-active { background: var(--brown); color: #fff; border-color: var(--brown); }

        /* ── CONTENT ── */
        .content { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 20px; }

        .content-head {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }

        .content-head__title-wrap { display: flex; align-items: baseline; gap: 10px; }
        .content-head__title { font-size: 22px; font-weight: 800; }
        .content-head__role { font-size: 15px; font-weight: 400; opacity: .5; }

        .sub-tabs { display: flex; gap: 6px; }

        .sub-tab {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: 999px; font-size: 12px; font-weight: 600;
            border: 1.5px solid var(--border); background: #fff; color: var(--brown);
            text-decoration: none; transition: all .15s ease; cursor: pointer;
        }

        .sub-tab:hover { border-color: var(--terra); color: var(--terra); }
        .sub-tab.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }
        .sub-tab.is-active svg { stroke: #fff; }

        .search-row { display: flex; align-items: center; gap: 10px; }

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

        /* Recipe card */
        .recipe-card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
        }

        .recipe-card__head {
            display: flex; align-items: center; gap: 10px;
            padding: 18px 24px; border-bottom: 1px solid var(--border);
        }

        .recipe-card__head svg { flex-shrink: 0; opacity: .6; }
        .recipe-card__name { font-size: 16px; font-weight: 800; }

        .recipe-table { width: 100%; border-collapse: collapse; }

        .recipe-table thead th {
            text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .45; padding: 12px 24px;
            background: rgba(92,45,27,.03); border-bottom: 1px solid var(--border);
        }

        .recipe-table tbody td {
            padding: 16px 24px; font-size: 13px; vertical-align: top; line-height: 1.7;
            border-bottom: 1px solid rgba(92,45,27,.06);
        }

        .recipe-table tbody tr:last-child td { border-bottom: none; }
        .recipe-table td strong { font-weight: 600; }

        .recipe-card__foot {
            display: flex; align-items: center; gap: 12px; justify-content: flex-end;
            padding: 14px 24px; border-top: 1px solid var(--border);
            background: rgba(92,45,27,.02);
        }

        .recipe-card__foot-label { font-size: 12px; font-weight: 600; opacity: .6; margin-right: auto; }

        .btn-edit {
            padding: 7px 20px; background: #fff; color: var(--brown);
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
            transition: all .15s ease;
        }

        .btn-edit:hover { background: var(--brown); color: var(--cream); border-color: var(--brown); }

        .btn-profile {
            padding: 7px 20px; background: var(--accent, #e17055); color: #fff;
            border: 1.5px solid var(--accent, #e17055); border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
            transition: all .15s ease;
        }

        .btn-profile:hover { filter: brightness(.92); }

        /* ── PROFILE DRILL-DOWN ── */
        .profile-size-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .5; margin-bottom: 6px;
        }

        .profile-summary {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;
            padding: 18px; margin-bottom: 20px;
            background: var(--accent-light, rgba(225,112,85,.08));
            border: 1px solid var(--border); border-radius: 12px;
        }

        .profile-metric { text-align: center; }
        .profile-metric__value { font-size: 20px; font-weight: 800; }
        .profile-metric__label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; opacity: .55; margin-top: 3px;
        }

        .margin-good  .profile-metric__value { color: #00b894; }
        .margin-warn  .profile-metric__value { color: #e17055; }
        .margin-bad   .profile-metric__value { color: #d63031; }

        .profile-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .profile-table th {
            text-align: left; padding: 9px 12px; font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em; opacity: .5;
            border-bottom: 1.5px solid var(--border);
        }
        .profile-table td { padding: 11px 12px; border-bottom: 1px solid var(--border); }
        .profile-table tr:last-child td { border-bottom: none; }
        .profile-table td.num { text-align: right; font-variant-numeric: tabular-nums; }

        .supplier-cell { font-size: 12px; }
        .supplier-cell__name { font-weight: 600; }
        .supplier-cell__contact { opacity: .5; font-size: 11px; }
        .supplier-cell--none { opacity: .4; font-style: italic; }

        .profile-hint {
            margin-top: 16px; padding: 10px 14px; border-radius: 8px;
            background: rgba(0,184,148,.07); border: 1px solid rgba(0,184,148,.2);
            font-size: 12px; font-weight: 600; color: #00b894;
        }

        /* ── MODAL OVERLAY ── */
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

        .modal-header h2 {
            font-size: 17px; font-weight: 800;
        }

        .modal-close {
            width: 32px; height: 32px; border-radius: 8px;
            border: none; background: transparent; color: var(--brown);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background .15s;
        }

        .modal-close:hover { background: rgba(92,45,27,.08); }

        .modal-body {
            flex: 1; overflow-y: auto; padding: 24px;
        }

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

        .procedures-section { margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }

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

        .size-tag {
            display: inline-block; padding: 2px 8px; border-radius: 4px;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .04em;
        }

        .size-tag--regular { background: rgba(92,45,27,.08); color: var(--brown); }
        .size-tag--large { background: rgba(188,97,75,.12); color: var(--terra); }

        .row-deleted td { opacity: .3; text-decoration: line-through; }

        @media (max-width: 900px) {
            .branch-bar { display: none; }
            .workspace { padding: 16px; }
            .sub-tabs { flex-wrap: wrap; }
            .search-row { flex-direction: column; align-items: stretch; }
            .search-input { width: 100%; }
            .ingredient-row { grid-template-columns: 1fr 1fr; }
            .ingredient-headers { display: none; }
        }
@endsection

@section('content')

<div class="workspace">

    {{-- Branch Sidebar --}}
    <div class="branch-bar">
        @php $userBranchId = auth()->user()->branch_id; @endphp
        <div class="biz-badge">
            <div class="biz-badge__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/>
                </svg>
            </div>
            <div class="biz-badge__name">{{ $branches->count() }} {{ Str::plural('Branch', $branches->count()) }}</div>
            <div class="biz-badge__sub">All Locations</div>
        </div>

        <div class="branch-divider"></div>

        <div class="branch-dots">
            @foreach ($branches as $branch)
                @php $initials = collect(explode(' ', $branch->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode(''); @endphp
                <a href="#" class="branch-dot {{ $branch->id === $userBranchId ? 'is-active' : '' }}" title="{{ $branch->name }}">{{ $initials }}</a>
            @endforeach
        </div>
    </div>

    {{-- Main Content --}}
    <div class="content">

        <div class="content-head">
            <div class="content-head__title-wrap">
                <h1 class="content-head__title">Businesses</h1>
                <span class="content-head__role">/ {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
            </div>
            <div class="sub-tabs">
                <a href="{{ url('/business/summary') }}" class="sub-tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
                    </svg>
                    Summary
                </a>
                <a href="{{ url('/business/recipes') }}" class="sub-tab is-active">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                    Recipe
                </a>
                <a href="{{ url('/business/workers') }}" class="sub-tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    Staff / Profile
                </a>
                <a href="{{ url('/business/verification') }}" class="sub-tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Verification
                </a>
            </div>
        </div>

        <div class="search-row">
            <input type="text" class="search-input" id="recipe-search" placeholder="Search product name…">
            <div class="cat-pills" id="cat-pills">
                <span class="cat-pill is-active" data-cat="">All</span>
                @foreach ($categories as $cat)
                    <span class="cat-pill" data-cat="{{ $cat }}">{{ $cat }}</span>
                @endforeach
            </div>
        </div>

        @forelse ($products as $product)
        <div class="recipe-card" data-cat="{{ $product->category }}" data-name="{{ strtolower($product->name) }}">
            <div class="recipe-card__head">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/>
                    <line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/>
                </svg>
                <span class="recipe-card__name">{{ $product->name }}</span>
                <span style="margin-left:auto;font-size:11px;font-weight:600;opacity:.45;text-transform:uppercase;letter-spacing:.04em">
                    &#8369;{{ number_format($product->price ?? 0, 2) }}
                    @if ($product->category)
                        &middot; {{ $product->category }}
                    @endif
                </span>
            </div>

            @if ($product->recipes->isEmpty())
                <div style="padding:20px 24px;font-size:13px;opacity:.4">No recipe ingredients defined yet.</div>
            @else
                @php
                    // Group recipes by ingredient for side-by-side display
                    $grouped = [];
                    foreach ($product->recipes as $r) {
                        $ingId = $r->ingredient_id;
                        if (!isset($grouped[$ingId])) {
                            $grouped[$ingId] = [
                                'ingredient' => $r->ingredient,
                                'regular' => null,
                                'large' => null,
                                'regular_id' => null,
                                'large_id' => null,
                            ];
                        }
                        if ($r->size === 'regular') {
                            $grouped[$ingId]['regular'] = $r->quantity_required;
                            $grouped[$ingId]['regular_id'] = $r->id;
                        } else {
                            $grouped[$ingId]['large'] = $r->quantity_required;
                            $grouped[$ingId]['large_id'] = $r->id;
                        }
                    }
                @endphp
                <table class="recipe-table">
                    <thead>
                        <tr>
                            <th>Ingredient</th>
                            <th><span class="size-tag size-tag--regular">Regular</span></th>
                            <th><span class="size-tag size-tag--large">Large</span></th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grouped as $ingId => $g)
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

            <div class="recipe-card__foot">
                <span class="recipe-card__foot-label">{{ $product->recipes->count() }} ingredient{{ $product->recipes->count() !== 1 ? 's' : '' }}</span>
                <button class="btn-profile" onclick="openProfile({{ $product->id }})">Profile</button>
                <button class="btn-edit" data-product-id="{{ $product->id }}">Edit</button>
            </div>
        </div>
        @empty
            <div style="text-align:center;padding:40px;opacity:.35;font-size:14px">No products with recipes found.</div>
        @endforelse

        {{-- Filter JS --}}
        <script>
        (function () {
            var activeCat = '';
            var search = document.getElementById('recipe-search');
            var pills = document.querySelectorAll('#cat-pills .cat-pill');
            var cards = document.querySelectorAll('.recipe-card');

            function filter() {
                var q = search.value.toLowerCase().trim();
                cards.forEach(function (card) {
                    var catMatch = !activeCat || card.dataset.cat === activeCat;
                    var nameMatch = !q || card.dataset.name.includes(q);
                    card.style.display = catMatch && nameMatch ? '' : 'none';
                });
            }

            pills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    pills.forEach(function (p) { p.classList.remove('is-active'); });
                    this.classList.add('is-active');
                    activeCat = this.dataset.cat;
                    filter();
                });
            });

            search.addEventListener('input', filter);
        })();
        </script>

    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     EDIT MODAL
     ═══════════════════════════════════════════════════════════════════ --}}
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
            {{-- Hidden product ID --}}
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

            {{-- Ingredients --}}
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

            {{-- Procedure --}}
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

{{-- ═══════════════════════════════════════════════════════════════════
     INGREDIENT PROFILE DRILL-DOWN MODAL
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="profile-modal">
    <div class="modal-box">
        <div class="modal-header">
            <h2 id="profile-title">Ingredient Profile</h2>
            <button class="modal-close" onclick="closeProfile()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="modal-body" id="profile-body">
            <div style="text-align:center;padding:40px;opacity:.4;font-size:13px">Loading…</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     INGREDIENT TEMPLATE (hidden, cloned by JS)
     ═══════════════════════════════════════════════════════════════════ --}}
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

{{-- ═══════════════════════════════════════════════════════════════════
     RECIPE DATA (page-embedded JSON)
     ═══════════════════════════════════════════════════════════════════ --}}
<script>
// ── All ingredients lookup ──
var ALL_INGREDIENTS = @json($allIngredients->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit]));

// ── CSRF token ──
var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content
    || document.querySelector('input[name="_token"]')?.value
    || '{{ csrf_token() }}';

// ── Modal state ──
var _editingProductId = null;

// ── Open modal ──
function openModal(productId) {
    _editingProductId = productId;
    document.getElementById('recipe-modal').classList.add('is-open');
    document.getElementById('modal-title').textContent = 'Loading…';
    document.getElementById('btn-save').disabled = true;
    fetchProductData(productId);
}

// ── Fetch product data via API ──
function fetchProductData(productId) {
    fetch('/business/recipes/product/' + productId + '/data', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        populateModal(data);
    })
    .catch(function () {
        // Fallback: populate from the server-rendered card
        populateFromCard(productId);
    });
}

// ── Populate from server-rendered page data ──
function populateFromCard(productId) {
    // Find this product's recipes from the page data
    // Use the embedded products JSON
    var product = PRODUCTS_DATA.find(function (p) { return p.id == productId; });
    if (product) {
        populateModal(product);
    } else {
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

    // Populate ingredients
    var list = document.getElementById('ingredient-list');
    list.innerHTML = '';

    if (data.recipes && data.recipes.length > 0) {
        // Group recipes by ingredient
        var grouped = {};
        data.recipes.forEach(function (r) {
            var ingId = r.ingredient_id;
            if (!grouped[ingId]) {
                grouped[ingId] = {
                    ingredient_id: ingId,
                    ingredient: r.ingredient,
                    regular_id: null,
                    large_id: null,
                    regular: null,
                    large: null
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

    // Set values if editing existing
    if (data.ingredient_id) {
        select.value = data.ingredient_id;
        var selected = select.options[select.selectedIndex];
        if (selected) {
            unitDisplay.textContent = selected.dataset.unit || '';
        }
    }

    if (data.regular) regInput.value = data.regular;
    if (data.large) lrgInput.value = data.large;

    // Store recipe IDs if they exist
    if (data.regular_id) row.dataset.regularId = data.regular_id;
    if (data.large_id) row.dataset.largeId = data.large_id;

    // Update unit display when ingredient changes
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

    // 1. Update product
    var productData = {
        name: document.getElementById('edit-product-name').value,
        category: document.getElementById('edit-product-category').value,
        price: parseFloat(document.getElementById('edit-product-price').value) || 0,
        procedure: document.getElementById('edit-product-procedure').value,
    };

    var requests = [];

    // Update product
    requests.push(
        fetch('/business/recipes/product/' + productId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify(productData)
        })
    );

    // 2. Process each ingredient row
    var rows = document.querySelectorAll('#ingredient-list [data-ingredient-row]');
    var addedIngredients = {}; // track which ingredient+size combos we've sent

    rows.forEach(function (row) {
        var select = row.querySelector('select');
        var ingredientId = select.value;
        if (!ingredientId) return;

        var regularQty = parseFloat(row.querySelector('[data-size="regular"]').value);
        var largeQty = parseFloat(row.querySelector('[data-size="large"]').value);
        var regularId = row.dataset.regularId;
        var largeId = row.dataset.largeId;

        // Regular size
        if (regularQty && regularQty > 0) {
            var key = ingredientId + '-regular';
            if (!addedIngredients[key]) {
                addedIngredients[key] = true;
                if (regularId) {
                    // Update existing
                    requests.push(
                        fetch('/business/recipes/ingredient/' + regularId, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
                            body: JSON.stringify({ quantity_required: regularQty })
                        })
                    );
                } else {
                    // Create new
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

        // Large size
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

    // 3. Wait for all requests
    Promise.all(requests)
    .then(function (responses) {
        var allOk = responses.every(function (r) { return r.ok; });
        if (allOk) {
            status.className = 'save-status is-visible save-status--success';
            status.textContent = 'Saved successfully!';
            setTimeout(function () {
                closeModal();
                refreshPage();
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
        refreshPage();
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

// ── Refresh page ──
function refreshPage() {
    window.location.reload();
}

// ── Close on overlay click ──
document.addEventListener('click', function (e) {
    var overlay = document.getElementById('recipe-modal');
    if (e.target === overlay) closeModal();
});

// ── Escape key closes modal ──
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
});

// ── Wire up all Edit buttons ──
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var productId = this.dataset.productId;
            if (productId) openModal(productId);
        });
    });
});

// ── Embed product data from server-side render ──
var PRODUCTS_DATA = @json($productsData);

// ═══════════════════════════════════════════════════════════════════
// INGREDIENT PROFILE DRILL-DOWN
// ═══════════════════════════════════════════════════════════════════
function openProfile(productId) {
    var modal = document.getElementById('profile-modal');
    var body  = document.getElementById('profile-body');

    document.getElementById('profile-title').textContent = 'Ingredient Profile';
    body.innerHTML = '<div style="text-align:center;padding:40px;opacity:.4;font-size:13px">Loading…</div>';
    modal.classList.add('is-open');

    fetch('/business/recipes/product/' + productId + '/profile', {
        headers: { 'Accept': 'application/json' }
    })
    .then(function (res) {
        if (!res.ok) throw new Error('Failed to load profile (' + res.status + ')');
        return res.json();
    })
    .then(renderProfile)
    .catch(function (err) {
        body.innerHTML = '<div style="text-align:center;padding:40px;opacity:.5;font-size:13px">'
            + escapeHtml(err.message) + '</div>';
    });
}

function closeProfile() {
    document.getElementById('profile-modal').classList.remove('is-open');
}

function renderProfile(data) {
    var p = data.product;
    document.getElementById('profile-title').textContent = p.name;

    var html = '';

    data.sizes.forEach(function (s) {
        var marginClass = s.margin_pct >= 60 ? 'margin-good'
                        : s.margin_pct >= 40 ? 'margin-warn'
                        : 'margin-bad';

        html += '<div class="profile-size-label">' + escapeHtml(s.size) + '</div>'
             +  '<div class="profile-summary ' + marginClass + '">'
             +    '<div class="profile-metric">'
             +      '<div class="profile-metric__value">' + peso(p.price) + '</div>'
             +      '<div class="profile-metric__label">Selling Price</div>'
             +    '</div>'
             +    '<div class="profile-metric">'
             +      '<div class="profile-metric__value">' + peso(s.total_cost) + '</div>'
             +      '<div class="profile-metric__label">Ingredient Cost</div>'
             +    '</div>'
             +    '<div class="profile-metric">'
             +      '<div class="profile-metric__value">' + s.margin_pct + '%</div>'
             +      '<div class="profile-metric__label">Gross Margin</div>'
             +    '</div>'
             +  '</div>';
    });

    if (!data.ingredients.length) {
        html += '<div style="text-align:center;padding:30px;opacity:.4;font-size:13px">'
             +  'No recipe ingredients defined yet.</div>';
    } else {
        html += '<table class="profile-table"><thead><tr>'
             +  '<th>Ingredient</th><th>Size</th><th style="text-align:right">Qty</th>'
             +  '<th style="text-align:right">Unit Cost</th><th style="text-align:right">Line Cost</th>'
             +  '<th>Primary Supplier</th>'
             +  '</tr></thead><tbody>';

        data.ingredients.forEach(function (ing) {
            var supplier = ing.supplier
                ? '<div class="supplier-cell">'
                +   '<div class="supplier-cell__name">' + escapeHtml(ing.supplier.name) + '</div>'
                +   (ing.supplier.contact_number
                        ? '<div class="supplier-cell__contact">' + escapeHtml(ing.supplier.contact_number) + '</div>'
                        : '')
                + '</div>'
                : '<span class="supplier-cell supplier-cell--none">Not linked</span>';

            html += '<tr>'
                 +  '<td><strong>' + escapeHtml(ing.name) + '</strong></td>'
                 +  '<td>' + escapeHtml(ing.size) + '</td>'
                 +  '<td class="num">' + trimNum(ing.quantity_required) + ' ' + escapeHtml(ing.unit) + '</td>'
                 +  '<td class="num">' + peso(ing.unit_cost) + '</td>'
                 +  '<td class="num"><strong>' + peso(ing.line_cost) + '</strong></td>'
                 +  '<td>' + supplier + '</td>'
                 +  '</tr>';
        });

        html += '</tbody></table>';
    }

    var hints = data.sizes.filter(function (s) { return s.suggested_price_65 > 0; });
    if (hints.length) {
        html += '<div class="profile-hint">Suggested price at 65% margin — '
             +  hints.map(function (s) {
                    return escapeHtml(s.size) + ': ' + peso(s.suggested_price_65);
                }).join(' &middot; ')
             +  '</div>';
    }

    document.getElementById('profile-body').innerHTML = html;
}

function peso(n) {
    return '₱' + Number(n).toLocaleString('en-PH', {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    });
}

function trimNum(n) {
    return parseFloat(Number(n).toFixed(3)).toString();
}

function escapeHtml(s) {
    var div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
}

document.getElementById('profile-modal').addEventListener('click', function (e) {
    if (e.target === this) closeProfile();
});
</script>

@endsection
