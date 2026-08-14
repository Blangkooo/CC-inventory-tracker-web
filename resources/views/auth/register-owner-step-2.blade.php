<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Documents — NITA</title>
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
        .step-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: .4; text-align: center; margin-bottom: 16px; }

        .form-box h1 { font-size: 20px; font-weight: 800; text-align: center; margin-bottom: 4px; }
        .form-subtitle { font-size: 13px; opacity: .5; text-align: center; margin-bottom: 22px; }

        .field { margin-bottom: 14px; }
        .field-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; opacity: .6; margin-bottom: 6px; }

        .input-block {
            width: 100%; height: 46px; padding: 0 14px;
            background: var(--cream); border: 1.5px solid var(--border);
            border-radius: 10px; font-size: 14px; color: var(--brown); font-family: var(--font);
            transition: border-color .15s, box-shadow .15s;
        }

        .input-block::placeholder { color: rgba(92,45,27,.35); }
        .input-block:focus { outline: none; border-color: var(--terra); box-shadow: 0 0 0 3px rgba(188,97,75,.12); }

        /* File upload row */
        .upload-block {
            width: 100%; height: 46px; display: flex; align-items: center;
            justify-content: space-between; padding: 0 14px;
            background: var(--cream); border: 1.5px solid var(--border);
            border-radius: 10px; cursor: pointer; transition: border-color .15s;
        }

        .upload-block:hover { border-color: var(--terra); }

        .upload-block .upload-name { font-size: 14px; color: rgba(92,45,27,.4); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .upload-block .upload-name.has-file { color: var(--brown); font-weight: 500; }

        .upload-icon {
            width: 28px; height: 28px; border-radius: 6px;
            background: rgba(188,97,75,.1); display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-left: 8px;
        }

        .upload-block input[type="file"] { display: none; }

        /* Nav row */
        .nav-row { display: flex; align-items: center; gap: 10px; margin-top: 20px; }

        .btn-back {
            height: 46px; padding: 0 20px;
            background: transparent; color: var(--brown);
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; font-weight: 600; font-family: var(--font);
            cursor: pointer; transition: all .15s; text-decoration: none;
            display: flex; align-items: center; white-space: nowrap;
        }

        .btn-back:hover { background: rgba(92,45,27,.05); border-color: var(--brown); }

        .btn-primary {
            flex: 1; height: 46px;
            background: var(--terra); color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; font-family: var(--font);
            cursor: pointer; transition: background .15s;
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
                    <div class="step-dot done">✓</div>
                    <div class="step-line"></div>
                    <div class="step-dot active">2</div>
                    <div class="step-line"></div>
                    <div class="step-dot">3</div>
                </div>
                <div class="step-label">Step 2 of 3 — Business Registration</div>

                <h1>Register your business</h1>
                <p class="form-subtitle">Upload the required government documents</p>

                <form method="POST" action="{{ url('/api/auth/register/step-2') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="email" value="{{ request('email', '') }}">

                    <div class="field">
                        <div class="field-label">Business Name</div>
                        <input type="text" class="input-block" name="business_name" placeholder="Your business name" required>
                    </div>

                    @foreach ([
                        ['dti_registration',  'DTI Registration'],
                        ['sec_registration',  'SEC Registration'],
                        ['bir_registration',  'BIR Registration'],
                        ['lgu_permit',        'LGU Permit'],
                    ] as [$name, $label])
                        <div class="field">
                            <div class="field-label">{{ $label }}</div>
                            <label class="upload-block">
                                <span class="upload-name" data-field="{{ $name }}">Upload {{ $label }} document</span>
                                <span class="upload-icon">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="17 8 12 3 7 8"/>
                                        <line x1="12" y1="3" x2="12" y2="15"/>
                                    </svg>
                                </span>
                                <input type="file" name="{{ $name }}" data-label="{{ $label }}">
                            </label>
                        </div>
                    @endforeach

                    <div class="nav-row">
                        <a href="{{ url('/auth/register/step-1') }}" class="btn-back">Back</a>
                        <button type="submit" class="btn-primary">Continue</button>
                    </div>
                </form>

                <div class="form-footer">
                    Already have an account? <a href="{{ url('/auth/login') }}">Sign In</a>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.querySelectorAll('.upload-block').forEach(function (block) {
            const input = block.querySelector('input[type="file"]');
            const nameEl = block.querySelector('.upload-name');
            input.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    nameEl.textContent = this.files[0].name;
                    nameEl.classList.add('has-file');
                }
            });
        });
    </script>
</body>
</html>
