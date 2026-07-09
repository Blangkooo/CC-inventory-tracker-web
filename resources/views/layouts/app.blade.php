<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — NITA</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --cream:  #FDF5D6;
            --brown:  #5C2D1B;
            --terra:  #BC614B;
            --border: rgba(92,45,27,.16);
            --shadow: 0 1px 3px rgba(92,45,27,.08), 0 4px 12px rgba(92,45,27,.06);
            --radius: 12px;
            --font:   -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body { font-family: var(--font); background: var(--cream); color: var(--brown); min-height: 100vh; }

        /* ══ NAV ══════════════════════════════════════════════════════════ */
        .nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(253,245,214,.92); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .nav__inner {
            max-width: 1400px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; height: 60px;
        }

        .nav__left { display: flex; align-items: center; gap: 36px; }
        .nav__logo img { height: 30px; display: block; }
        .nav__pills { display: flex; gap: 4px; }

        .nav__pill {
            padding: 7px 18px; border-radius: 999px; font-size: 13px; font-weight: 600;
            color: var(--brown); text-decoration: none; transition: all .15s ease;
            border: 1.5px solid transparent;
        }

        .nav__pill:hover { background: rgba(92,45,27,.06); }
        .nav__pill.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }

        .nav__right { display: flex; align-items: center; gap: 8px; }

        .nav__icon {
            width: 36px; height: 36px; border-radius: 8px; display: flex;
            align-items: center; justify-content: center;
            background: transparent; border: none; color: var(--brown);
            cursor: pointer; transition: background .15s ease; text-decoration: none;
        }

        .nav__icon:hover { background: rgba(92,45,27,.07); }
        .nav__icon--box { background: #fff; border: 1.5px solid var(--border); }
        .nav__sep { width: 1px; height: 20px; background: var(--border); margin: 0 4px; }

        .nav__logout {
            padding: 7px 16px; background: transparent; color: var(--brown);
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
            transition: all .15s ease;
        }

        .nav__logout:hover { background: var(--brown); color: var(--cream); border-color: var(--brown); }

        /* ══ PAGE SHELL ══════════════════════════════════════════════════ */
        .main-content { max-width: 1400px; margin: 0 auto; padding: 28px 32px; }

        .page-header {
            display: flex; align-items: baseline; gap: 10px; margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 22px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em;
        }

        .page-header h1 .underline { border-bottom: 3px solid var(--brown); padding-bottom: 2px; }
        .page-subtitle { font-size: 13px; opacity: .5; font-weight: 400; }

        /* ══ TOOLBAR / FILTER ROW ════════════════════════════════════════ */
        .toolbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; flex-wrap: wrap; margin-bottom: 16px;
        }

        .search-input {
            height: 38px; padding: 0 14px; background: #fff;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; color: var(--brown); font-family: var(--font);
            min-width: 220px; transition: border-color .15s;
        }

        .search-input::placeholder { color: rgba(92,45,27,.4); }
        .search-input:focus { outline: none; border-color: var(--terra); }

        .select-filter {
            height: 38px; padding: 0 14px; background: #fff;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; color: var(--brown); font-family: var(--font); cursor: pointer;
        }

        .btn-primary {
            padding: 8px 20px; background: var(--terra); color: #fff;
            border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
            cursor: pointer; font-family: var(--font); transition: background .15s;
        }

        .btn-primary:hover { background: #a8523e; }

        .btn-pill {
            padding: 5px 14px; background: rgba(188,97,75,.1); color: var(--terra);
            border: 1px solid rgba(188,97,75,.3); border-radius: 6px;
            font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
            transition: all .15s; text-decoration: none;
        }

        .btn-pill:hover { background: var(--terra); color: #fff; }

        /* ══ FILTER TABS ═════════════════════════════════════════════════ */
        .filter-tabs { display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap; }

        .tab {
            padding: 7px 16px; border-radius: 999px; font-size: 13px; font-weight: 600;
            color: var(--brown); opacity: .6; cursor: pointer;
            border: 1.5px solid transparent; background: transparent;
            transition: all .15s; text-decoration: none; font-family: var(--font);
        }

        .tab:hover { opacity: 1; background: rgba(92,45,27,.05); }
        .tab.active { background: rgba(188,97,75,.1); color: var(--terra); border-color: var(--terra); opacity: 1; }

        /* ══ SUMMARY CARDS ═══════════════════════════════════════════════ */
        .summary-cards { display: flex; gap: 16px; margin-bottom: 20px; }

        .summary-card {
            flex: 1; background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            padding: 20px; text-align: center;
        }

        .summary-count { font-size: 32px; font-weight: 800; }
        .summary-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: .5; margin-top: 4px; }
        .summary-card.green .summary-count { color: #16a34a; }
        .summary-card.amber .summary-count { color: #d97706; }
        .summary-card.red   .summary-count { color: #dc2626; }

        /* ══ CARD PANEL / TABLE ══════════════════════════════════════════ */
        .card-panel {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
        }

        .data-table, .alerts-table { width: 100%; border-collapse: collapse; }

        .data-table thead th, .alerts-table thead th {
            text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .45; padding: 12px 16px;
            background: rgba(92,45,27,.03); border-bottom: 1px solid var(--border);
        }

        .data-table tbody td, .alerts-table tbody td {
            padding: 13px 16px; font-size: 13px; border-bottom: 1px solid rgba(92,45,27,.06);
        }

        .data-table tbody tr:last-child td, .alerts-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover, .alerts-table tbody tr:hover { background: rgba(92,45,27,.02); }
        .cell-primary { font-weight: 600; }

        /* ══ BADGES ═══════════════════════════════════════════════════════ */
        .badge {
            display: inline-block; font-size: 11px; font-weight: 600;
            padding: 3px 10px; border-radius: 999px;
        }

        .badge.green  { background: #dcfce7; color: #166534; }
        .badge.amber  { background: #fef3c7; color: #92400e; }
        .badge.red    { background: #fee2e2; color: #991b1b; }
        .badge.blue   { background: #dbeafe; color: #1e40af; }
        .badge.gray   { background: rgba(92,45,27,.08); color: var(--brown); opacity: .7; }

        .variance-cell { color: #dc2626; font-weight: 700; }

        /* ══ EMPTY / ALL CLEAR ═══════════════════════════════════════════ */
        .empty-state { color: var(--brown); opacity: .4; font-size: 13px; text-align: center; padding: 36px 8px; }
        .all-clear   { color: #16a34a; font-size: 13px; font-weight: 600; text-align: center; padding: 36px 8px; }

        /* ══ MASTER-DETAIL (branches/show) ═══════════════════════════════ */
        .master-detail { display: grid; grid-template-columns: 200px 1fr; gap: 20px; }

        .widget {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
            margin-bottom: 16px;
        }

        .widget-head {
            padding: 14px 20px; border-bottom: 1px solid var(--border);
        }

        .widget-head h2 { font-size: 13px; font-weight: 700; }

        .biz-list { display: flex; flex-direction: column; padding: 6px; }

        .biz-list a {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px;
            border-radius: 8px; font-size: 13px; font-weight: 500; text-decoration: none;
            color: var(--brown); transition: background .12s;
        }

        .biz-list a:hover { background: rgba(92,45,27,.05); }
        .biz-list a.active { background: rgba(188,97,75,.1); color: var(--terra); font-weight: 700; }

        .biz-list .dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        .biz-list .dot.green { background: #16a34a; }
        .biz-list .dot.red   { background: #dc2626; }

        .detail-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .detail-header h2 { font-size: 18px; font-weight: 800; }
        .detail-sub { font-size: 12px; opacity: .5; margin-left: auto; }

        /* ══ BRANCH ANALYTICS TAB ════════════════════════════════════════ */
        .dash-main { display: flex; flex-direction: column; gap: 16px; }

        .two-up { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .stat-card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow); padding: 20px;
        }

        .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; opacity: .5; }
        .stat-value { font-size: 30px; font-weight: 800; margin: 6px 0 4px; }
        .stat-note  { font-size: 11px; opacity: .45; }

        .bar-chart {
            display: flex; align-items: flex-end; gap: 6px;
            height: 100px; padding: 12px 20px 0;
        }

        .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; height: 100%; }
        .bar { width: 100%; background: var(--terra); border-radius: 4px 4px 0 0; min-height: 2px; transition: height .3s ease; }
        .bar-day-label { font-size: 10px; font-weight: 600; opacity: .5; flex-shrink: 0; }

        /* ══ RESPONSIVE ══════════════════════════════════════════════════ */
        @media (max-width: 900px) {
            .master-detail { grid-template-columns: 1fr; }
            .two-up { grid-template-columns: 1fr; }
            .summary-cards { flex-direction: column; }
            .main-content { padding: 16px; }
            .nav__inner { padding: 0 16px; }
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
                <a href="{{ url('/dashboard') }}"        class="nav__pill {{ request()->is('dashboard') || request()->is('branches*') || request()->is('inventory*') || request()->is('recipes*') || request()->is('alerts*') ? 'is-active' : '' }}">Dashboard</a>
                <a href="{{ url('/business/recipes') }}"  class="nav__pill {{ request()->is('business*') ? 'is-active' : '' }}">Businesses</a>
                <a href="{{ url('/logistics') }}"         class="nav__pill {{ request()->is('logistics*') ? 'is-active' : '' }}">Logistics</a>
            </div>
        </div>
        <div class="nav__right">
            <a href="{{ url('/alerts') }}" class="nav__icon" title="Alerts">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </a>
            <a href="{{ url('/alerts') }}" class="nav__icon nav__icon--box" title="Messages">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
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

<div class="main-content">
    <div class="page-header">
        <h1><span class="underline">@yield('title', 'Dashboard')</span></h1>
        @hasSection('subtitle')
            <span class="page-subtitle">/ @yield('subtitle')</span>
        @endif
    </div>

    @yield('content')
</div>

</body>
</html>
