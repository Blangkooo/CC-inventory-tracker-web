@php
    use App\Models\StockMovement;
    if (!isset($stockItems) || $stockItems->isEmpty()) {
        $stockItems = collect([
            (object)['item_name'=>'Milk','unit'=>'L','branch_name'=>'QC Main','estimated_amount'=>100,'on_site_amount'=>100,'min_threshold'=>20,'status'=>'ok'],
            (object)['item_name'=>'Sugar','unit'=>'kg','branch_name'=>'QC Main','estimated_amount'=>100,'on_site_amount'=>95,'min_threshold'=>15,'status'=>'ok'],
            (object)['item_name'=>'Coffee','unit'=>'kg','branch_name'=>'QC Main','estimated_amount'=>100,'on_site_amount'=>100,'min_threshold'=>10,'status'=>'ok'],
            (object)['item_name'=>'Cream','unit'=>'L','branch_name'=>'QC Main','estimated_amount'=>100,'on_site_amount'=>85,'min_threshold'=>20,'status'=>'low'],
            (object)['item_name'=>'Cups','unit'=>'pcs','branch_name'=>'QC Main','estimated_amount'=>100,'on_site_amount'=>100,'min_threshold'=>30,'status'=>'ok'],
        ]);
    }
    if (!isset($recentMovements) || $recentMovements->isEmpty()) { $recentMovements = collect(); }
    if (!isset($activeAlerts) || $activeAlerts->isEmpty()) { $activeAlerts = collect(); }
    if (!isset($recentTransactions) || $recentTransactions->isEmpty()) { $recentTransactions = collect(); }
@endphp

@extends('layouts.sidebar')

@section('title', 'Logistics')

