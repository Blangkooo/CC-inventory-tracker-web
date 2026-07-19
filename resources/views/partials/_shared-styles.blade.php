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

    /* ══ NAV ══ */
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
        transition: background .15s ease; text-decoration: none;
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

    /* ══ BADGES ══ */
    .badge {
        display: inline-block; padding: 2px 10px; border-radius: 999px;
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    }
    .badge-green  { background: rgba(22,163,74,.1); color: #16a34a; }
    .badge-amber  { background: rgba(217,119,6,.1); color: #d97706; }
    .badge-red    { background: rgba(220,38,38,.1); color: #dc2626; }
    .badge-gray   { background: rgba(92,45,27,.06); color: rgba(92,45,27,.5); }

    /* ══ SUB-TABS ══ */
    .sub-tabs { display: flex; gap: 6px; flex-wrap: wrap; }

    .sub-tab {
        display: flex; align-items: center; gap: 6px;
        padding: 7px 16px; border-radius: 999px; font-size: 12px; font-weight: 600;
        color: var(--brown); border: 1.5px solid var(--border); background: #fff;
        text-decoration: none; transition: all .15s ease; cursor: pointer;
    }

    .sub-tab:hover { border-color: var(--terra); color: var(--terra); }
    .sub-tab.is-active,
    .sub-tab.active { background: var(--terra); color: #fff; border-color: var(--terra); }

    /* ══ COMMON BUTTONS ══ */
    .btn-edit {
        padding: 6px 18px; background: #fff; color: var(--brown);
        border: 1.5px solid var(--border); border-radius: 8px;
        font-size: 12px; font-weight: 600; cursor: pointer; font-family: var(--font);
        transition: all .15s ease;
    }

    .btn-edit:hover { background: var(--brown); color: var(--cream); border-color: var(--brown); }

    @media (max-width: 900px) {
        .nav__inner { padding: 0 16px; }
    }
</style>
