<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - NITA</title>
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
            max-width: 460px;
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
            font-size: 20px;
            font-weight: 700;
            color: #5C2D1B;
            margin-bottom: 16px;
            line-height: 1.3;
        }

        .instruction-block {
            text-align: center;
            font-size: 13px;
            color: #5C2D1B;
            opacity: 0.8;
            margin-bottom: 8px;
            padding: 10px 14px;
            background: rgba(188, 97, 75, 0.06);
            border-radius: 8px;
        }

        .list-section {
            margin-bottom: 20px;
        }

        .list-section-title {
            font-size: 13px;
            font-weight: 700;
            color: #5C2D1B;
            margin-bottom: 10px;
        }

        .dot-list {
            list-style: none;
            padding: 0;
        }

        .dot-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            font-size: 14px;
            color: #5C2D1B;
            border-bottom: 1px solid rgba(92, 45, 27, 0.08);
        }

        .dot-list li:last-child {
            border-bottom: none;
        }

        .dot-bullet {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #5C2D1B;
            flex-shrink: 0;
        }

        .confidential-note {
            text-align: center;
            font-size: 13px;
            color: #5C2D1B;
            opacity: 0.75;
            margin-bottom: 20px;
            line-height: 1.5;
        }

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

                <h1>Thank you for trusting us to take care of your business!</h1>

                <div class="instruction-block">
                    The following documents will be sent to your provided email address
                </div>

                <div class="list-section">
                    <div class="list-section-title">Sent to provided email:</div>
                    <ul class="dot-list">
                        <li><span class="dot-bullet"></span> Privacy Policy</li>
                        <li><span class="dot-bullet"></span> Cookie Policy</li>
                        <li><span class="dot-bullet"></span> Terms and Conditions</li>
                    </ul>
                </div>

                <div class="instruction-block">
                    By consenting, you agree to receive the following results
                </div>

                <div class="list-section">
                    <div class="list-section-title">Consenting results:</div>
                    <ul class="dot-list">
                        <li><span class="dot-bullet"></span> AdminID</li>
                        <li><span class="dot-bullet"></span> FranchiseID</li>
                    </ul>
                </div>

                <p class="confidential-note">Please make sure to keep them confidential. It's nice working with you!</p>

                <button type="button" class="btn-done" onclick="window.location.href='{{ url('/auth/login') }}'">Done</button>
            </div>
        </div>
    </div>
</body>
</html>
