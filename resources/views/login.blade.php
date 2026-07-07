<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - NITA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #fdf3d0;
            color: #4a1d18;
        }

        .split {
            display: flex;
            min-height: 100vh;
        }

        /* Left navy panel */
        .panel-left {
            width: 50%;
            background: #7b2d26;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            padding: 64px;
        }

        .panel-left .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-left .logo-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #a03d2e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
            flex-shrink: 0;
        }

        .panel-left .app-name {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
        }

        .panel-left .app-role {
            font-size: 9px;
            font-weight: 400;
            color: #f3dfc0;
        }

        .panel-left .middle {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .panel-left .tagline {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            max-width: 360px;
            margin-bottom: 12px;
        }

        .panel-left .sub-tagline {
            font-size: 13px;
            font-weight: 400;
            color: #f3dfc0;
        }

        .panel-left .footer {
            margin-top: auto;
        }

        .panel-left .footer-line {
            width: 64px;
            height: 3px;
            background: #a03d2e;
            margin-bottom: 12px;
        }

        .panel-left .footer-text {
            font-size: 10px;
            color: #f3dfc0;
            margin-bottom: 20px;
        }

        .panel-left .footer-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .panel-left .footer-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #63241f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
            flex-shrink: 0;
        }

        .panel-left .footer-user-name {
            font-size: 12px;
            font-weight: 600;
            color: #ffffff;
        }

        .panel-left .footer-user-email {
            font-size: 11px;
            color: #f3dfc0;
        }

        /* Right form panel */
        .panel-right {
            width: 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
        }

        .login-card h1 {
            font-size: 26px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .login-card .subtitle {
            color: #6b7380;
            font-size: 13px;
            margin-bottom: 28px;
        }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 13px;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            color: #111827;
            margin-bottom: 6px;
        }

        .input-wrap input {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border: 1px solid #e9d9b6;
            border-radius: 8px;
            font-size: 15px;
            background: #fdf8ec;
        }

        .input-wrap input:focus {
            outline: none;
            border-color: #a03d2e;
            background: #ffffff;
        }

        .row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #374151;
        }

        .forgot {
            color: #a03d2e;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-signin {
            width: 100%;
            height: 48px;
            background: #a03d2e;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-signin:hover {
            background: #7b2d26;
        }

        .divider-line {
            border: none;
            border-top: 1px solid #e9d9b6;
            margin: 24px 0;
        }

        .footer-note {
            font-size: 11px;
            color: #9ca3b0;
            text-align: center;
        }

        @media (max-width: 820px) {
            .panel-left {
                display: none;
            }
            .panel-right {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="split">
        <div class="panel-left">
            <div class="brand">
                <div class="logo-circle">&#127978;</div>
                <div>
                    <div class="app-name">NITA</div>
                    <div class="app-role">Inventory Tracker</div>
                </div>
            </div>

            <div class="middle">
                <div class="tagline">Multi-Branch Inventory Tracker</div>
                <div class="sub-tagline">Anti-theft. Offline-first. Real-time.</div>
            </div>

            <div class="footer">
                <div class="footer-line"></div>
                <div class="footer-text">&copy; 2026 NITA &middot; Certicode</div>

                <div class="footer-user">
                    <div class="footer-avatar">O</div>
                    <div>
                        <div class="footer-user-name">Owner</div>
                        <div class="footer-user-email">owner@inventory.test</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-right">
            <div class="login-card">
                <h1>Welcome Back!</h1>
                <p class="subtitle">Sign in to your owner account</p>

                @if ($errors->any())
                    <div class="error-box">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email address</label>
                        <div class="input-wrap">
                            <input type="email" id="email" name="email" placeholder="owner@inventory.test"
                                   value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <input type="password" id="password" name="password" placeholder="••••••••••••" required>
                        </div>
                    </div>

                    <div class="row-between">
                        <label class="remember">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="#" class="forgot">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-signin">Sign In</button>
                </form>

                <hr class="divider-line">

                <p class="footer-note">Need help? Contact admin</p>
            </div>
        </div>
    </div>
</body>
</html>
