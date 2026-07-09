{{-- Shared navbar partial --}}
<nav class="nita-nav">
    <div class="nita-nav__inner">
        <div class="nita-nav__left">
            <a href="{{ url('/dashboard') }}" class="nita-nav__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="NITA" height="32">
            </a>
            <div class="nita-nav__pills">
                <a href="{{ url('/dashboard') }}"       class="nita-nav__pill {{ request()->is('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                <a href="{{ url('/business/recipes') }}" class="nita-nav__pill {{ request()->is('business*') ? 'is-active' : '' }}">Businesses</a>
                <a href="{{ url('/logistics') }}"        class="nita-nav__pill {{ request()->is('logistics*') ? 'is-active' : '' }}">Logistics</a>
            </div>
        </div>
        <div class="nita-nav__right">
            <button class="nita-nav__icon-btn" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </button>
            <button class="nita-nav__icon-btn nita-nav__icon-btn--bordered" title="Messages">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </button>
            <button class="nita-nav__icon-btn" title="Settings">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </button>
            <div class="nita-nav__divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nita-nav__logout">Logout</button>
            </form>
        </div>
    </div>
</nav>
