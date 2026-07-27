@extends('layouts.sidebar')

@section('title', 'Settings')

@section('styles')

        /* ── Dimmed overlay ── */
        .overlay {
            position: fixed; top: 0; right: 0; bottom: 0; left: 250px;
            background: rgba(26,26,46,.4); backdrop-filter: blur(3px);
            z-index: 100; display: flex; justify-content: flex-end;
            cursor: pointer;
        }

        /* ── Drawer ── */
        .drawer {
            width: 380px; max-width: 90vw; height: 100vh;
            background: var(--card, #fff); border-radius: 20px 0 0 20px;
            padding: 32px 28px; display: flex; flex-direction: column;
            box-shadow: -8px 0 40px rgba(0,0,0,.18);
            animation: slideIn .22s ease-out;
            cursor: default; color: var(--text, #1a1a2e);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); }
            to   { transform: translateX(0); }
        }

        /* ── Header ── */
        .drawer-header {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 28px; padding-bottom: 20px;
            border-bottom: 1.5px solid var(--border);
        }

        .drawer-header h2 { font-size: 20px; font-weight: 800; }

        /* ── Profile card ── */
        .profile-card {
            display: flex; align-items: center; gap: 14px;
            padding: 16px; margin-bottom: 24px;
            background: var(--accent-light, rgba(225,112,85,.08)); border: 1px solid var(--border);
            border-radius: 12px;
        }

        .profile-avatar {
            width: 46px; height: 46px; border-radius: 50%;
            background: var(--accent, #e17055); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; flex-shrink: 0;
            text-transform: uppercase;
        }

        .profile-info .name  { font-size: 15px; font-weight: 700; }
        .profile-info .email { font-size: 12px; color: var(--text-2, #636e72); margin-top: 2px; }
        .profile-info .role  { font-size: 11px; font-weight: 600; color: var(--accent, #e17055); margin-top: 3px; text-transform: capitalize; }

        /* ── Sections ── */
        .drawer-section { margin-bottom: 20px; }

        .drawer-section h3 {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .5; margin-bottom: 8px;
        }

        .drawer-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 11px 0; font-size: 13.5px; font-weight: 500;
            border-bottom: 1px solid rgba(92,45,27,.07);
        }

        .drawer-row:last-child { border-bottom: none; }

        .row-value { opacity: .55; font-size: 13px; font-weight: 400; }

        /* ── Logout ── */
        .logout-section {
            margin-top: auto; padding-top: 20px;
            border-top: 1.5px solid var(--border);
        }

        .btn-logout {
            display: flex; align-items: center; justify-content: center;
            gap: 10px; width: 100%; height: 50px;
            background: transparent; color: #dc2626;
            border: 1.5px solid #dc2626; border-radius: 12px;
            font-size: 15px; font-weight: 700; font-family: var(--font);
            cursor: pointer; transition: all .15s ease;
        }

        .btn-logout:hover { background: #dc2626; color: #fff; }

        .dismiss-hint {
            position: fixed; bottom: 20px; left: 270px; z-index: 101;
            font-size: 11px; color: rgba(255,255,255,.45); cursor: default;
        }
@endsection

@section('content')

    {{-- Dimmed background -- click to close --}}
    <div class="overlay" onclick="window.location.href='{{ url('/dashboard') }}'">

        <div class="drawer" onclick="event.stopPropagation()">

            {{-- Header --}}
            <div class="drawer-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <h2>Settings</h2>
            </div>

            {{-- Profile Card --}}
            @php $user = auth()->user(); @endphp
            <div class="profile-card">
                <div class="profile-avatar">{{ mb_substr($user->name, 0, 1) }}</div>
                <div class="profile-info">
                    <div class="name">{{ $user->name }}</div>
                    <div class="email">{{ $user->email }}</div>
                    <div class="role">{{ str_replace('_', ' ', $user->role) }}</div>
                </div>
            </div>

            {{-- Account --}}
            <div class="drawer-section">
                <h3>Account</h3>
                <div class="drawer-row">
                    <span>Name</span>
                    <span class="row-value">{{ $user->name }}</span>
                </div>
                <div class="drawer-row">
                    <span>Email</span>
                    <span class="row-value">{{ $user->email }}</span>
                </div>
                <div class="drawer-row">
                    <span>Role</span>
                    <span class="row-value">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span>
                </div>
                @if ($user->branch_id)
                    <div class="drawer-row">
                        <span>Branch</span>
                        <span class="row-value">{{ $user->branch->name ?? '—' }}</span>
                    </div>
                @endif
                <div style="margin-top:12px;">
                    <a href="{{ url('/business/workers') }}?worker={{ $user->id }}" style="display:flex;align-items:center;gap:8px;padding:9px 14px;background:rgba(188,97,75,.08);border:1px solid rgba(188,97,75,.2);border-radius:8px;color:var(--terra);font-size:12px;font-weight:600;text-decoration:none;transition:all.12s ease;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        Edit Profile
                    </a>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="drawer-section">
                <h3>Quick Actions</h3>
                <div class="drawer-row" style="cursor:pointer;" onclick="window.location.href='{{ url('/alerts') }}'">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:6px;">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        View Alerts
                    </span>
                    <span class="row-value">&rarr;</span>
                </div>
                <div class="drawer-row" style="cursor:pointer;" onclick="window.location.href='{{ url('/api-docs') }}'">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:6px;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        API Documentation
                    </span>
                    <span class="row-value">&rarr;</span>
                </div>
                @if ($user->isOwner())
                <div class="drawer-row" style="cursor:pointer;" onclick="window.location.href='{{ url('/logistics') }}'">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:6px;">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                        Logistics Dashboard
                    </span>
                    <span class="row-value">&rarr;</span>
                </div>
                @endif
            </div>

            {{-- Preferences --}}
            <div class="drawer-section">
                <h3>Preferences</h3>
                <div class="drawer-row">
                    <span>Currency</span>
                    <span class="row-value">Philippine Peso (&#8369;)</span>
                </div>
                <div class="drawer-row">
                    <span>Language</span>
                    <span class="row-value">English</span>
                </div>
                <div class="drawer-row">
                    <span>Notifications</span>
                    <span class="row-value">Enabled</span>
                </div>
                <div class="drawer-row">
                    <span>Timezone</span>
                    <span class="row-value">Asia/Manila (PHT)</span>
                </div>
            </div>

            {{-- Logout --}}
            <div class="logout-section">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Log Out
                    </button>
                </form>
            </div>

        </div>
    </div>

    <div class="dismiss-hint">Click outside to close</div>

@endsection
