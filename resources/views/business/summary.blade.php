@php
    // ── Placeholder fallbacks ─────────────────────────────────────────
    if (!isset($branches) || $branches->isEmpty()) {
        $branches = collect([
            (object)['id'=>1,'name'=>'QC Main Branch'],
            (object)['id'=>2,'name'=>'Makati Outlet'],
            (object)['id'=>3,'name'=>'BGC Branch'],
        ]);
    }
    if (!isset($activeBranch) || !$activeBranch) {
        $activeBranch = $branches->first();
    }
    if (!isset($totalRevenue) || $totalRevenue == 0) $totalRevenue = 1_240_000;

    if (!isset($recentTransactions) || $recentTransactions->isEmpty()) {
        $recentTransactions = collect([
            (object)['id'=>1005,'total_amount'=>375.00,'product'=>(object)['name'=>'Classic Milk Tea'],   'user'=>(object)['name'=>'Maria S.'],'created_at'=>now()->subMinutes(10)],
            (object)['id'=>1004,'total_amount'=>600.00,'product'=>(object)['name'=>'Black Forest Milk Tea'],'user'=>(object)['name'=>'Juan D.'], 'created_at'=>now()->subMinutes(32)],
            (object)['id'=>1003,'total_amount'=>275.00,'product'=>(object)['name'=>'Iced Coffee'],        'user'=>(object)['name'=>'Ana R.'],  'created_at'=>now()->subMinutes(58)],
            (object)['id'=>1002,'total_amount'=>50.00, 'product'=>(object)['name'=>'Extra Sugar Shot'],   'user'=>(object)['name'=>'Maria S.'],'created_at'=>now()->subHours(2)],
            (object)['id'=>1001,'total_amount'=>450.00,'product'=>(object)['name'=>'Taro Milk Tea'],      'user'=>(object)['name'=>'Juan D.'], 'created_at'=>now()->subHours(3)],
        ]);
    }
    if (!isset($leakageRows) || $leakageRows->isEmpty()) {
        $leakageRows = collect([
            (object)['ingredient'=>(object)['name'=>'Whole Milk','unit'=>'L'],   'variance'=>-12.5],
            (object)['ingredient'=>(object)['name'=>'Flavor Powder','unit'=>'kg'],'variance'=>-3.2],
            (object)['ingredient'=>(object)['name'=>'Sugar','unit'=>'kg'],        'variance'=>-8.7],
            (object)['ingredient'=>(object)['name'=>'Black Tea Base','unit'=>'L'],'variance'=>-2.1],
        ]);
    }
    if (!isset($monthlySales) || $monthlySales->isEmpty()) {
        $monthlySales = collect([1=>42000,2=>38000,3=>55000,4=>61000,5=>70000,6=>89000,7=>94000,8=>0,9=>0,10=>0,11=>0,12=>0]);
    }
@endphp
@extends('layouts.sidebar')

@section('title', 'Business Summary')

