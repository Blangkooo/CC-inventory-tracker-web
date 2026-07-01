<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Inventory Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f4f6fb;
            color: #1f2433;
        }

        .split {
            display: flex;
            min-height: 100vh;
        }

        /* Left navy panel */
        .panel-left {
            width: 50%;
            background: #1e3a8a;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px;
        }

        .panel-left .logo {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 24px;
        }

        .panel-left .tagline {
            font-size: 18px;
            line-height: 1.6;
            color: #c7d2fe;
            max-width: 360px;
        }

        /* Right form panel */
        .panel-right {
            width: 50%;
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
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .login-card .subtitle {
            color: #6b7280;
            font-size: 15px;
            margin-bottom: 28px;
        }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 14px;
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
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #9aa1b1;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #e2e5f0;
            border-radius: 8px;
            font-size: 15px;
            background: #f8f9fc;
        }

        .input-wrap input:focus {
            outline: none;
            border-color: #3b5fd9;
            background: #ffffff;
        }

        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            font-weight: 600;
            color: #3b5fd9;
            background: none;
            border: none;
            cursor: pointer;
        }

        .row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #374151;
        }

        .forgot {
            color: #3b5fd9;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-signin {
            width: 100%;
            padding: 14px;
            background: #1e3a8a;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-signin:hover {
            background: #3b5fd9;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #9aa1b1;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.05em;
            margin: 24px 0;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #e7eaf3;
        }

        .divider::before {
            margin-right: 12px;
        }

        .divider::after {
            margin-left: 12px;
        }

        .btn-google {
            width: 100%;
            padding: 12px;
            background: #ffffff;
            border: 1px solid #e2e5f0;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-google .g {
            font-weight: 800;
            color: #4285f4;
        }

        .footer-note {
            margin-top: 28px;
            font-size: 12px;
            color: #9aa1b1;
            text-align: center;
            line-height: 1.5;
        }

        .footer-note a {
            color: #6b7280;
            text-decoration: underline;
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
            <div class="logo">Inventory Tracker</div>
            <div class="tagline">Monitor your branches, recipes, and stock in real time &mdash; all in one place.</div>
        </div>

        <div class="panel-right">
            <div class="login-card">
                <h1>Welcome Back!</h1>
                <p class="subtitle">Sign in to continue to your dashboard</p>

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
                        <label for="email">Email</label>
                        <div class="input-wrap">
                            <span class="icon">&#9993;</span>
                            <input type="email" id="email" name="email" placeholder="you@example.com"
                                   value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <span class="icon">&#128274;</span>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="toggle-pw" onclick="togglePassword()">Show</button>
                        </div>
                    </div>

                    <div class="row-between">
                        <label class="remember">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="#" class="forgot">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-signin">Sign In</button>
                </form>

                <div class="divider">OR CONTINUE WITH</div>

                <button type="button" class="btn-google"><span class="g">G</span> Sign in with Google</button>

                <p class="footer-note">
                    By signing the account, you accept our
                    <a href="#">Terms &amp; Conditions</a> and <a href="#">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var input = document.getElementById('password');
            var btn = document.querySelector('.toggle-pw');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Hide';
            } else {
                input.type = 'password';
                btn.textContent = 'Show';
            }
        }
    </script>
</body>
</html>
