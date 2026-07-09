<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Manager - NITA</title>
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

        .panel-left {
            display: none;
            background: #FDF5D6;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .panel-left { display: block; }
        }

        .topographic-grid {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-template-rows: repeat(12, 1fr);
            width: 100%;
            height: 100%;
        }

        .topo-block { width: 100%; height: 100%; border: 1px solid rgba(188, 97, 75, 0.15); }
        .topo-block.solid { background: rgba(188, 97, 75, 0.08); border-color: rgba(188, 97, 75, 0.25); }
        .topo-block.wave-h { border-top: 3px solid rgba(188, 97, 75, 0.2); border-bottom: 3px solid rgba(188, 97, 75, 0.2); }
        .topo-block.wave-v { border-left: 3px solid rgba(188, 97, 75, 0.2); border-right: 3px solid rgba(188, 97, 75, 0.2); }
        .topo-block.dense { background: repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(188, 97, 75, 0.06) 3px, rgba(188, 97, 75, 0.06) 6px); }
        .topo-block.dense-h { background: repeating-linear-gradient(90deg, transparent, transparent 3px, rgba(188, 97, 75, 0.06) 3px, rgba(188, 97, 75, 0.06) 6px); }

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
            margin-bottom: 32px;
        }

        .logo-area .logo-svg {
            width: 140px;
            height: auto;
            flex-shrink: 0;
        }

        .form-card h1 {
            text-align: center;
            font-size: 26px;
            font-weight: 700;
            color: #5C2D1B;
            margin-bottom: 6px;
        }

        .form-subtitle {
            text-align: center;
            font-size: 13px;
            color: #5C2D1B;
            opacity: 0.7;
            margin-bottom: 28px;
        }

        .field { margin-bottom: 16px; }

        .field-label {
            font-size: 13px;
            font-weight: 600;
            color: #5C2D1B;
            margin-bottom: 6px;
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
        .input-block:focus { outline: none; border-color: #BC614B; box-shadow: 0 0 0 3px rgba(188, 97, 75, 0.12); }

        .nav-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-top: 24px;
        }

        .nav-link {
            color: #5C2D1B;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .nav-link:hover {
            text-decoration: underline;
        }

        .btn-done {
            height: 44px;
            padding: 0 32px;
            background: #ffffff;
            color: #5C2D1B;
            border: 1.5px solid #5C2D1B;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-done:hover {
            background: #5C2D1B;
            color: #FDF5D6;
        }
    </style>
</head>
<body>
    <div class="split">
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

        <div class="panel-right">
            <div class="form-card">
                <div class="logo-area">
                    <img class="logo-svg" src="{{ asset('images/logo.svg') }}" alt="NITA Logo">
                </div>

                <h1>Branch Manager</h1>
                <p class="form-subtitle">Please supply the needed information to start the process</p>

                <form method="GET" action="{{ url('/auth/register/manager/step-3') }}">
                    <div class="field">
                        <div class="field-label">Business Name</div>
                        <input type="text" class="input-block" name="business_name" placeholder="Enter the name of the business" required>
                    </div>

                    <div class="field">
                        <div class="field-label">Branch Location</div>
                        <input type="text" class="input-block" name="branch_location" placeholder="Enter the complete address of the branch" required>
                    </div>

                    <div class="field">
                        <div class="field-label">Business Owner</div>
                        <input type="text" class="input-block" name="business_owner" placeholder="Enter the full name of the business owner" required>
                    </div>

                    <div class="nav-row">
                        <a href="{{ url('/auth/register/step-1') }}" class="nav-link">Back</a>
                        <button type="submit" class="btn-done">Done</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