@section('styles')
        /* ══ LAYOUT ══ */
        .main-wrapper {
            display: flex; gap: 16px; max-width: 1400px;
            margin: 24px auto 0; padding: 0 32px 40px;
        }

        /* ══ BRANCH SIDEBAR ══ */
        .branch-sidebar {
            width: 120px; flex-shrink: 0;
            background: var(--terra); border-radius: var(--radius);
            display: flex; flex-direction: column; align-items: center;
            padding: 16px 10px; gap: 10px;
        }

        .biz-header {
            text-align: center; padding: 10px 6px;
            background: rgba(250, 249, 247,.95);
            border: 1.5px solid var(--brown);
            border-radius: 10px; width: 100%;
        }

        .biz-header .biz-name { font-size: 11px; font-weight: 700; margin-top: 6px; }
        .biz-header .biz-sub  { font-size: 8px; font-weight: 600; opacity: .6; text-transform: uppercase; letter-spacing: .5px; }

        .branch-list {
            display: flex; flex-direction: column; align-items: center;
            gap: 8px; width: 100%; padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,.2);
        }

        .branch-dot {
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(250, 249, 247,.9); border: 1.5px solid var(--brown);
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700; color: var(--brown);
            cursor: pointer; transition: all .15s ease; text-decoration: none; flex-shrink: 0;
        }

        .branch-dot.active { background: var(--brown); color: var(--cream); border-color: var(--brown); }
        .branch-dot:hover  { transform: scale(1.08); background: var(--brown); color: var(--cream); }

        /* ══ CONTENT ══ */
        .content-area { flex: 1; min-width: 0; }

        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
        }

        .page-header h1 { font-size: 22px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; }
        .page-header h1 .pipe { font-weight: 400; opacity: .5; }

        .sub-tabs { display: flex; gap: 6px; flex-wrap: wrap; }

        .sub-tab {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: 999px; font-size: 12px; font-weight: 600;
            color: var(--brown); border: 1.5px solid var(--border); background: #fff;
            text-decoration: none; transition: all .15s ease;
        }

        .sub-tab:hover { border-color: var(--terra); color: var(--terra); }
        .sub-tab.active { background: var(--terra); color: #fff; border-color: var(--terra); }

        /* ══ SUMMARY GRID ══ */
        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            padding: 20px; margin-bottom: 16px;
        }

        .card h3 {
            font-size: 13px; font-weight: 700; margin-bottom: 14px; padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        /* Activity log */
        .activity-log { list-style: none; }

        .activity-log li {
            display: flex; justify-content: space-between; align-items: baseline;
            padding: 8px 0; font-size: 13px;
            border-bottom: 1px solid rgba(92,45,27,.06);
        }

        .activity-log li:last-child { border-bottom: none; }
        .activity-log .items-list { font-size: 11px; opacity: .6; padding: 0; border-bottom: none; }
        .activity-log .total-row { font-weight: 800; font-size: 14px; padding-top: 12px; border-top: 2px solid var(--brown); }

        /* Leakage rows */
        .leakage-row {
            display: flex; justify-content: space-between; padding: 9px 0;
            font-size: 13px; border-bottom: 1px solid rgba(92,45,27,.06);
        }

        .leakage-row:last-child { border-bottom: none; }
        .qty.red   { color: #dc2626; font-weight: 700; }
        .qty.amber { color: #d97706; font-weight: 700; }

        /* Profit highlight */
        .profit-card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            padding: 24px 20px; margin-bottom: 16px; text-align: center;
        }

        .profit-card h3 { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; opacity: .5; margin-bottom: 10px; }
        .profit-value { font-size: 38px; font-weight: 800; color: #16a34a; }
        .profit-value .arrow { font-size: 22px; }

        /* Charts */
        .chart-card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            padding: 20px; margin-bottom: 16px;
        }

        .chart-card h3 { font-size: 13px; font-weight: 700; margin-bottom: 14px; }
        .chart-canvas  { width: 100%; height: 140px; position: relative; overflow: hidden; }
        .chart-svg     { width: 100%; height: 100%; }

        .chart-milestones {
            display: flex; justify-content: space-between;
            font-size: 10px; opacity: .5; margin-top: 6px;
        }

        @media (max-width: 900px) {
            .branch-sidebar { display: none; }
            .summary-grid   { grid-template-columns: 1fr; }
            .main-wrapper   { padding: 0 16px 32px; }
        }
@endsection

@section('content')

<div class="main-wrapper">

    {{-- Branch Sidebar --}}
    <div class="branch-sidebar">
        <div class="biz-header">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="6" width="20" height="16" rx="2" fill="#BC614B" stroke="#5C2D1B" stroke-width="1.5"/>
                <path d="M22 8c2 0 4 1 4 4s-2 4-4 4" stroke="#5C2D1B" stroke-width="1.5" fill="none"/>
            </svg>
            <div class="biz-name">{{ $activeBranch?->name ?? 'All Branches' }}</div>
            <div class="biz-sub">{{ $activeBranch ? 'Active Branch' : 'Owner View' }}</div>
        </div>

        @php
            $isOwner = auth()->user()->role === 'super_admin';
            $userBranchId = auth()->user()->branch_id;
        @endphp

        <div class="branch-list">
            @foreach ($branches as $branch)
                @php $ini = collect(explode(' ', $branch->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode(''); @endphp
                <a href="#" class="branch-dot {{ $activeBranch?->id === $branch->id ? 'active' : '' }}" title="{{ $branch->name }}">{{ $ini }}</a>
            @endforeach
        </div>
    </div>

    {{-- Content Area --}}
    <div class="content-area">

        <div class="page-header">
            <h1>Businesses <span class="pipe">|</span> {{ $isOwner ? 'Owner' : 'Manager' }}</h1>
            <div class="sub-tabs">
                <a href="{{ url('/business/summary') }}" class="sub-tab active">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    Summary
                </a>
                <a href="{{ url('/business/recipes') }}" class="sub-tab">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                    Recipe
                </a>
                <a href="{{ url('/business/workers') }}" class="sub-tab">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    Staff
                </a>
                <a href="{{ url('/business/verification') }}" class="sub-tab">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Verification
                </a>
            </div>
        </div>

        {{-- 2-Column Grid --}}
        <div class="summary-grid">

            {{-- LEFT COLUMN --}}
            <div class="left-col">
                <div class="card">
                    <h3>Recent Transactions — {{ $activeBranch?->name ?? 'All' }}</h3>
                    @if ($recentTransactions->isEmpty())
                        <p style="font-size:13px;opacity:.4;padding:8px 0">No transactions recorded yet.</p>
                    @else
                        <ul class="activity-log">
                            @foreach ($recentTransactions as $i => $tx)
                                <li>
                                    <span><strong>Txn #{{ $tx->id }}</strong></span>
                                    <span>&#8369;{{ number_format($tx->total_amount, 2) }}</span>
                                </li>
                                <li class="items-list">
                                    <span>{{ $tx->product?->name ?? 'Unknown Item' }} — {{ $tx->created_at->format('M d, g:iA') }} · {{ $tx->user?->name ?? '—' }}</span>
                                </li>
                            @endforeach
                            <li class="total-row">
                                <span>TOTAL:</span>
                                <span>&#8369;{{ number_format($recentTransactions->sum('total_amount'), 2) }}</span>
                            </li>
                        </ul>
                    @endif
                </div>

                <div class="card">
                    <h3>Leakage Log (Negative Variance)</h3>
                    @if ($leakageRows->isEmpty())
                        <p style="font-size:13px;opacity:.4;padding:8px 0">No leakage records found.</p>
                    @else
                        @foreach ($leakageRows as $row)
                            <div class="leakage-row">
                                <span>{{ $row->ingredient?->name ?? 'Unknown' }}</span>
                                <span class="qty red">{{ number_format($row->variance, 2) }} {{ $row->ingredient?->unit ?? '' }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="right-col">
                <div class="profit-card">
                    <h3>Annual Revenue ({{ now()->year }})</h3>
                    <div class="profit-value">
                        &#8369;{{ $totalRevenue >= 1_000_000
                            ? number_format($totalRevenue / 1_000_000, 2) . 'M'
                            : ($totalRevenue >= 1_000 ? number_format($totalRevenue / 1_000, 1) . 'k' : number_format($totalRevenue, 2)) }}
                        @if ($totalRevenue > 0)<span class="arrow">&uarr;</span>@endif
                    </div>
                </div>

                @php
                    // Build 12-point SVG line from $monthlySales (month => total)
                    $maxSales = $monthlySales->max() ?: 1;
                    $svgW = 300; $svgH = 110; $pad = 10;
                    $pts = collect(range(1, 12))->map(function ($m) use ($monthlySales, $maxSales, $svgW, $svgH, $pad) {
                        $x = $pad + ($m - 1) * (($svgW - $pad * 2) / 11);
                        $val = $monthlySales->get($m, 0);
                        $y = $svgH - $pad - ($val / $maxSales) * ($svgH - $pad * 2);
                        return "$x,$y";
                    })->implode(' ');
                    $polyClose = $pts . " $svgW,$svgH 0,$svgH";
                    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                @endphp

                <div class="chart-card">
                    <h3>Monthly Sales — {{ now()->year }}</h3>
                    <div class="chart-canvas">
                        <svg class="chart-svg" viewBox="0 0 {{ $svgW }} {{ $svgH }}" preserveAspectRatio="none">
                            <polygon points="{{ $polyClose }}" fill="rgba(188,97,75,.08)"/>
                            <polyline points="{{ $pts }}" fill="none" stroke="#BC614B" stroke-width="2.5" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="chart-milestones">
                        @foreach (['Jan','Mar','May','Jul','Sep','Nov'] as $ml)<span>{{ $ml }}</span>@endforeach
                    </div>
                    @if ($monthlySales->isEmpty())
                        <p style="font-size:12px;opacity:.4;margin-top:8px;text-align:center">No sales data yet for this year.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
