<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - NITA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #FDF5D6;
            color: #5C2D1B;
            min-height: 100vh;
        }

        /* ── Top Navigation Bar (Unified) ── */
        .navbar {
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            margin-top: 8px;
            background: #FDF5D6;
            border: 1px solid #5C2D1B;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 24px;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .logo-area {
            display: flex;
            align-items: center;
        }

        .logo-area .logo-svg {
            width: 120px;
            height: 36px;
            flex-shrink: 0;
        }

        .nav-pills {
            display: flex;
            gap: 8px;
        }

        .nav-pill {
            padding: 8px 20px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: 1.5px solid #5C2D1B;
            background: #ffffff;
            color: #5C2D1B;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .nav-pill.active {
            background: #BC614B;
            color: #ffffff;
            border-color: #BC614B;
        }

        .nav-pill:hover { opacity: 0.85; }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s ease;
            background: transparent;
            border: none;
        }

        .icon-btn:hover { background: rgba(92, 45, 27, 0.08); }

        .icon-btn.mail { background: #ffffff; border: 1.5px solid #5C2D1B; }

        .logout-form { margin-left: auto; }

        .logout-btn {
            padding: 6px 14px;
            background: transparent;
            color: #5C2D1B;
            border: 1.5px solid #5C2D1B;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.15s ease;
        }

        .logout-btn:hover {
            background: #5C2D1B;
            color: #FDF5D6;
        }

        /* ── Main Content ── */
        .main-content {
            padding: 20px 24px;
            max-width: 1440px;
            margin: 0 auto;
        }

        /* ── Page Header ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: #5C2D1B;
            letter-spacing: 0.02em;
        }

        .page-header h1 .pipe {
            font-weight: 400;
            opacity: 0.6;
        }

        .page-subtitle {
            color: #5C2D1B;
            opacity: 0.7;
            font-size: 13px;
            margin-bottom: 20px;
        }

        /* ── Shared page utilities (from old app layout) ── */
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
            border: 1.5px solid #5C2D1B;
            border-radius: 8px;
            font-size: 13px;
            background: #ffffff;
            color: #5C2D1B;
        }

        .search-input:focus {
            outline: none;
            border-color: #BC614B;
            box-shadow: 0 0 0 3px rgba(188, 97, 75, 0.12);
        }

        .search-input::placeholder { color: #a0897a; }

        .select-filter {
            padding: 10px 14px;
            border: 1.5px solid #5C2D1B;
            border-radius: 8px;
            font-size: 13px;
            background: #ffffff;
            color: #5C2D1B;
        }

        .btn-primary {
            padding: 10px 20px;
            background: #BC614B;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary:hover { background: #a8523e; }

        .btn-pill {
            padding: 6px 14px;
            background: rgba(188, 97, 75, 0.12);
            color: #BC614B;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-pill-sm { font-size: 10px; padding: 5px 12px; }

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
            color: #5C2D1B;
            opacity: 0.6;
            cursor: pointer;
            border: 1.5px solid transparent;
            background: transparent;
            text-decoration: none;
        }

        .tab.active {
            background: rgba(188, 97, 75, 0.12);
            color: #BC614B;
            border-color: #BC614B;
        }

        .card-panel {
            background: #FDF5D6;
            border: 1.5px solid #5C2D1B;
            border-radius: 12px;
            overflow: hidden;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: #FDF5D6;
        }

        .data-table thead th {
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            color: #5C2D1B;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 12px;
            background: rgba(92, 45, 27, 0.04);
            border-bottom: 1px solid rgba(92, 45, 27, 0.15);
        }

        .data-table tbody td {
            padding: 14px 12px;
            font-size: 11px;
            color: #5C2D1B;
            border-bottom: 1px solid rgba(92, 45, 27, 0.1);
        }

        .data-table tbody tr:nth-child(even) { background: rgba(92, 45, 27, 0.03); }
        .data-table tbody tr:last-child td { border-bottom: none; }

        .data-table .cell-primary {
            color: #5C2D1B;
            font-weight: 500;
        }

        .badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
        }

        .badge.green { background: #dcfce7; color: #166534; }
        .badge.gray { background: rgba(92, 45, 27, 0.08); color: #5C2D1B; opacity: 0.7; }
        .badge.amber { background: #fef3c7; color: #92400e; }
        .badge.red { background: #fee2e2; color: #991b1b; }
        .badge.blue { background: #d9e5ff; color: #2563eb; }

        .summary-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
        }

        .summary-card {
            flex: 1;
            border: 1px solid #5C2D1B;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #FDF5D6;
        }

        .summary-card .summary-count {
            font-size: 28px;
            font-weight: 800;
            color: #5C2D1B;
        }

        .summary-card .summary-label {
            font-size: 12px;
            color: #5C2D1B;
            opacity: 0.7;
            font-weight: 600;
            margin-top: 4px;
        }

        .summary-card.green .summary-count { color: #16a34a; }
        .summary-card.amber .summary-count { color: #f59e0b; }
        .summary-card.red .summary-count { color: #ef4444; }

        .empty-state {
            color: #5C2D1B;
            opacity: 0.5;
            font-size: 13px;
            text-align: center;
            padding: 32px 8px;
        }

        .variance-cell { color: #ef4444; font-weight: 700; }

        .all-clear {
            color: #16a34a;
            font-size: 14px;
            text-align: center;
            padding: 32px 8px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .navbar { flex-wrap: wrap; gap: 10px; }
            .nav-pills { order: 3; width: 100%; justify-content: center; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .summary-cards { flex-direction: column; }
            .toolbar { flex-direction: column; align-items: stretch; }
            .search-input { width: 100%; }
        }
    </style>
</head>
<body>

    {{-- ── UNIFIED MASTER TOP NAVBAR ── --}}
    <nav class="navbar">
        <div class="navbar-left">
            <div class="logo-area">
                <img class="logo-svg" src="{{ asset('images/logo.svg') }}" alt="NITA Logo">
            </div>

            <div class="nav-pills">
                <a href="{{ url('/dashboard') }}" class="nav-pill {{ request()->is('dashboard') || request()->is('branches*') || request()->is('inventory*') || request()->is('recipes*') || request()->is('alerts*') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ url('/business/recipes') }}" class="nav-pill {{ request()->is('business*') ? 'active' : '' }}">Businesses</a>
                <a href="{{ url('/logistics') }}" class="nav-pill {{ request()->is('logistics*') ? 'active' : '' }}">Logistics</a>
            </div>
        </div>

        <div class="navbar-right">
            <div class="icon-btn bell">
                <svg width="22" height="22" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 32,10 C 23.5,10 21,22 17,40 C 13,44 14,48 19,48 L 45,48 C 50,48 51,44 47,40 C 43,22 40.5,10 32,10 Z" fill="#FFAA2C"/>
                    <path d="M 27,48 A 5,5 0 0 0 37,48" fill="#5C2D1B"/>
                </svg>
            </div>
            <div class="icon-btn mail">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5C2D1B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </div>
            <div class="icon-btn gear">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4b5563" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </nav>

    {{-- ── MAIN CONTENT ── --}}
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>@yield('title', 'Dashboard')</h1>
                <div class="page-subtitle">@yield('subtitle', 'Overview across all branches')</div>
            </div>
        </div>

        @yield('content')
    </div>

</body>
</html>
