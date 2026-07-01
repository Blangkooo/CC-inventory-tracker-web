<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Inventory Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f4f6fb;
            color: #1f2433;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: #1e3a8a;
            color: #ffffff;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            padding: 24px 0;
        }

        .sidebar .brand {
            font-size: 20px;
            font-weight: 700;
            padding: 0 24px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 16px;
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 0 12px;
        }

        .sidebar nav a {
            display: block;
            color: #d6deff;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            transition: background 0.15s ease;
        }

        .sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .sidebar nav a.active {
            background: #3b5fd9;
            color: #ffffff;
        }

        /* Main content */
        .main {
            flex: 1;
            background: #ffffff;
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 32px;
            border-bottom: 1px solid #e7eaf3;
        }

        .topbar h1 {
            font-size: 24px;
            font-weight: 700;
        }

        .topbar .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .search-box {
            padding: 10px 16px;
            border: 1px solid #e2e5f0;
            border-radius: 8px;
            background: #f8f9fc;
            font-size: 14px;
            width: 220px;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f1f3fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: default;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b5fd9;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1f2433;
        }

        .btn-logout {
            padding: 8px 14px;
            background: #f1f3fa;
            color: #374151;
            border: 1px solid #e2e5f0;
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
            border: 1px solid #e7eaf3;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        }

        .stat-card .stat-label {
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 800;
            margin-top: 8px;
            color: #1e3a8a;
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
            border: 1px solid #e7eaf3;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
        }

        .widget h2 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .empty-state {
            color: #9aa1b1;
            font-size: 14px;
            text-align: center;
            padding: 32px 8px;
        }

        /* Recipe list */
        .recipe-item {
            border-bottom: 1px solid #f0f1f6;
            padding: 14px 0;
        }

        .recipe-item:last-child {
            border-bottom: none;
        }

        .recipe-item .recipe-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 6px;
        }

        .recipe-item .recipe-name {
            font-weight: 600;
            font-size: 15px;
        }

        .recipe-item .recipe-price {
            font-weight: 700;
            color: #1e3a8a;
        }

        .ingredient-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .ingredient-tag {
            background: #f1f3fa;
            color: #374151;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
        }

        /* Branch stock list */
        .stock-item {
            border-bottom: 1px solid #f0f1f6;
            padding: 14px 0;
        }

        .stock-item:last-child {
            border-bottom: none;
        }

        .stock-head {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .stock-head .stock-name {
            font-weight: 600;
        }

        .stock-meta {
            color: #6b7280;
            font-size: 12px;
            margin-top: 6px;
        }

        .progress-track {
            height: 8px;
            background: #eef0f6;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 999px;
        }

        .progress-fill.green {
            background: #22c55e;
        }

        .progress-fill.yellow {
            background: #eab308;
        }

        .progress-fill.red {
            background: #ef4444;
        }

        /* Branches table-ish list */
        .branch-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid #f0f1f6;
        }

        .branch-row:last-child {
            border-bottom: none;
        }

        .branch-info .branch-name {
            font-weight: 600;
            font-size: 15px;
        }

        .branch-info .branch-location {
            color: #6b7280;
            font-size: 13px;
            margin-top: 2px;
        }

        .badge {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
        }

        .badge.active {
            background: #dcfce7;
            color: #15803d;
        }

        .badge.inactive {
            background: #f1f2f5;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">Inventory Tracker</div>
            <nav>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="#">Branches</a>
                <a href="#">Sales Overview</a>
                <a href="#">Inventory</a>
                <a href="#">Reports</a>
            </nav>
        </aside>

        <div class="main">
            <div class="topbar">
                <div>
                    <h1>@yield('title', 'Dashboard')</h1>
                    <div class="subtitle">@yield('subtitle', 'Welcome back, Owner!')</div>
                </div>
                <div class="topbar-right">
                    <input type="text" class="search-box" placeholder="Search...">
                    <div class="icon-btn">&#128276;</div>
                    @auth
                        <div class="user-box">
                            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <span class="user-name">{{ auth()->user()->name }}</span>
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
