<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Registration - NITA</title>
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
            font-size: 22px;
            font-weight: 700;
            color: #5C2D1B;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .h1-info-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #ef4444;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
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

        /* File upload block */
        .upload-block {
            width: 100%;
            height: 48px;
            background: #ffffff;
            border: 1.5px solid #5C2D1B;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            cursor: pointer;
            transition: border-color 0.15s ease;
        }

        .upload-block:hover {
            border-color: #BC614B;
        }

        .upload-block .upload-placeholder {
            font-size: 14px;
            color: #a0897a;
        }

        .upload-block .upload-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: rgba(188, 97, 75, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .upload-block input[type="file"] {
            display: none;
        }

        /* Nav controls */
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

        .btn-secondary {
            height: 44px;
            padding: 0 24px;
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

        .btn-secondary:hover {
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

                <h1>Business Registration ID <span class="h1-info-icon">?</span></h1>
                <p class="form-subtitle">Please provide the needed paperwork for each business you own</p>

                <form method="POST" action="{{ url('/api/auth/register/step-2') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="field">
                        <div class="field-label">Business Name</div>
                        <input type="text" class="input-block" name="business_name" placeholder="Enter your business name" required>
                    </div>

                    <div class="field">
                        <div class="field-label">DTI Registration</div>
                        <label class="upload-block">
                            <span class="upload-placeholder">Upload DTI document</span>
                            <span class="upload-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                            </span>
                            <input type="file" name="dti_registration">
                        </label>
                    </div>

                    <div class="field">
                        <div class="field-label">SEC Registration</div>
                        <label class="upload-block">
                            <span class="upload-placeholder">Upload SEC document</span>
                            <span class="upload-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                            </span>
                            <input type="file" name="sec_registration">
                        </label>
                    </div>

                    <div class="field">
                        <div class="field-label">BIR Registration</div>
                        <label class="upload-block">
                            <span class="upload-placeholder">Upload BIR document</span>
                            <span class="upload-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                            </span>
                            <input type="file" name="bir_registration">
                        </label>
                    </div>

                    <div class="field">
                        <div class="field-label">LGU Permit</div>
                        <label class="upload-block">
                            <span class="upload-placeholder">Upload LGU permit</span>
                            <span class="upload-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#BC614B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                            </span>
                            <input type="file" name="lgu_permit">
                        </label>
                    </div>

                    <div class="nav-row">
                        <a href="{{ url('/auth/register/step-1') }}" class="nav-link">Back</a>
                        <button type="button" class="btn-secondary" onclick="duplicateBusinessFields()">Register Another</button>
                        <a href="{{ url('/auth/register/owner/step-3') }}" class="nav-link">Next</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // File upload name display
        document.querySelectorAll('.upload-block').forEach(function(block) {
            const fileInput = block.querySelector('input[type="file"]');
            const placeholder = block.querySelector('.upload-placeholder');
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    placeholder.textContent = this.files[0].name;
                }
            });
        });

        // Duplicate business registration fields for 'Register Another'
        function duplicateBusinessFields() {
            const container = document.getElementById('business-forms-container') || document.querySelector('.form-card');
            const form = document.querySelector('form');
            const newForm = form.cloneNode(true);
            // Clear all input values in the clone
            newForm.querySelectorAll('input[type="text"], input[type="file"]').forEach(function(input) {
                input.value = '';
            });
            newForm.querySelectorAll('.upload-placeholder').forEach(function(el) {
                const label = el.closest('.field')?.querySelector('.field-label');
                if (label) {
                    el.textContent = 'Upload ' + label.textContent.trim().toLowerCase() + ' document';
                }
            });
            // Remove the nav-row from the clone so it's clean
            const navRow = newForm.querySelector('.nav-row');
            if (navRow) navRow.remove();
            // Insert before the original nav row
            const originalNav = form.querySelector('.nav-row');
            form.insertBefore(document.createElement('hr'), originalNav);
            const hr = form.querySelector('hr:last-of-type');
            if (hr) {
                hr.style.cssText = 'border: none; border-top: 1px solid rgba(92,45,27,0.15); margin: 16px 0;';
                form.insertBefore(newForm, hr.nextSibling);
            }
        }
    </script>
</body>
</html>
