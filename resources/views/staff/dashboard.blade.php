<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard — NITA</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --cream: #FFFFFF;
            --brown:   #5C2D1B;
            --terra:   #BC614B;
            --terra-dk:#A8523E;
            --green:   #1C8F5B;
            --green-bg:#E5F6EC;
            --border:  rgba(92, 45, 27, 0.18);
            --shadow:  0 1px 3px rgba(92,45,27,.08), 0 4px 12px rgba(92,45,27,.06);
            --radius:  12px;
            --font:    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body { font-family: var(--font); background: var(--cream); color: var(--brown); min-height: 100vh; }

        /* ── NAV ── */
        .nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(255,255,255,.92); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }
        .nav__inner {
            max-width: 900px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; height: 60px;
        }
        .nav__logo img { height: 28px; display: block; }
        .nav__right { display: flex; align-items: center; gap: 12px; }
        .nav__icon {
            width: 36px; height: 36px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            background: transparent; border: none; color: var(--brown); cursor: pointer;
        }
        .nav__icon:hover { background: rgba(92,45,27,.07); }
        .nav__user { display: flex; align-items: center; gap: 8px; padding-left: 8px; border-left: 1px solid var(--border); }
        .nav__user-avatar {
            width: 32px; height: 32px; border-radius: 50%; background: var(--terra); color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;
        }
        .nav__user-name { font-size: 13px; font-weight: 700; line-height: 1.2; }
        .nav__user-branch { font-size: 11px; color: rgba(92,45,27,.6); line-height: 1.2; }
        .nav__sep { width: 1px; height: 20px; background: var(--border); margin: 0 2px; }
        .nav__logout {
            padding: 7px 14px; background: transparent; color: var(--brown);
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
        }
        .nav__logout:hover { background: var(--brown); color: #fff; border-color: var(--brown); }

        /* ── PAGE ── */
        .page { max-width: 900px; margin: 0 auto; padding: 24px; display: flex; flex-direction: column; gap: 16px; }

        .status-bar {
            display: flex; align-items: center; gap: 8px;
            background: #FBEFEC; border: 1px solid rgba(188,97,75,.25); color: var(--terra-dk);
            border-radius: 10px; padding: 10px 16px; font-size: 13px; font-weight: 600;
        }

        .flash {
            background: var(--green-bg); border: 1px solid rgba(28,143,91,.3); color: var(--green);
            border-radius: 10px; padding: 10px 16px; font-size: 13px; font-weight: 600;
        }

        .open-card {
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            background: #fff; border: 1px solid var(--border); border-radius: var(--radius);
            box-shadow: var(--shadow); padding: 16px 20px;
        }
        .open-card__text { font-size: 13px; font-weight: 600; color: rgba(92,45,27,.75); }
        .btn-open {
            background: var(--terra); color: #fff; border: none; border-radius: 8px;
            padding: 10px 22px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: var(--font);
        }
        .btn-open:hover { background: var(--terra-dk); }
        .btn-open[disabled] { background: var(--green); cursor: default; }

        .section-title { font-size: 13px; font-weight: 700; color: rgba(92,45,27,.7); margin: 4px 0 -4px; }

        .task-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .task-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #fff; border: 1.5px solid var(--border); border-radius: 10px;
            padding: 12px 10px; font-size: 12.5px; font-weight: 700; color: var(--brown);
            cursor: pointer; font-family: var(--font); transition: all .15s ease;
        }
        .task-btn:hover { border-color: var(--terra); color: var(--terra); }
        .task-btn.is-done { background: var(--green-bg); border-color: var(--green); color: var(--green); }
        .task-btn.is-danger:hover { border-color: #c8433f; color: #c8433f; }

        .stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .stat-card {
            background: #fff; border: 1px solid var(--border); border-radius: var(--radius);
            box-shadow: var(--shadow); padding: 16px 18px;
        }
        .stat-label { font-size: 10.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: rgba(92,45,27,.5); margin-bottom: 6px; }
        .stat-value { font-size: 22px; font-weight: 800; }
        .stat-sub { font-size: 12px; color: rgba(92,45,27,.6); margin-top: 2px; }
        .stat-list { list-style: none; font-size: 12.5px; margin-top: 2px; }
        .stat-list li { display: flex; align-items: center; gap: 6px; padding: 3px 0; }
        .dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        .dot--ok { background: var(--green); }
        .dot--low { background: #d38a1a; }
        .dot--out { background: #c8433f; }

        .action-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .action-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #fff; border: 1.5px solid var(--border); border-radius: 10px;
            padding: 13px 10px; font-size: 13px; font-weight: 700; color: var(--brown);
            cursor: pointer; font-family: var(--font); text-decoration: none;
        }
        .action-btn:hover { border-color: var(--terra); color: var(--terra); }
        .action-btn--danger { color: #c8433f; border-color: rgba(200,67,63,.35); }
        .action-btn--danger:hover { background: #c8433f; color: #fff; border-color: #c8433f; }

        @media (max-width: 600px) {
            .task-row, .stat-row, .action-row { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<nav class="nav">
    <div class="nav__inner">
        <a href="{{ route('staff.dashboard') }}" class="nav__logo">
            <img src="{{ asset('images/logo.svg') }}" alt="NITA">
        </a>
        <div class="nav__right">
            <button type="button" class="nav__icon" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </button>
            <div class="nav__user">
                <div class="nav__user-avatar">{{ strtoupper(substr($staffUser->name, 0, 1)) }}</div>
                <div>
                    <div class="nav__user-name">{{ $staffUser->name }}</div>
                    <div class="nav__user-branch">{{ $branch->name ?? '—' }}</div>
                </div>
            </div>
            <div class="nav__sep"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav__logout">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="page">

    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <div class="status-bar">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Store officially opens at 10:00 AM
    </div>

    <div class="open-card">
        <div class="open-card__text">
            @if ($openShift)
                Shift is open — started {{ $openShift->shift_start->format('g:ia') }}.
            @else
                Store officially opens at 10:00 AM. Open your shift to begin.
            @endif
        </div>
        @if ($openShift)
            <button type="button" class="btn-open" disabled>Shift Open</button>
        @else
            <form method="POST" action="{{ route('staff.clock-in') }}">
                @csrf
                <button type="submit" class="btn-open">Open</button>
            </form>
        @endif
    </div>

    <div class="section-title">Proceed with the pre-opening tasks</div>
    <div class="task-row">
        <button type="button" class="task-btn" onclick="comingSoon('Verify Till Amount')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/></svg>
            Verify Till Amount
        </button>

        <form method="POST" action="{{ route('staff.verify-stock') }}">
            @csrf
            <button type="submit" class="task-btn {{ $hasVerifiedStock ? 'is-done' : '' }}" style="width:100%">
                @if ($hasVerifiedStock)
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                @else
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                @endif
                Verify Stock
            </button>
        </form>

        <button type="button" class="task-btn" onclick="comingSoon('Prep and Set-up')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            Prep and Set-up
        </button>

        <button type="button" class="task-btn is-danger" onclick="comingSoon('Close')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Close
        </button>
    </div>

    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-label">Total Employees</div>
            <div class="stat-value">{{ str_pad($clockedIn, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($totalStaff, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="stat-sub">clocked in today</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Orders Today</div>
            @forelse ($transactionsToday as $category => $count)
                <div class="stat-sub">{{ $category }}: {{ $count }} order{{ $count !== 1 ? 's' : '' }}</div>
            @empty
                <div class="stat-value" style="font-size:16px;">0</div>
                <div class="stat-sub">No orders yet</div>
            @endforelse
        </div>
        <div class="stat-card">
            <div class="stat-label">Inventory</div>
            <ul class="stat-list">
                @forelse ($lowStockIngredients->take(4) as $s)
                    <li><span class="dot dot--low"></span> {{ $s->ingredient->name ?? 'Ingredient' }}</li>
                @empty
                    <li><span class="dot dot--ok"></span> All {{ $totalIngredients }} ingredients stocked</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="action-row">
        <button type="button" class="action-btn" onclick="comingSoon('Messages')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Messages
        </button>
        <button type="button" class="action-btn" onclick="comingSoon('Clean')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 18 18"/><path d="M13 5.5 9 9l-6 6 3.5 3.5L13 12l4-4"/></svg>
            Clean
        </button>
        <form method="POST" action="{{ route('staff.clock-out') }}" onsubmit="return confirm('Clock out now? This will end your current shift.');">
            @csrf
            <button type="submit" class="action-btn action-btn--danger" style="width:100%">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Clock Out
            </button>
        </form>
    </div>

</div>

<script>
    function comingSoon(label) {
        alert(label + ' — coming soon.');
    }
</script>

</body>
</html>
