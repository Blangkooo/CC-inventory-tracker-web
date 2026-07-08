<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onboarding - InvenTrack</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f4f6fb;
            color: #1f2433;
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
            background: #E5E7EB;
        }

        @media (min-width: 768px) {
            .panel-left {
                display: block;
            }
        }

        .panel-right {
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
        }

        .form-card {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border: 4px solid #FF007F;
            border-radius: 20px;
            padding: 48px 40px 40px;
            box-shadow: 0 0 0 6px rgba(255, 0, 127, 0.08),
                        0 8px 32px rgba(255, 0, 127, 0.10);
        }

        .logo-placeholder {
            width: 64px;
            height: 64px;
            background: #d1d5db;
            border-radius: 12px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #9ca3af;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .form-card h1 {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            line-height: 1.3;
            margin-bottom: 12px;
        }

        .description {
            text-align: center;
            font-size: 13px;
            color: #6b7380;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .checklist {
            list-style: none;
            margin-bottom: 20px;
        }

        .checklist li {
            padding: 10px 0;
            font-size: 15px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }

        .checklist li:last-child {
            border-bottom: none;
        }

        .check-row {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            user-select: none;
        }

        /* Hide native checkbox visually but keep accessible */
        .check-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        .check-box {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #e5e7eb;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s ease, transform 0.15s ease;
        }

        .check-box::after {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ffffff;
            transform: scale(0);
            transition: transform 0.15s ease;
        }

        .check-input:checked + .check-box {
            background: #FF007F;
            transform: scale(1.05);
        }

        .check-input:checked + .check-box::after {
            transform: scale(1);
        }

        .check-input:focus-visible + .check-box {
            outline: 2px solid #FF007F;
            outline-offset: 3px;
        }

        .confirmation-statement {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 24px;
            padding: 14px;
            background: #fef2f9;
            border-radius: 10px;
            border-left: 4px solid #FF007F;
        }

        .btn-finish {
            display: block;
            width: 100%;
            height: 48px;
            background: #FF007F;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-finish:hover {
            background: #cc0066;
        }

        .form-footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #6b7380;
        }

        .form-footer-link a {
            color: #FF007F;
            font-weight: 600;
            text-decoration: none;
        }

        .form-footer-link a:hover {
            text-decoration: underline;
        }

        .btn-finish:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="split">
        <div class="panel-left"></div>

        <div class="panel-right">
            <div class="form-card">
                <div class="logo-placeholder">Logo</div>
                <h1>Thank you for trusting us to help you take care of your business.</h1>
                <p class="description">
                    We're almost done! Please review the documents below and confirm that everything is in order to complete your registration.
                </p>

                <form method="POST" action="{{ url('/api/auth/register/confirm') }}">
                    @csrf
                    <input type="hidden" name="owner_id" value="mock-owner-001">

                    <ul class="checklist">
                        <li>
                            <label class="check-row">
                                <input type="checkbox" name="owner_id_confirmed" value="1" checked class="check-input">
                                <span class="check-box"></span>
                                <span>Owner ID</span>
                            </label>
                        </li>
                        <li>
                            <label class="check-row">
                                <input type="checkbox" name="permit_validity" value="1" checked class="check-input">
                                <span class="check-box"></span>
                                <span>Permit / Validity</span>
                            </label>
                        </li>
                        <li>
                            <label class="check-row">
                                <input type="checkbox" name="terms_accepted" value="1" checked class="check-input">
                                <span class="check-box"></span>
                                <span>Terms of Service</span>
                            </label>
                        </li>
                        <li>
                            <label class="check-row">
                                <input type="checkbox" name="legal_papers_submitted" value="1" checked class="check-input">
                                <span class="check-box"></span>
                                <span>Legal Papers</span>
                            </label>
                        </li>
                        <li>
                            <label class="check-row">
                                <input type="checkbox" name="legal_papers_secondary_submitted" value="1" checked class="check-input">
                                <span class="check-box"></span>
                                <span>Legal Papers (Secondary)</span>
                            </label>
                        </li>
                    </ul>

                    <div class="confirmation-statement">
                        Please check carefully to avoid updates confirmation. It's nice working with you!
                    </div>

                    <button type="submit" class="btn-finish" id="finish-btn">Finish</button>

                    <div class="form-footer-link">
                        Already have an account? <a href="{{ url('/auth/login') }}">Log in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.check-input');
            const finishBtn = document.getElementById('finish-btn');

            function updateButtonState() {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                finishBtn.disabled = !allChecked;
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateButtonState);
            });

            updateButtonState();
        });
    </script>
</body>
</html>
