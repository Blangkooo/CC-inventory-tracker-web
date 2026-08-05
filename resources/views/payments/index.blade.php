@extends('layouts.app')

@section('title', 'owner payments')

@section('content')
<style>
    .workspace {
        padding: 20px 32px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* ══ VIEW TABS ═══════════════════════════════════════════════════════ */
    .view-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border);
    }

    .view-tab {
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: var(--brown);
        opacity: 0.5;
        transition: all .15s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .view-tab:hover {
        opacity: 0.8;
    }

    .view-tab.active {
        opacity: 1;
        border-bottom-color: var(--terra);
    }

    /* ══ STATS ROW ═══════════════════════════════════════════════════════ */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        margin-bottom: 24px;
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
    }

    .stat-card {
        padding: 24px;
        text-align: center;
        border-right: 1px solid var(--border);
    }

    .stat-card:last-child {
        border-right: none;
    }

    .stat-card__label {
        font-size: 14px;
        font-weight: 600;
        color: var(--brown);
        margin-bottom: 8px;
    }

    .stat-card__value {
        font-size: 42px;
        font-weight: 800;
        color: var(--terra);
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-card__trend {
        font-size: 13px;
        font-weight: 600;
    }

    .stat-card__trend.positive { color: #16a34a; }
    .stat-card__trend.negative { color: #dc2626; }
    .stat-card__trend .vs { opacity: .6; }

    /* ══ BREAKDOWN SECTION ═══════════════════════════════════════════════ */
    .breakdown-section {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .breakdown-section__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .breakdown-section__title {
        font-size: 18px;
        font-weight: 700;
        color: var(--brown);
    }

    /* ══ HORIZONTAL BAR CHART ════════════════════════════════════════════ */
    .bar-chart {
        height: 28px;
        border-radius: 14px;
        overflow: hidden;
        display: flex;
        margin-bottom: 20px;
        gap: 0;
    }

    .bar-segment {
        height: 100%;
        transition: width .3s ease;
        margin: 0;
        padding: 0;
        flex-shrink: 0;
    }

    .bar-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
    }

    .bar-legend__item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }

    .bar-legend__dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .bar-legend__label {
        color: var(--brown);
    }

    .bar-legend__value {
        font-weight: 600;
        color: var(--terra);
    }

    /* ══ BUSINESS TABS ═══════════════════════════════════════════════════ */
    .business-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--border);
    }

    .business-tab {
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: var(--brown);
        opacity: 0.5;
        transition: all .15s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .business-tab:hover {
        opacity: 0.8;
    }

    .business-tab.active {
        opacity: 1;
        border-bottom-color: var(--terra);
    }

    /* ══ EXPENSE CARDS ═══════════════════════════════════════════════════ */
    .expense-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }

    @media (max-width: 1100px) {
        .expense-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .expense-grid { grid-template-columns: 1fr; }
    }

    .expense-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 12px;
        padding: 16px;
    }

    .expense-card--wide {
        grid-column: span 2;
    }

    @media (max-width: 768px) {
        .expense-card--wide { grid-column: span 1; }
    }

    .expense-card__badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        background: var(--cream);
        font-size: 11px;
        font-weight: 600;
        color: var(--brown);
        margin-bottom: 12px;
    }

    .expense-group {
        margin-bottom: 12px;
    }

    .expense-group:last-child {
        margin-bottom: 0;
    }

    .expense-group__title {
        font-size: 13px;
        font-weight: 700;
        color: var(--brown);
        margin-bottom: 6px;
    }

    .expense-row {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        padding: 3px 0;
    }

    .expense-row__branch {
        color: var(--brown);
    }

    .expense-row__amount {
        font-weight: 600;
        color: var(--terra);
    }

    /* ══ PAYROLL CARDS ═══════════════════════════════════════════════════ */
    .payroll-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    @media (max-width: 1100px) {
        .payroll-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .payroll-grid { grid-template-columns: 1fr; }
    }

    .payroll-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 12px;
        padding: 20px;
    }

    .payroll-card__badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        background: var(--cream);
        font-size: 11px;
        font-weight: 600;
        color: var(--brown);
        margin-bottom: 16px;
    }

    .payroll-branch {
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }

    .payroll-branch:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .payroll-branch__name {
        font-size: 14px;
        font-weight: 700;
        color: var(--terra);
        margin-bottom: 8px;
    }

    .payroll-row {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        padding: 3px 0;
    }

    .payroll-row__label {
        color: var(--brown);
    }

    .payroll-row__amount {
        font-weight: 600;
    }

    .payroll-row--total {
        font-weight: 700;
        color: var(--terra);
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px solid var(--border);
    }

    /* ══ SALARY GUIDE ════════════════════════════════════════════════════ */
    .salary-guide {
        font-size: 13px;
        color: var(--brown);
    }

    .salary-guide strong {
        color: var(--terra);
        font-weight: 700;
    }
