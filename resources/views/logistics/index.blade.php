<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics — NITA</title>
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

        /* ── NAV ── */
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
            background: transparent; border: none; color: var(--brown); cursor: pointer;
            transition: background .15s ease;
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

        .subnav__pill.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }

        /* ── CARD GRID ── */
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

        /* Card 1: Variables */
        .var-list { list-style: none; }

        .var-list li {
            display: flex; align-items: baseline; gap: 8px;
            padding: 10px 0; border-bottom: 1px solid rgba(92,45,27,.06);
            font-size: 13px;
        }

        .var-list li:last-child { border-bottom: none; }
        .var-label { font-weight: 600; opacity: .75; min-width: 160px; }
        .var-value { font-weight: 700; }

        /* Card 2: Remarks */
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
        .dot-green { background: #16a34a; }
        .dot-yellow { background: #eab308; }
        .dot-orange { background: #f97316; }
        .dot-red { background: #ef4444; }
        .dot-dkred { background: #dc2626; }

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
        .bd-green { font-weight: 700; color: #16a34a; }
        .bd-amber { font-weight: 700; color: #d97706; }
        .bd-red   { font-weight: 700; color: #dc2626; }

        @media (max-width: 900px) {
            .card-grid { grid-template-columns: 1fr; }
            .page { padding: 16px; }
            .page-head { flex-direction: column; align-items: flex-start; gap: 12px; }
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
            <button class="nav__icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </button>
            <button class="nav__icon nav__icon--box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </button>
            <button class="nav__icon">
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

    <div class="page-head">
        <div class="page-head__left">
            <h1 class="page-head__title"><span>Logistics</span></h1>
            <span class="page-head__role">/ {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
        </div>
        <div class="subnav">
            <a href="#" class="subnav__pill">Summary</a>
            <a href="#" class="subnav__pill is-active">Flags</a>
        </div>
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
                    <li><span class="var-label">Total Inventory</span><span class="var-value">100 packages</span></li>
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
</div>

</body>
</html>
