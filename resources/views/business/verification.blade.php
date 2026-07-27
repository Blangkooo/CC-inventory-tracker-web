@php
    use App\Models\User;

    if (!isset($branches) || $branches->isEmpty()) {
        $branches = collect([
            (object)['id'=>1,'name'=>'QC Main Branch'],
            (object)['id'=>2,'name'=>'Makati Outlet'],
            (object)['id'=>3,'name'=>'BGC Branch'],
        ]);
    }
@endphp
@extends('layouts.sidebar')

@section('title', 'Verification')

@section('styles')
        .workspace {
            max-width: 1400px; margin: 0 auto; padding: 24px 32px;
        }

        .page-head {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
        }

        .page-head__title { font-size: 22px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; }
        .page-head__role { font-size: 15px; font-weight: 400; opacity: .5; }

        .sub-tabs { display: flex; gap: 6px; }

        .sub-tab {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: 999px; font-size: 12px; font-weight: 600;
            border: 1.5px solid var(--border); background: #fff; color: var(--brown);
            text-decoration: none; transition: all .15s ease; cursor: pointer;
        }

        .sub-tab:hover { border-color: var(--terra); color: var(--terra); }
        .sub-tab.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }

        .card-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }

        .card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            padding: 20px;
        }

        .card h3 {
            font-size: 13px; font-weight: 700; margin-bottom: 14px;
            padding-bottom: 10px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 8px;
        }

        .status-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 0; font-size: 13px;
            border-bottom: 1px solid rgba(92,45,27,.06);
        }

        .status-row:last-child { border-bottom: none; }

        .status-badge {
            display: inline-block; padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 600;
        }

        .status-badge.verified { background: rgba(22,163,74,.1); color: #16a34a; }
        .status-badge.pending { background: rgba(234,179,8,.1); color: #a16207; }
        .status-badge.missing { background: rgba(220,38,38,.1); color: #dc2626; }

        .empty-state { text-align: center; padding: 40px; opacity: .4; font-size: 14px; }

        @media (max-width: 900px) {
            .card-grid { grid-template-columns: 1fr; }
            .workspace { padding: 16px; }
        }
@endsection

@section('content')

<div class="workspace">

    <div class="page-head">
        <div class="page-head__title">Verification</div>
        <span class="page-head__role">/ {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
    </div>

    <div class="sub-tabs" style="margin-bottom:24px;">
        <a href="{{ url('/business/summary') }}" class="sub-tab">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            Summary
        </a>
        <a href="{{ url('/business/recipes') }}" class="sub-tab">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            Recipe
        </a>
        <a href="{{ url('/business/workers') }}" class="sub-tab">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Staff
        </a>
        <a href="{{ url('/business/verification') }}" class="sub-tab is-active">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            Verification
        </a>
    </div>

    <div class="card-grid">
        <div class="card">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12l2 2 4-4"/><path d="M12 2a10 10 0 1 0 10 10"/>
                </svg>
                Business Permits
            </h3>
            @forelse ($branches as $branch)
                <div class="status-row">
                    <span>{{ $branch->name }}</span>
                    <span class="status-badge verified">Verified</span>
                </div>
            @empty
                <div class="empty-state">No branches registered.</div>
            @endforelse
        </div>

        <div class="card">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                Document Compliance
            </h3>
            <div class="status-row">
                <span>Terms of Service</span>
                <span class="status-badge verified">Accepted</span>
            </div>
            <div class="status-row">
                <span>Data Privacy Agreement</span>
                <span class="status-badge verified">Accepted</span>
            </div>
            <div class="status-row">
                <span>Employee Contracts</span>
                <span class="status-badge pending">Pending Review</span>
            </div>
            <div class="status-row">
                <span>Tax Registration</span>
                <span class="status-badge verified">On File</span>
            </div>
        </div>

        <div class="card">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Staff Verification
            </h3>
            <div class="status-row">
                <span>Government IDs Collected</span>
                <span class="status-badge pending">In Progress</span>
            </div>
            <div class="status-row">
                <span>Employment Contracts Signed</span>
                <span class="status-badge verified">Complete</span>
            </div>
            <div class="status-row">
                <span>NDA Agreements</span>
                <span class="status-badge missing">Not Started</span>
            </div>
        </div>

        <div class="card">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Insurance &amp; Bonds
            </h3>
            <div class="status-row">
                <span>Property Insurance</span>
                <span class="status-badge verified">Active</span>
            </div>
            <div class="status-row">
                <span>Liability Coverage</span>
                <span class="status-badge verified">Active</span>
            </div>
            <div class="status-row">
                <span>Cash Bond (Staff)</span>
                <span class="status-badge pending">Pending</span>
            </div>
        </div>
    </div>

</div>

@endsection
