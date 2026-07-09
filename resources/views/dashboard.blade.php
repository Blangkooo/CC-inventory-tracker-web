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
            <a href="{{ url('/alerts') }}" class="nav__icon" title="Alerts">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </a>
            <a href="{{ url('/alerts') }}" class="nav__icon nav__icon--box" title="Messages" style="text-decoration:none">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </a>
            <a href="{{ url('/settings') }}" class="nav__icon" title="Settings" style="text-decoration:none">
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

@php
    // ── Metric formatting helper ──────────────────────────────────────
    $fmt = fn($n) => $n >= 1_000_000
        ? '&#8369;' . number_format($n / 1_000_000, 1) . 'M'
        : ($n >= 1_000 ? '&#8369;' . number_format($n / 1_000, 1) . 'k' : '&#8369;' . number_format($n));

    // ── Branch alert severity map ─────────────────────────────────────
    $sevMap   = []; // branch_id => worst severity
    $sevOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
    foreach ($recent_flags as $f) {
        $bid = $f->branch_id;
        if (!isset($sevMap[$bid]) || ($sevOrder[$f->severity] ?? 0) > ($sevOrder[$sevMap[$bid]] ?? 0)) {
            $sevMap[$bid] = $f->severity;
        }
    }
    $sevColors = ['high' => '#ef4444', 'medium' => '#f97316', 'low' => '#eab308'];

    // ── Dynamic calendar ─────────────────────────────────────────────
    $calNow    = now();
    $firstDow  = $calNow->copy()->startOfMonth()->dayOfWeek; // 0=Sun
    $daysInMon = $calNow->daysInMonth;
    $todayDay  = $calNow->day;
    $prevMonth = $calNow->copy()->subMonth();
    $daysInPrev= $prevMonth->daysInMonth;
@endphp

