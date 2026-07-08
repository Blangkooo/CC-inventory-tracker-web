<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NITA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #FDF5D6;
            color: #5C2D1B;
            min-height: 100vh;
        }

        /* ── Top Navigation Bar ── */
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
            gap: 10px;
        }

        .logo-area svg.logo-svg {
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

        /* ── Flat Inline SVGs ── */
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
        .icon-btn.gear svg { color: #4b5563; }

        /* ── Main Layout (72/28 two-column) ── */
        .main-layout {
            display: flex;
            gap: 20px;
            padding: 20px 24px;
            max-width: 1440px;
            margin: 0 auto;
        }

        .left-column {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
        }

        .right-column {
            width: 320px;
            flex-shrink: 0;
        }

        /* ── Title Banner ── */
        .title-banner {
            background: #FDF5D6;
            border: 1px solid #5C2D1B;
            border-radius: 10px;
            padding: 14px 20px;
        }

        .title-banner h1 {
            font-size: 22px;
            font-weight: 800;
            color: #5C2D1B;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .title-banner h1 span.role {
            font-weight: 400;
            font-size: 18px;
        }

        .title-banner h1 span.underline {
            display: inline-block;
            border-bottom: 3px solid #5C2D1B;
            padding-bottom: 2px;
        }

        /* ── Card ── */
        .card {
            background: #FDF5D6;
            border: 1px solid #5C2D1B;
            border-radius: 10px;
        }

        .card-pad { padding: 16px 20px; }

        /* ── Flag Summary ── */
        .flag-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .flag-header h2 {
            font-size: 16px;
            font-weight: 700;
            color: #5C2D1B;
        }

        .legend {
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #5C2D1B;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        .legend-dot.yellow { background: #eab308; }
        .legend-dot.orange { background: #f97316; }
        .legend-dot.red { background: #ef4444; }

        .flag-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .flag-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            font-size: 13px;
            font-weight: 500;
            color: #5C2D1B;
        }

        .flag-badge {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #5C2D1B;
            flex-shrink: 0;
        }

        /* ── Key Metrics ── */
        .metrics-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .metric-card {
            background: #FDF5D6;
            border: 1px solid #5C2D1B;
            border-radius: 10px;
            padding: 20px 16px;
            text-align: center;
        }

        .metric-label {
            font-size: 11px;
            font-weight: 700;
            color: #5C2D1B;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        .metric-value {
            font-size: 30px;
            font-weight: 800;
            color: #5C2D1B;
        }

        .metric-sub {
            font-size: 10px;
            color: #5C2D1B;
            opacity: 0.7;
            margin-top: 4px;
        }

        .metric-value .down-arrow { color: #ef4444; }

        /* ── Rankings Row ── */
        .rankings-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .rank-card {
            background: #FDF5D6;
            border: 1px solid #5C2D1B;
            border-radius: 10px;
            padding: 16px 20px;
        }

        .rank-card h3 {
            font-size: 14px;
            font-weight: 700;
            color: #5C2D1B;
            text-align: center;
            margin-bottom: 12px;
        }

        .rank-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid rgba(92, 45, 27, 0.1);
            font-size: 13px;
            font-weight: 500;
        }

        .rank-row:last-child { border-bottom: none; }

        .rank-row .green { color: #16a34a; font-weight: 700; }
        .rank-row .red { color: #dc2626; font-weight: 700; }

        /* ── Right Sidebar (Terracotta) ── */
        .sidebar-card {
            background: #BC614B;
            border: 1px solid #5C2D1B;
            border-radius: 10px;
            padding: 20px 18px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        /* Topographic wave pattern overlay */
        .sidebar-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 30%, rgba(255,255,255,0.05) 0%, transparent 60%),
                radial-gradient(circle at 70% 60%, rgba(255,255,255,0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 85%, rgba(255,255,255,0.04) 0%, transparent 40%),
                radial-gradient(circle at 85% 15%, rgba(255,255,255,0.04) 0%, transparent 40%);
            border-radius: 6px;
            pointer-events: none;
        }

        .sidebar-card > * { position: relative; z-index: 1; }

        .sidebar-card h3 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: 0.02em;
        }

        .sidebar-card h4 {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 10px;
            margin-top: 20px;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-size: 10px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }

        .cal-cell {
            text-align: center;
            padding: 5px 0;
            font-size: 12px;
            font-weight: 500;
            color: #ffffff;
            border-radius: 4px;
        }

        .cal-cell.faded { opacity: 0.4; }
        .cal-cell.highlight-ring { border: 1.5px solid #ffffff; }
        .cal-cell.highlight-solid { background: #5C2D1B; color: #ffffff; font-weight: 700; }

        .schedule-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .schedule-item {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .schedule-bullet {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ffffff;
            flex-shrink: 0;
            margin-top: 5px;
        }

        .schedule-content { flex: 1; }

        .schedule-content .title {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
        }

        .schedule-content .meta {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 2px;
            line-height: 1.5;
        }

        .schedule-content .meta-line {
            display: flex;
            justify-content: space-between;
        }

        /* ── Logout ── */
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

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .right-column { width: 260px; }
        }

        @media (max-width: 768px) {
            .main-layout { flex-direction: column; padding: 12px; }
            .right-column { width: 100%; }
            .metrics-row, .rankings-row { grid-template-columns: 1fr; }
            .flag-grid { grid-template-columns: repeat(2, 1fr); }
            .navbar { flex-wrap: wrap; gap: 10px; }
            .nav-pills { order: 3; width: 100%; justify-content: center; }
            .navbar-right { gap: 8px; }
        }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{--  VIEW 1: MAIN OWNER DASHBOARD                                --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}

    {{-- ── TOP NAVIGATION BAR ── --}}
    <nav class="navbar">
        <div class="navbar-left">
            <div class="logo-area">
                <svg class="logo-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 180" width="120" height="36">
                    <g id="store-icon">
                        <path d="M 50,110 L 160,110 A 15,15 0 0 1 175,125 L 175,145 A 25,25 0 0 1 150,170 L 75,170 A 25,25 0 0 1 50,145 Z" fill="#FDF5D6" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                        <path d="M 50,135 Q 110,140 175,115 L 175,145 A 25,25 0 0 1 150,170 L 75,170 A 25,25 0 0 1 50,145 Z" fill="#E67A15" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                        <rect x="62" y="90" width="10" height="20" fill="#FDF5D6" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                        <rect x="153" y="90" width="10" height="20" fill="#FDF5D6" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                        <path d="M 45,90 L 60,50 L 165,50 L 180,90 Z" fill="#E12D2D" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                        <path d="M 45,90 Q 53,102 61,90 Q 69,102 78,90 Q 87,102 96,90 Q 105,102 114,90 Q 123,102 132,90 Q 141,102 150,90 Q 159,102 168,90 Q 174,102 180,90" fill="none" stroke="#5C2D1B" stroke-width="5" stroke-linecap="round"/>
                        <line x1="78" y1="50" x2="78" y2="92" stroke="#5C2D1B" stroke-width="4"/>
                        <line x1="112" y1="50" x2="112" y2="92" stroke="#5C2D1B" stroke-width="4"/>
                        <line x1="146" y1="50" x2="146" y2="92" stroke="#5C2D1B" stroke-width="4"/>
                    </g>
                    <g stroke="#5C2D1B" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M 210,115 L 210,50 M 216,115 L 216,50 M 222,115 L 222,50"/>
                        <path d="M 210,50 L 250,115 M 216,50 L 256,115 M 222,50 L 262,115"/>
                        <path d="M 250,115 L 250,50 M 256,115 L 256,50 M 262,115 L 262,50"/>
                        <path d="M 285,115 L 285,50 M 292,115 L 292,50 M 299,115 L 299,50"/>
                        <path d="M 320,50 L 375,50 M 320,56 L 375,56 M 320,62 L 375,62"/>
                        <path d="M 341,62 L 341,115 M 347,62 L 347,115 M 353,62 L 353,115"/>
                        <path d="M 410,50 L 385,115 M 415,50 L 391,115 M 420,50 L 397,115"/>
                        <path d="M 410,50 L 435,115 M 415,50 L 441,115 M 420,50 L 447,115"/>
                        <path d="M 396,95 L 430,95 M 394,101 L 433,101"/>
                    </g>
                    <text x="207" y="148" font-family="sans-serif" font-weight="900" font-size="25" fill="#5C2D1B" letter-spacing="3">INVENTORY TRACKER</text>
                </svg>
            </div>

            <div class="nav-pills">
                <a href="{{ url('/dashboard') }}" class="nav-pill active">Dashboard</a>
                <a href="{{ url('/business/recipes') }}" class="nav-pill">Businesses</a>
                <a href="{{ url('/logistics') }}" class="nav-pill">Logistics</a>
            </div>
        </div>

        <div class="navbar-right">
            {{-- New Bell Icon --}}
            <div class="icon-btn bell">
                <svg width="22" height="22" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 32,10 C 23.5,10 21,22 17,40 C 13,44 14,48 19,48 L 45,48 C 50,48 51,44 47,40 C 43,22 40.5,10 32,10 Z" fill="#FFAA2C"/>
                    <path d="M 27,48 A 5,5 0 0 0 37,48" fill="#5C2D1B"/>
                </svg>
            </div>
            {{-- White Rounded Mail Pill --}}
            <div class="icon-btn mail">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5C2D1B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </div>
            {{-- Gray Hollowed Gear (mask) --}}
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

    {{-- ── MAIN LAYOUT (TWO-COLUMN: 72/28) ── --}}
    <div class="main-layout">

        {{-- ── LEFT COLUMN (72%) ── --}}
        <div class="left-column">

            {{-- Header Text Banner --}}
            <div class="title-banner">
                <h1>
                    <span class="underline">DASHBOARD</span>&nbsp;
                    <span class="role">| {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
                </h1>
            </div>

            {{-- Row 1: Flag Summary Card --}}
            <div class="card card-pad">
                <div class="flag-header">
                    <h2>Flag Summary</h2>
                    <div class="legend">
                        <span class="legend-item">
                            <span class="legend-dot yellow"></span> Low Importance
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot orange"></span> Moderate Importance
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot red"></span> High Importance
                        </span>
                    </div>
                </div>
                <div class="flag-grid">
                    <div class="flag-item">
                        <span class="flag-badge" style="border-color: #eab308;"></span>
                        <span>Main Branch</span>
                    </div>
                    <div class="flag-item">
                        <span class="flag-badge" style="border-color: #eab308;"></span>
                        <span>Downtown Outlet</span>
                    </div>
                    <div class="flag-item">
                        <span class="flag-badge" style="border-color: #f97316;"></span>
                        <span>Uptown Mall</span>
                    </div>
                    <div class="flag-item">
                        <span class="flag-badge" style="border-color: #ef4444;"></span>
                        <span>Airport Branch</span>
                    </div>
                    <div class="flag-item">
                        <span class="flag-badge" style="border-color: #eab308;"></span>
                        <span>Harbor Side</span>
                    </div>
                    <div class="flag-item">
                        <span class="flag-badge" style="border-color: #f97316;"></span>
                        <span>University Branch</span>
                    </div>
                    <div class="flag-item">
                        <span class="flag-badge" style="border-color: #eab308;"></span>
                        <span>Business Park</span>
                    </div>
                    <div class="flag-item">
                        <span class="flag-badge" style="border-color: #ef4444;"></span>
                        <span>Residential Hub</span>
                    </div>
                    <div class="flag-item">
                        <span class="flag-badge" style="border-color: #eab308;"></span>
                        <span>Express Kiosk</span>
                    </div>
                </div>
            </div>

            {{-- Row 2: Key Metrics --}}
            <div class="metrics-row">
                <div class="metric-card">
                    <div class="metric-label">Total Monthly Revenue</div>
                    <div class="metric-value">$300k</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Overall Leakage</div>
                    <div class="metric-value">20% <span class="down-arrow">&darr;</span></div>
                    <div class="metric-sub">compared to last month</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Total Value Saved</div>
                    <div class="metric-value">$80k</div>
                </div>
            </div>

            {{-- Row 3: Performance Rankings --}}
            <div class="rankings-row">
                {{-- Top Earner last Month --}}
                <div class="rank-card">
                    <h3>Top Earner last Month</h3>
                    @php
                        $topEarners = [
                            ['Main Branch', '$50,000'],
                            ['Downtown Outlet', '$48,000'],
                            ['Uptown Mall', '$42,000'],
                            ['Airport Branch', '$39,000'],
                            ['Harbor Side', '$35,000'],
                            ['University Branch', '$25,000'],
                            ['Business Park', '$22,000'],
                            ['Residential Hub', '$19,000'],
                        ];
                    @endphp
                    @foreach ($topEarners as [$name, $amount])
                        <div class="rank-row">
                            <span>{{ $name }}</span>
                            <span class="green">{{ $amount }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Least Leakage last Month --}}
                <div class="rank-card">
                    <h3>Least Leakage last Month</h3>
                    @php
                        $leastLeakage = [
                            ['Main Branch', '$1,000'],
                            ['Downtown Outlet', '$1,400'],
                            ['Uptown Mall', '$1,800'],
                            ['Airport Branch', '$2,000'],
                            ['Harbor Side', '$2,100'],
                            ['University Branch', '$2,300'],
                            ['Business Park', '$2,900'],
                            ['Residential Hub', '$3,000'],
                        ];
                    @endphp
                    @foreach ($leastLeakage as [$name, $amount])
                        <div class="rank-row">
                            <span>{{ $name }}</span>
                            <span class="red">{{ $amount }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── RIGHT COLUMN (28%) ── --}}
        <div class="right-column">
            <div class="sidebar-card">

                {{-- JUNE SCHEDULE --}}
                <h3>JUNE SCHEDULE</h3>

                <div class="calendar-days">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                </div>

                <div class="calendar-grid">
                    @php
                        $days = [
                            ['label' => '31', 'class' => 'faded'],
                            ['label' => '01', 'class' => ''],
                            ['label' => '02', 'class' => 'highlight-ring'],
                            ['label' => '03', 'class' => ''],
                            ['label' => '04', 'class' => ''],
                            ['label' => '05', 'class' => ''],
                            ['label' => '06', 'class' => ''],
                            ['label' => '07', 'class' => ''],
                            ['label' => '08', 'class' => ''],
                            ['label' => '09', 'class' => 'highlight-ring'],
                            ['label' => '10', 'class' => ''],
                            ['label' => '11', 'class' => 'highlight-solid'],
                            ['label' => '12', 'class' => ''],
                            ['label' => '13', 'class' => ''],
                            ['label' => '14', 'class' => ''],
                            ['label' => '15', 'class' => ''],
                            ['label' => '16', 'class' => 'highlight-solid'],
                            ['label' => '17', 'class' => ''],
                            ['label' => '18', 'class' => ''],
                            ['label' => '19', 'class' => ''],
                            ['label' => '20', 'class' => ''],
                            ['label' => '21', 'class' => ''],
                            ['label' => '22', 'class' => ''],
                            ['label' => '23', 'class' => ''],
                            ['label' => '24', 'class' => ''],
                            ['label' => '25', 'class' => ''],
                            ['label' => '26', 'class' => 'highlight-solid'],
                            ['label' => '27', 'class' => ''],
                            ['label' => '28', 'class' => ''],
                            ['label' => '29', 'class' => ''],
                            ['label' => '30', 'class' => ''],
                            ['label' => '01', 'class' => 'faded'],
                            ['label' => '02', 'class' => 'faded'],
                            ['label' => '03', 'class' => 'faded'],
                            ['label' => '04', 'class' => 'faded'],
                        ];
                    @endphp
                    @foreach ($days as $day)
                        <span class="cal-cell {{ $day['class'] }}">{{ $day['label'] }}</span>
                    @endforeach
                </div>

                {{-- Upcoming Schedules --}}
                <h4>Upcoming Schedules:</h4>

                <div class="schedule-list">
                    <div class="schedule-item">
                        <span class="schedule-bullet"></span>
                        <div class="schedule-content">
                            <div class="title">Team Meeting</div>
                            <div class="meta">
                                <div class="meta-line">
                                    <span>10:00 AM</span>
                                    <span>12/06/2025</span>
                                </div>
                                <div>Board Room A</div>
                            </div>
                        </div>
                    </div>

                    <div class="schedule-item">
                        <span class="schedule-bullet"></span>
                        <div class="schedule-content">
                            <div class="title">Submit requirements</div>
                            <div class="meta">
                                <div class="meta-line">
                                    <span>11:30 AM</span>
                                    <span>15/06/2025</span>
                                </div>
                                <div>Compliance Office</div>
                            </div>
                        </div>
                    </div>

                    <div class="schedule-item">
                        <span class="schedule-bullet"></span>
                        <div class="schedule-content">
                            <div class="title">Funding Handover</div>
                            <div class="meta">
                                <div class="meta-line">
                                    <span>2:00 PM</span>
                                    <span>20/06/2025</span>
                                </div>
                                <div>Finance Dept</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
