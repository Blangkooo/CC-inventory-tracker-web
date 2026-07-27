<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Registration — NITA</title>
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

        .form-box h1 { font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 8px; }
        .form-subtitle { font-size: 13px; opacity: .5; text-align: center; margin-bottom: 24px; line-height: 1.5; }

        /* Checklist */
        .check-list { list-style: none; margin-bottom: 20px; }

        .check-list li {
            padding: 10px 0; border-bottom: 1px solid rgba(92,45,27,.06);
        }

        .check-list li:last-child { border-bottom: none; }

        .check-row {
            display: flex; align-items: center; gap: 12px;
            cursor: pointer; user-select: none; font-size: 14px;
        }

        .check-input { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }

        .check-box {
            width: 22px; height: 22px; border-radius: 50%; flex-shrink: 0;
            background: rgba(92,45,27,.08); display: flex; align-items: center;
            justify-content: center; transition: background .15s;
        }

        .check-box::after {
            content: ''; width: 8px; height: 8px; border-radius: 50%;
            background: #fff; transform: scale(0); transition: transform .15s;
        }

        .check-input:checked + .check-box { background: var(--terra); }
        .check-input:checked + .check-box::after { transform: scale(1); }
        .check-input:focus-visible + .check-box { outline: 2px solid var(--terra); outline-offset: 3px; }

        .consent-note {
            font-size: 12px; opacity: .5; text-align: center;
            margin-bottom: 20px; line-height: 1.5; padding: 10px 12px;
            background: rgba(188,97,75,.05); border-radius: 8px;
        }

        .btn-primary {
            display: block; width: 100%; height: 46px;
            background: var(--terra); color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; font-family: var(--font);
            cursor: pointer; transition: background .15s;
        }

        .btn-primary:hover { background: #a8523e; }
        .btn-primary:disabled { opacity: .45; cursor: not-allowed; }

        .form-footer { text-align: center; margin-top: 18px; font-size: 13px; opacity: .65; }
        .form-footer a { color: var(--terra); font-weight: 600; text-decoration: none; opacity: 1; }
        .form-footer a:hover { text-decoration: underline; }
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
                <div class="step-label">Step 3 of 3 — Confirm</div>

                <h1>One last step</h1>
                <p class="form-subtitle">Review and confirm all documents before completing your registration.</p>

                <form method="POST" action="{{ url('/api/auth/register/confirm') }}">
                    @csrf
                    <input type="hidden" name="owner_id" value="mock-owner-001">

                    <ul class="check-list">
                        @foreach ([
                            ['owner_id_confirmed',             'Owner ID'],
                            ['permit_validity',                'Permit / Validity'],
                            ['terms_accepted',                 'Terms of Service'],
                            ['legal_papers_submitted',         'Legal Papers'],
                            ['legal_papers_secondary_submitted','Legal Papers (Secondary)'],
                        ] as [$name, $label])
                            <li>
                                <label class="check-row">
                                    <input type="checkbox" name="{{ $name }}" value="1" checked class="check-input">
                                    <span class="check-box"></span>
                                    <span>{{ $label }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>

                    <div class="consent-note">
                        Please review carefully before finishing. It's great working with you!
                    </div>

                    <button type="submit" class="btn-primary" id="finish-btn">Finish Registration</button>
                </form>

                <div class="form-footer">
                    Already have an account? <a href="{{ url('/auth/login') }}">Sign In</a>
                </div>
            </div>
        </div>

    </div>

    <script>
        (function () {
            const checkboxes = document.querySelectorAll('.check-input');
            const btn = document.getElementById('finish-btn');

            function update() {
                btn.disabled = !Array.from(checkboxes).every(function (cb) { return cb.checked; });
            }

            checkboxes.forEach(function (cb) { cb.addEventListener('change', update); });
            update();
        })();
    </script>
</body>
</html>
