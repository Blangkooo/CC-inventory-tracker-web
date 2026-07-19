@php
    use App\Models\StockMovement;

    // ── Fallbacks for demo / empty DB ─────────────────────────────────
    if (!isset($stockItems) || $stockItems->isEmpty()) {
        $stockItems = collect([
            (object) ['item_name'=>'Milk', 'unit'=>'L', 'branch_name'=>'QC Main',   'estimated_amount'=>100, 'on_site_amount'=>100, 'min_threshold'=>20,  'status'=>'ok'],
            (object) ['item_name'=>'Sugar','unit'=>'kg','branch_name'=>'QC Main',   'estimated_amount'=>100, 'on_site_amount'=>95,  'min_threshold'=>15,  'status'=>'ok'],
            (object) ['item_name'=>'Coffee','unit'=>'kg','branch_name'=>'QC Main',   'estimated_amount'=>100, 'on_site_amount'=>100, 'min_threshold'=>10,  'status'=>'ok'],
            (object) ['item_name'=>'Cream', 'unit'=>'L', 'branch_name'=>'QC Main',   'estimated_amount'=>100, 'on_site_amount'=>85,  'min_threshold'=>20,  'status'=>'low'],
            (object) ['item_name'=>'Cups',  'unit'=>'pcs','branch_name'=>'QC Main',   'estimated_amount'=>100, 'on_site_amount'=>100, 'min_threshold'=>30,  'status'=>'ok'],
            (object) ['item_name'=>'Milk', 'unit'=>'L', 'branch_name'=>'Makati',    'estimated_amount'=>100, 'on_site_amount'=>100, 'min_threshold'=>20,  'status'=>'ok'],
            (object) ['item_name'=>'Sugar','unit'=>'kg','branch_name'=>'Makati',    'estimated_amount'=>100, 'on_site_amount'=>98,  'min_threshold'=>15,  'status'=>'ok'],
            (object) ['item_name'=>'Coffee','unit'=>'kg','branch_name'=>'Makati',    'estimated_amount'=>100, 'on_site_amount'=>100, 'min_threshold'=>10,  'status'=>'ok'],
            (object) ['item_name'=>'Cream', 'unit'=>'L', 'branch_name'=>'Makati',    'estimated_amount'=>100, 'on_site_amount'=>80,  'min_threshold'=>20,  'status'=>'low'],
            (object) ['item_name'=>'Cups',  'unit'=>'pcs','branch_name'=>'BGC',       'estimated_amount'=>100, 'on_site_amount'=>100, 'min_threshold'=>30,  'status'=>'ok'],
            (object) ['item_name'=>'Milk', 'unit'=>'L', 'branch_name'=>'BGC',       'estimated_amount'=>100, 'on_site_amount'=>75,  'min_threshold'=>20,  'status'=>'low'],
            (object) ['item_name'=>'Sugar','unit'=>'kg','branch_name'=>'BGC',       'estimated_amount'=>100, 'on_site_amount'=>100, 'min_threshold'=>15,  'status'=>'ok'],
            (object) ['item_name'=>'Coffee','unit'=>'kg','branch_name'=>'BGC',       'estimated_amount'=>100, 'on_site_amount'=>100, 'min_threshold'=>10,  'status'=>'ok'],
            (object) ['item_name'=>'Cream', 'unit'=>'L', 'branch_name'=>'BGC',       'estimated_amount'=>100, 'on_site_amount'=>60,  'min_threshold'=>20,  'status'=>'low'],
            (object) ['item_name'=>'Cups',  'unit'=>'pcs','branch_name'=>'BGC',       'estimated_amount'=>100, 'on_site_amount'=>100, 'min_threshold'=>30,  'status'=>'ok'],
        ]);
    }
    if (!isset($recentMovements) || $recentMovements->isEmpty()) {
        $recentMovements = collect();
    }
    if (!isset($activeAlerts) || $activeAlerts->isEmpty()) {
        $activeAlerts = collect();
    }
    if (!isset($recentTransactions) || $recentTransactions->isEmpty()) {
        $recentTransactions = collect();
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics — NITA</title>
    @include('partials._shared-styles')

    <style>
        /* ── PAGE ── */
        .page { max-width: 1400px; margin: 0 auto; padding: 28px 32px; }

        .page-head {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px;
        }

        .page-head__left { display: flex; align-items: baseline; gap: 10px; }
        .page-head__title { font-size: 22px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; }
        .page-head__title span { border-bottom: 3px solid var(--brown); padding-bottom: 2px; }
        .page-head__role { font-size: 15px; font-weight: 400; opacity: .5; }

        .subnav { display: flex; gap: 6px; }

        .subnav__pill {
            padding: 7px 18px; border-radius: 999px; font-size: 13px; font-weight: 600;
            border: 1.5px solid var(--border); background: #fff; color: var(--brown);
            cursor: pointer; text-decoration: none; transition: all .15s ease;
        }

        .subnav__pill:hover { border-color: var(--terra); color: var(--terra); }
        .subnav__pill.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }

        /* ── SUMMARY TABLE ── */
        .summary-table-wrap {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            overflow: hidden; margin-bottom: 24px;
        }

        .summary-table-wrap__head {
            padding: 14px 20px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }

        .summary-table-wrap__head svg { opacity: .7; flex-shrink: 0; }
        .summary-table-wrap__title { font-size: 13px; font-weight: 700; }

        .summary-table {
            width: 100%; border-collapse: collapse;
        }

        .summary-table th {
            padding: 10px 20px; font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .06em; opacity: .5;
            text-align: left; border-bottom: 1px solid var(--border);
            background: rgba(253,245,214,.3);
        }

        .summary-table td {
            padding: 10px 20px; font-size: 13px; font-weight: 500;
            border-bottom: 1px solid rgba(92,45,27,.05);
        }

        .summary-table tr:last-child td { border-bottom: none; }
        .summary-table tr:hover td { background: rgba(188,97,75,.04); }
        .summary-table td.num { text-align: right; font-variant-numeric: tabular-nums; }

        .badge {
            display: inline-block; padding: 2px 10px; border-radius: 999px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        }
        .badge-green  { background: rgba(22,163,74,.1); color: #16a34a; }
        .badge-amber  { background: rgba(217,119,6,.1); color: #d97706; }
        .badge-red    { background: rgba(220,38,38,.1); color: #dc2626; }
        .badge-gray   { background: rgba(92,45,27,.06); color: rgba(92,45,27,.5); }

        /* ── Legend Cards (Summary) ── */
        .branch-card-sm {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            padding: 16px 18px;
        }

        .branch-card-sm__name { font-size: 14px; font-weight: 700; margin-bottom: 8px; }
        .branch-card-sm__stat { font-size: 12px; font-weight: 500; opacity: .7; display: flex; justify-content: space-between; padding: 4px 0; }

        /* ── CARD GRID (Flags) ── */
        .card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            display: flex; flex-direction: column; overflow: hidden;
        }

        .card__head {
            display: flex; align-items: center; gap: 10px;
            padding: 16px 20px; border-bottom: 1px solid var(--border);
        }

        .card__head svg { flex-shrink: 0; opacity: .7; }
        .card__head-title { font-size: 13px; font-weight: 700; letter-spacing: .01em; }

        .card__body { padding: 16px 20px; flex: 1; }

        .card__foot {
            padding: 12px 20px; border-top: 1px solid var(--border);
            display: flex; justify-content: flex-end;
        }

        .btn-edit {
            padding: 6px 18px; background: #fff; color: var(--brown);
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
            transition: all .15s ease;
        }

        .btn-edit:hover { background: var(--brown); color: var(--cream); border-color: var(--brown); }

        /* Variables */
        .var-list { list-style: none; }

        .var-list li {
            display: flex; align-items: baseline; gap: 8px;
            padding: 10px 0; border-bottom: 1px solid rgba(92,45,27,.06);
            font-size: 13px;
        }

        .var-list li:last-child { border-bottom: none; }
        .var-label { font-weight: 600; opacity: .75; min-width: 160px; }
        .var-value { font-weight: 700; }

        /* Remarks */
        .remarks-sub { font-size: 12px; opacity: .6; margin-bottom: 14px; line-height: 1.6; }
        .remarks-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .remarks-col h4 {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .5; margin-bottom: 10px;
            padding-bottom: 8px; border-bottom: 1px solid var(--border);
        }

        .remark-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 7px 0; font-size: 12px;
            border-bottom: 1px solid rgba(92,45,27,.05);
        }

        .remark-row:last-child { border-bottom: none; }

        .dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        .dot-green  { background: #16a34a; }
        .dot-yellow { background: #eab308; }
        .dot-orange { background: #f97316; }
        .dot-red    { background: #ef4444; }
        .dot-dkred  { background: #dc2626; }

        /* Formula cards */
        .formula-sub { font-size: 12px; opacity: .6; margin-bottom: 14px; line-height: 1.6; }

        .formula-box {
            background: rgba(220,38,38,.04); border: 1px solid rgba(220,38,38,.18);
            border-radius: 8px; padding: 14px 16px;
        }

        .formula-expr { font-size: 13px; font-weight: 700; color: #dc2626; }
        .formula-note { font-size: 11px; opacity: .6; margin-top: 6px; line-height: 1.5; }

        /* Breakdown */
        .breakdown-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .breakdown-col h4 {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .5; margin-bottom: 10px;
            padding-bottom: 8px; border-bottom: 1px solid var(--border);
        }

        .breakdown-row {
            display: flex; justify-content: space-between;
            padding: 7px 0; font-size: 12px;
            border-bottom: 1px solid rgba(92,45,27,.05);
        }

        .breakdown-row:last-child { border-bottom: none; }
        .bd-green  { font-weight: 700; color: #16a34a; }
        .bd-amber  { font-weight: 700; color: #d97706; }
        .bd-red    { font-weight: 700; color: #dc2626; }

        /* ── Alert Table (Flags view) ── */
        .alert-table-wrap {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            overflow: hidden;
        }

        .alert-table {
            width: 100%; border-collapse: collapse;
        }

        .alert-table th {
            padding: 10px 16px; font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .06em; opacity: .5;
            text-align: left; border-bottom: 1px solid var(--border);
            background: rgba(253,245,214,.3);
        }

        .alert-table td {
            padding: 10px 16px; font-size: 13px; font-weight: 500;
            border-bottom: 1px solid rgba(92,45,27,.05);
        }

        .alert-table tr:last-child td { border-bottom: none; }
        .alert-table tr:hover td { background: rgba(188,97,75,.04); }

        .severity-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 999px;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
        }
        .sev-none    { background: rgba(22,163,74,.1); color: #16a34a; }
        .sev-mild    { background: rgba(234,179,8,.1);  color: #a16207; }
        .sev-moderate { background: rgba(249,115,22,.1); color: #c2410c; }
        .sev-severe  { background: rgba(220,38,38,.1);  color: #b91c1c; }

        @media (max-width: 900px) {
            .card-grid { grid-template-columns: 1fr; }
            .kpi-row { grid-template-columns: repeat(2, 1fr); }
            .page { padding: 16px; }
            .page-head { flex-direction: column; align-items: flex-start; gap: 12px; }
        }

        @media (max-width: 600px) {
            .kpi-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<nav class="nav">
    <div class="nav__inner">
        <div class="nav__left">
            <a href="{{ url('/dashboard') }}" class="nav__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="NITA">
            </a>
            <div class="nav__pills">
                <a href="{{ url('/dashboard') }}"        class="nav__pill">Dashboard</a>
                <a href="{{ url('/business/recipes') }}"  class="nav__pill">Businesses</a>
                <a href="{{ url('/logistics') }}"         class="nav__pill is-active">Logistics</a>
            </div>
        </div>
        <div class="nav__right">
            <a href="{{ url('/alerts') }}" class="nav__icon" title="Alerts">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </a>
            <a href="{{ url('/alerts') }}" class="nav__icon nav__icon--box" title="Messages">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </a>
            <a href="{{ url('/settings') }}" class="nav__icon" title="Settings">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </a>
            <div class="nav__sep"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav__logout">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="page">

    <div class="page-head">
        <div class="page-head__left">
            <h1 class="page-head__title"><span>Logistics</span></h1>
            <span class="page-head__role">/ {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
        </div>
        <div class="subnav">
            <a href="{{ url('/logistics') }}?tab=summary" class="subnav__pill {{ $tab === 'summary' ? 'is-active' : '' }}">Summary</a>
            <a href="{{ url('/logistics') }}?tab=flags" class="subnav__pill {{ $tab === 'flags' ? 'is-active' : '' }}">Flags</a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SUMMARY TAB — Item-Level Inventory Reconciliation Table
        ═══════════════════════════════════════════════════════════════ --}}
    @if ($tab === 'summary')

        {{-- Inventory Reconciliation Table --}}
        <div class="summary-table-wrap">
            <div class="summary-table-wrap__head">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
                <span class="summary-table-wrap__title">Stock Reconciliation by Item</span>
            </div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Branch</th>
                        <th>Estimated Amount</th>
                        <th>On-site Amount</th>
                        <th>Leakage Indicator</th>
                        <th>Inventory Indicator</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockItems as $item)
                        @php
                            $onSite = (float) $item->on_site_amount;
                            $est    = (float) $item->estimated_amount;
                            $pct    = $est > 0 ? ($onSite / $est) * 100 : 100;

                            // Leakage indicator
                            if ($pct >= 95) {
                                $leakLabel = 'None';
                                $leakClass = 'sev-none';
                            } elseif ($pct >= 85) {
                                $leakLabel = 'Mild';
                                $leakClass = 'sev-mild';
                            } elseif ($pct >= 70) {
                                $leakLabel = 'Moderate';
                                $leakClass = 'sev-moderate';
                            } else {
                                $leakLabel = 'Severe';
                                $leakClass = 'sev-severe';
                            }

                            // Inventory indicator
                            if ($item->status === 'out') {
                                $invLabel = 'Out';
                                $invClass = 'badge-red';
                            } elseif ($item->status === 'low') {
                                $invLabel = 'Low';
                                $invClass = 'badge-amber';
                            } else {
                                $invLabel = 'Stocked';
                                $invClass = 'badge-green';
                            }

                            // Remarks
                            if ($pct >= 95) {
                                $remark = 'Normal';
                                $remarkColor = '#16a34a';
                            } elseif ($pct >= 85) {
                                $remark = 'Low';
                                $remarkColor = '#d97706';
                            } elseif ($pct >= 70) {
                                $remark = 'Moderate';
                                $remarkColor = '#c2410c';
                            } else {
                                $remark = 'Critical';
                                $remarkColor = '#dc2626';
                            }
                        @endphp
                        <tr>
                            <td style="font-weight:600">
                                {{ $item->item_name }}
                                <span style="opacity:.4;font-weight:400;font-size:11px">{{ $item->unit }}</span>
                            </td>
                            <td>{{ $item->branch_name }}</td>
                            <td class="num">{{ number_format($est, $item->unit === 'pcs' ? 0 : 1) }} {{ $item->unit }}</td>
                            <td class="num">{{ number_format($onSite, $item->unit === 'pcs' ? 0 : 1) }} {{ $item->unit }}</td>
                            <td>
                                <span class="severity-badge {{ $leakClass }}">{{ $leakLabel }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $invClass }}">{{ $invLabel }}</span>
                            </td>
                            <td style="font-weight:700;color:{{ $remarkColor }}">{{ $remark }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;opacity:.4;padding:24px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 8px;opacity:.3">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r=".5" fill="currentColor"/>
                                </svg>
                                No stock items found for the selected branch.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Legend / Threshold Reference Card --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:24px;">
            {{-- Leakage Indicator Legend --}}
            <div class="branch-card-sm">
                <div class="branch-card-sm__name" style="font-size:12px;text-transform:uppercase;letter-spacing:.05em;opacity:.6">Leakage Indicator</div>
                <div style="margin-top:8px">
                    <div class="branch-card-sm__stat"><span>Normal</span><span style="color:#16a34a;font-weight:700">≥ 95%</span></div>
                    <div class="branch-card-sm__stat"><span>Mild</span><span style="color:#a16207;font-weight:700">85% – 94%</span></div>
                    <div class="branch-card-sm__stat"><span>Moderate</span><span style="color:#c2410c;font-weight:700">70% – 84%</span></div>
                    <div class="branch-card-sm__stat"><span>Severe</span><span style="color:#b91c1c;font-weight:700">&lt; 70%</span></div>
                </div>
            </div>

            {{-- Inventory Indicator Legend --}}
            <div class="branch-card-sm">
                <div class="branch-card-sm__name" style="font-size:12px;text-transform:uppercase;letter-spacing:.05em;opacity:.6">Inventory Indicator</div>
                <div style="margin-top:8px">
                    <div class="branch-card-sm__stat"><span>Stocked</span><span class="badge badge-green">OK</span></div>
                    <div class="branch-card-sm__stat"><span>Low</span><span class="badge badge-amber">Low</span></div>
                    <div class="branch-card-sm__stat"><span>Out</span><span class="badge badge-red">Out</span></div>
                </div>
            </div>

            {{-- Summary Stats --}}
            <div class="branch-card-sm">
                <div class="branch-card-sm__name" style="font-size:12px;text-transform:uppercase;letter-spacing:.05em;opacity:.6">Summary</div>
                <div style="margin-top:8px">
                    @php
                        $totalItems = $stockItems->count();
                        $flaggedItems = $stockItems->filter(fn($i) => (float) $i->on_site_amount < (float) $i->estimated_amount)->count();
                        $leakItems = $stockItems->filter(fn($i) => (float) $i->on_site_amount < (float) $i->estimated_amount * 0.85)->count();
                    @endphp
                    <div class="branch-card-sm__stat"><span>Total Items</span><span style="font-weight:700">{{ $totalItems }}</span></div>
                    <div class="branch-card-sm__stat"><span>With Variance</span><span style="font-weight:700;color:#d97706">{{ $flaggedItems }}</span></div>
                    <div class="branch-card-sm__stat"><span>Probable Leak</span><span style="font-weight:700;color:#dc2626">{{ $leakItems }}</span></div>
                </div>
            </div>
        </div>

    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         FLAGS TAB
        ═══════════════════════════════════════════════════════════════ --}}
    @if ($tab === 'flags')

        {{-- Active Alerts Table --}}
        <div class="alert-table-wrap" style="margin-bottom:24px;">
            <div class="summary-table-wrap__head">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                    <line x1="4" y1="22" x2="4" y2="15"/>
                </svg>
                <span class="summary-table-wrap__title">Active Discrepancy Flags</span>
            </div>
            <table class="alert-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Branch</th>
                        <th>Expected</th>
                        <th>Actual</th>
                        <th>Leakage</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activeAlerts as $alert)
                        <tr>
                            <td style="font-weight:600">{{ $alert->ingredient?->name ?? 'Raw Materials' }}</td>
                            <td>{{ $alert->branch?->name ?? '—' }}</td>
                            <td>{{ number_format($alert->expected_value, 2) }} {{ $alert->ingredient?->unit ?? '' }}</td>
                            <td>{{ number_format($alert->actual_value, 2) }} {{ $alert->ingredient?->unit ?? '' }}</td>
                            <td>
                                @php
                                    $variance = $alert->variance;
                                    $absPct = $alert->expected_value > 0 ? abs($variance / $alert->expected_value) * 100 : 0;
                                    $sev = $alert->severity ?? 'none';
                                @endphp
                                <span class="severity-badge sev-{{ $sev }}">
                                    {{ ucfirst($sev) }}
                                </span>
                            </td>
                            <td><span class="badge badge-red">Flagged</span></td>
                            <td style="font-weight:600;color:{{ $absPct > 15 ? '#dc2626' : ($absPct > 5 ? '#d97706' : '#16a34a') }}">
                                @if ($absPct > 15)
                                    Low
                                @elseif ($absPct > 5)
                                    Moderate
                                @else
                                    Normal
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;opacity:.4;padding:24px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 8px;opacity:.3">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r=".5" fill="currentColor"/>
                                </svg>
                                No active flags — all stock levels are normal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-grid">
            {{-- Variables --}}
            <div class="card">
                <div class="card__head">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                        <circle cx="3" cy="6" r="1"/><circle cx="3" cy="12" r="1"/><circle cx="3" cy="18" r="1"/>
                    </svg>
                    <span class="card__head-title">Variables</span>
                </div>
                <div class="card__body">
                    <ul class="var-list">
                        <li><span class="var-label">Constant Float Value</span><span class="var-value">&#8369;200</span></li>
                        <li><span class="var-label">Expected Total Sales</span><span class="var-value">Total EOD Transactions</span></li>
                        <li><span class="var-label">Total Inventory</span><span class="var-value">{{ $totalStockItems }} items</span></li>
                    </ul>
                </div>
                <div class="card__foot"><button class="btn-edit">Edit</button></div>
            </div>

            {{-- Remarks --}}
            <div class="card">
                <div class="card__head">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    <span class="card__head-title">Remarks</span>
                </div>
                <div class="card__body">
                    <p class="remarks-sub">Indicator rules for Leakage &amp; Inventory status thresholds.</p>
                    <div class="remarks-cols">
                        <div class="remarks-col">
                            <h4>Leakage Indicator</h4>
                            <div class="remark-row"><span>Normal</span><span class="dot dot-green"></span></div>
                            <div class="remark-row"><span>Running Low</span><span class="dot dot-yellow"></span></div>
                            <div class="remark-row"><span>Low</span><span class="dot dot-orange"></span></div>
                            <div class="remark-row"><span>Almost Out</span><span class="dot dot-red"></span></div>
                            <div class="remark-row"><span>Out</span><span class="dot dot-dkred"></span></div>
                        </div>
                        <div class="remarks-col">
                            <h4>Inventory Indicator</h4>
                            <div class="remark-row"><span>Normal</span><span class="dot dot-green"></span></div>
                            <div class="remark-row"><span>Running Low</span><span class="dot dot-yellow"></span></div>
                            <div class="remark-row"><span>Low</span><span class="dot dot-orange"></span></div>
                            <div class="remark-row"><span>Almost Out</span><span class="dot dot-red"></span></div>
                            <div class="remark-row"><span>Out</span><span class="dot dot-dkred"></span></div>
                        </div>
                    </div>
                </div>
                <div class="card__foot"><button class="btn-edit">Edit</button></div>
            </div>

            {{-- Float Amount Discrepancy --}}
            <div class="card">
                <div class="card__head">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r=".5" fill="currentColor"/>
                    </svg>
                    <span class="card__head-title">Float Amount Discrepancy</span>
                </div>
                <div class="card__body">
                    <p class="formula-sub">Verifies that the amount left in the till is exact.</p>
                    <div class="formula-box">
                        <div class="formula-expr">Actual Till Amount / Constant Float Value</div>
                        <div class="formula-note">Must equate to 1 to be true — a flag is sent otherwise.</div>
                    </div>
                </div>
                <div class="card__foot"><button class="btn-edit">Edit</button></div>
            </div>

            {{-- Total Sales Discrepancy --}}
            <div class="card">
                <div class="card__head">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
                    </svg>
                    <span class="card__head-title">Total Sales Discrepancy</span>
                </div>
                <div class="card__body">
                    <p class="formula-sub">Tracks variance between cash collected and expected revenue.</p>
                    <div class="formula-box">
                        <div class="formula-expr">Actual Cash / Expected Total Sales</div>
                    </div>
                </div>
                <div class="card__foot"><button class="btn-edit">Edit</button></div>
            </div>

            {{-- EOD Inventory Discrepancy --}}
            <div class="card">
                <div class="card__head">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    <span class="card__head-title">EOD Inventory Discrepancy</span>
                </div>
                <div class="card__body">
                    <p class="formula-sub">Compares end-of-day physical count against expected inventory levels.</p>
                    <div class="formula-box">
                        <div class="formula-expr">Actual Inventory Left / Expected Inventory Left</div>
                    </div>
                </div>
                <div class="card__foot"><button class="btn-edit">Edit</button></div>
            </div>

            {{-- Leakage & Inventory Breakdown --}}
            <div class="card">
                <div class="card__head">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    <span class="card__head-title">Leakage &amp; Inventory Breakdown</span>
                </div>
                <div class="card__body">
                    <div class="breakdown-cols">
                        <div class="breakdown-col">
                            <h4>Leakage Indicator</h4>
                            <div class="breakdown-row"><span>Normal</span><span class="bd-green">&lt; 5%</span></div>
                            <div class="breakdown-row"><span>Running Low</span><span class="bd-amber">5% – 10%</span></div>
                            <div class="breakdown-row"><span>Low</span><span class="bd-amber">10% – 15%</span></div>
                            <div class="breakdown-row"><span>Almost Out</span><span class="bd-red">15% – 20%</span></div>
                            <div class="breakdown-row"><span>Out</span><span class="bd-red">&gt; 20%</span></div>
                        </div>
                        <div class="breakdown-col">
                            <h4>Inventory Indicator</h4>
                            <div class="breakdown-row"><span>Normal</span><span class="bd-green">&gt; 60%</span></div>
                            <div class="breakdown-row"><span>Running Low</span><span class="bd-amber">40% – 60%</span></div>
                            <div class="breakdown-row"><span>Low</span><span class="bd-amber">20% – 40%</span></div>
                            <div class="breakdown-row"><span>Almost Out</span><span class="bd-red">10% – 20%</span></div>
                            <div class="breakdown-row"><span>Out</span><span class="bd-red">&lt; 10%</span></div>
                        </div>
                    </div>
                </div>
                <div class="card__foot"><button class="btn-edit">Edit</button></div>
            </div>

        </div>
    @endif

</div>

</body>
</html>
