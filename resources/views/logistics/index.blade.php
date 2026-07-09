<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics - NITA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #FDF5D6;
            color: #5C2D1B;
            min-height: 100vh;
        }

        /* ── Top Navigation Bar (same as dashboard) ── */
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

        .logo-area .logo-svg {
            width: 120px;
            height: auto;
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
        .icon-btn.gear svg { color: #4b5563; }

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
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: #5C2D1B;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .page-header h1 span.role {
            font-weight: 400;
            font-size: 18px;
        }

        .page-header h1 span.underline {
            display: inline-block;
            border-bottom: 3px solid #5C2D1B;
            padding-bottom: 2px;
        }

        .subnav-pills {
            display: flex;
            gap: 8px;
        }

        .subnav-pill {
            padding: 7px 18px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            cursor: default;
            border: 1.5px solid #5C2D1B;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .subnav-pill.active {
            background: #BC614B;
            color: #ffffff;
            border-color: #BC614B;
        }

        .subnav-pill.inactive {
            background: #ffffff;
            color: #5C2D1B;
        }

        /* ── 2-Column Card Grid ── */
        .card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .log-card {
            background: #FDF5D6;
            border: 1.5px solid #5C2D1B;
            border-radius: 12px;
            padding: 20px 24px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .log-card h2 {
            font-size: 16px;
            font-weight: 700;
            color: #5C2D1B;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1.5px solid rgba(92, 45, 27, 0.15);
        }

        .log-card-body {
            flex: 1;
        }

        .log-card-body p {
            font-size: 13px;
            line-height: 1.6;
            color: #5C2D1B;
        }

        /* ── Edit button (bottom-right) ── */
        .btn-edit {
            position: absolute;
            bottom: 16px;
            right: 20px;
            padding: 5px 16px;
            background: #ffffff;
            color: #5C2D1B;
            border: 1.5px solid #5C2D1B;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: default;
            font-family: inherit;
            transition: all 0.15s ease;
        }

        .btn-edit:hover {
            background: #5C2D1B;
            color: #FDF5D6;
        }

        /* ── Card-specific styles ── */

        /* Card 1: Variables */
        .var-list {
            list-style: none;
        }

        .var-list li {
            display: flex;
            align-items: baseline;
            gap: 6px;
            padding: 8px 0;
            font-size: 13px;
            color: #5C2D1B;
            border-bottom: 1px solid rgba(92, 45, 27, 0.08);
        }

        .var-list li:last-child { border-bottom: none; }

        .var-list .var-label {
            font-weight: 600;
            color: #5C2D1B;
        }

        .var-list .var-value {
            font-weight: 700;
            color: #5C2D1B;
        }

        .var-list .var-sub {
            font-size: 11px;
            color: #5C2D1B;
            opacity: 0.7;
        }

        /* Card 2: Remarks */
        .remarks-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .remark-col h3 {
            font-size: 12px;
            font-weight: 700;
            color: #5C2D1B;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid rgba(92, 45, 27, 0.15);
        }

        .remark-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 12px;
            color: #5C2D1B;
            border-bottom: 1px solid rgba(92, 45, 27, 0.06);
        }

        .remark-item:last-child { border-bottom: none; }

        .remark-status {
            font-weight: 700;
        }

        .remark-status.normal { color: #16a34a; }
        .remark-status.running-low { color: #eab308; }
        .remark-status.low { color: #f97316; }
        .remark-status.almost-out { color: #ef4444; }
        .remark-status.out { color: #dc2626; }

        /* Cards 3-5: Discrepancy formula cards */
        .discrepancy-sub {
            font-size: 12px;
            color: #5C2D1B;
            opacity: 0.75;
            margin-bottom: 14px;
            line-height: 1.5;
        }

        .formula-box {
            background: rgba(239, 68, 68, 0.06);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 10px;
        }

        .formula-box .formula-label {
            font-size: 14px;
            font-weight: 700;
            color: #dc2626;
        }

        .formula-box .formula-sub {
            font-size: 11px;
            color: #5C2D1B;
            opacity: 0.7;
            margin-top: 6px;
            line-height: 1.5;
        }

        /* Card 6: Breakdown 2-column */
        .breakdown-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .breakdown-col h3 {
            font-size: 12px;
            font-weight: 700;
            color: #5C2D1B;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1.5px solid rgba(92, 45, 27, 0.15);
        }

        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 12px;
            color: #5C2D1B;
            border-bottom: 1px solid rgba(92, 45, 27, 0.06);
        }

        .breakdown-row:last-child { border-bottom: none; }

        .breakdown-row .bd-value {
            font-weight: 700;
        }

        .breakdown-row .bd-value.green { color: #16a34a; }
        .breakdown-row .bd-value.amber { color: #d97706; }
        .breakdown-row .bd-value.red { color: #dc2626; }

        /* Ensure Edit button doesn't overlap content */
        .log-card.has-edit { padding-bottom: 48px; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .card-grid { grid-template-columns: 1fr; }
            .remarks-grid { grid-template-columns: 1fr; }
            .breakdown-grid { grid-template-columns: 1fr; }
            .navbar { flex-wrap: wrap; gap: 10px; }
            .nav-pills { order: 3; width: 100%; justify-content: center; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
        }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{--  VIEW 2: OWNER LOGISTICS METRICS                             --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}

    {{-- ── TOP NAVIGATION BAR ── --}}
    <nav class="navbar">
        <div class="navbar-left">
            <div class="logo-area">
                <img class="logo-svg" src="{{ asset('images/logo.svg') }}" alt="NITA Logo">
            </div>

            <div class="nav-pills">
                <a href="{{ url('/dashboard') }}" class="nav-pill">Dashboard</a>
                <a href="{{ url('/business/recipes') }}" class="nav-pill">Businesses</a>
                <a href="{{ url('/logistics') }}" class="nav-pill active">Logistics</a>
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
            {{-- Gray Hollowed Gear --}}
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

        {{-- Main Header Block: "Logistics | Owner" + sub-navigation pills --}}
        <div class="page-header">
            <h1>
                <span class="underline">LOGISTICS</span>&nbsp;
                <span class="role">| {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
            </h1>
            <div class="subnav-pills">
                <span class="subnav-pill inactive">Summary</span>
                <span class="subnav-pill active">Flags</span>
            </div>
        </div>

        {{-- 2-Column Card Grid --}}
        <div class="card-grid">

            {{-- Card 1: VARIABLES --}}
            <div class="log-card has-edit">
                <h2>&#128203; VARIABLES</h2>
                <div class="log-card-body">
                    <ul class="var-list">
                        <li>
                            <span class="var-label">Constant Float Value:</span>
                            <span class="var-value">$200</span>
                        </li>
                        <li>
                            <span class="var-label">Expected Total Sales:</span>
                            <span class="var-value">Total EOD Transactions</span>
                        </li>
                        <li>
                            <span class="var-label">Total Inventory:</span>
                            <span class="var-value">100 packages</span>
                        </li>
                    </ul>
                </div>
                <span class="btn-edit">Edit</span>
            </div>

            {{-- Card 2: Remarks --}}
            <div class="log-card has-edit">
                <h2>&#128221; Remarks</h2>
                <div class="log-card-body">
                    <p style="font-size:12px; color:#5C2D1B; opacity:0.75; margin-bottom:14px; line-height:1.5;">
                        Indicator rules for Leakage &amp; Inventory status thresholds.
                    </p>
                    <div class="remarks-grid">
                        <div class="remark-col">
                            <h3>Leakage Indicator</h3>
                            <div class="remark-item"><span>Normal</span><span class="remark-status normal">&#9679;</span></div>
                            <div class="remark-item"><span>Running low</span><span class="remark-status running-low">&#9679;</span></div>
                            <div class="remark-item"><span>Low</span><span class="remark-status low">&#9679;</span></div>
                            <div class="remark-item"><span>Almost out</span><span class="remark-status almost-out">&#9679;</span></div>
                            <div class="remark-item"><span>Out</span><span class="remark-status out">&#9679;</span></div>
                        </div>
                        <div class="remark-col">
                            <h3>Inventory Indicator</h3>
                            <div class="remark-item"><span>Normal</span><span class="remark-status normal">&#9679;</span></div>
                            <div class="remark-item"><span>Running low</span><span class="remark-status running-low">&#9679;</span></div>
                            <div class="remark-item"><span>Low</span><span class="remark-status low">&#9679;</span></div>
                            <div class="remark-item"><span>Almost out</span><span class="remark-status almost-out">&#9679;</span></div>
                            <div class="remark-item"><span>Out</span><span class="remark-status out">&#9679;</span></div>
                        </div>
                    </div>
                </div>
                <span class="btn-edit">Edit</span>
            </div>

            {{-- Card 3: Float Amount Discrepancy --}}
            <div class="log-card has-edit">
                <h2>&#128176; Float Amount Discrepancy</h2>
                <div class="log-card-body">
                    <p class="discrepancy-sub">
                        Verifies that the amount left in the till is exact.
                    </p>
                    <div class="formula-box">
                        <div class="formula-label">Actual Till Amount / Constant Float Value</div>
                        <div class="formula-sub">Must equate to 1 to be true, a flag is sent otherwise.</div>
                    </div>
                </div>
                <span class="btn-edit">Edit</span>
            </div>

            {{-- Card 4: Total Sales Discrepancy --}}
            <div class="log-card has-edit">
                <h2>&#128202; Total Sales Discrepancy</h2>
                <div class="log-card-body">
                    <p class="discrepancy-sub">
                        Tracks variance between cash collected and expected revenue.
                    </p>
                    <div class="formula-box">
                        <div class="formula-label">Actual Cash / Expected Total Sales</div>
                    </div>
                </div>
                <span class="btn-edit">Edit</span>
            </div>

            {{-- Card 5: EOD Inventory Discrepancy --}}
            <div class="log-card has-edit">
                <h2>&#128230; EOD Inventory Discrepancy</h2>
                <div class="log-card-body">
                    <p class="discrepancy-sub">
                        Compares end-of-day physical count against expected inventory levels.
                    </p>
                    <div class="formula-box">
                        <div class="formula-label">Actual Inventory Left / Expected Inventory Left</div>
                    </div>
                </div>
                <span class="btn-edit">Edit</span>
            </div>

            {{-- Card 6: Leakage & Inventory Breakdown --}}
            <div class="log-card has-edit">
                <h2>&#128200; Leakage &amp; Inventory Breakdown</h2>
                <div class="log-card-body">
                    <div class="breakdown-grid">
                        <div class="breakdown-col">
                            <h3>Leakage Indicator</h3>
                            <div class="breakdown-row">
                                <span>Normal</span>
                                <span class="bd-value green">&lt; 5%</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Running low</span>
                                <span class="bd-value amber">5% – 10%</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Low</span>
                                <span class="bd-value amber">10% – 15%</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Almost out</span>
                                <span class="bd-value red">15% – 20%</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Out</span>
                                <span class="bd-value red">&gt; 20%</span>
                            </div>
                        </div>
                        <div class="breakdown-col">
                            <h3>Inventory Indicator</h3>
                            <div class="breakdown-row">
                                <span>Normal</span>
                                <span class="bd-value green">&gt; 60%</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Running low</span>
                                <span class="bd-value amber">40% – 60%</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Low</span>
                                <span class="bd-value amber">20% – 40%</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Almost out</span>
                                <span class="bd-value red">10% – 20%</span>
                            </div>
                            <div class="breakdown-row">
                                <span>Out</span>
                                <span class="bd-value red">&lt; 10%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="btn-edit">Edit</span>
            </div>

        </div>
    </div>

</body>
</html>
