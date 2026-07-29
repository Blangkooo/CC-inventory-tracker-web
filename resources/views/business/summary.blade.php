@php
    // ── Placeholder fallbacks ─────────────────────────────────────────
    if (!isset($branches) || $branches->isEmpty()) {
        $branches = collect([
            (object)['id'=>1,'name'=>'QC Main Branch'],
            (object)['id'=>2,'name'=>'Makati Outlet'],
            (object)['id'=>3,'name'=>'BGC Branch'],
        ]);
    }
    if (!isset($activeBranch) || !$activeBranch) {
        $activeBranch = $branches->first();
    }
    if (!isset($totalRevenue) || $totalRevenue == 0) $totalRevenue = 1_240_000;

    if (!isset($recentTransactions) || $recentTransactions->isEmpty()) {
        $recentTransactions = collect([
            (object)['id'=>1005,'total_amount'=>375.00,'product'=>(object)['name'=>'Classic Milk Tea'],   'user'=>(object)['name'=>'Maria S.'],'created_at'=>now()->subMinutes(10)],
            (object)['id'=>1004,'total_amount'=>600.00,'product'=>(object)['name'=>'Black Forest Milk Tea'],'user'=>(object)['name'=>'Juan D.'], 'created_at'=>now()->subMinutes(32)],
            (object)['id'=>1003,'total_amount'=>275.00,'product'=>(object)['name'=>'Iced Coffee'],        'user'=>(object)['name'=>'Ana R.'],  'created_at'=>now()->subMinutes(58)],
            (object)['id'=>1002,'total_amount'=>50.00, 'product'=>(object)['name'=>'Extra Sugar Shot'],   'user'=>(object)['name'=>'Maria S.'],'created_at'=>now()->subHours(2)],
            (object)['id'=>1001,'total_amount'=>450.00,'product'=>(object)['name'=>'Taro Milk Tea'],      'user'=>(object)['name'=>'Juan D.'], 'created_at'=>now()->subHours(3)],
        ]);
    }
    if (!isset($leakageRows) || $leakageRows->isEmpty()) {
        $leakageRows = collect([
            (object)['ingredient'=>(object)['name'=>'Whole Milk','unit'=>'L'],   'variance'=>-12.5],
            (object)['ingredient'=>(object)['name'=>'Flavor Powder','unit'=>'kg'],'variance'=>-3.2],
            (object)['ingredient'=>(object)['name'=>'Sugar','unit'=>'kg'],        'variance'=>-8.7],
            (object)['ingredient'=>(object)['name'=>'Black Tea Base','unit'=>'L'],'variance'=>-2.1],
        ]);
    }
    if (!isset($monthlySales) || $monthlySales->isEmpty()) {
        $monthlySales = collect([1=>42000,2=>38000,3=>55000,4=>61000,5=>70000,6=>89000,7=>94000,8=>0,9=>0,10=>0,11=>0,12=>0]);
    }
@endphp
@extends('layouts.sidebar')

@section('title', 'Business Summary')

@section('content')

