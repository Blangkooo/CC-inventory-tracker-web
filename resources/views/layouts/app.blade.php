<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - NITA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #fdf3d0;
            color: #111827;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background: #7b2d26;
            color: #ffffff;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
        }

        .sidebar .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .sidebar .logo-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #a03d2e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
            flex-shrink: 0;
        }

        .sidebar .app-name {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
        }

        .sidebar .app-role {
            font-size: 9px;
            color: #f3dfc0;
        }

        .sidebar .search-box {
            width: 100%;
            padding: 10px 12px;
            border: none;
            border-radius: 8px;
            background: #63241f;
            color: #ffffff;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .sidebar .search-box::placeholder {
            color: #d9b49a;
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #f3dfc0;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.15s ease;
        }

        .sidebar nav a:hover {
            background: #63241f;
        }

        .sidebar nav a.active {
            background: #a03d2e;
            color: #ffffff;
            font-weight: 600;
        }

        .sidebar .nav-icon {
            font-size: 15px;
            width: 18px;
            text-align: center;
        }

        .sidebar .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 16px;
            margin-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar .sidebar-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #63241f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
            flex-shrink: 0;
        }

        .sidebar .sidebar-user-name {
            font-size: 12px;
            font-weight: 600;
            color: #ffffff;
        }

        .sidebar .sidebar-user-email {
            font-size: 11px;
            color: #f3dfc0;
        }

        /* Main content */
        .main {
            flex: 1;
            background: #fdf3d0;
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 32px;
            border-bottom: 1px solid #e9d9b6;
        }

        .topbar h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .topbar .subtitle {
            color: #6b7380;
            font-size: 13px;
            margin-top: 4px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #f5ead0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            cursor: default;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #a03d2e;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
        }

        .dropdown-arrow {
            font-size: 10px;
            color: #9ca3b0;
        }

        .btn-logout {
            padding: 8px 14px;
            background: #f5ead0;
            color: #374151;
            border: 1px solid #e9d9b6;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .btn-logout:hover {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .content {
            padding: 32px;
        }

        /* Stat cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e9d9b6;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        }

        .stat-card .stat-label {
            color: #6b7380;
            font-size: 13px;
            font-weight: 600;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 800;
            margin-top: 8px;
            color: #111827;
        }

        .stat-badge {
            display: inline-block;
            margin-top: 12px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .stat-badge.green {
            background: #dcfce7;
            color: #10b981;
        }

        .stat-badge.blue {
            background: #f3dfc0;
            color: #a03d2e;
        }

        .stat-badge.red {
            background: #fee2e2;
            color: #ef4444;
        }

        .stat-badge.amber {
            background: #fef3c7;
            color: #f59e0b;
        }

        /* Widget grid */
        .widget-grid {
            display: grid;
            grid-template-columns: 60% 40%;
            gap: 20px;
            margin-bottom: 28px;
        }

        .widget {
            background: #ffffff;
            border: 1px solid #e9d9b6;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        }

        .widget-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .widget h2 {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .widget-link {
            font-size: 13px;
            font-weight: 600;
            color: #a03d2e;
            text-decoration: none;
        }

        .empty-state {
            color: #9ca3b0;
            font-size: 13px;
            text-align: center;
            padding: 32px 8px;
        }

        /* Bar chart */
        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            height: 180px;
            padding-top: 8px;
        }

        .bar-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
        }

        .bar {
            width: 100%;
            max-width: 32px;
            background: #a03d2e;
            border-radius: 4px 4px 0 0;
            min-height: 2px;
        }

        .bar-day-label {
            margin-top: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #6b7380;
        }

        /* Branches live list */
        .branch-live-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f2e4c8;
        }

        .branch-live-row:last-child {
            border-bottom: none;
        }

        .branch-live-left {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot.green {
            background: #10b981;
        }

        .dot.red {
            background: #ef4444;
        }

        .branch-live-amount {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        /* Alerts table */
        .alerts-table {
            width: 100%;
            border-collapse: collapse;
        }

        .alerts-table th {
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #6b7380;
            padding: 10px 8px;
            border-bottom: 1px solid #e9d9b6;
        }

        .alerts-table td {
            padding: 14px 8px;
            font-size: 14px;
            border-bottom: 1px solid #f2e4c8;
            color: #111827;
        }

        .alerts-table tr:last-child td {
            border-bottom: none;
        }

        .variance-cell {
            color: #ef4444;
            font-weight: 700;
        }

        .btn-review {
            padding: 6px 14px;
            background: #f3dfc0;
            color: #a03d2e;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .all-clear {
            color: #10b981;
            font-size: 14px;
            text-align: center;
            padding: 32px 8px;
            font-weight: 600;
        }

        /* Shared page utilities (recipes, inventory, branches, alerts) */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .search-input {
            width: 400px;
            max-width: 100%;
            padding: 10px 14px;
            border: 1px solid #e9d9b6;
            border-radius: 8px;
            font-size: 13px;
            background: #fdf8ec;
        }

        .search-input:focus {
            outline: none;
            border-color: #a03d2e;
            background: #ffffff;
        }

        .select-filter {
            padding: 10px 14px;
            border: 1px solid #e9d9b6;
            border-radius: 8px;
            font-size: 13px;
            background: #ffffff;
            color: #111827;
        }

        .btn-primary {
            padding: 10px 20px;
            background: #a03d2e;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #7b2d26;
        }

        .btn-pill {
            padding: 6px 14px;
            background: #f3dfc0;
            color: #a03d2e;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-pill-sm {
            font-size: 10px;
            padding: 5px 12px;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .tab {
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            color: #6b7380;
            cursor: pointer;
            border: none;
            background: transparent;
            text-decoration: none;
        }

        .tab.active {
            background: #f3dfc0;
            color: #a03d2e;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        .data-table thead th {
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            color: #6b7380;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 12px;
            background: #faf3e0;
            border-bottom: 1px solid #e9d9b6;
        }

        .data-table tbody td {
            padding: 14px 12px;
            font-size: 11px;
            color: #6b7380;
            border-bottom: 1px solid #f2e4c8;
        }

        .data-table tbody tr:nth-child(even) {
            background: #faf3e0;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table .cell-primary {
            color: #111827;
            font-weight: 500;
        }

        .badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
        }

        .badge.green {
            background: #dcfce7;
            color: #166534;
        }

        .badge.gray {
            background: #f0e6cc;
            color: #6b7380;
        }

        .badge.amber {
            background: #fef3c7;
            color: #92400e;
        }

        .badge.red {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge.blue {
            background: #f3dfc0;
            color: #a03d2e;
        }

        .summary-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
        }

        .summary-card {
            flex: 1;
            border: 1px solid #e9d9b6;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #ffffff;
        }

        .summary-card .summary-count {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
        }

        .summary-card .summary-label {
            font-size: 12px;
            color: #6b7380;
            font-weight: 600;
            margin-top: 4px;
        }

        .summary-card.green .summary-count {
            color: #10b981;
        }

        .summary-card.amber .summary-count {
            color: #f59e0b;
        }

        .summary-card.red .summary-count {
            color: #ef4444;
        }

        .card-panel {
            background: #ffffff;
            border: 1px solid #e9d9b6;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        }

        /* Dashboard two-column shell */
        .dash-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            align-items: start;
        }

        .dash-main,
        .dash-aside {
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .two-up {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .stat-note {
            display: block;
            margin-top: 8px;
            font-size: 11px;
            color: #9ca3b0;
        }

        .rank-num {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #f3dfc0;
            color: #a03d2e;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .flag-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Calendar */
        .calendar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .calendar-head .cal-month {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            text-align: center;
        }

        .cal-dow {
            font-size: 11px;
            color: #6b7380;
            font-weight: 600;
            padding: 4px 0;
        }

        .cal-day {
            font-size: 12px;
            padding: 6px 0;
            border-radius: 8px;
            color: #111827;
        }

        .cal-day.muted {
            color: #d8c9a8;
        }

        .cal-day.today {
            background: #a03d2e;
            color: #ffffff;
            font-weight: 700;
        }

        /* Businesses master-detail */
        .master-detail {
            display: grid;
            grid-template-columns: 230px 1fr;
            gap: 20px;
            align-items: start;
        }

        .biz-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .biz-list a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #111827;
            text-decoration: none;
        }

        .biz-list a:hover {
            background: #f5ead0;
        }

        .biz-list a.active {
            background: #f3dfc0;
            color: #a03d2e;
            font-weight: 600;
        }

        .detail-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .detail-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .detail-header .detail-sub {
            font-size: 13px;
            color: #6b7380;
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <div class="logo-circle">&#127978;</div>
                <div>
                    <div class="app-name">NITA</div>
                    <div class="app-role">Inventory Tracker</div>
                </div>
            </div>

            <input type="text" class="search-box" placeholder="Search&hellip;">

            <nav>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">&#127968;</span> Dashboard
                </a>
                <a href="{{ route('recipes') }}" class="{{ request()->routeIs('recipes') ? 'active' : '' }}">
                    <span class="nav-icon">&#127861;</span> Recipes
                </a>
                <a href="{{ route('inventory') }}" class="{{ request()->routeIs('inventory') ? 'active' : '' }}">
                    <span class="nav-icon">&#128230;</span> Inventory
                </a>
                <a href="{{ route('branches') }}" class="{{ request()->routeIs('branches') || request()->routeIs('branches.show') ? 'active' : '' }}">
                    <span class="nav-icon">&#127978;</span> Businesses
                </a>
                <a href="{{ route('alerts') }}" class="{{ request()->routeIs('alerts') ? 'active' : '' }}">
                    <span class="nav-icon">&#128276;</span> Alerts
                </a>
            </nav>

            @auth
                <div class="sidebar-user">
                    <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div>
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-email">{{ auth()->user()->email }}</div>
                    </div>
                </div>
            @endauth
        </aside>

        <div class="main">
            <div class="topbar">
                <div>
                    <h1>@yield('title', 'Dashboard')</h1>
                    <div class="subtitle">@yield('subtitle', 'Overview across all branches')</div>
                </div>
                <div class="topbar-right">
                    <div class="icon-btn">&#128276;</div>
                    @auth
                        <div class="user-box">
                            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <span class="dropdown-arrow">&#9660;</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-logout">Logout</button>
                        </form>
                    @endauth
                </div>
            </div>

            <div class="content">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