<div class="page">

    {{-- MAIN COLUMN --}}
    <div class="main">

        <div class="page-head">
            <h1 class="page-head__title"><span>Dashboard</span></h1>
            <span class="page-head__role">/ {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
        </div>

        {{-- Branch Flag Summary --}}
        <div class="card">
            <div class="card__head">
                <span class="card__title">Branch Status — Today</span>
                <div class="legend">
                    <span class="legend__item"><span class="legend__dot" style="background:#16a34a"></span>Active</span>
                    <span class="legend__item"><span class="legend__dot" style="background:#eab308"></span>Low Alert</span>
                    <span class="legend__item"><span class="legend__dot" style="background:#f97316"></span>Med Alert</span>
                    <span class="legend__item"><span class="legend__dot" style="background:#ef4444"></span>High Alert</span>
                </div>
            </div>
            <div class="card__body">
                @if ($branches_with_sales->isEmpty())
                    <div style="font-size:13px;opacity:.4;text-align:center;padding:12px 0;">No branch data yet.</div>
                @else
                    <div class="flag-grid">
                        @foreach ($branches_with_sales as $b)
                            @php
                                // Find this branch's worst alert severity
                                // branches_with_sales has no ID, match by name via recent_flags
                                $flagMatch = $recent_flags->first(fn($f) => $f->branch?->name === $b['name']);
                                $branchSev = $flagMatch ? ($sevMap[$flagMatch->branch_id] ?? null) : null;
                                $pip = $branchSev ? ($sevColors[$branchSev] ?? '#eab308') : ($b['has_sales'] ? '#16a34a' : 'rgba(92,45,27,.25)');
                            @endphp
                            <div class="flag-row">
                                <span class="flag-pip" style="background:{{ $pip }}"></span>
                                <span>{{ $b['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Key Metrics --}}
        <div class="metrics">
            <div class="metric">
                <div class="metric__label">Annual Revenue ({{ now()->year }})</div>
                <div class="metric__value">{!! $fmt($annual_revenue) !!}</div>
                <div class="metric__sub">Today: {!! $fmt($total_sales) !!}</div>
            </div>
            <div class="metric">
                <div class="metric__label">Overall Leakage</div>
                <div class="metric__value">
                    {{ number_format($leakage_pct, 1) }}%
                    @if ($leakage_pct > 0)<span class="down">&darr;</span>@endif
                </div>
                <div class="metric__sub">Based on shift count variances</div>
            </div>
            <div class="metric">
                <div class="metric__label">Est. Value Saved</div>
                <div class="metric__value">{!! $fmt($value_saved) !!}</div>
                <div class="metric__sub">From reviewed alerts</div>
            </div>
        </div>

        {{-- Stats row --}}
        <div class="metrics">
            <div class="metric">
                <div class="metric__label">Total Branches</div>
                <div class="metric__value" style="font-size:28px">{{ $total_branches }}</div>
            </div>
            <div class="metric">
                <div class="metric__label">Pending Alerts</div>
                <div class="metric__value" style="font-size:28px;color:{{ $pending_alerts > 0 ? '#dc2626' : '#16a34a' }}">{{ $pending_alerts }}</div>
            </div>
            <div class="metric">
                <div class="metric__label">Low Stock Items</div>
                <div class="metric__value" style="font-size:28px;color:{{ $low_stock_count > 0 ? '#d97706' : '#16a34a' }}">{{ $low_stock_count }}</div>
            </div>
        </div>

        {{-- Rankings --}}
        <div class="rankings">
            <div class="rank">
                <div class="rank__head">Top Earners — {{ now()->year }}</div>
                <div class="rank__body">
                    @forelse ($top_earners as $i => $branch)
                        <div class="rank__row">
                            <div class="rank__row-left">
                                <span class="rank__num">{{ $i + 1 }}</span>
                                <span class="name">{{ $branch->name }}</span>
                            </div>
                            <span class="val-green">&#8369;{{ number_format($branch->revenue ?? 0) }}</span>
                        </div>
                    @empty
                        <div style="font-size:13px;opacity:.4;padding:12px 0;">No transaction data yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="rank">
                <div class="rank__head">Least Leakage (Units)</div>
                <div class="rank__body">
                    @forelse ($least_leakage->take(8) as $i => $item)
                        <div class="rank__row">
                            <div class="rank__row-left">
                                <span class="rank__num">{{ $i + 1 }}</span>
                                <span class="name">{{ $item['name'] }}</span>
                            </div>
                            <span class="{{ $item['leak'] > 0 ? 'val-red' : 'val-green' }}">
                                {{ $item['leak'] > 0 ? '−' . number_format($item['leak'], 2) . 'u' : 'Clean' }}
                            </span>
                        </div>
                    @empty
                        <div style="font-size:13px;opacity:.4;padding:12px 0;">No leakage data yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Pending Flags --}}
        @if ($recent_flags->isNotEmpty())
        <div class="card">
            <div class="card__head">
                <span class="card__title">Recent Pending Alerts</span>
                <a href="{{ url('/alerts') }}" style="font-size:12px;font-weight:600;color:var(--terra);text-decoration:none;">View All &rarr;</a>
            </div>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;opacity:.4;background:rgba(92,45,27,.03);">
                            <th style="text-align:left;padding:10px 20px;border-bottom:1px solid var(--border)">Branch</th>
                            <th style="text-align:left;padding:10px 20px;border-bottom:1px solid var(--border)">Ingredient</th>
                            <th style="text-align:left;padding:10px 20px;border-bottom:1px solid var(--border)">Severity</th>
                            <th style="text-align:left;padding:10px 20px;border-bottom:1px solid var(--border)">Variance</th>
                            <th style="text-align:left;padding:10px 20px;border-bottom:1px solid var(--border)">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent_flags as $flag)
                        @php
                            $sc = ['high'=>'#fee2e2','medium'=>'#fef3c7','low'=>'#dbeafe'];
                            $tc = ['high'=>'#991b1b','medium'=>'#92400e','low'=>'#1e40af'];
                        @endphp
                        <tr style="font-size:13px;border-bottom:1px solid rgba(92,45,27,.06)">
                            <td style="padding:11px 20px;font-weight:600">{{ $flag->branch->name ?? '—' }}</td>
                            <td style="padding:11px 20px">{{ $flag->ingredient->name ?? '—' }}</td>
                            <td style="padding:11px 20px">
                                <span style="padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:{{ $sc[$flag->severity] ?? '#f3f4f6' }};color:{{ $tc[$flag->severity] ?? '#374151' }}">
                                    {{ ucfirst($flag->severity) }}
                                </span>
                            </td>
                            <td style="padding:11px 20px;color:#dc2626;font-weight:700">{{ $flag->variance !== null ? '−' . number_format(abs($flag->variance), 2) : '—' }}</td>
                            <td style="padding:11px 20px;opacity:.5">{{ $flag->created_at->format('M d, g:iA') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="cal-card">
            <div class="cal-card__head">{{ now()->format('F Y') }}</div>
            <div class="cal-grid-wrap">
                <div class="cal-days">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                </div>
                <div class="cal-grid">
                    {{-- Leading faded days from previous month --}}
                    @for ($i = $firstDow - 1; $i >= 0; $i--)
                        <span class="cal-cell faded">{{ $daysInPrev - $i }}</span>
                    @endfor
                    {{-- Current month days --}}
                    @for ($d = 1; $d <= $daysInMon; $d++)
                        <span class="cal-cell {{ $d === $todayDay ? 'today' : '' }}">{{ $d }}</span>
                    @endfor
                    {{-- Trailing faded days --}}
                    @php $cellsUsed = $firstDow + $daysInMon; $trail = (7 - ($cellsUsed % 7)) % 7; @endphp
                    @for ($d = 1; $d <= $trail; $d++)
                        <span class="cal-cell faded">{{ $d }}</span>
                    @endfor
                </div>
            </div>
            <div class="cal-schedule">
                <div class="cal-schedule__label">
                    {{ $ongoing_shifts->isNotEmpty() ? 'Open Shifts' : 'No Active Shifts' }}
                </div>
                <div class="sched-list">
                    @forelse ($ongoing_shifts as $shift)
                        <div class="sched-item">
                            <span class="sched-dot"></span>
                            <div>
                                <div class="sched-title">{{ $shift->branch->name ?? 'Unknown Branch' }}</div>
                                <div class="sched-meta">
                                    <div class="sched-meta-row">
                                        <span>{{ $shift->shift_start->format('g:i A') }}</span>
                                        <span>{{ $shift->shift_start->format('M d') }}</span>
                                    </div>
                                    <div>{{ $shift->user->name ?? 'Unknown Staff' }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="sched-meta" style="opacity:.5;font-size:12px">All shifts closed</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="card" style="padding:16px 20px">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;opacity:.4;margin-bottom:12px">Alert Breakdown</div>
            @foreach (['high' => '#dc2626', 'medium' => '#d97706', 'low' => '#2563eb'] as $sev => $color)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid rgba(92,45,27,.06)">
                    <span style="font-size:13px;font-weight:500">{{ ucfirst($sev) }}</span>
                    <span style="font-size:16px;font-weight:800;color:{{ $color }}">{{ $flag_counts[$sev] ?? 0 }}</span>
                </div>
            @endforeach
            <div style="margin-top:12px">
                <a href="{{ url('/alerts') }}" style="display:block;text-align:center;padding:9px;background:var(--terra);color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none">
                    View All Alerts
                </a>
            </div>
        </div>

    </aside>

</div>

</body>
</html>
