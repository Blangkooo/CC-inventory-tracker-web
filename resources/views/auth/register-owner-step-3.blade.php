<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete — NITA</title>
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

        .split { display: grid; grid-template-columns: 1fr; min-height: 100vh; }
        @media (min-width: 768px) { .split { grid-template-columns: 1fr 1fr; } }

        .panel-left { display: none; background: var(--cream); position: relative; overflow: hidden; }
        @media (min-width: 768px) { .panel-left { display: block; } }

        .topo-grid {
            position: absolute; inset: 0;
            display: grid; grid-template-columns: repeat(12, 1fr); grid-template-rows: repeat(12, 1fr);
            width: 100%; height: 100%;
        }

        .tb { width: 100%; height: 100%; border: 1px solid rgba(188,97,75,.12); }
        .tb.s  { background: rgba(188,97,75,.07); border-color: rgba(188,97,75,.22); }
        .tb.wh { border-top: 2.5px solid rgba(188,97,75,.18); border-bottom: 2.5px solid rgba(188,97,75,.18); }
        .tb.wv { border-left: 2.5px solid rgba(188,97,75,.18); border-right: 2.5px solid rgba(188,97,75,.18); }
        .tb.d  { background: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(188,97,75,.05) 3px, rgba(188,97,75,.05) 6px); }
        .tb.dh { background: repeating-linear-gradient(90deg, transparent, transparent 3px, rgba(188,97,75,.05) 3px, rgba(188,97,75,.05) 6px); }

        .panel-brand {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 12px; padding: 40px;
        }

        .panel-brand img { width: 120px; filter: drop-shadow(0 2px 8px rgba(92,45,27,.12)); }
        .panel-brand p { font-size: 13px; font-weight: 600; opacity: .5; letter-spacing: .04em; text-align: center; }

        .panel-right { background: var(--cream); display: flex; align-items: center; justify-content: center; padding: 48px 24px; }

        .form-box {
            width: 100%; max-width: 420px;
            background: #fff; border: 1px solid var(--border); border-radius: 16px;
            box-shadow: 0 1px 3px rgba(92,45,27,.06), 0 8px 24px rgba(92,45,27,.08);
            padding: 36px 32px;
        }

        .logo-area { display: flex; justify-content: center; margin-bottom: 24px; }
        .logo-area img { height: 32px; }

        .steps { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 24px; }
        .step-dot {
            width: 28px; height: 28px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
            background: rgba(92,45,27,.08); color: rgba(92,45,27,.4);
        }
        .step-dot.active { background: var(--terra); color: #fff; }
        .step-dot.done   { background: #16a34a; color: #fff; }
        .step-line { width: 24px; height: 1.5px; background: var(--border); }
        .step-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: .4; text-align: center; margin-bottom: 20px; }

        .check-icon {
            width: 56px; height: 56px; border-radius: 50%;
            background: #dcfce7; display: flex; align-items: center;
            justify-content: center; margin: 0 auto 16px;
        }

        .form-box h1 { font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 8px; line-height: 1.3; }
        .form-subtitle { font-size: 13px; opacity: .5; text-align: center; margin-bottom: 24px; line-height: 1.5; }

        .info-block {
            background: rgba(188,97,75,.06); border: 1px solid rgba(188,97,75,.15);
            border-radius: 10px; padding: 14px 16px; margin-bottom: 14px;
        }

        .info-block h3 { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; opacity: .5; margin-bottom: 10px; }

        .dot-list { list-style: none; }

        .dot-list li {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 0; font-size: 13px;
            border-bottom: 1px solid rgba(92,45,27,.06);
        }

        .dot-list li:last-child { border-bottom: none; }

        .dot-bullet { width: 6px; height: 6px; border-radius: 50%; background: var(--terra); flex-shrink: 0; }

        .btn-primary {
            display: block; width: 100%; height: 46px;
            background: var(--terra); color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; font-family: var(--font);
            cursor: pointer; transition: background .15s; margin-top: 20px;
        }

        .btn-primary:hover { background: #a8523e; }
    </style>
</head>
<body>
    <div class="split">

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
                    @foreach ($row as $c)<span class="tb {{ $c }}"></span>@endforeach
                @endforeach
            </div>
            <div class="panel-brand">
                <img src="{{ asset('images/logo.svg') }}" alt="NITA">
                <p>Inventory Intelligence for<br>Philippine Micro-Franchises</p>
            </div>
        </div>

        <div class="panel-right">
            <div class="form-box">
                <div class="logo-area">
                    <img src="{{ asset('images/logo.svg') }}" alt="NITA">
                </div>

                <div class="steps">
                    <div class="step-dot done">✓</div>
                    <div class="step-line"></div>
                    <div class="step-dot done">✓</div>
                    <div class="step-line"></div>
                    <div class="step-dot active">3</div>
                </div>
                <div class="step-label">Step 3 of 3 — All Done</div>

                <div class="check-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>

                <h1>Thank you for trusting us!</h1>
                <p class="form-subtitle">Your registration is complete. We'll send the following to your provided email address.</p>

                <div class="info-block">
                    <h3>Sent to your email</h3>
                    <ul class="dot-list">
                        <li><span class="dot-bullet"></span>Privacy Policy</li>
                        <li><span class="dot-bullet"></span>Cookie Policy</li>
                        <li><span class="dot-bullet"></span>Terms and Conditions</li>
                    </ul>
                </div>

                <div class="info-block">
                    <h3>Your credentials</h3>
                    <ul class="dot-list">
                        <li><span class="dot-bullet"></span>AdminID — keep this confidential</li>
                        <li><span class="dot-bullet"></span>FranchiseID — keep this confidential</li>
                    </ul>
                </div>

                <button type="button" class="btn-primary" onclick="window.location.href='{{ url('/auth/login') }}'">
                    Go to Sign In
                </button>
            </div>
        </div>

    </div>
</body>
</html>