<div class="flex gap-4 max-w-[1400px] mx-auto mt-6 px-8 pb-10 max-[900px]:px-4 max-[900px]:pb-8">

    {{-- Branch Sidebar --}}
    <div class="w-[120px] shrink-0 bg-accent rounded-[var(--radius-card)] flex flex-col items-center px-2.5 py-4 gap-2.5 max-[900px]:hidden">
        <div class="text-center px-1.5 py-2.5 bg-[rgba(250,249,247,.95)] border-[1.5px] border-[#5c2d1b] rounded-[10px] w-full">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="6" width="20" height="16" rx="2" fill="#BC614B" stroke="#5C2D1B" stroke-width="1.5"/>
                <path d="M22 8c2 0 4 1 4 4s-2 4-4 4" stroke="#5C2D1B" stroke-width="1.5" fill="none"/>
            </svg>
            <div class="text-[11px] font-bold mt-1.5">{{ $activeBranch?->name ?? 'All Branches' }}</div>
            <div class="text-[8px] font-semibold opacity-60 uppercase tracking-wider">{{ $activeBranch ? 'Active Branch' : 'Owner View' }}</div>
        </div>

        @php
            $isOwner = auth()->user()->role === 'super_admin';
            $userBranchId = auth()->user()->branch_id;
        @endphp

        <div class="flex flex-col items-center gap-2 w-full pt-2 border-t border-white/20">
            @foreach ($branches as $branch)
                @php $ini = collect(explode(' ', $branch->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode(''); @endphp
                <a href="#" class="{{ $activeBranch?->id === $branch->id ? 'bg-[#5c2d1b] text-cream border-[#5c2d1b]' : 'bg-[rgba(250,249,247,.9)] text-[#5c2d1b] hover:scale-[1.08] hover:bg-[#5c2d1b] hover:text-cream' }} w-10 h-10 rounded-full border-[1.5px] border-[#5c2d1b] flex items-center justify-center text-[10px] font-bold cursor-pointer transition-all duration-150 no-underline shrink-0" title="{{ $branch->name }}">{{ $ini }}</a>
            @endforeach
        </div>
    </div>

    {{-- Content Area --}}
    <div class="flex-1 min-w-0">

        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <h1 class="text-[22px] font-extrabold uppercase tracking-[.03em]">Businesses <span class="font-normal opacity-50">|</span> {{ $isOwner ? 'Owner' : 'Manager' }}</h1>
            @include('partials._business-tabs', ['active' => 'summary'])
        </div>

        {{-- 2-Column Grid --}}
        <div class="grid grid-cols-2 gap-4 max-[900px]:grid-cols-1">

            {{-- LEFT COLUMN --}}
            <div>
                <div class="card p-5 mb-4">
                    <h3 class="text-[13px] font-bold mb-3.5 pb-2.5 border-b border-line">Recent Transactions — {{ $activeBranch?->name ?? 'All' }}</h3>
                    @if ($recentTransactions->isEmpty())
                        <p class="text-[13px] opacity-40 py-2">No transactions recorded yet.</p>
                    @else
                        <ul class="list-none">
                            @foreach ($recentTransactions as $i => $tx)
                                <li class="flex justify-between items-baseline py-2 text-[13px] border-b border-[rgba(92,45,27,.06)]">
                                    <span><strong>Txn #{{ $tx->id }}</strong></span>
                                    <span>&#8369;{{ number_format($tx->total_amount, 2) }}</span>
                                </li>
                                <li class="text-[11px] opacity-60">
                                    <span>{{ $tx->product?->name ?? 'Unknown Item' }} — {{ $tx->created_at->format('M d, g:iA') }} · {{ $tx->user?->name ?? '—' }}</span>
                                </li>
                            @endforeach
                            <li class="flex justify-between items-baseline font-extrabold text-sm pt-3 border-t-2 border-[#5c2d1b]">
                                <span>TOTAL:</span>
                                <span>&#8369;{{ number_format($recentTransactions->sum('total_amount'), 2) }}</span>
                            </li>
                        </ul>
                    @endif
                </div>

                <div class="card p-5 mb-4">
                    <h3 class="text-[13px] font-bold mb-3.5 pb-2.5 border-b border-line">Leakage Log (Negative Variance)</h3>
                    @if ($leakageRows->isEmpty())
                        <p class="text-[13px] opacity-40 py-2">No leakage records found.</p>
                    @else
                        @foreach ($leakageRows as $row)
                            <div class="flex justify-between py-[9px] text-[13px] border-b border-[rgba(92,45,27,.06)] last:border-b-0">
                                <span>{{ $row->ingredient?->name ?? 'Unknown' }}</span>
                                <span class="font-bold text-red-600">{{ number_format($row->variance, 2) }} {{ $row->ingredient?->unit ?? '' }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div>
                <div class="card p-6 mb-4 text-center">
                    <h3 class="text-[11px] font-bold uppercase tracking-[.06em] opacity-50 mb-2.5">Annual Revenue ({{ now()->year }})</h3>
                    <div class="text-[38px] font-extrabold text-green-600">
                        &#8369;{{ $totalRevenue >= 1_000_000
                            ? number_format($totalRevenue / 1_000_000, 2) . 'M'
                            : ($totalRevenue >= 1_000 ? number_format($totalRevenue / 1_000, 1) . 'k' : number_format($totalRevenue, 2)) }}
                        @if ($totalRevenue > 0)<span class="text-[22px]">&uarr;</span>@endif
                    </div>
                </div>

                @php
                    $maxSales = $monthlySales->max() ?: 1;
                    $svgW = 300; $svgH = 110; $pad = 10;
                    $pts = collect(range(1, 12))->map(function ($m) use ($monthlySales, $maxSales, $svgW, $svgH, $pad) {
                        $x = $pad + ($m - 1) * (($svgW - $pad * 2) / 11);
                        $val = $monthlySales->get($m, 0);
                        $y = $svgH - $pad - ($val / $maxSales) * ($svgH - $pad * 2);
                        return "$x,$y";
                    })->implode(' ');
                    $polyClose = $pts . " $svgW,$svgH 0,$svgH";
                    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                @endphp

                <div class="card p-5 mb-4">
                    <h3 class="text-[13px] font-bold mb-3.5">Monthly Sales — {{ now()->year }}</h3>
                    <div class="w-full h-[140px] relative overflow-hidden">
                        <svg class="w-full h-full" viewBox="0 0 {{ $svgW }} {{ $svgH }}" preserveAspectRatio="none">
                            <polygon points="{{ $polyClose }}" fill="rgba(188,97,75,.08)"/>
                            <polyline points="{{ $pts }}" fill="none" stroke="#BC614B" stroke-width="2.5" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="flex justify-between text-[10px] opacity-50 mt-1.5">
                        @foreach (['Jan','Mar','May','Jul','Sep','Nov'] as $ml)<span>{{ $ml }}</span>@endforeach
                    </div>
                    @if ($monthlySales->isEmpty())
                        <p class="text-xs opacity-40 mt-2 text-center">No sales data yet for this year.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
