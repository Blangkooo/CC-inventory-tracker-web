<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — NITA</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --sidebar-w: 250px;
            --bg: #f0f2f5;
            --card: #ffffff;
            --text: #1a1a2e;
            --text-2: #636e72;
            --text-3: #b2bec3;
            --accent: #e17055;
            --accent-2: #d63031;
            --accent-light: rgba(225,112,85,.08);
            --blue: #6c5ce7;
            --green: #00b894;
            --orange: #fdcb6e;
            --pink: #fd79a8;
            --border: rgba(0,0,0,.06);
            --shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.03);
            --shadow-md: 0 4px 20px rgba(0,0,0,.08);
            --radius: 14px;
            --font: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body { font-family: var(--font); background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* ═══ SIDEBAR ═══ */
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh; position: fixed; left: 0; top: 0;
            background: var(--card); border-right: 1px solid var(--border);
            display: flex; flex-direction: column; z-index: 100;
            padding: 20px 0;
        }

        .sidebar__brand {
            display: flex; align-items: center; gap: 10px;
            padding: 0 20px; margin-bottom: 28px;
        }
        .sidebar__brand img { height: 26px; }
        .sidebar__brand-text { font-size: 18px; font-weight: 900; color: var(--text); letter-spacing: -.02em; }
        .sidebar__brand-badge {
            font-size: 9px; font-weight: 700; background: var(--accent); color: #fff;
            padding: 2px 6px; border-radius: 4px; margin-left: 2px; letter-spacing: .05em;
        }

        .sidebar__nav { flex: 1; display: flex; flex-direction: column; gap: 2px; padding: 0 12px; }

        .sidebar__link {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 10px;
            font-size: 13px; font-weight: 500; color: var(--text-2);
            text-decoration: none; transition: all .15s;
        }
        .sidebar__link:hover { color: var(--text); background: rgba(0,0,0,.03); }
        .sidebar__link.is-active {
            color: var(--accent); background: var(--accent-light);
            font-weight: 700;
        }
        .sidebar__link svg { width: 18px; height: 18px; flex-shrink: 0; }
        .sidebar__link .badge-count {
            margin-left: auto; background: var(--accent-2); color: #fff;
            font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 99px;
        }

        .sidebar__section {
            font-size: 10px; font-weight: 700; color: var(--text-3);
            text-transform: uppercase; letter-spacing: .1em;
            padding: 18px 14px 6px;
        }

        .sidebar__footer {
            padding: 12px 12px 0; border-top: 1px solid var(--border); margin-top: auto;
        }
        .sidebar__user {
            display: flex; align-items: center; gap: 10px; padding: 10px 14px;
        }
        .sidebar__avatar {
            width: 34px; height: 34px; border-radius: 10px;
            background: linear-gradient(135deg, var(--accent), var(--pink));
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800;
        }
        .sidebar__user-name { font-size: 13px; font-weight: 700; color: var(--text); }
        .sidebar__user-role { font-size: 10px; color: var(--text-3); font-weight: 600; }

        .sidebar__logout {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 10px 14px; border-radius: 10px;
            font-size: 12px; font-weight: 600; color: var(--text-3);
            background: none; border: none; cursor: pointer; font-family: var(--font);
            transition: all .15s; margin-top: 4px;
        }
        .sidebar__logout:hover { color: var(--accent-2); background: rgba(214,48,49,.05); }
        .sidebar__logout svg { width: 16px; height: 16px; }

        /* ═══ MAIN ═══ */
        .content { margin-left: var(--sidebar-w); flex: 1; padding: 28px 32px; min-height: 100vh; }

        .content-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .content-title { font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
        .content-date { font-size: 13px; color: var(--text-2); font-weight: 500; }

        /* ═══ SHARED COMPONENTS ═══ */
        .card {
            background: var(--card); border-radius: var(--radius);
            box-shadow: var(--shadow); border: 1px solid var(--border);
        }
        .card__head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-bottom: 1px solid var(--border);
        }
        .card__title { font-size: 13px; font-weight: 700; }
        .card__body { padding: 16px 20px; }
        .card__link { font-size: 12px; font-weight: 600; color: var(--accent); text-decoration: none; }
        .card__link:hover { text-decoration: underline; }

        .badge { display: inline-block; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 999px; text-transform: uppercase; }
        .badge-green { background: rgba(0,184,148,.1); color: #00b894; }
        .badge-amber { background: rgba(253,203,110,.15); color: #e17055; }
        .badge-red { background: rgba(214,48,49,.08); color: #d63031; }
        .badge-gray { background: rgba(0,0,0,.04); color: var(--text-2); }
        .badge-blue { background: rgba(108,92,231,.08); color: #6c5ce7; }

        .empty-state { text-align: center; padding: 32px; font-size: 13px; color: var(--text-3); }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 20px; background: var(--accent); color: #fff;
            border: none; border-radius: 10px; font-size: 13px; font-weight: 700;
            font-family: var(--font); cursor: pointer; transition: all .15s;
        }
        .btn-primary:hover { background: #d35e47; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(225,112,85,.3); }

        /* ═══ RESPONSIVE ═══ */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .content { margin-left: 0; padding: 16px; }
        }

        /* ═══ PAGE-SPECIFIC ═══ */
        @yield('styles')
    </style>
</head>
<body>

@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
    $roleName = $user->isSuperAdmin() ? 'Owner' : ($user->isManager() ? 'Manager' : 'Staff');
    $pendingAlertCount = \App\Models\DiscrepancyAlert::where('status', 'pending')->count();
    $currentRoute = request()->route()?->getName() ?? '';
@endphp

<aside class="sidebar">
    <div class="sidebar__brand">
        <img src="{{ asset('images/logo.svg') }}" alt="NITA">
        <span class="sidebar__brand-text">NITA</span>
        <span class="sidebar__brand-badge">PRO</span>
    </div>

    <nav class="sidebar__nav">
        <span class="sidebar__section">Overview</span>
        <a href="{{ route('dashboard') }}" class="sidebar__link {{ $currentRoute === 'dashboard' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="{{ route('business.recipes') }}" class="sidebar__link {{ str_starts_with($currentRoute, 'business') ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            Business
        </a>
        <a href="{{ route('logistics') }}" class="sidebar__link {{ $currentRoute === 'logistics' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            Logistics
        </a>

        <span class="sidebar__section">Tools</span>
        <a href="{{ route('suppliers.index') }}" class="sidebar__link {{ $currentRoute === 'suppliers.index' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Suppliers
        </a>
        <a href="{{ route('pricing.index') }}" class="sidebar__link {{ $currentRoute === 'pricing.index' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Pricing
        </a>
        <a href="{{ route('alerts') }}" class="sidebar__link {{ $currentRoute === 'alerts' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            Alerts
            @if ($pendingAlertCount > 0)
                <span class="badge-count">{{ $pendingAlertCount }}</span>
            @endif
        </a>

        <span class="sidebar__section">Manage</span>
        <a href="{{ route('business.workers') }}" class="sidebar__link {{ $currentRoute === 'business.workers' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            Workers
        </a>
        <a href="{{ route('branches') }}" class="sidebar__link {{ str_starts_with($currentRoute, 'branches') ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h1"/><path d="M14 9h1"/><path d="M9 13h1"/><path d="M14 13h1"/></svg>
            Branches
        </a>
        <a href="{{ route('settings') }}" class="sidebar__link {{ $currentRoute === 'settings' ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Settings
        </a>
    </nav>

    <div class="sidebar__footer">
        <div class="sidebar__user">
            <div class="sidebar__avatar">{{ $initials }}</div>
            <div>
                <div class="sidebar__user-name">{{ $user->name }}</div>
                <div class="sidebar__user-role">{{ $roleName }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar__logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Log Out
            </button>
        </form>
    </div>
</aside>

<div class="content">
    @yield('content')
</div>

</body>
</html>
