<style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
        --cream:  #F8F6F3;
        --brown:  #1C1917;
        --terra:  #B45353;
        --terra-dk: #9F4242;
        --terra-lt:#D4897C;
        --terra-bg:rgba(180,83,83,.08);
        --border: rgba(28,25,23,.12);
        --shadow: 0 1px 3px rgba(28,25,23,.06), 0 4px 12px rgba(28,25,23,.04);
        --radius: 12px;
        --font:   -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    body { font-family: var(--font); background: var(--cream); color: var(--brown); min-height: 100vh; }

    /* ══ NAV ══════════════════════════════════════════════════════════ */
    .nav {
        position: sticky; top: 0; z-index: 50;
        background: rgba(253,245,214,.92); backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border);
    }

    .nav__inner {
        margin: 0 auto;
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
    .nav__sep { width: 1px; height: 20px; background: var(--border); margin: 0 4px; }

    .nav__user {
        display: flex; align-items: center; gap: 8px; margin-left: 4px;
    }

    .nav__avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: var(--terra); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700;
    }

    .nav__user-info { line-height: 1.2; }
    .nav__user-name { font-size: 12px; font-weight: 700; }
    .nav__user-email { font-size: 10px; opacity: .45; }

    /* ══ BUSINESS HEADER BAR ══════════════════════════════════════════ */
    .biz-header-bar {
        position: sticky; top: 60px; z-index: 40;
        background: rgba(255,255,255,.95); backdrop-filter: blur(8px);
        border-bottom: 1px solid var(--border);
        padding: 14px 32px;
    }

    .biz-header-bar__inner {
        display: flex; align-items: center; justify-content: space-between;
    }

    .biz-header-bar__left {
        display: flex; align-items: center; gap: 20px;
    }

    .biz-header-bar__title {
        font-size: 20px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .02em; margin: 0; padding: 0; line-height: 1.2;
    }

    .biz-header-bar__sep {
        font-weight: 400; opacity: .5; margin: 0 4px;
    }

    .biz-header-bar__dots-wrap {
        display: flex; flex-direction: column; gap: 4px;
    }

    .biz-header-bar__dots {
        display: flex; gap: 6px;
    }

    .biz-header-bar__branch-info {
        display: flex; align-items: center; gap: 6px;
        font-size: 11px; opacity: .55;
    }

    .biz-header-bar__branch-name {
        font-weight: 600;
    }

    .biz-header-bar__branch-loc {
        font-weight: 400;
    }

    .biz-header-bar__branch-loc::before {
        content: '—'; margin-right: 6px; opacity: .5;
    }

    .biz-header-bar__tabs {
        display: flex; gap: 6px;
    }

    .branch-dot-sm {
        width: 28px; height: 28px; border-radius: 50%;
        background: rgba(253,245,214,.9); border: 1.5px solid rgba(92,45,27,.25);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 9px; font-weight: 700; color: var(--brown);
        cursor: pointer; transition: all .12s ease; text-decoration: none;
    }

    .branch-dot-sm.active { background: var(--brown); color: #fff; border-color: var(--brown); }
    .branch-dot-sm:hover { transform: scale(1.1); background: var(--brown); color: #fff; }

    .sub-tab {
        display: flex; align-items: center; gap: 6px;
        padding: 7px 16px; border-radius: 999px; font-size: 12px; font-weight: 600;
        color: var(--brown); border: 1.5px solid var(--border); background: #fff;
        text-decoration: none; transition: all .15s ease; white-space: nowrap;
    }

    .sub-tab:hover { border-color: var(--terra); color: var(--terra); }
    .sub-tab.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }
    .sub-tab.is-active svg { stroke: #fff; }

    /* ══ SHELL + SIDEBAR ═══════════════════════════════════════════════ */
    .shell {
        display: grid;
        grid-template-columns: 200px 1fr;
        min-height: calc(100vh - 60px);
    }

    .sidebar {
        background: #fff;
        border-right: 1px solid var(--border);
        padding: 20px 0;
        display: flex; flex-direction: column;
        position: sticky; top: 60px; height: calc(100vh - 60px);
        overflow-y: auto;
    }

    .sidebar__section {
        padding: 0 12px;
        margin-bottom: 4px;
    }

    .sidebar__section + .sidebar__section {
        border-top: 1px solid var(--border);
        padding-top: 12px;
        margin-top: 8px;
    }

    .sidebar__link {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px; border-radius: 8px;
        font-size: 13px; font-weight: 500; color: var(--brown);
        text-decoration: none; transition: all .12s ease;
    }

    .sidebar__link:hover { background: rgba(180,83,83,.06); color: var(--terra); }
    .sidebar__link.is-active { background: var(--terra-bg); color: var(--terra); font-weight: 700; }
    .sidebar__link svg { flex-shrink: 0; }

    .sidebar__logout-wrap {
        margin-top: auto; padding: 0 12px;
    }

    .sidebar__logout-btn {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px; border-radius: 8px;
        font-size: 13px; font-weight: 600; color: #dc2626;
        background: rgba(220,38,38,.06); border: none; cursor: pointer;
        font-family: var(--font); transition: all .12s ease; width: 100%;
    }

    .sidebar__logout-btn:hover { background: rgba(220,38,38,.12); }

    @media (max-width: 900px) {
        .shell { grid-template-columns: 1fr; }
        .sidebar { display: none; }
        .nav__inner { padding: 0 16px; }
        .biz-header-bar__inner { padding: 12px 16px; flex-wrap: wrap; gap: 12px; }
        .toast-container { top: auto; bottom: 20px; right: 16px; left: 16px; }
        .toast { max-width: 100%; }
    }

    /* ══ HOVER EFFECTS & TRANSITIONS ════════════════════════════════ */
    .group-card, .recipe-card, .card, .stat-card, .widget {
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .group-card:hover, .recipe-card:hover, .card:hover, .stat-card:hover, .widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(28,25,23,.08), 0 8px 24px rgba(28,25,23,.06);
    }

    .btn-primary, .btn-edit, .btn-save, .sub-tab, .nav__pill, .cat-pill, .sidebar__link, .sidebar__logout-btn {
        transition: all .15s ease;
    }
    .btn-primary:active, .btn-edit:active, .btn-save:active, .sidebar__logout-btn:active {
        transform: scale(.97);
    }

    .sidebar__link, .nav__icon, .branch-dot-sm, .employee-row {
        transition: all .12s ease;
    }

    /* ══ EMPTY STATE ILLUSTRATIONS ══════════════════════════════════ */
    .empty-state-illustration {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 48px 24px; text-align: center;
    }
    .empty-state-illustration svg {
        width: 120px; height: 120px; margin-bottom: 20px; opacity: .6;
    }
    .empty-state-illustration__title {
        font-size: 16px; font-weight: 700; color: var(--brown); margin-bottom: 8px;
    }
    .empty-state-illustration__desc {
        font-size: 13px; color: var(--brown); opacity: .5; margin-bottom: 20px; max-width: 320px;
    }
    .empty-state-illustration .btn-primary {
        padding: 10px 24px; font-size: 13px;
    }

    /* ══ TOAST NOTIFICATIONS ═════════════════════════════════════════ */
    .toast-container {
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        display: flex; flex-direction: column; gap: 8px;
        pointer-events: none;
    }
    .toast {
        padding: 14px 20px; border-radius: 10px;
        font-size: 13px; font-weight: 600; color: #fff;
        box-shadow: 0 4px 16px rgba(28,25,23,.15);
        display: flex; align-items: center; gap: 10px;
        pointer-events: auto;
        animation: toast-slide-in .3s ease;
        max-width: 380px;
    }
    .toast.is-leaving {
        animation: toast-slide-out .25s ease forwards;
    }
    .toast--success { background: #16a34a; }
    .toast--error { background: #dc2626; }
    .toast--info { background: var(--brown); }
    .toast--warning { background: #d97706; }
    .toast svg { flex-shrink: 0; }
    .toast__close {
        margin-left: auto; background: none; border: none; color: rgba(255,255,255,.7);
        cursor: pointer; padding: 0; font-size: 16px; line-height: 1;
    }
    .toast__close:hover { color: #fff; }

    @keyframes toast-slide-in {
        from { opacity: 0; transform: translateX(60px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes toast-slide-out {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(60px); }
    }

    /* ══ LOADING SKELETON ════════════════════════════════════════════ */
    .skeleton {
        background: linear-gradient(90deg, rgba(28,25,23,.06) 25%, rgba(28,25,23,.1) 50%, rgba(28,25,23,.06) 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.5s ease-in-out infinite;
        border-radius: 6px;
    }
    .skeleton--text { height: 14px; margin-bottom: 8px; }
    .skeleton--title { height: 22px; width: 60%; margin-bottom: 12px; }
    .skeleton--avatar { width: 40px; height: 40px; border-radius: 50%; }
    .skeleton--card { height: 120px; border-radius: var(--radius); }
    .skeleton--btn { height: 36px; width: 100px; border-radius: 8px; }

    @keyframes skeleton-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* ══ SPINNER ══════════════════════════════════════════════════════ */
    .spinner {
        width: 20px; height: 20px; border: 2.5px solid rgba(28,25,23,.1);
        border-top-color: var(--terra); border-radius: 50%;
        animation: spinner-spin .6s linear infinite;
        display: inline-block;
    }
    .spinner--sm { width: 14px; height: 14px; border-width: 2px; }
    .spinner--lg { width: 32px; height: 32px; border-width: 3px; }
    .spinner--white { border-color: rgba(255,255,255,.3); border-top-color: #fff; }

    .loading-overlay {
        display: flex; align-items: center; justify-content: center;
        flex-direction: column; gap: 12px; padding: 40px;
        color: var(--brown); opacity: .5; font-size: 13px; font-weight: 600;
    }

    @keyframes spinner-spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
if (!window.__nitaToastInit) {
    window.__nitaToastInit = true;
    window.showToast = function(message, type, duration) {
        type = type || 'success';
        duration = duration || 3500;
        var container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        var icons = {
            success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
            error: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            info: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
            warning: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
        };
        var toast = document.createElement('div');
        toast.className = 'toast toast--' + type;
        toast.innerHTML = (icons[type] || '') + '<span>' + message + '</span><button class="toast__close" onclick="this.parentElement.remove()">&times;</button>';
        container.appendChild(toast);
        setTimeout(function() {
            toast.classList.add('is-leaving');
            setTimeout(function() { toast.remove(); }, 250);
        }, duration);
    };
}
</script>
