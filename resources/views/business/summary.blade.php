<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Summary - NITA</title>
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

        .navbar-left { display: flex; align-items: center; gap: 32px; }
        .logo-area { display: flex; align-items: center; }

        .logo-area svg.logo-svg { width: 120px; height: 36px; flex-shrink: 0; }

        .nav-pills { display: flex; gap: 8px; }

        .nav-pill {
            padding: 8px 20px; border-radius: 999px; font-size: 13px; font-weight: 700;
            cursor: pointer; border: 1.5px solid #5C2D1B; background: #ffffff; color: #5C2D1B;
            transition: all 0.15s ease; text-decoration: none;
        }

        .nav-pill.active { background: #BC614B; color: #ffffff; border-color: #BC614B; }
        .nav-pill:hover { opacity: 0.85; }

        .navbar-right { display: flex; align-items: center; gap: 16px; }

        .icon-btn {
            width: 34px; height: 34px; border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background 0.15s ease;
            background: transparent; border: none;
        }

        .icon-btn:hover { background: rgba(92, 45, 27, 0.08); }
        .icon-btn.mail { background: #ffffff; border: 1.5px solid #5C2D1B; }

        .logout-form { margin-left: auto; }

        .logout-btn {
            padding: 6px 14px; background: transparent; color: #5C2D1B;
            border: 1.5px solid #5C2D1B; border-radius: 8px;
            font-size: 11px; font-weight: 600; cursor: pointer;
            font-family: inherit; transition: all 0.15s ease;
        }

        .logout-btn:hover { background: #5C2D1B; color: #FDF5D6; }

        /* ── Main Wrapper ── */
        .main-wrapper {
            display: flex;
            gap: 16px;
            max-width: 1440px;
            margin: 20px auto 0;
            padding: 0 24px;
        }

        /* ── Left Sidebar (Branch Selector) ── */
        .branch-sidebar {
            width: 120px; flex-shrink: 0;
            background: #BC614B; border: 1px solid #5C2D1B; border-radius: 12px;
            display: flex; flex-direction: column; align-items: center;
            padding: 16px 10px; gap: 10px;
        }

        .biz-header {
            text-align: center;
            padding: 10px 6px;
            background: rgba(253, 245, 214, 0.95);
            border: 1.5px solid #5C2D1B;
            border-radius: 10px;
            width: 100%;
        }

        .biz-header .biz-icon { width: 28px; height: 28px; margin: 0 auto 4px; }
        .biz-header .biz-icon svg { width: 100%; height: 100%; }

        .biz-header .biz-name {
            font-size: 11px; font-weight: 700; color: #5C2D1B; line-height: 1.2;
        }

        .biz-header .biz-sub {
            font-size: 8px; font-weight: 600; color: #5C2D1B;
            opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px;
        }

        .branch-list {
            display: flex; flex-direction: column; align-items: center;
            gap: 8px; width: 100%; padding-top: 6px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .branch-dot {
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(253, 245, 214, 0.9); border: 1.5px solid #5C2D1B;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700; color: #5C2D1B;
            cursor: pointer; transition: all 0.15s ease;
            text-decoration: none; flex-shrink: 0;
        }

        .branch-dot.active { background: #5C2D1B; color: #FDF5D6; border-color: #5C2D1B; }
        .branch-dot:hover { transform: scale(1.08); background: #5C2D1B; color: #FDF5D6; }



        /* ── Content Area ── */
        .content-area { flex: 1; min-width: 0; padding-left: 20px; }

        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
        }

        .page-header h1 {
            font-size: 22px; font-weight: 800; color: #5C2D1B; letter-spacing: 0.02em;
        }

        .page-header h1 .pipe { font-weight: 400; opacity: 0.6; }

        /* ── Sub-Header Context Tabs ── */
        .sub-tabs { display: flex; gap: 6px; }

        .sub-tab {
            padding: 7px 16px; border-radius: 999px; font-size: 12px; font-weight: 700;
            cursor: pointer; border: 1.5px solid #5C2D1B; background: #ffffff; color: #5C2D1B;
            text-decoration: none; transition: all 0.15s ease;
        }

        .sub-tab.active { background: #BC614B; color: #ffffff; border-color: #BC614B; }
        .sub-tab:hover { opacity: 0.85; }

        /* ── 2-Column Grid ── */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .sum-card {
            background: #FDF5D6;
            border: 1.5px solid #5C2D1B;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 20px;
        }

        .sum-card h3 {
            font-size: 15px; font-weight: 700; color: #5C2D1B;
            margin-bottom: 14px; padding-bottom: 8px;
            border-bottom: 1.5px solid rgba(92, 45, 27, 0.15);
        }

        .activity-log { list-style: none; }

        .activity-log li {
            display: flex; justify-content: space-between;
            padding: 8px 0; font-size: 13px; color: #5C2D1B;
            border-bottom: 1px solid rgba(92, 45, 27, 0.08);
        }

        .activity-log li:last-child { border-bottom: none; }

        .activity-log .total-row {
            font-weight: 800; font-size: 14px; color: #5C2D1B;
            padding-top: 10px; border-top: 2px solid #5C2D1B; margin-top: 4px;
        }

        .activity-log .items-list {
            font-size: 11px; color: #5C2D1B; opacity: 0.7;
        }

        .leakage-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; font-size: 13px; color: #5C2D1B;
            border-bottom: 1px solid rgba(92, 45, 27, 0.08);
        }

        .leakage-row:last-child { border-bottom: none; }
        .leakage-row .qty { font-weight: 700; }
        .leakage-row .qty.red { color: #dc2626; }
        .leakage-row .qty.amber { color: #d97706; }

        .profit-card {
            background: #FDF5D6; border: 1.5px solid #5C2D1B; border-radius: 12px;
            padding: 18px 20px; margin-bottom: 20px; text-align: center;
        }

        .profit-card h3 {
            font-size: 13px; font-weight: 700; color: #5C2D1B;
            margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.04em;
        }

        .profit-value { font-size: 36px; font-weight: 800; color: #16a34a; }
        .profit-value span { font-size: 20px; }

        .chart-card {
            background: #FDF5D6; border: 1.5px solid #5C2D1B; border-radius: 12px;
            padding: 18px 20px; margin-bottom: 20px;
        }

        .chart-card h3 {
            font-size: 14px; font-weight: 700; color: #5C2D1B; margin-bottom: 14px;
        }

        .chart-canvas { width: 100%; height: 140px; position: relative; overflow: hidden; }
        .chart-svg { width: 100%; height: 100%; }

        .chart-milestones {
            display: flex; justify-content: space-between;
            font-size: 10px; color: #5C2D1B; opacity: 0.7; margin-top: 6px;
        }

        @media (max-width: 900px) {
            .branch-sidebar { display: none; }
            .content-area { padding-left: 0; }
            .summary-grid { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .sub-tabs { flex-wrap: wrap; }
        }

        @media (max-width: 768px) {
            .navbar { flex-wrap: wrap; gap: 10px; }
            .nav-pills { order: 3; width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    {{-- ── UNIFIED MASTER TOP NAVBAR ── --}}
    <nav class="navbar">
        <div class="navbar-left">
            <div class="logo-area">
                <svg class="logo-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 180" width="120" height="36">
                    <g>
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
                <a href="{{ url('/dashboard') }}" class="nav-pill">Dashboard</a>
                <a href="{{ url('/business/recipes') }}" class="nav-pill active">Businesses</a>
                <a href="{{ url('/logistics') }}" class="nav-pill">Logistics</a>
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

    {{-- ── MAIN WRAPPER ── --}}
    <div class="main-wrapper">

        {{-- Left Sidebar: Branch Selector --}}
        <div class="branch-sidebar">
            {{-- Active Business Unit Header --}}
            <div class="biz-header">
                <div class="biz-icon">
                    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="6" width="20" height="16" rx="2" fill="#BC614B" stroke="#5C2D1B" stroke-width="1.5"/>
                        <path d="M22 8c2 0 4 1 4 4s-2 4-4 4" stroke="#5C2D1B" stroke-width="1.5" fill="none"/>
                    </svg>
                </div>
                <div class="biz-name">Coffee Shop</div>
                <div class="biz-sub">Main Branch</div>
            </div>

            {{-- Branch List --}}
            <div class="branch-list">
                @php
                    $allBranches = [
                        ['id' => 1, 'label' => 'QC'],
                        ['id' => 2, 'label' => 'Makati'],
                        ['id' => 3, 'label' => 'BGC'],
                        ['id' => 4, 'label' => 'Cebu'],
                        ['id' => 5, 'label' => 'Davao'],
                        ['id' => 6, 'label' => 'Clark'],
                    ];
                    $isOwner = auth()->user()->isOwner();
                    $userBranchId = auth()->user()->branch_id;
                    $visibleBranches = $isOwner
                        ? $allBranches
                        : collect($allBranches)->where('id', $userBranchId)->values()->all();
                    $activeId = $isOwner ? 1 : $userBranchId;
                @endphp
                @foreach ($visibleBranches as $branch)
                    <a href="#" class="branch-dot {{ $branch['id'] === $activeId ? 'active' : '' }}" title="{{ $branch['label'] }} Branch">{{ $branch['label'] }}</a>
                @endforeach
            </div>
        </div>

        {{-- Content Area --}}
        <div class="content-area">

            {{-- Page Header + Sub-Header Tabs --}}
            <div class="page-header">
                <h1>Businesses <span class="pipe">|</span> {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</h1>
                <div class="sub-tabs">
                    <a href="{{ url('/business/summary') }}" class="sub-tab active">&#128200; Summary</a>
                    <a href="{{ url('/business/recipes') }}" class="sub-tab">&#127858; Recipe</a>
                    <a href="#" class="sub-tab">&#128100; Staff/Profile</a>
                    <a href="#" class="sub-tab">&#10004;&#65039; Verification</a>
                </div>
            </div>

            {{-- 2-Column Summary Grid --}}
            <div class="summary-grid">
                {{-- LEFT COLUMN --}}
                <div class="left-col">
                    <div class="sum-card">
                        <h3>&#128196; Current Activity</h3>
                        <ul class="activity-log">
                            <li><span><strong>Transaction 5</strong></span><span>$7.50</span></li>
                            <li class="items-list">- Classic Milk Tea (x1), Tapioca (x1)</li>
                            <li><span><strong>Transaction 4</strong></span><span>$12.00</span></li>
                            <li class="items-list">- Black Forest Milk Tea (x2)</li>
                            <li><span><strong>Transaction 3</strong></span><span>$5.50</span></li>
                            <li class="items-list">- Iced Coffee (x1)</li>
                            <li><span><strong>Transaction 2</strong></span><span>$1.00</span></li>
                            <li class="items-list">- Extra Sugar Shot (x1)</li>
                            <li><span><strong>Transaction 1</strong></span><span>$0.00</span></li>
                            <li class="items-list">- Voided</li>
                            <li class="total-row"><span>TOTAL:</span><span>$26.00</span></li>
                        </ul>
                    </div>

                    <div class="sum-card">
                        <h3>&#128200; Annual Leakages History</h3>
                        <div class="leakage-row"><span>Whole Milk</span><span class="qty red">-12.5 L</span></div>
                        <div class="leakage-row"><span>Flavor Powder</span><span class="qty amber">-3.2 kg</span></div>
                        <div class="leakage-row"><span>Sugar</span><span class="qty red">-8.7 kg</span></div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="right-col">
                    <div class="profit-card">
                        <h3>Total Profit Margin</h3>
                        <div class="profit-value">20% <span>&uarr;</span></div>
                    </div>

                    <div class="chart-card">
                        <h3>Annual Performance Analytics</h3>
                        <div class="chart-canvas">
                            <svg class="chart-svg" viewBox="0 0 300 120" preserveAspectRatio="none">
                                <polyline points="0,90 30,70 60,80 90,45 120,55 150,30 180,40 210,20 240,35 270,15 300,25" fill="none" stroke="#BC614B" stroke-width="2.5"/>
                                <polygon points="0,90 30,70 60,80 90,45 120,55 150,30 180,40 210,20 240,35 270,15 300,25 300,120 0,120" fill="rgba(188,97,75,0.08)"/>
                                <polyline points="0,100 30,85 60,90 90,65 120,70 150,50 180,60 210,40 240,50 270,35 300,45" fill="none" stroke="rgba(92,45,27,0.3)" stroke-width="1.5" stroke-dasharray="4,3"/>
                            </svg>
                        </div>
                        <div class="chart-milestones">
                            <span>Jan</span><span>Mar</span><span>May</span><span>Jul</span><span>Sep</span><span>Nov</span>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3>Annual Historical Trends</h3>
                        <div class="chart-canvas">
                            <svg class="chart-svg" viewBox="0 0 300 120" preserveAspectRatio="none">
                                <polyline points="0,50 25,35 50,55 75,30 100,45 125,25 150,40 175,18 200,30 225,15 250,22 275,10 300,18" fill="none" stroke="#5C2D1B" stroke-width="2.5"/>
                                <polygon points="0,50 25,35 50,55 75,30 100,45 125,25 150,40 175,18 200,30 225,15 250,22 275,10 300,18 300,120 0,120" fill="rgba(92,45,27,0.06)"/>
                                <circle cx="75" cy="30" r="3" fill="#BC614B"/>
                                <circle cx="150" cy="40" r="3" fill="#BC614B"/>
                                <circle cx="225" cy="15" r="3" fill="#BC614B"/>
                                <circle cx="275" cy="10" r="3" fill="#BC614B"/>
                            </svg>
                        </div>
                        <div class="chart-milestones">
                            <span>2022</span><span>2023</span><span>2024</span><span>2025</span><span>2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