@section('styles')
    .page-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .page-head__left { display: flex; align-items: baseline; gap: 10px; }
    .page-head__title { font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
    .page-head__role { font-size: 14px; color: var(--text-3); }

    .subnav { display: flex; gap: 6px; }
    .subnav__pill {
        padding: 7px 18px; border-radius: 999px; font-size: 13px; font-weight: 600;
        border: 1.5px solid var(--border); background: var(--card); color: var(--text-2);
        cursor: pointer; text-decoration: none; transition: all .15s;
    }
    .subnav__pill:hover { border-color: var(--accent); color: var(--accent); }
    .subnav__pill.is-active { background: var(--accent); color: #fff; border-color: var(--accent); }

    .summary-table-wrap {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-bottom: 24px;
    }
    .summary-table-wrap__head {
        padding: 14px 20px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 10px;
    }
    .summary-table-wrap__head svg { opacity: .6; }
    .summary-table-wrap__title { font-size: 13px; font-weight: 700; }

    .summary-table { width: 100%; border-collapse: collapse; }
    .summary-table th {
        padding: 10px 20px; font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em; color: var(--text-3);
        text-align: left; border-bottom: 1px solid var(--border); background: rgba(0,0,0,.01);
    }
    .summary-table td { padding: 10px 20px; font-size: 13px; border-bottom: 1px solid var(--border); }
    .summary-table tr:last-child td { border-bottom: none; }
    .summary-table tr:hover td { background: var(--accent-light); }
    .summary-table td.num { text-align: right; font-variant-numeric: tabular-nums; }

    .severity-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase;
    }
    .sev-none { background: rgba(0,184,148,.1); color: var(--green); }
    .sev-mild { background: rgba(253,203,110,.15); color: #a16207; }
    .sev-moderate { background: rgba(225,112,85,.1); color: #c2410c; }
    .sev-severe { background: rgba(214,48,49,.1); color: #d63031; }

    .branch-card-sm {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius); box-shadow: var(--shadow); padding: 16px 18px;
    }
    .branch-card-sm__name { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-3); }
    .branch-card-sm__stat { font-size: 12px; display: flex; justify-content: space-between; padding: 4px 0; }

    .card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .card__head svg { flex-shrink: 0; opacity: .6; }
    .card__head-title { font-size: 13px; font-weight: 700; }
    .card__foot { padding: 12px 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; }

    .btn-edit {
        padding: 6px 18px; background: var(--card); color: var(--text-2);
        border: 1.5px solid var(--border); border-radius: 8px;
        font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font); transition: all .15s;
    }
    .btn-edit:hover { background: var(--text); color: #fff; border-color: var(--text); }

    .var-list { list-style: none; }
    .var-list li { display: flex; align-items: baseline; gap: 8px; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
    .var-list li:last-child { border-bottom: none; }
    .var-label { font-weight: 600; color: var(--text-2); min-width: 160px; }
    .var-value { font-weight: 700; }

    .remarks-sub { font-size: 12px; color: var(--text-2); margin-bottom: 14px; line-height: 1.6; }
    .remarks-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .remarks-col h4 { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-3); margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
    .remark-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; font-size: 12px; border-bottom: 1px solid var(--border); }
    .remark-row:last-child { border-bottom: none; }

    .dot { width: 7px; height: 7px; border-radius: 50%; }
    .dot-green { background: #00b894; }
    .dot-yellow { background: #fdcb6e; }
    .dot-orange { background: #e17055; }
    .dot-red { background: #d63031; }
    .dot-dkred { background: #d63031; }

    .formula-sub { font-size: 12px; color: var(--text-2); margin-bottom: 14px; }
    .formula-box { background: rgba(214,48,49,.04); border: 1px solid rgba(214,48,49,.15); border-radius: 10px; padding: 14px 16px; }
    .formula-expr { font-size: 13px; font-weight: 700; color: #d63031; }
    .formula-note { font-size: 11px; color: var(--text-2); margin-top: 6px; }

    .breakdown-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .breakdown-col h4 { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-3); margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
    .breakdown-row { display: flex; justify-content: space-between; padding: 7px 0; font-size: 12px; border-bottom: 1px solid var(--border); }
    .breakdown-row:last-child { border-bottom: none; }
    .bd-green { font-weight: 700; color: var(--green); }
    .bd-amber { font-weight: 700; color: #e17055; }
    .bd-red { font-weight: 700; color: #d63031; }

    .alert-table-wrap { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
    .alert-table { width: 100%; border-collapse: collapse; }
    .alert-table th { padding: 10px 16px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-3); text-align: left; border-bottom: 1px solid var(--border); background: rgba(0,0,0,.01); }
    .alert-table td { padding: 10px 16px; font-size: 13px; border-bottom: 1px solid var(--border); }
    .alert-table tr:last-child td { border-bottom: none; }
    .alert-table tr:hover td { background: var(--accent-light); }

    @media (max-width: 900px) {
        .card-grid { grid-template-columns: 1fr; }
        .page-head { flex-direction: column; align-items: flex-start; gap: 12px; }
    }
@endsection

@section('content')
<div class="page-head">
    <div class="page-head__left">
        <h1 class="page-head__title">Logistics</h1>
        <span class="page-head__role">/ {{ auth()->user()->isSuperAdmin() ? 'Owner' : 'Manager' }}</span>
    </div>
    <div class="subnav">
        <a href="{{ url('/logistics') }}?tab=summary" class="subnav__pill {{ $tab === 'summary' ? 'is-active' : '' }}">Summary</a>
        <a href="{{ url('/logistics') }}?tab=flags" class="subnav__pill {{ $tab === 'flags' ? 'is-active' : '' }}">Flags</a>
    </div>
</div>

@if ($tab === 'summary')
<div class="summary-table-wrap">
    <div class="summary-table-wrap__head">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        <span class="summary-table-wrap__title">Stock Reconciliation by Item</span>
    </div>
    <table class="summary-table">
        <thead><tr><th>Item</th><th>Branch</th><th>Estimated</th><th>On-site</th><th>Leakage</th><th>Inventory</th><th>Remarks</th></tr></thead>
        <tbody>
            @forelse ($stockItems as $item)
                @php
                    $onSite = (float) $item->on_site_amount;
                    $est = (float) $item->estimated_amount;
                    $pct = $est > 0 ? ($onSite / $est) * 100 : 100;
                    if ($pct >= 95) { $leakLabel = 'None'; $leakClass = 'sev-none'; }
                    elseif ($pct >= 85) { $leakLabel = 'Mild'; $leakClass = 'sev-mild'; }
                    elseif ($pct >= 70) { $leakLabel = 'Moderate'; $leakClass = 'sev-moderate'; }
                    else { $leakLabel = 'Severe'; $leakClass = 'sev-severe'; }
                    if ($item->status === 'out') { $invLabel = 'Out'; $invClass = 'badge-red'; }
                    elseif ($item->status === 'low') { $invLabel = 'Low'; $invClass = 'badge-amber'; }
                    else { $invLabel = 'Stocked'; $invClass = 'badge-green'; }
                    $remarkColor = $pct >= 95 ? 'var(--green)' : ($pct >= 85 ? '#e17055' : '#d63031');
                    $remark = $pct >= 95 ? 'Normal' : ($pct >= 85 ? 'Low' : ($pct >= 70 ? 'Moderate' : 'Critical'));
                @endphp
                <tr>
                    <td style="font-weight:600">{{ $item->item_name }} <span style="opacity:.4;font-size:11px">{{ $item->unit }}</span></td>
                    <td>{{ $item->branch_name }}</td>
                    <td class="num">{{ number_format($est, $item->unit === 'pcs' ? 0 : 1) }} {{ $item->unit }}</td>
                    <td class="num">{{ number_format($onSite, $item->unit === 'pcs' ? 0 : 1) }} {{ $item->unit }}</td>
                    <td><span class="severity-badge {{ $leakClass }}">{{ $leakLabel }}</span></td>
                    <td><span class="badge {{ $invClass }}">{{ $invLabel }}</span></td>
                    <td style="font-weight:700;color:{{ $remarkColor }}">{{ $remark }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-state">No stock items found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
    <div class="branch-card-sm">
        <div class="branch-card-sm__name">Leakage Indicator</div>
        <div style="margin-top:8px">
            <div class="branch-card-sm__stat"><span>Normal</span><span style="color:var(--green);font-weight:700">≥ 95%</span></div>
            <div class="branch-card-sm__stat"><span>Mild</span><span style="color:#a16207;font-weight:700">85% – 94%</span></div>
            <div class="branch-card-sm__stat"><span>Moderate</span><span style="color:#c2410c;font-weight:700">70% – 84%</span></div>
            <div class="branch-card-sm__stat"><span>Severe</span><span style="color:#d63031;font-weight:700">&lt; 70%</span></div>
        </div>
    </div>
    <div class="branch-card-sm">
        <div class="branch-card-sm__name">Inventory Indicator</div>
        <div style="margin-top:8px">
            <div class="branch-card-sm__stat"><span>Stocked</span><span class="badge badge-green">OK</span></div>
            <div class="branch-card-sm__stat"><span>Low</span><span class="badge badge-amber">Low</span></div>
            <div class="branch-card-sm__stat"><span>Out</span><span class="badge badge-red">Out</span></div>
        </div>
    </div>
    <div class="branch-card-sm">
        <div class="branch-card-sm__name">Summary</div>
        @php
            $totalItems = $stockItems->count();
            $flaggedItems = $stockItems->filter(fn($i) => (float)$i->on_site_amount < (float)$i->estimated_amount)->count();
            $leakItems = $stockItems->filter(fn($i) => (float)$i->on_site_amount < (float)$i->estimated_amount * 0.85)->count();
        @endphp
        <div style="margin-top:8px">
            <div class="branch-card-sm__stat"><span>Total Items</span><span style="font-weight:700">{{ $totalItems }}</span></div>
            <div class="branch-card-sm__stat"><span>With Variance</span><span style="font-weight:700;color:#e17055">{{ $flaggedItems }}</span></div>
            <div class="branch-card-sm__stat"><span>Probable Leak</span><span style="font-weight:700;color:#d63031">{{ $leakItems }}</span></div>
        </div>
    </div>
</div>
@endif

@if ($tab === 'flags')
<div class="alert-table-wrap" style="margin-bottom:24px;">
    <div class="summary-table-wrap__head">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
        <span class="summary-table-wrap__title">Active Discrepancy Flags</span>
    </div>
    <table class="alert-table">
        <thead><tr><th>Item</th><th>Branch</th><th>Expected</th><th>Actual</th><th>Leakage</th><th>Status</th><th>Remarks</th></tr></thead>
        <tbody>
            @forelse ($activeAlerts as $alert)
                @php
                    $variance = $alert->variance;
                    $absPct = $alert->expected_value > 0 ? abs($variance / $alert->expected_value) * 100 : 0;
                    $sev = $alert->severity ?? 'none';
                @endphp
                <tr>
                    <td style="font-weight:600">{{ $alert->ingredient?->name ?? 'Raw Materials' }}</td>
                    <td>{{ $alert->branch?->name ?? '—' }}</td>
                    <td>{{ number_format($alert->expected_value, 2) }} {{ $alert->ingredient?->unit ?? '' }}</td>
                    <td>{{ number_format($alert->actual_value, 2) }} {{ $alert->ingredient?->unit ?? '' }}</td>
                    <td><span class="severity-badge sev-{{ $sev }}">{{ ucfirst($sev) }}</span></td>
                    <td><span class="badge badge-red">Flagged</span></td>
                    <td style="font-weight:600;color:{{ $absPct > 15 ? '#d63031' : ($absPct > 5 ? '#e17055' : 'var(--green)') }}">
                        @if ($absPct > 15) Low @elseif ($absPct > 5) Moderate @else Normal @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-state">No active flags — all stock levels are normal.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card-grid">
    <div class="card">
        <div class="card__head"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3" cy="6" r="1"/><circle cx="3" cy="12" r="1"/><circle cx="3" cy="18" r="1"/></svg><span class="card__head-title">Variables</span></div>
        <div class="card__body">
            <ul class="var-list">
                <li><span class="var-label">Constant Float Value</span><span class="var-value">&#8369;200</span></li>
                <li><span class="var-label">Expected Total Sales</span><span class="var-value">Total EOD Transactions</span></li>
                <li><span class="var-label">Total Inventory</span><span class="var-value">{{ $totalStockItems }} items</span></li>
            </ul>
        </div>
        <div class="card__foot"><button class="btn-edit">Edit</button></div>
    </div>
    <div class="card">
        <div class="card__head"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg><span class="card__head-title">Remarks</span></div>
        <div class="card__body">
            <p class="remarks-sub">Indicator rules for Leakage &amp; Inventory status thresholds.</p>
            <div class="remarks-cols">
                <div class="remarks-col"><h4>Leakage Indicator</h4><div class="remark-row"><span>Normal</span><span class="dot dot-green"></span></div><div class="remark-row"><span>Running Low</span><span class="dot dot-yellow"></span></div><div class="remark-row"><span>Low</span><span class="dot dot-orange"></span></div><div class="remark-row"><span>Almost Out</span><span class="dot dot-red"></span></div><div class="remark-row"><span>Out</span><span class="dot dot-dkred"></span></div></div>
                <div class="remarks-col"><h4>Inventory Indicator</h4><div class="remark-row"><span>Normal</span><span class="dot dot-green"></span></div><div class="remark-row"><span>Running Low</span><span class="dot dot-yellow"></span></div><div class="remark-row"><span>Low</span><span class="dot dot-orange"></span></div><div class="remark-row"><span>Almost Out</span><span class="dot dot-red"></span></div><div class="remark-row"><span>Out</span><span class="dot dot-dkred"></span></div></div>
            </div>
        </div>
        <div class="card__foot"><button class="btn-edit">Edit</button></div>
    </div>
    <div class="card">
        <div class="card__head"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r=".5" fill="currentColor"/></svg><span class="card__head-title">Float Amount Discrepancy</span></div>
        <div class="card__body"><p class="formula-sub">Verifies that the amount left in the till is exact.</p><div class="formula-box"><div class="formula-expr">Actual Till Amount / Constant Float Value</div><div class="formula-note">Must equate to 1 to be true — a flag is sent otherwise.</div></div></div>
        <div class="card__foot"><button class="btn-edit">Edit</button></div>
    </div>
    <div class="card">
        <div class="card__head"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg><span class="card__head-title">Total Sales Discrepancy</span></div>
        <div class="card__body"><p class="formula-sub">Tracks variance between cash collected and expected revenue.</p><div class="formula-box"><div class="formula-expr">Actual Cash / Expected Total Sales</div></div></div>
        <div class="card__foot"><button class="btn-edit">Edit</button></div>
    </div>
    <div class="card">
        <div class="card__head"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg><span class="card__head-title">EOD Inventory Discrepancy</span></div>
        <div class="card__body"><p class="formula-sub">Compares end-of-day physical count against expected inventory levels.</p><div class="formula-box"><div class="formula-expr">Actual Inventory Left / Expected Inventory Left</div></div></div>
        <div class="card__foot"><button class="btn-edit">Edit</button></div>
    </div>
    <div class="card">
        <div class="card__head"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg><span class="card__head-title">Leakage &amp; Inventory Breakdown</span></div>
        <div class="card__body">
            <div class="breakdown-cols">
                <div class="breakdown-col"><h4>Leakage Indicator</h4><div class="breakdown-row"><span>Normal</span><span class="bd-green">&lt; 5%</span></div><div class="breakdown-row"><span>Running Low</span><span class="bd-amber">5% – 10%</span></div><div class="breakdown-row"><span>Low</span><span class="bd-amber">10% – 15%</span></div><div class="breakdown-row"><span>Almost Out</span><span class="bd-red">15% – 20%</span></div><div class="breakdown-row"><span>Out</span><span class="bd-red">&gt; 20%</span></div></div>
                <div class="breakdown-col"><h4>Inventory Indicator</h4><div class="breakdown-row"><span>Normal</span><span class="bd-green">&gt; 60%</span></div><div class="breakdown-row"><span>Running Low</span><span class="bd-amber">40% – 60%</span></div><div class="breakdown-row"><span>Low</span><span class="bd-amber">20% – 40%</span></div><div class="breakdown-row"><span>Almost Out</span><span class="bd-red">10% – 20%</span></div><div class="breakdown-row"><span>Out</span><span class="bd-red">&lt; 10%</span></div></div>
            </div>
        </div>
        <div class="card__foot"><button class="btn-edit">Edit</button></div>
    </div>
</div>
@endif
@endsection
