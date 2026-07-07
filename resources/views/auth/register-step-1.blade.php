<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - NITA</title>
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
            .split {
                grid-template-columns: 1fr 1fr;
            }
        }

        .panel-left {
            display: none;
            background: #FDF5D6;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .panel-left {
                display: block;
            }
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

        .topo-block {
            width: 100%;
            height: 100%;
            border: 1px solid rgba(188, 97, 75, 0.15);
        }

        .topo-block.solid {
            background: rgba(188, 97, 75, 0.08);
            border-color: rgba(188, 97, 75, 0.25);
        }

        .topo-block.wave-h {
            border-top: 3px solid rgba(188, 97, 75, 0.2);
            border-bottom: 3px solid rgba(188, 97, 75, 0.2);
        }

        .topo-block.wave-v {
            border-left: 3px solid rgba(188, 97, 75, 0.2);
            border-right: 3px solid rgba(188, 97, 75, 0.2);
        }

        .topo-block.dense {
            background: repeating-linear-gradient(
                0deg, transparent, transparent 3px,
                rgba(188, 97, 75, 0.06) 3px, rgba(188, 97, 75, 0.06) 6px
            );
        }

        .topo-block.dense-h {
            background: repeating-linear-gradient(
                90deg, transparent, transparent 3px,
                rgba(188, 97, 75, 0.06) 3px, rgba(188, 97, 75, 0.06) 6px
            );
        }

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

        .logo-area svg.logo-svg {
            width: 140px;
            height: 42px;
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

        .field {
            margin-bottom: 16px;
        }

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
            -webkit-appearance: none;
            appearance: none;
        }

        .input-block::placeholder {
            color: #a0897a;
        }

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
        }

        select.input-block option {
            color: #5C2D1B;
            background: #ffffff;
            padding: 8px;
        }

        .btn-next {
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

        .btn-next:hover {
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

        .form-footer-link a:hover {
            text-decoration: underline;
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
                    <svg class="logo-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 180" width="140" height="42">
                        <g id="store-icon">
                            <path d="M 50,110 L 160,110 A 15,15 0 0 1 175,125 L 175,145 A 25,25 0 0 1 150,170 L 75,170 A 25,25 0 0 1 50,145 Z" fill="#FDF5D6" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                            <path d="M 50,135 Q 110,140 175,115 L 175,145 A 25,25 0 0 1 150,170 L 75,170 A 25,25 0 0 1 50,145 Z" fill="#E67A15" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                            <rect x="62" y="90" width="10" height="20" fill="#FDF5D6" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                            <rect x="153" y="90" width="10" height="20" fill="#FDF5D6" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                            <path d="M 45,90 L 60,50 L 165,50 L 180,90 Z" fill="#E12D2D" stroke="#5C2D1B" stroke-width="5" stroke-linejoin="round"/>
                            <path d="M 45,90 Q 53,102 61,90 Q 69,102 78,90 Q 87,102 96,90 Q 105,102 114,90 Q 123,102 132,90 Q 141,102 150,90 Q 159,102 168,90 Q 174,102 180,90" fill="none" stroke="#5C2D1B" stroke-width="5" stroke-linecap="round"/>
                            <line x1="78" y1="50" x2="78" y2="92" stroke="#5C2D1B" stroke-width="4"/>
                            <line x1="112" y1="50" x2="112" y2="92" stroke="#5C2D1B" stroke-width="4"/>
                            <line x1="146" y1="50" x2="146" y2="92" stroke="#5C2D1B" stroke-width="4"/>
                        </g>
                        <g stroke="#5C2D1B" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M 210,115 L 210,50 M 216,115 L 216,50 M 222,115 L 222,50"/>
                            <path d="M 210,50 L 250,115 M 216,50 L 256,115 M 222,50 L 262,115"/>
                            <path d="M 250,115 L 250,50 M 256,115 L 256,50 M 262,115 L 262,50"/>
                            <path d="M 285,115 L 285,50 M 292,115 L 292,50 M 299,115 L 299,50"/>
                            <path d="M 320,50 L 375,50 M 320,56 L 375,56 M 320,62 L 375,62"/>
                            <path d="M 341,62 L 341,115 M 347,62 L 347,115 M 353,62 L 353,115"/>
                            <path d="M 410,50 L 385,115 M 415,50 L 391,115 M 420,50 L 397,115"/>
                            <path d="M 410,50 L 435,115 M 415,50 L 441,115 M 420,50 L 447,115"/>
                            <path d="M 396,95 L 430,95 M 394,101 L 433,101"/>
                        </g>
                        <text x="207" y="148" font-family="sans-serif" font-weight="900" font-size="25" fill="#5C2D1B" letter-spacing="3">INVENTORY TRACKER</text>
                    </svg>
                </div>

                <h1>Welcome!</h1>
                <p class="form-subtitle">Please supply the needed information to start the process</p>

                <form method="GET" action="{{ url('/auth/register/step-2') }}" id="role-form">
                    <div class="field">
                        <div class="field-label">Full Name</div>
                        <input type="text" class="input-block" name="full_name" placeholder="Enter your full name" required>
                    </div>

                    <div class="field">
                        <div class="field-label">Email Address</div>
                        <input type="email" class="input-block" name="email" placeholder="Enter your email address" required>
                    </div>

                    <div class="field">
                        <div class="field-label">Contact Number</div>
                        <input type="tel" class="input-block" name="contact_number" placeholder="Enter your contact number" required>
                    </div>

                    <div class="field">
                        <div class="field-label">What is your role?</div>
                        <select class="input-block" name="role" id="role-select" required>
                            <option value="" disabled selected>Select your role</option>
                            <option value="owner">Business Owner</option>
                            <option value="manager">Branch Manager</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-next" id="next-btn">Next</button>

                    <div class="form-footer-link">
                        Already have an account? <a href="{{ url('/auth/login') }}">Log In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('role-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const role = document.getElementById('role-select').value;
            if (!role) return;

            const params = new URLSearchParams(new FormData(this));
            if (role === 'owner') {
                window.location.href = "{{ url('/auth/register/owner/step-2') }}" + '?' + params.toString();
            } else if (role === 'manager') {
                window.location.href = "{{ url('/auth/register/manager/step-2') }}" + '?' + params.toString();
            }
        });
    </script>
</body>
</html>
