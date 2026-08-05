@extends('layouts.app')

@section('title', 'owner legal')

@section('content')
<style>
    .workspace {
        padding: 20px 32px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .legal-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    @media (max-width: 900px) {
        .legal-grid { grid-template-columns: 1fr; }
    }

    .legal-card {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        padding: 24px;
    }

    .legal-card__header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .legal-card__icon {
        font-size: 20px;
    }

    .legal-card__name {
        font-size: 16px;
        font-weight: 700;
        color: var(--brown);
    }

    .legal-docs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 24px;
    }

    .legal-doc {
        font-size: 13px;
        color: var(--terra);
        cursor: pointer;
        transition: opacity .15s ease;
    }

    .legal-doc:hover {
        opacity: .7;
        text-decoration: underline;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--brown);
        opacity: .5;
        font-size: 14px;
        grid-column: 1 / -1;
    }
</style>

<div class="legal-grid">
    @if(isset($branches) && $branches->isNotEmpty())
        @foreach($branches as $branch)
            <div class="legal-card">
                <div class="legal-card__header">
                    <span class="legal-card__icon">B</span>
                    <span class="legal-card__name">{{ $branch->name }}</span>
                </div>
                <div class="legal-docs">
                    <a href="#" class="legal-doc">DTIRegistration.pdf</a>
                    <a href="#" class="legal-doc">BIRRegistration.pdf</a>
                    <a href="#" class="legal-doc">SECRegistration.pdf</a>
                    <a href="#" class="legal-doc">LGURegistration.pdf</a>
                    <a href="#" class="legal-doc">EmploymentContract.pdf</a>
                    <a href="#" class="legal-doc">NDAAgreement.pdf</a>
                </div>
            </div>
        @endforeach
    @else
        {{-- Fallback placeholder data --}}
        @php
            $placeholderBusinesses = [
                ['name' => 'Coffee Shop', 'icon' => 'C'],
                ['name' => 'Bakery', 'icon' => 'B'],
                ['name' => 'Frozen Yogurt', 'icon' => 'F'],
                ['name' => 'Burger Shop', 'icon' => 'BG'],
                ['name' => 'Printing Shop', 'icon' => 'P'],
                ['name' => 'Computer Shop', 'icon' => 'CS'],
            ];
        @endphp
        @foreach($placeholderBusinesses as $biz)
            <div class="legal-card">
                <div class="legal-card__header">
                    <span class="legal-card__icon">{{ $biz['icon'] }}</span>
                    <span class="legal-card__name">{{ $biz['name'] }}</span>
                </div>
                <div class="legal-docs">
                    <a href="#" class="legal-doc">DTIRegistration.pdf</a>
                    <a href="#" class="legal-doc">BIRRegistration.pdf</a>
                    <a href="#" class="legal-doc">SECRegistration.pdf</a>
                    <a href="#" class="legal-doc">LGURegistration.pdf</a>
                    <a href="#" class="legal-doc">EmploymentContract.pdf</a>
                    <a href="#" class="legal-doc">NDAAgreement.pdf</a>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
