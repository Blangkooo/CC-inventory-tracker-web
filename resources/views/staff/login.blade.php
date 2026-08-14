<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Clock In — NITA</title>
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
            position: absolute; inset: 0; display: grid;
            grid-template-columns: repeat(12, 1fr); grid-template-rows: repeat(12, 1fr);
            width: 100%; height: 100%;
        }
        .tb { width: 100%; height: 100%; border: 1px solid rgba(188,97,75,.12); }
        .tb.s  { background: rgba(188,97,75,.07); border-color: rgba(188,97,75,.22); }
        .tb.wh { border-top: 2.5px solid rgba(188,97,75,.18); border-bottom: 2.5px solid rgba(188,97,75,.18); }
        .tb.wv { border-left: 2.5px solid rgba(188,97,75,.18); border-right: 2.5px solid rgba(188,97,75,.18); }
        .tb.d  { background: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(188,97,75,.05) 3px, rgba(188,97,75,.05) 6px); }
        .tb.dh { background: repeating-linear-gradient(90deg, transparent, transparent 3px, rgba(188,97,75,.05) 3px, rgba(188,97,75,.05) 6px); }

        .panel-brand {
            position: absolute; inset: 0; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 12px; padding: 40px;
        }
        .panel-brand-logo { width: 120px; filter: drop-shadow(0 2px 8px rgba(92,45,27,.12)); }
        .panel-brand-tagline { font-size: 13px; font-weight: 600; color: var(--brown); opacity: .5; letter-spacing: .04em; text-align: center; }

        .panel-right { background: var(--cream); display: flex; align-items: center; justify-content: center; padding: 48px 24px; }

        .form-box {
            width: 100%; max-width: 380px; background: #fff; border: 1px solid var(--border);
            border-radius: 16px; box-shadow: 0 1px 3px rgba(92,45,27,.06), 0 8px 24px rgba(92,45,27,.08);
            padding: 36px 32px;
        }

        .badge-area { display: flex; justify-content: center; margin-bottom: 22px; }
        .badge {
            background: var(--terra); color: #fff; font-weight: 800; font-size: 13px;
            letter-spacing: .06em; padding: 8px 18px; border-radius: 8px;
        }

        .form-box h1 { font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 4px; }
        .form-subtitle { font-size: 13px; opacity: .5; text-align: center; margin-bottom: 24px; }

        .field { margin-bottom: 14px; }
        .field-label {
            display: flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; opacity: .6; margin-bottom: 6px;
        }
        .field-label svg { opacity: .7; }

        .select-wrap { position: relative; }
        .select-wrap svg.leading {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: .55;
        }
        .select-wrap svg.trailing {
            position: absolute; right: 13px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: .45;
        }

        select.input-block { appearance: none; padding-left: 38px; padding-right: 34px; }

        .input-block {
            width: 100%; height: 46px; padding: 0 14px;
            background: var(--cream); border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px; color: var(--brown);
            font-family: var(--font); transition: border-color .15s, box-shadow .15s;
        }
        .input-block::placeholder { color: rgba(92,45,27,.35); }
        .input-block:focus { outline: none; border-color: var(--terra); box-shadow: 0 0 0 3px rgba(188,97,75,.12); }

        .error-box {
            margin-bottom: 14px; padding: 11px 14px; background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 10px; font-size: 13px; color: #991b1b; text-align: center;
        }

        .btn-primary {
            display: block; width: 100%; height: 46px; background: var(--terra); color: #fff;
            border: none; border-radius: 10px; font-size: 15px; font-weight: 700; font-family: var(--font);
            cursor: pointer; transition: background .15s; margin-top: 6px;
        }
        .btn-primary:hover { background: #a8523e; }

        .form-footer { text-align: center; margin-top: 16px; font-size: 13px; }
        .form-footer button {
            background: none; border: none; color: rgba(92,45,27,.55); font-weight: 600;
            font-family: var(--font); font-size: 13px; cursor: pointer; text-decoration: underline;
        }
        .form-footer button:hover { color: var(--terra); }
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

        <div class="panel-right">
            <div class="form-box">
                <div class="badge-area"><span class="badge">NITA</span></div>

                <h1>Welcome Back!</h1>
                <p class="form-subtitle">Sign in with your branch and Worker ID</p>

                <form method="POST" action="{{ route('staff.login.post') }}">
                    @csrf

                    @if ($errors->any())
                        <div class="error-box">
                            @foreach ($errors->all() as $error){{ $error }}@endforeach
                        </div>
                    @endif

                    <div class="field">
                        <div class="field-label">Branch</div>
                        <div class="select-wrap">
                            <svg class="leading" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9h1"/><path d="M14 9h1"/><path d="M9 13h1"/><path d="M14 13h1"/></svg>
                            <select name="branch_id" class="input-block" required>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <svg class="trailing" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                    </div>

                    <div class="field">
                        <div class="field-label">
                            Worker ID (PIN)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" title="Ask your manager if you don't know your PIN"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        </div>
                        <input type="password" class="input-block" name="pin"
                               placeholder="Enter your Worker ID" required inputmode="numeric" autofocus>
                    </div>

                    <button type="submit" class="btn-primary">Clock In</button>
                </form>

                <div class="form-footer">
                    <button type="button" onclick="alert('Demo Mode — coming soon.')">Demo Mode</button>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
