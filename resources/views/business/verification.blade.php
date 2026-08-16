@extends('layouts.sidebar')

@section('title', 'Verification')

@section('content')
@php
    $badge = [
        'verified' => 'bg-green/10 text-green',
        'pending'  => 'bg-orange/20 text-[#a16207]',
        'missing'  => 'bg-accent-2/10 text-accent-2',
    ];

    $panels = [
        [
            'Business Permits',
            '<path d="M9 12l2 2 4-4"/><path d="M12 2a10 10 0 1 0 10 10"/>',
            $permitRows,
            'No branches registered.',
        ],
        [
            'Document Compliance',
            '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            $complianceRows,
            'No compliance documents on file.',
        ],
        [
            'Staff Verification',
            '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            [],
            'Staff verification is not tracked in the system yet.',
        ],
        [
            'Insurance &amp; Bonds',
            '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            $insuranceRows,
            'No branches registered.',
        ],
    ];
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-[22px] font-extrabold uppercase tracking-[.03em]">Verification</h1>
    <span class="text-[15px] text-ink-3">/ {{ auth()->user()->isOwner() ? 'Owner' : 'Manager' }}</span>
</div>

@include('partials._business-tabs', ['active' => 'verification'])

<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    @foreach ($panels as [$heading, $icon, $rows, $emptyLabel])
        <div class="rounded-card border border-line bg-card p-5 shadow-card">
            <h3 class="mb-3.5 flex items-center gap-2 border-b border-line pb-2.5 text-[13px] font-bold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
                {!! $heading !!}
            </h3>
            @forelse ($rows as [$label, $status, $tone])
                <div class="flex items-center justify-between border-b border-line py-2.5 text-[13px] last:border-b-0">
                    <span>{{ $label }}</span>
                    <span class="rounded-full px-2.5 py-[3px] text-[11px] font-semibold {{ $badge[$tone] }}">{{ $status }}</span>
                </div>
            @empty
                <div class="empty-state-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span class="empty-state-text">{{ $emptyLabel }}</span>
                </div>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
