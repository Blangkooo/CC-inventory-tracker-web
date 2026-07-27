<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — NITA</title>
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
            width: 100%; max-width: 400px;
            background: #fff; border: 1px solid var(--border); border-radius: 16px;
            box-shadow: 0 1px 3px rgba(92,45,27,.06), 0 8px 24px rgba(92,45,27,.08);
            padding: 36px 32px;
        }

        .logo-area { display: flex; justify-content: center; margin-bottom: 24px; }
        .logo-area img { height: 32px; }

        /* Step indicator */
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

        .step-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: .4; text-align: center; margin-bottom: 16px; }

        .form-box h1 { font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 4px; }
        .form-subtitle { font-size: 13px; opacity: .5; text-align: center; margin-bottom: 22px; }

        .field { margin-bottom: 14px; }
        .field-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: .6; margin-bottom: 6px; }

        .input-block {
            width: 100%; height: 46px; padding: 0 14px;
            background: var(--cream); border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px; color: var(--brown); font-family: var(--font);
            transition: border-color .15s, box-shadow .15s; -webkit-appearance: none; appearance: none;
        }

        .input-block::placeholder { color: rgba(92,45,27,.35); }
        .input-block:focus { outline: none; border-color: var(--terra); box-shadow: 0 0 0 3px rgba(188,97,75,.12); }

        select.input-block {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%235C2D1B' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center;
            background-size: 12px; padding-right: 36px; cursor: pointer;
        }

        .btn-primary {
            display: block; width: 100%; height: 46px;
            background: var(--terra); color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; font-family: var(--font);
            cursor: pointer; transition: background .15s; margin-top: 6px;
        }

        .btn-primary:hover { background: #a8523e; }

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
                    <div class="step-dot active">1</div>
                    <div class="step-line"></div>
                    <div class="step-dot">2</div>
                    <div class="step-line"></div>
                    <div class="step-dot">3</div>
                </div>
                <div class="step-label">Step 1 of 3 — Your Details</div>

                <h1>Create an account</h1>
                <p class="form-subtitle">Tell us who you are to get started</p>

                <form method="GET" action="{{ url('/auth/register/step-2') }}" id="role-form">
                    <div class="field">
                        <div class="field-label">Full Name</div>
                        <input type="text" class="input-block" name="full_name" placeholder="Your full name" required>
                    </div>

                    <div class="field">
                        <div class="field-label">Email Address</div>
                        <input type="email" class="input-block" name="email" placeholder="you@example.com" required>
                    </div>

                    <div class="field">
                        <div class="field-label">Contact Number</div>
                        <input type="tel" class="input-block" name="contact_number" placeholder="+63 9xx xxx xxxx" required>
                    </div>

                    <div class="field">
                        <div class="field-label">Your Role</div>
                        <select class="input-block" name="role" id="role-select" required>
                            <option value="" disabled selected>Select your role</option>
                            <option value="owner">Business Owner</option>
                            <option value="manager">Branch Manager</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">Continue</button>
                </form>

                <div class="form-footer">
                    Already have an account? <a href="{{ url('/auth/login') }}">Sign In</a>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.getElementById('role-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const role = document.getElementById('role-select').value;
            if (!role) return;
            const params = new URLSearchParams(new FormData(this));
            window.location.href = (role === 'owner'
                ? "{{ url('/auth/register/owner/step-2') }}"
                : "{{ url('/auth/register/manager/step-2') }}"
            ) + '?' + params.toString();
        });
    </script>
</body>
</html>
