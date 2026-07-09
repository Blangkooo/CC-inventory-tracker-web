<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - NITA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #5C2D1B;
        }

        .split {
            display: grid;
            grid-template-columns: 1fr;
            min-height: 100vh;
        }

        @media (min-width: 768px) {
            .split { grid-template-columns: 1fr 1fr; }
        }

        /* ── Left Panel: Topographic Pattern ── */
        .panel-left {
            display: none;
            background: #FDF5D6;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 768px) { .panel-left { display: block; } }

        .topographic-grid {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-template-rows: repeat(12, 1fr);
            gap: 0;
            width: 100%;
            height: 100%;
        }

        .topo-block {
            width: 100%;
            height: 100%;
            border: 1px solid rgba(188, 97, 75, 0.15);
        }
        .topo-block.solid { background: rgba(188, 97, 75, 0.08); border-color: rgba(188, 97, 75, 0.25); }
        .topo-block.wave-h { border-top: 3px solid rgba(188, 97, 75, 0.2); border-bottom: 3px solid rgba(188, 97, 75, 0.2); }
        .topo-block.wave-v { border-left: 3px solid rgba(188, 97, 75, 0.2); border-right: 3px solid rgba(188, 97, 75, 0.2); }
        .topo-block.dense { background: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(188, 97, 75, 0.06) 3px, rgba(188, 97, 75, 0.06) 6px); }
        .topo-block.dense-h { background: repeating-linear-gradient(90deg, transparent, transparent 3px, rgba(188, 97, 75, 0.06) 3px, rgba(188, 97, 75, 0.06) 6px); }

        /* ── Right Panel: Form ── */
        .panel-right {
            background: #FDF5D6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
        }

        .form-card {
            width: 100%;
            max-width: 420px;
        }

        .logo-area {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .logo-area .logo-svg {
            width: 140px;
            height: auto;
            flex-shrink: 0;
        }

        .form-card h1 {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: #5C2D1B;
            margin-bottom: 6px;
        }

        .form-subtitle {
            text-align: center;
            font-size: 13px;
            color: #5C2D1B;
            opacity: 0.7;
            margin-bottom: 24px;
        }

        .field { margin-bottom: 16px; }

        .field-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #5C2D1B;
            margin-bottom: 6px;
        }

        .field-label .info-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ef4444;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            cursor: help;
            position: relative;
        }

        .input-block {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            background: #ffffff;
            border: 1.5px solid #5C2D1B;
            border-radius: 10px;
            font-size: 15px;
            color: #5C2D1B;
            font-family: inherit;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .input-block::placeholder { color: #a0897a; }

        .input-block:focus {
            outline: none;
            border-color: #BC614B;
            box-shadow: 0 0 0 3px rgba(188, 97, 75, 0.12);
        }

        select.input-block {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='10' viewBox='0 0 14 10'%3E%3Cpath fill='%235C2D1B' d='M7 10L0 0h14z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 14px 10px;
            padding-right: 40px;
            cursor: pointer;
            -webkit-appearance: none;
            appearance: none;
        }

        select.input-block option {
            color: #5C2D1B;
            background: #ffffff;
        }

        /* ── Tooltip ── */
        .tooltip-container { position: relative; margin-bottom: 4px; }

        .tooltip-card {
            display: none;
            background: #fef5f0;
            border: 1px solid #BC614B;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12px;
            color: #5C2D1B;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .tooltip-card.visible { display: block; }

        /* ── Buttons ── */
        .btn-done {
            display: block;
            width: 100%;
            height: 48px;
            background: #ffffff;
            color: #5C2D1B;
            border: 1.5px solid #5C2D1B;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-top: 8px;
        }

        .btn-done:hover {
            background: #5C2D1B;
            color: #FDF5D6;
        }

        .form-footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #5C2D1B;
        }

        .form-footer-link a {
            color: #BC614B;
            font-weight: 600;
            text-decoration: none;
        }
        .form-footer-link a:hover { text-decoration: underline; }

        .error-box {
            margin-bottom: 16px;
            padding: 12px 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            font-size: 13px;
            color: #991b1b;
            text-align: center;
        }


    </style>
</head>
<body>
    <div class="split">
        {{-- Left Panel: Topographic Pattern --}}
        <div class="panel-left">
            <div class="topographic-grid">
                @php
                    $pattern = [
                        ['','','','wave-h','','','dense','','','wave-v','',''],
                        ['','solid','','','','wave-v','','dense-h','','','',''],
                        ['','','','dense','','','','','wave-v','','dense',''],
                        ['wave-v','','solid','','','dense-h','','solid','','','','wave-h'],
                        ['','','','','','','','','','dense-h','',''],
                        ['dense','','wave-v','','solid','','','wave-h','','','',''],
                        ['','','','','','wave-h','','','','solid','',''],
                        ['','solid','','','','','dense','','','','wave-v',''],
                        ['','','','wave-v','','','','','dense-h','','',''],
                        ['wave-h','','','','dense-h','','solid','','','','',''],
                        ['','','dense','','','','','','wave-v','','solid',''],
                        ['','','','','wave-v','','dense-h','','','','',''],
                    ];
                @endphp
                @foreach ($pattern as $row)
                    @foreach ($row as $cell)
                        <span class="topo-block {{ $cell }}"></span>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Right Panel: Form --}}
        <div class="panel-right">
            <div class="form-card">
                <div class="logo-area">
                    <img class="logo-svg" src="{{ asset('images/logo.svg') }}" alt="NITA Logo">
                </div>

                <h1>Welcome back!</h1>
                <p class="form-subtitle">Please supply the needed information to Log In</p>

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="field">
                        <div class="field-label">Full Name</div>
                        <input type="text" class="input-block" name="full_name" placeholder="Enter your full name">
                    </div>

                    <div class="field">
                        <div class="field-label">Email Address</div>
                        <input type="email" class="input-block" name="email" placeholder="Enter a valid email address" required>
                    </div>

                    <div class="field">
                        <div class="field-label">
                            AdminID
                            <span class="info-icon" onmouseenter="document.getElementById('adminid-tooltip').classList.add('visible')" onmouseleave="document.getElementById('adminid-tooltip').classList.remove('visible')">?</span>
                        </div>
                        <div class="tooltip-container">
                            <div class="tooltip-card" id="adminid-tooltip">
                                AdminID: A unique ID given by NITA to business owners and managers.
                            </div>
                            <input type="text" class="input-block" name="admin_id" placeholder="Enter your unique AdminID">
                        </div>
                    </div>

                    <div class="field">
                        <div class="field-label">Password</div>
                        <input type="password" class="input-block" name="password" placeholder="Enter your password" required>
                    </div>

                    @if ($errors->any())
                        <div class="error-box">
                            @foreach ($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="btn-done">Done</button>

                    <div class="form-footer-link">
                        Don't have an account yet? <a href="{{ url('/auth/register/step-1') }}">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>


</body>
</html>
