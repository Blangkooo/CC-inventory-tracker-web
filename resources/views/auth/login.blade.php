<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — NITA</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --cream: #FFFFFF;
            --brown:  #5C2D1B;
            --terra:  #BC614B;
            --border: rgba(92,45,27,.16);
            --font:   -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body { font-family: var(--font); color: var(--brown); }

        /* ══ SPLIT LAYOUT ══ */
        .split {
            display: grid;
            grid-template-columns: 1fr;
            min-height: 100vh;
        }

        @media (min-width: 768px) {
            .split { grid-template-columns: 1fr 1fr; }
        }

        /* ══ LEFT PANEL ══ */
        .panel-left {
            display: none;
            background: var(--cream);
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 768px) { .panel-left { display: block; } }

        .topo-grid {
            position: absolute; inset: 0;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-template-rows: repeat(12, 1fr);
            width: 100%; height: 100%;
        }

        .tb { width: 100%; height: 100%; border: 1px solid rgba(188,97,75,.12); }
        .tb.s  { background: rgba(188,97,75,.07); border-color: rgba(188,97,75,.22); }
        .tb.wh { border-top: 2.5px solid rgba(188,97,75,.18); border-bottom: 2.5px solid rgba(188,97,75,.18); }
        .tb.wv { border-left: 2.5px solid rgba(188,97,75,.18); border-right: 2.5px solid rgba(188,97,75,.18); }
        .tb.d  { background: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(188,97,75,.05) 3px, rgba(188,97,75,.05) 6px); }
        .tb.dh { background: repeating-linear-gradient(90deg, transparent, transparent 3px, rgba(188,97,75,.05) 3px, rgba(188,97,75,.05) 6px); }

        /* Brand overlay on left panel */
        .panel-brand {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 12px; padding: 40px;
        }

        .panel-brand-logo {
            width: 120px; filter: drop-shadow(0 2px 8px rgba(92,45,27,.12));
        }

        .panel-brand-tagline {
            font-size: 13px; font-weight: 600; color: var(--brown);
            opacity: .5; letter-spacing: .04em; text-align: center;
        }

        /* ══ RIGHT PANEL ══ */
        .panel-right {
            background: var(--cream);
            display: flex; align-items: center; justify-content: center;
            padding: 48px 24px;
        }

        /* ══ FORM BOX ══ */
        .form-box {
            width: 100%; max-width: 400px;
            background: #fff; border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(92,45,27,.06), 0 8px 24px rgba(92,45,27,.08);
            padding: 36px 32px;
        }

        .logo-area {
            display: flex; justify-content: center; margin-bottom: 28px;
        }

        .logo-area img { height: 32px; }

        .form-box h1 {
            font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 4px;
        }

        .form-subtitle {
            font-size: 13px; opacity: .5; text-align: center; margin-bottom: 24px;
        }

        /* ══ FIELDS ══ */
        .field { margin-bottom: 14px; }

        .field-label {
            font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; opacity: .6; margin-bottom: 6px;
        }

        .input-block {
            width: 100%; height: 46px; padding: 0 14px;
            background: var(--cream); border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px; color: var(--brown);
            font-family: var(--font);
            transition: border-color .15s, box-shadow .15s;
        }

        .input-block::placeholder { color: rgba(92,45,27,.35); }

        .input-block:focus {
            outline: none; border-color: var(--terra);
            box-shadow: 0 0 0 3px rgba(188,97,75,.12);
        }

        /* ══ ERROR ══ */
        .error-box {
            margin-bottom: 14px; padding: 11px 14px;
            background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 10px; font-size: 13px; color: #991b1b;
            text-align: center;
        }

        /* ══ BUTTON ══ */
        .btn-primary {
            display: block; width: 100%; height: 46px;
            background: var(--terra); color: #fff;
            border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; font-family: var(--font);
            cursor: pointer; transition: background .15s; margin-top: 6px;
        }

        .btn-primary:hover { background: #a8523e; }

        /* ══ FOOTER LINK ══ */
        .form-footer {
            text-align: center; margin-top: 18px;
            font-size: 13px; opacity: .65;
        }

        .form-footer a { color: var(--terra); font-weight: 600; text-decoration: none; opacity: 1; }
        .form-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="split">

        {{-- Left Panel --}}
        <div class="panel-left">
            <div class="topo-grid">
                @php $topo = [
                    ['','','','wh','','','d','','','wv','',''],
                    ['','s','','','','wv','','dh','','','',''],
                    ['','','','d','','','','','wv','','d',''],
                    ['wv','','s','','','dh','','s','','','','wh'],
                    ['','','','','','','','','','dh','',''],
                    ['d','','wv','','s','','','wh','','','',''],
                    ['','','','','','wh','','','','s','',''],
                    ['','s','','','','','d','','','','wv',''],
                    ['','','','wv','','','','','dh','','',''],
                    ['wh','','','','dh','','s','','','','',''],
                    ['','','d','','','','','','wv','','s',''],
                    ['','','','','wv','','dh','','','','',''],
                ]; @endphp
                @foreach ($topo as $row)
                    @foreach ($row as $c)
                        <span class="tb {{ $c }}"></span>
                    @endforeach
                @endforeach
            </div>
            <div class="panel-brand">
                <img class="panel-brand-logo" src="{{ asset('images/logo.svg') }}" alt="NITA">
                <span class="panel-brand-tagline">Inventory Intelligence for<br>Philippine Micro-Franchises</span>
            </div>
        </div>

        {{-- Right Panel --}}
        <div class="panel-right">
            <div class="form-box">
                <div class="logo-area">
                    <img src="{{ asset('images/logo.svg') }}" alt="NITA">
                </div>

                <h1>Welcome back</h1>
                <p class="form-subtitle">Sign in to your account to continue</p>

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    @if ($errors->any())
                        <div class="error-box">
                            @foreach ($errors->all() as $error){{ $error }}@endforeach
                        </div>
                    @endif

                    <div class="field">
                        <div class="field-label">Email Address</div>
                        <input type="email" class="input-block" name="email"
                               placeholder="you@example.com" required
                               value="{{ old('email') }}">
                    </div>

                    <div class="field">
                        <div class="field-label">Password</div>
                        <input type="password" class="input-block" name="password"
                               placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn-primary">Sign In</button>
                </form>

                <div class="form-footer">
                    Don't have an account? <a href="{{ url('/auth/register/step-1') }}">Sign Up</a>
                </div>
                <div class="form-footer" style="margin-top:6px;">
                    Staff member? <a href="{{ route('staff.login') }}">Clock in with Worker ID</a>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
