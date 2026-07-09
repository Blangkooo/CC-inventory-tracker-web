<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Businesses — NITA</title>
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

        /* ── LAYOUT ── */
        .workspace {
            max-width: 1400px; margin: 0 auto; padding: 24px 32px;
            display: flex; gap: 20px;
        }

        /* ── BRANCH SIDEBAR ── */
        .branch-bar {
            width: 108px; flex-shrink: 0;
            background: var(--terra);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            display: flex; flex-direction: column;
            align-items: center; padding: 14px 10px; gap: 10px;
        }

        .biz-badge {
            width: 100%; background: #fff; border-radius: 10px;
            border: 1px solid rgba(92,45,27,.2);
            padding: 10px 8px; text-align: center;
        }

        .biz-badge__icon { margin-bottom: 6px; }

        .biz-badge__name { font-size: 11px; font-weight: 700; line-height: 1.2; }
        .biz-badge__sub { font-size: 9px; font-weight: 600; opacity: .5; text-transform: uppercase; letter-spacing: .03em; margin-top: 2px; }

        .branch-divider { width: 100%; height: 1px; background: rgba(255,255,255,.2); }

        .branch-dots { display: flex; flex-direction: column; align-items: center; gap: 8px; width: 100%; }

        .branch-dot {
            width: 42px; height: 42px; border-radius: 50%;
            background: rgba(253,245,214,.85); border: 1.5px solid rgba(92,45,27,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700; color: var(--brown);
            cursor: pointer; text-decoration: none; transition: all .15s ease;
            flex-shrink: 0;
        }

        .branch-dot:hover,
        .branch-dot.is-active { background: var(--brown); color: #fff; border-color: var(--brown); }

        /* ── CONTENT ── */
        .content { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 20px; }

        /* Page header */
        .content-head {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }

        .content-head__title-wrap { display: flex; align-items: baseline; gap: 10px; }
        .content-head__title { font-size: 22px; font-weight: 800; }
        .content-head__role { font-size: 15px; font-weight: 400; opacity: .5; }

        /* Sub-tabs */
        .sub-tabs { display: flex; gap: 6px; }

        .sub-tab {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: 999px; font-size: 12px; font-weight: 600;
            border: 1.5px solid var(--border); background: #fff; color: var(--brown);
            text-decoration: none; transition: all .15s ease; cursor: pointer;
        }

        .sub-tab:hover { border-color: var(--terra); color: var(--terra); }
        .sub-tab.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }
        .sub-tab.is-active svg { stroke: #fff; }

        /* Search + category */
        .search-row { display: flex; align-items: center; gap: 10px; }

        .search-input {
            width: 280px; height: 38px; padding: 0 14px;
            background: #fff; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; color: var(--brown); font-family: var(--font);
            transition: border-color .15s ease;
        }

        .search-input::placeholder { color: rgba(92,45,27,.4); }
        .search-input:focus { outline: none; border-color: var(--terra); }

        .cat-pills { display: flex; gap: 6px; }

        .cat-pill {
            padding: 7px 18px; border-radius: 999px; font-size: 12px; font-weight: 600;
            border: 1.5px solid var(--border); background: #fff; color: var(--brown);
            cursor: pointer; transition: all .15s ease;
        }

        .cat-pill.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }

        /* Recipe card */
        .recipe-card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
        }

        .recipe-card__head {
            display: flex; align-items: center; gap: 10px;
            padding: 18px 24px; border-bottom: 1px solid var(--border);
        }

        .recipe-card__head svg { flex-shrink: 0; opacity: .6; }

        .recipe-card__name { font-size: 16px; font-weight: 800; }

        .recipe-table { width: 100%; border-collapse: collapse; }

        .recipe-table thead th {
            text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .45; padding: 12px 24px;
            background: rgba(92,45,27,.03); border-bottom: 1px solid var(--border);
        }

        .recipe-table tbody td {
            padding: 16px 24px; font-size: 13px; vertical-align: top; line-height: 1.7;
            border-bottom: 1px solid rgba(92,45,27,.06);
        }

        .recipe-table tbody tr:last-child td { border-bottom: none; }

        .recipe-table td strong { font-weight: 600; }

        .recipe-card__foot {
            display: flex; align-items: center; gap: 12px; justify-content: flex-end;
            padding: 14px 24px; border-top: 1px solid var(--border);
            background: rgba(92,45,27,.02);
        }

        .recipe-card__foot-label { font-size: 12px; font-weight: 600; opacity: .6; }

        .btn-edit {
            padding: 7px 20px; background: #fff; color: var(--brown);
            border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
            transition: all .15s ease;
        }

        .btn-edit:hover { background: var(--brown); color: var(--cream); border-color: var(--brown); }

        @media (max-width: 900px) {
            .branch-bar { display: none; }
            .workspace { padding: 16px; }
            .sub-tabs { flex-wrap: wrap; }
            .search-row { flex-direction: column; align-items: stretch; }
            .search-input { width: 100%; }
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
                <a href="{{ url('/business/recipes') }}"  class="nav__pill is-active">Businesses</a>
                <a href="{{ url('/logistics') }}"         class="nav__pill">Logistics</a>
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

<div class="workspace">

    {{-- Branch Sidebar --}}
    <div class="branch-bar">
        <div class="biz-badge">
            <div class="biz-badge__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/>
                </svg>
            </div>
            <div class="biz-badge__name">Coffee Shop</div>
            <div class="biz-badge__sub">Main Branch</div>
        </div>

        <div class="branch-divider"></div>

        <div class="branch-dots">
            @php
                $branches = [['QC',1],['Makati',2],['BGC',3],['Cebu',4],['Davao',5],['Clark',6]];
                $active = auth()->user()->isOwner() ? 1 : auth()->user()->branch_id;
            @endphp
            @foreach ($branches as [$label, $id])
                <a href="#" class="branch-dot {{ $id === $active ? 'is-active' : '' }}" title="{{ $label }} Branch">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- Main Content --}}
    <div class="content">

        <div class="content-head">
            <div class="content-head__title-wrap">
                <h1 class="content-head__title">Businesses</h1>
                <span class="content-head__role">/ {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
            </div>
            <div class="sub-tabs">
                <a href="{{ url('/business/summary') }}" class="sub-tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
                    </svg>
                    Summary
                </a>
                <a href="{{ url('/business/recipes') }}" class="sub-tab is-active">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    Recipe
                </a>
                <a href="#" class="sub-tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    Staff / Profile
                </a>
                <a href="#" class="sub-tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Verification
                </a>
            </div>
        </div>

        <div class="search-row">
            <input type="text" class="search-input" placeholder="Search item name…">
            <div class="cat-pills">
                <span class="cat-pill is-active">Drinks</span>
                <span class="cat-pill">Goods</span>
                <span class="cat-pill">Sets</span>
            </div>
        </div>

        <div class="recipe-card">
            <div class="recipe-card__head">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/>
                    <line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/>
                </svg>
                <span class="recipe-card__name">Black Forest Milk Tea</span>
            </div>

            <table class="recipe-table">
                <thead>
                    <tr>
                        <th>Regular — Measurements / Ingredients</th>
                        <th>Large — Measurements / Ingredients</th>
                        <th>Procedure</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Black Tea Base:</strong> 200ml<br>
                            <strong>Whole Milk:</strong> 60ml<br>
                            <strong>Chocolate Syrup:</strong> 30ml<br>
                            <strong>Cherry Syrup:</strong> 15ml<br>
                            <strong>Flavor Powder:</strong> 25g<br>
                            <strong>Sugar:</strong> 20g<br>
                            <strong>Ice:</strong> 1 cup
                        </td>
                        <td>
                            <strong>Black Tea Base:</strong> 300ml<br>
                            <strong>Whole Milk:</strong> 90ml<br>
                            <strong>Chocolate Syrup:</strong> 45ml<br>
                            <strong>Cherry Syrup:</strong> 22ml<br>
                            <strong>Flavor Powder:</strong> 38g<br>
                            <strong>Sugar:</strong> 30g<br>
                            <strong>Ice:</strong> 1.5 cups
                        </td>
                        <td>
                            1. Brew black tea base and let cool to room temperature.<br>
                            2. Combine chocolate syrup and cherry syrup in a mixing cup.<br>
                            3. Add flavor powder and sugar, stir until fully dissolved.<br>
                            4. Pour in the black tea base and whole milk.<br>
                            5. Fill serving cup with ice, then pour the mixture over.<br>
                            6. Stir gently and serve chilled. Garnish with cherry if desired.
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="recipe-card__foot">
                <span class="recipe-card__foot-label">Edit the recipe?</span>
                <button class="btn-edit">Edit</button>
            </div>
        </div>

    </div>
</div>

</body>
</html>
