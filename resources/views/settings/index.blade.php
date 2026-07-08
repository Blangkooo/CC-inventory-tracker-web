<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - NITA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #FDF5D6;
            color: #5C2D1B;
            min-height: 100vh;
        }

        /* ── Dimmed Overlay ── */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(92, 45, 27, 0.55);
            z-index: 100;
            display: flex;
            justify-content: flex-end;
        }

        /* ── Slide-out Sidebar ── */
        .drawer {
            width: 380px;
            max-width: 90vw;
            height: 100vh;
            background: #FDF5D6;
            border-radius: 20px 0 0 20px;
            padding: 32px 28px;
            display: flex;
            flex-direction: column;
            box-shadow: -8px 0 24px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.25s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        /* ── Drawer Header ── */
        .drawer-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
            padding-bottom: 16px;
            border-bottom: 1.5px solid rgba(92, 45, 27, 0.15);
        }

        .gear-icon {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
        }

        .gear-icon svg { width: 100%; height: 100%; }

        .drawer-header h2 {
            font-size: 20px;
            font-weight: 800;
            color: #5C2D1B;
        }

        /* ── Drawer Body ── */
        .drawer-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .drawer-section {
            margin-bottom: 24px;
        }

        .drawer-section h3 {
            font-size: 13px;
            font-weight: 700;
            color: #5C2D1B;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 10px;
            opacity: 0.7;
        }

        .drawer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 14px;
            font-weight: 500;
            color: #5C2D1B;
            border-bottom: 1px solid rgba(92, 45, 27, 0.08);
            cursor: default;
        }

        .drawer-row:last-child { border-bottom: none; }

        .drawer-row .row-value {
            color: #5C2D1B;
            opacity: 0.6;
            font-size: 13px;
        }

        /* ── Profile card ── */
        .profile-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            background: rgba(188, 97, 75, 0.08);
            border: 1px solid rgba(92, 45, 27, 0.15);
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .profile-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #BC614B;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .profile-info .name {
            font-size: 15px;
            font-weight: 700;
            color: #5C2D1B;
        }

        .profile-info .email {
            font-size: 12px;
            color: #5C2D1B;
            opacity: 0.6;
        }

        /* ── Logout Button ── */
        .logout-section {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1.5px solid rgba(92, 45, 27, 0.15);
        }

        .btn-logout-drawer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 50px;
            background: transparent;
            color: #dc2626;
            border: 1.5px solid #dc2626;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-logout-drawer:hover {
            background: #dc2626;
            color: #ffffff;
        }

        .btn-logout-drawer svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }



        /* ── Dismiss hint ── */
        .dismiss-hint {
            text-align: center;
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 101;
            cursor: default;
        }
    </style>
</head>
<body>
    {{-- Underlying dashboard simulation (dimmed) --}}
    <div style="padding: 24px; opacity: 0.3; pointer-events: none; filter: blur(2px);">
        <div style="background:#FDF5D6; border:1px solid #5C2D1B; border-radius:10px; padding:14px 20px; margin-bottom:20px;">
            <div style="font-size:22px; font-weight:800; color:#5C2D1B;">DASHBOARD</div>
        </div>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
            <div style="background:#FDF5D6; border:1px solid #5C2D1B; border-radius:10px; padding:20px; text-align:center;">
                <div style="font-size:11px; font-weight:700; color:#5C2D1B; text-transform:uppercase;">Total Monthly Revenue</div>
                <div style="font-size:30px; font-weight:800; color:#5C2D1B; margin-top:8px;">$300k</div>
            </div>
            <div style="background:#FDF5D6; border:1px solid #5C2D1B; border-radius:10px; padding:20px; text-align:center;">
                <div style="font-size:11px; font-weight:700; color:#5C2D1B; text-transform:uppercase;">Overall Leakage</div>
                <div style="font-size:30px; font-weight:800; color:#5C2D1B; margin-top:8px;">20% &darr;</div>
            </div>
            <div style="background:#FDF5D6; border:1px solid #5C2D1B; border-radius:10px; padding:20px; text-align:center;">
                <div style="font-size:11px; font-weight:700; color:#5C2D1B; text-transform:uppercase;">Total Value Saved</div>
                <div style="font-size:30px; font-weight:800; color:#5C2D1B; margin-top:8px;">$80k</div>
            </div>
        </div>
    </div>

    {{─ Overlay ──}}
    <div class="overlay" onclick="window.location.href='{{ url('/dashboard') }}'">
        {{-- Click the dimmed overlay to dismiss — drawer stops propagation --}}

        {{-- Drawer --}}
        <div class="drawer" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="drawer-header">
                <div class="gear-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="3" stroke="#5C2D1B" stroke-width="2"/>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"
                            stroke="#5C2D1B" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h2>Settings</h2>
            </div>

            {{-- Body --}}
            <div class="drawer-body">
                {{-- Profile --}}
                <div class="profile-card">
                    <div class="profile-avatar">A</div>
                    <div class="profile-info">
                        <div class="name">Admin Owner</div>
                        <div class="email">admin@inventory.ph</div>
                    </div>
                </div>

                {{-- Account Section --}}
                <div class="drawer-section">
                    <h3>Account</h3>
                    <div class="drawer-row">
                        <span>Name</span>
                        <span class="row-value">Admin Owner</span>
                    </div>
                    <div class="drawer-row">
                        <span>Email</span>
                        <span class="row-value">admin@inventory.ph</span>
                    </div>
                    <div class="drawer-row">
                        <span>Role</span>
                        <span class="row-value">Owner</span>
                    </div>
                </div>

                {{-- Preferences Section --}}
                <div class="drawer-section">
                    <h3>Preferences</h3>
                    <div class="drawer-row">
                        <span>Currency</span>
                        <span class="row-value">USD ($)</span>
                    </div>
                    <div class="drawer-row">
                        <span>Language</span>
                        <span class="row-value">English</span>
                    </div>
                    <div class="drawer-row">
                        <span>Notifications</span>
                        <span class="row-value">Enabled</span>
                    </div>
                </div>

                {{-- Logout --}}
                <div class="logout-section">
                    <form method="POST" action="{{ url('/api/logout') }}" onsubmit="event.preventDefault(); alert('Sanctum placeholder: POST /api/logout — token revoked.');">
                        <button type="submit" class="btn-logout-drawer">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
    </div>

    <div class="dismiss-hint">Click the dimmed area to close</div>
</body>
</html>
