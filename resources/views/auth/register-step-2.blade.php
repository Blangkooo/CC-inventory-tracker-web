<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Business - InvenTrack</title>
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
            max-width: 420px;
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
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 28px;
        }

        .field {
            margin-bottom: 14px;
        }

        .input-block {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            background: #f3f4f6;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            color: #111827;
            font-family: inherit;
            transition: background 0.15s ease;
            -webkit-appearance: none;
            appearance: none;
        }

        .input-block::placeholder {
            color: #9ca3af;
        }

        .input-block:focus {
            outline: none;
            background: #e8eaf0;
        }

        /* Dropdown styled as a flat gray block */
        select.input-block {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%239ca3af' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 12px 8px;
            padding-right: 40px;
            color: #6b7380;
        }

        select.input-block option {
            color: #111827;
        }

        .btn-row {
            display: flex;
            gap: 12px;
            margin-top: 12px;
        }

        .btn-next {
            flex: 1;
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

        .btn-next:hover {
            background: #cc0066;
        }

        .btn-add-another {
            height: 48px;
            padding: 0 20px;
            background: #f3f4f6;
            color: #374151;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .btn-add-another:hover {
            background: #e5e7eb;
            color: #FF007F;
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

    </style>
</head>
<body>
    <div class="split">
        <div class="panel-left"></div>

        <div class="panel-right">
            <div class="form-card">
                <div class="logo-placeholder">Logo</div>
                <h1>Register your business/es</h1>

                <form method="POST" action="{{ url('/api/auth/register/step-2') }}">
                    @csrf
                    <div id="business-fields-container">
                        <div class="business-group" data-index="0">
                            <div class="field">
                                <input type="text" class="input-block" name="businesses[0][business_name]" placeholder="Business Name" required>
                            </div>
                            <div class="field">
                                <select class="input-block" name="businesses[0][type_of_business]" required>
                                    <option value="" disabled selected>Type of Business</option>
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="service">Service</option>
                                    <option value="manufacturing">Manufacturing</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="field">
                                <input type="text" class="input-block" name="businesses[0][business_registration]" placeholder="Business Registration" required>
                            </div>
                            <div class="field">
                                <input type="text" class="input-block" name="businesses[0][business_permit]" placeholder="Business Permit" required>
                            </div>
                            <div class="field">
                                <input type="text" class="input-block" name="businesses[0][location]" placeholder="Location" required>
                            </div>
                        </div>
                    </div>

                    <div class="btn-row">
                        <button type="button" class="btn-add-another" id="add-another-btn">+ Add Another</button>
                        <button type="submit" class="btn-next">Next</button>
                    </div>

                    <div class="form-footer-link">
                        Already have an account? <a href="{{ url('/auth/login') }}">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let bizIdx = 1;
            const container = document.getElementById('business-fields-container');
            const addBtn = document.getElementById('add-another-btn');

            function createBusinessGroup(index) {
                const div = document.createElement('div');
                div.className = 'business-group';
                div.dataset.index = index;
                div.innerHTML = `
                    <div class="field">
                        <input type="text" class="input-block" name="businesses[${index}][business_name]" placeholder="Business Name" required>
                    </div>
                    <div class="field">
                        <select class="input-block" name="businesses[${index}][type_of_business]" required>
                            <option value="" disabled selected>Type of Business</option>
                            <option value="retail">Retail</option>
                            <option value="wholesale">Wholesale</option>
                            <option value="restaurant">Restaurant</option>
                            <option value="service">Service</option>
                            <option value="manufacturing">Manufacturing</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="field">
                        <input type="text" class="input-block" name="businesses[${index}][business_registration]" placeholder="Business Registration" required>
                    </div>
                    <div class="field">
                        <input type="text" class="input-block" name="businesses[${index}][business_permit]" placeholder="Business Permit" required>
                    </div>
                    <div class="field">
                        <input type="text" class="input-block" name="businesses[${index}][location]" placeholder="Location" required>
                    </div>
                `;
                return div;
            }

            addBtn.addEventListener('click', function () {
                const group = createBusinessGroup(bizIdx);
                container.appendChild(group);
                bizIdx++;
            });
        });
    </script>
</body>
</html>