</style>

{{-- ═══ VIEW TABS ═══ --}}
<div class="view-tabs">
    <button class="view-tab active" onclick="switchView('all', this)" style="background: #B45353; color: #fff; border-color: #B45353;">
        <span>👥</span> All
    </button>
    <button class="view-tab" onclick="switchView('expenses', this)" style="background: #fff; color: #1C1917; border-color: rgba(28,25,23,.12);">
        <span>💰</span> Expenses
    </button>
    <button class="view-tab" onclick="switchView('payroll', this)" style="background: #fff; color: #1C1917; border-color: rgba(28,25,23,.12);">
        <span>👥</span> Payroll
    </button>
</div>

{{-- ═══ ALL VIEW ═══ --}}
<div id="allView">
    {{-- Stats Row --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-card__label">Total Earnings</div>
            <div class="stat-card__value">$548,000</div>
            <div class="stat-card__trend positive">67% ↑ <span class="vs">vs last month</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">Total Expenses</div>
            <div class="stat-card__value">$250,000</div>
            <div class="stat-card__trend negative">45% ↑ <span class="vs">vs last month</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">Net Profit</div>
            <div class="stat-card__value">$298,000</div>
            <div class="stat-card__trend positive">67% ↑ <span class="vs">vs last month</span></div>
        </div>
    </div>

    {{-- Expenses Breakdown Chart --}}
    <div class="breakdown-section">
        <div class="breakdown-section__header">
            <h2 class="breakdown-section__title">Expenses Breakdown</h2>
        </div>
        <div class="bar-chart"><div class="bar-segment" style="width:22%;background:#F5D5A8"></div><div class="bar-segment" style="width:22%;background:#F5B066"></div><div class="bar-segment" style="width:27%;background:#E8924A"></div><div class="bar-segment" style="width:19%;background:#D97740"></div><div class="bar-segment" style="width:10%;background:#8B4513"></div></div>
        <div class="bar-legend">
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #F5D5A8;"></span>
                <span class="bar-legend__label">Operating Expenses</span>
                <span class="bar-legend__value">22%</span>
            </div>
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #F5B066;"></span>
                <span class="bar-legend__label">Direct Cost</span>
                <span class="bar-legend__value">22%</span>
            </div>
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #E8924A;"></span>
                <span class="bar-legend__label">Payroll</span>
                <span class="bar-legend__value">27%</span>
            </div>
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #D97740;"></span>
                <span class="bar-legend__label">Sales & Marketing</span>
                <span class="bar-legend__value">19%</span>
            </div>
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #8B4513;"></span>
                <span class="bar-legend__label">Administrative Fees</span>
                <span class="bar-legend__value">10%</span>
            </div>
        </div>
    </div>

    {{-- Payroll Breakdown Chart --}}
    <div class="breakdown-section">
        <div class="breakdown-section__header">
            <h2 class="breakdown-section__title">Payroll Breakdown</h2>
        </div>
        <div class="bar-chart"><div class="bar-segment" style="width:22%;background:#F5D5A8"></div><div class="bar-segment" style="width:19%;background:#E8924A"></div><div class="bar-segment" style="width:10%;background:#8B4513"></div><div class="bar-segment" style="width:22%;background:#F5B066"></div><div class="bar-segment" style="width:22%;background:#D97740"></div><div class="bar-segment" style="width:5%;background:#4A2C2A"></div></div>
        <div class="bar-legend">
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #F5D5A8;"></span>
                <span class="bar-legend__label">Coffee Shop</span>
                <span class="bar-legend__value">22%</span>
            </div>
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #E8924A;"></span>
                <span class="bar-legend__label">Burger Shop</span>
                <span class="bar-legend__value">19%</span>
            </div>
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #8B4513;"></span>
                <span class="bar-legend__label">Printing Shop</span>
                <span class="bar-legend__value">10%</span>
            </div>
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #F5B066;"></span>
                <span class="bar-legend__label">Frozen Yogurt</span>
                <span class="bar-legend__value">22%</span>
            </div>
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #D97740;"></span>
                <span class="bar-legend__label">Bakery</span>
                <span class="bar-legend__value">22%</span>
            </div>
            <div class="bar-legend__item">
                <span class="bar-legend__dot" style="background: #4A2C2A;"></span>
                <span class="bar-legend__label">Computer Shop</span>
                <span class="bar-legend__value">5%</span>
            </div>
        </div>
    </div>
</div>

{{-- ═══ EXPENSES VIEW ═══ --}}
<div id="expensesView" style="display: none;">
    <div class="breakdown-section__header" style="margin-bottom: 16px;">
        <h2 class="breakdown-section__title">Expenses Breakdown</h2>
    </div>

    <div class="business-tabs">
        <button class="business-tab active" onclick="switchBusinessTab(this)">☕ Coffee Shop</button>
        <button class="business-tab" onclick="switchBusinessTab(this)">🍔 Burger Shop</button>
        <button class="business-tab" onclick="switchBusinessTab(this)">🥐 Bakery</button>
        <button class="business-tab" onclick="switchBusinessTab(this)">🍦 Frozen Yogurt</button>
        <button class="business-tab" onclick="switchBusinessTab(this)">💻 Computer Shop</button>
        <button class="business-tab" onclick="switchBusinessTab(this)">🖨️ Printing Shop</button>
    </div>

    <div class="expense-grid">
        {{-- Operating Expenses --}}
        <div class="expense-card">
            <div class="expense-card__badge">Operating Expenses</div>
            
            <div class="expense-group">
                <div class="expense-group__title">Lease</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$100</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$150</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$80</span></div>
            </div>

            <div class="expense-group">
                <div class="expense-group__title">Utilities</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$80</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$90</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$60</span></div>
            </div>

            <div class="expense-group">
                <div class="expense-group__title">Insurance</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$100</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$100</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$100</span></div>
            </div>

            <div class="expense-group">
                <div class="expense-group__title">Equipment</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$100</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$100</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$100</span></div>
            </div>
        </div>

        {{-- Direct Cost --}}
        <div class="expense-card">
            <div class="expense-card__badge">Direct Cost</div>
            
            <div class="expense-group">
                <div class="expense-group__title">Raw Materials</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$1000</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$1000</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$1000</span></div>
            </div>

            <div class="expense-group">
                <div class="expense-group__title">Direct Labor</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$30</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$30</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$30</span></div>
            </div>

            <div class="expense-group">
                <div class="expense-group__title">Packaging & Delivery</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$500</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$500</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$500</span></div>
            </div>

            <div class="expense-group">
                <div class="expense-group__title">Payment Processing Fee</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$50</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$50</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$50</span></div>
            </div>
        </div>

        {{-- Payroll --}}
        <div class="expense-card">
            <div class="expense-card__badge">Payroll</div>
            
            <div class="expense-group">
                <div class="expense-group__title">Wages</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$5000</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$4000</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$4000</span></div>
            </div>

            <div class="expense-group">
                <div class="expense-group__title">Taxes</div>
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$500</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$500</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$500</span></div>
            </div>
        </div>

        {{-- Employee Benefits --}}
        <div class="expense-card">
            <div class="expense-card__badge">Employee Benefits</div>
            
            <div class="expense-group">
                <div class="expense-row"><span class="expense-row__branch">QC Branch</span><span class="expense-row__amount">$2500</span></div>
                <div class="expense-row"><span class="expense-row__branch">Makati Branch</span><span class="expense-row__amount">$2000</span></div>
                <div class="expense-row"><span class="expense-row__branch">UST Branch</span><span class="expense-row__amount">$2000</span></div>
            </div>
        </div>

        {{-- Sales & Marketing --}}
        <div class="expense-card expense-card--wide">
            <div class="expense-card__badge">Sales & Marketing</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="expense-group">
                    <div class="expense-group__title">Advertising</div>
                    <div class="expense-row"><span class="expense-row__branch">Social Media</span><span class="expense-row__amount">$500</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Digital Ad Campaigns</span><span class="expense-row__amount">$500</span></div>
                </div>

                <div class="expense-group">
                    <div class="expense-group__title">Branding & Marketing Assets</div>
                    <div class="expense-row"><span class="expense-row__branch">Graphic Designs</span><span class="expense-row__amount">$2500</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Photographs</span><span class="expense-row__amount">$80</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Promotional Videos</span><span class="expense-row__amount">$100</span></div>
                </div>
            </div>
        </div>

        {{-- Administrative Fees --}}
        <div class="expense-card" style="grid-column: 1 / -1;">
            <div class="expense-card__badge">Administrative Fees</div>
            
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <div class="expense-group">
                    <div class="expense-group__title">Legal Fees</div>
                    <div class="expense-row"><span class="expense-row__branch">Business Registration</span><span class="expense-row__amount">$100</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Legal Council</span><span class="expense-row__amount">$150</span></div>
                </div>

                <div class="expense-group">
                    <div class="expense-group__title">Accounting</div>
                    <div class="expense-row"><span class="expense-row__branch">Tax Preparation</span><span class="expense-row__amount">$100</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Monthly Audit</span><span class="expense-row__amount">$100</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Payroll Processing</span><span class="expense-row__amount">$80</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Bookkeeping</span><span class="expense-row__amount">$80</span></div>
                </div>

                <div class="expense-group">
                    <div class="expense-group__title">License & Permits</div>
                    <div class="expense-row"><span class="expense-row__branch">Business Permit</span><span class="expense-row__amount">$100</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Local Government License</span><span class="expense-row__amount">$150</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Industry Certification</span><span class="expense-row__amount">$80</span></div>
                </div>

                <div class="expense-group">
                    <div class="expense-group__title">Finance Fees</div>
                    <div class="expense-row"><span class="expense-row__branch">Maintenance Fee</span><span class="expense-row__amount">$100</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Wire Fee</span><span class="expense-row__amount">$100</span></div>
                    <div class="expense-row"><span class="expense-row__branch">Loan Interest</span><span class="expense-row__amount">$0</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ PAYROLL VIEW ═══ --}}
