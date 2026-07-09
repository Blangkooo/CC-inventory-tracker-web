<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — NITA</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --cream:   #FDF5D6;
            --cream-2: #F7ECC0;
            --brown:   #5C2D1B;
            --terra:   #BC614B;
            --terra-dk:#A8523E;
            --border:  rgba(92, 45, 27, 0.18);
            --shadow:  0 1px 3px rgba(92,45,27,.08), 0 4px 12px rgba(92,45,27,.06);
            --shadow-md: 0 2px 8px rgba(92,45,27,.1), 0 8px 24px rgba(92,45,27,.07);
            --radius:  12px;
            --font:    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body { font-family: var(--font); background: var(--cream); color: var(--brown); min-height: 100vh; }

        /* ── NAV ── */
        .nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(253,245,214,.92);
            backdrop-filter: blur(12px);
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
            color: var(--brown); text-decoration: none; letter-spacing: .01em;
            transition: all .15s ease; border: 1.5px solid transparent;
        }

        .nav__pill:hover { background: rgba(92,45,27,.06); }
        .nav__pill.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }

        .nav__right { display: flex; align-items: center; gap: 8px; }

        .nav__icon {
            width: 36px; height: 36px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            background: transparent; border: none; color: var(--brown);
            cursor: pointer; transition: background .15s ease;
        }

        .nav__icon:hover { background: rgba(92,45,27,.07); }
        .nav__icon--box { background: #fff; border: 1.5px solid var(--border); }

        .nav__sep { width: 1px; height: 20px; background: var(--border); margin: 0 4px; }

        .nav__logout {
            padding: 7px 16px; background: transparent; color: var(--brown);
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
            transition: all .15s ease; letter-spacing: .01em;
        }

        .nav__logout:hover { background: var(--brown); color: var(--cream); border-color: var(--brown); }

        /* ── LAYOUT ── */
        .page {
            max-width: 1400px; margin: 0 auto;
            padding: 28px 32px;
            display: grid;
            grid-template-columns: 1fr 288px;
            gap: 24px;
        }

        .main { display: flex; flex-direction: column; gap: 20px; min-width: 0; }

        /* ── PAGE HEADER ── */
        .page-head { display: flex; align-items: baseline; gap: 10px; padding-bottom: 4px; }
        .page-head__title { font-size: 22px; font-weight: 800; letter-spacing: .03em; text-transform: uppercase; }
        .page-head__title span { display: inline-block; border-bottom: 3px solid var(--brown); padding-bottom: 2px; }
        .page-head__role { font-size: 15px; font-weight: 400; opacity: .55; }

        /* ── CARD ── */
        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .card__head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px 12px;
            border-bottom: 1px solid var(--border);
        }

        .card__title { font-size: 13px; font-weight: 700; letter-spacing: .02em; }

        .card__body { padding: 16px 20px; }

        /* ── LEGEND ── */
        .legend { display: flex; gap: 14px; }
        .legend__item { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; opacity: .75; }
        .legend__dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

        /* ── FLAG GRID ── */
        .flag-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; }

        .flag-row {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 10px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            transition: background .1s ease;
        }

        .flag-row:hover { background: rgba(92,45,27,.04); }

        .flag-pip { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        /* ── METRICS ── */
        .metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }

        .metric {
            background: #fff; border: 1px solid var(--border); border-radius: var(--radius);
            box-shadow: var(--shadow); padding: 20px;
            display: flex; flex-direction: column; gap: 4px;
        }

        .metric__label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .5;
        }

        .metric__value { font-size: 32px; font-weight: 800; line-height: 1; margin-top: 6px; }
        .metric__value .down { color: #e53e3e; font-size: 22px; }
        .metric__sub { font-size: 10px; opacity: .5; margin-top: 2px; }

        /* ── RANKINGS ── */
        .rankings { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .rank { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); }

        .rank__head {
            padding: 14px 20px 12px; border-bottom: 1px solid var(--border);
            font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .04em; opacity: .6; text-align: center;
        }

        .rank__body { padding: 4px 20px 12px; }

        .rank__row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 0; border-bottom: 1px solid rgba(92,45,27,.06);
            font-size: 13px;
        }

        .rank__row:last-child { border-bottom: none; }
        .rank__row .name { font-weight: 500; }
        .rank__row .val-green { font-weight: 700; color: #16a34a; }
        .rank__row .val-red { font-weight: 700; color: #dc2626; }

        .rank__num {
            width: 20px; height: 20px; border-radius: 50%;
            background: rgba(92,45,27,.07); color: var(--brown);
            font-size: 10px; font-weight: 700; display: flex;
            align-items: center; justify-content: center; flex-shrink: 0;
        }

        .rank__row-left { display: flex; align-items: center; gap: 10px; }

        /* ── SIDEBAR ── */
        .sidebar { display: flex; flex-direction: column; gap: 16px; }

        .cal-card {
            background: var(--terra); border-radius: var(--radius);
            box-shadow: var(--shadow-md); overflow: hidden;
        }

        .cal-card__head {
            padding: 18px 20px 14px;
            font-size: 11px; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: rgba(255,255,255,.7);
        }

        .cal-grid-wrap { padding: 0 16px; }

        .cal-days {
            display: grid; grid-template-columns: repeat(7, 1fr);
            text-align: center; font-size: 9px; font-weight: 700;
            color: rgba(255,255,255,.5); text-transform: uppercase;
            margin-bottom: 6px; letter-spacing: .03em;
        }

        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 16px; }

        .cal-cell {
            text-align: center; padding: 6px 2px; font-size: 11px;
            font-weight: 500; color: rgba(255,255,255,.85); border-radius: 6px;
            cursor: default;
        }

        .cal-cell.faded { color: rgba(255,255,255,.25); }
        .cal-cell.today { background: var(--brown); color: #fff; font-weight: 700; }
        .cal-cell.event { background: rgba(255,255,255,.15); color: #fff; }

        .cal-schedule { border-top: 1px solid rgba(255,255,255,.15); padding: 14px 20px 20px; }

        .cal-schedule__label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .07em; color: rgba(255,255,255,.5); margin-bottom: 14px;
        }

        .sched-list { display: flex; flex-direction: column; gap: 14px; }

        .sched-item { display: flex; gap: 12px; }

        .sched-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: rgba(255,255,255,.7); flex-shrink: 0; margin-top: 5px;
        }

        .sched-title { font-size: 12px; font-weight: 700; color: #fff; }
        .sched-meta { font-size: 10px; color: rgba(255,255,255,.6); margin-top: 2px; line-height: 1.5; }
        .sched-meta-row { display: flex; justify-content: space-between; }

        /* ── RESPONSIVE ── */
        @media (max-width: 1100px) {
            .page { grid-template-columns: 1fr 260px; }
        }

        @media (max-width: 880px) {
            .page { grid-template-columns: 1fr; padding: 16px; }
            .metrics, .rankings { grid-template-columns: 1fr; }
            .flag-grid { grid-template-columns: repeat(2,1fr); }
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
                <a href="{{ url('/dashboard') }}"        class="nav__pill is-active">Dashboard</a>
                <a href="{{ url('/business/recipes') }}"  class="nav__pill">Businesses</a>
                <a href="{{ url('/logistics') }}"         class="nav__pill">Logistics</a>
            </div>
        </div>
        <div class="nav__right">
            <button class="nav__icon" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </button>
            <button class="nav__icon nav__icon--box" title="Messages">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </button>
            <button class="nav__icon" title="Settings">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </button>
            <div class="nav__sep"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav__logout">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="page">

    {{-- MAIN COLUMN --}}
    <div class="main">

        <div class="page-head">
            <h1 class="page-head__title"><span>Dashboard</span></h1>
            <span class="page-head__role">/ {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
        </div>

        {{-- Flag Summary --}}
        <div class="card">
            <div class="card__head">
                <span class="card__title">Flag Summary</span>
                <div class="legend">
                    <span class="legend__item"><span class="legend__dot" style="background:#eab308"></span>Low</span>
                    <span class="legend__item"><span class="legend__dot" style="background:#f97316"></span>Moderate</span>
                    <span class="legend__item"><span class="legend__dot" style="background:#ef4444"></span>High</span>
                </div>
            </div>
            <div class="card__body">
                <div class="flag-grid">
                    @php
                        $flags = [
                            ['Main Branch',       '#eab308'],
                            ['Downtown Outlet',   '#eab308'],
                            ['Uptown Mall',       '#f97316'],
                            ['Airport Branch',    '#ef4444'],
                            ['Harbor Side',       '#eab308'],
                            ['University Branch', '#f97316'],
                            ['Business Park',     '#eab308'],
                            ['Residential Hub',   '#ef4444'],
                            ['Express Kiosk',     '#eab308'],
                        ];
                    @endphp
                    @foreach ($flags as [$name, $color])
                        <div class="flag-row">
                            <span class="flag-pip" style="background:{{ $color }}"></span>
                            <span>{{ $name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Key Metrics --}}
        <div class="metrics">
            <div class="metric">
                <div class="metric__label">Total Monthly Revenue</div>
                <div class="metric__value">&#8369;300k</div>
            </div>
            <div class="metric">
                <div class="metric__label">Overall Leakage</div>
                <div class="metric__value">20% <span class="down">&darr;</span></div>
                <div class="metric__sub">vs. last month</div>
            </div>
            <div class="metric">
                <div class="metric__label">Total Value Saved</div>
                <div class="metric__value">&#8369;80k</div>
            </div>
        </div>

        {{-- Rankings --}}
        <div class="rankings">
            <div class="rank">
                <div class="rank__head">Top Earner — Last Month</div>
                <div class="rank__body">
                    @php
                        $earners = [
                            'Main Branch','Downtown Outlet','Uptown Mall','Airport Branch',
                            'Harbor Side','University Branch','Business Park','Residential Hub',
                        ];
                        $earnAmounts = ['&#8369;50,000','&#8369;48,000','&#8369;42,000','&#8369;39,000','&#8369;35,000','&#8369;25,000','&#8369;22,000','&#8369;19,000'];
                    @endphp
                    @foreach ($earners as $i => $name)
                        <div class="rank__row">
                            <div class="rank__row-left">
                                <span class="rank__num">{{ $i + 1 }}</span>
                                <span class="name">{{ $name }}</span>
                            </div>
                            <span class="val-green">{!! $earnAmounts[$i] !!}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rank">
                <div class="rank__head">Least Leakage — Last Month</div>
                <div class="rank__body">
                    @php
                        $leakBranches = [
                            'Main Branch','Downtown Outlet','Uptown Mall','Airport Branch',
                            'Harbor Side','University Branch','Business Park','Residential Hub',
                        ];
                        $leakAmounts = ['&#8369;1,000','&#8369;1,400','&#8369;1,800','&#8369;2,000','&#8369;2,100','&#8369;2,300','&#8369;2,900','&#8369;3,000'];
                    @endphp
                    @foreach ($leakBranches as $i => $name)
                        <div class="rank__row">
                            <div class="rank__row-left">
                                <span class="rank__num">{{ $i + 1 }}</span>
                                <span class="name">{{ $name }}</span>
                            </div>
                            <span class="val-red">{!! $leakAmounts[$i] !!}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="cal-card">
            <div class="cal-card__head">July 2026</div>
            <div class="cal-grid-wrap">
                <div class="cal-days">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                </div>
                <div class="cal-grid">
                    @php
                        $cal = [
                            ['28','faded'],['29','faded'],['30','faded'],['1',''],['2',''],['3',''],['4',''],
                            ['5',''],['6',''],['7',''],['8',''],['9','today'],['10',''],['11',''],
                            ['12',''],['13',''],['14',''],['15','event'],['16',''],['17',''],['18','event'],
                            ['19',''],['20',''],['21',''],['22',''],['23',''],['24',''],['25',''],
                            ['26',''],['27',''],['28',''],['29',''],['30',''],['31',''],['1','faded'],
                        ];
                    @endphp
                    @foreach ($cal as [$d, $cls])
                        <span class="cal-cell {{ $cls }}">{{ $d }}</span>
                    @endforeach
                </div>
            </div>
            <div class="cal-schedule">
                <div class="cal-schedule__label">Upcoming</div>
                <div class="sched-list">
                    <div class="sched-item">
                        <span class="sched-dot"></span>
                        <div>
                            <div class="sched-title">Inventory Audit</div>
                            <div class="sched-meta">
                                <div class="sched-meta-row"><span>9:00 AM</span><span>Jul 15</span></div>
                                <div>All Branches</div>
                            </div>
                        </div>
                    </div>
                    <div class="sched-item">
                        <span class="sched-dot"></span>
                        <div>
                            <div class="sched-title">Staff Review</div>
                            <div class="sched-meta">
                                <div class="sched-meta-row"><span>2:00 PM</span><span>Jul 18</span></div>
                                <div>Main Branch</div>
                            </div>
                        </div>
                    </div>
                    <div class="sched-item">
                        <span class="sched-dot"></span>
                        <div>
                            <div class="sched-title">Monthly Report</div>
                            <div class="sched-meta">
                                <div class="sched-meta-row"><span>10:00 AM</span><span>Jul 31</span></div>
                                <div>Finance Dept</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

</div>

</body>
</html>