<div id="payrollView" style="display: none;">
    <div class="breakdown-section__header" style="margin-bottom: 16px;">
        <h2 class="breakdown-section__title">Payroll Breakdown</h2>
        <div class="salary-guide">
            Salary Guide (Monthly): <strong>Manager: $1000</strong> &nbsp; Full Time Worker: <strong>$800</strong> &nbsp; Part Time Worker: <strong>$500</strong>
        </div>
    </div>

    <div class="payroll-grid">
        {{-- Coffee Shop --}}
        <div class="payroll-card">
            <div class="payroll-card__badge">☕ Coffee Shop</div>
            
            <div class="payroll-branch">
                <div class="payroll-branch__name">QC Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>

            <div class="payroll-branch">
                <div class="payroll-branch__name">Makati Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>

            <div class="payroll-branch">
                <div class="payroll-branch__name">UST Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>
        </div>

        {{-- Burger Shop --}}
        <div class="payroll-card">
            <div class="payroll-card__badge">🍔 Burger Shop</div>
            
            <div class="payroll-branch">
                <div class="payroll-branch__name">QC Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>

            <div class="payroll-branch">
                <div class="payroll-branch__name">Makati Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>

            <div class="payroll-branch">
                <div class="payroll-branch__name">UP Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>
        </div>

        {{-- Bakery --}}
        <div class="payroll-card">
            <div class="payroll-card__badge">🥐 Bakery</div>
            
            <div class="payroll-branch">
                <div class="payroll-branch__name">QC Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>

            <div class="payroll-branch">
                <div class="payroll-branch__name">Makati Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>
        </div>

        {{-- Frozen Yogurt --}}
        <div class="payroll-card">
            <div class="payroll-card__badge">🍦 Frozen Yogurt</div>
            
            <div class="payroll-branch">
                <div class="payroll-branch__name">Makati Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>
        </div>

        {{-- Computer Shop --}}
        <div class="payroll-card">
            <div class="payroll-card__badge">💻 Computer Shop</div>
            
            <div class="payroll-branch">
                <div class="payroll-branch__name">UST Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>
        </div>

        {{-- Printing Shop --}}
        <div class="payroll-card">
            <div class="payroll-card__badge">🖨️ Printing Shop</div>
            
            <div class="payroll-branch">
                <div class="payroll-branch__name">QC Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>

            <div class="payroll-branch">
                <div class="payroll-branch__name">UP Branch</div>
                <div class="payroll-row"><span class="payroll-row__label">Manager</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Full Time Employees</span><span class="payroll-row__amount">$3200</span></div>
                <div class="payroll-row"><span class="payroll-row__label">Part Time Employees</span><span class="payroll-row__amount">$1000</span></div>
                <div class="payroll-row payroll-row--total"><span>Total:</span><span>$5200</span></div>
            </div>
        </div>
    </div>
</div>

<script>function switchView(view, el) {
    // Reset all tabs
    document.querySelectorAll('.view-tab').forEach(t => {
        t.classList.remove('active');
    });
    // Activate clicked tab
    el.classList.add('active');

    // Show/hide views
    document.getElementById('allView').style.display = 'none';
    document.getElementById('expensesView').style.display = 'none';
    document.getElementById('payrollView').style.display = 'none';

    if (view === 'all') {
        document.getElementById('allView').style.display = 'block';
    } else if (view === 'expenses') {
        document.getElementById('expensesView').style.display = 'block';
    } else if (view === 'payroll') {
        document.getElementById('payrollView').style.display = 'block';
    }
}

function switchBusinessTab(el) {
    // Reset all business tabs in the same parent
    el.parentElement.querySelectorAll('.business-tab').forEach(t => {
        t.classList.remove('active');
    });
    // Activate clicked tab
    el.classList.add('active');
}
</script>
@endsection
