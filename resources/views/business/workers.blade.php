@php
    use App\Models\User;

    $branches = $branches ?? collect();
    $workers = $workers ?? collect();

    // ── Resolve selected worker ──────────────────────────────────────
    $reqWorkerId = request()->integer('worker', 0);
    $selectedUser = null;
    if ($reqWorkerId) {
        $selectedUser = $workers->firstWhere('id', $reqWorkerId);
    }
    if (!$selectedUser) {
        $selectedUser = $workers->first();
    }

    $name = $selectedUser?->name ?? 'Worker';
    $profile = $selectedUser?->profile ?? null;

    $selectedWorker = (object) [
        'id'              => $selectedUser?->id ?? 0,
        'name'            => $name,
        'role'            => $selectedUser?->role ?? 'staff',
        'role_label'      => $selectedUser?->role === User::ROLE_STAFF ? 'Staff' : 'Manager',
        'number'          => $profile?->phone ?? '—',
        'email'           => $selectedUser?->email ?? '—',
        'address'         => $profile?->address ?? '—',
        'birthday'        => $profile?->birthday ?? '—',
        'senior_high'     => $profile?->senior_high ?? '—',
        'college'         => $profile?->college ?? '—',
        'partner_contact' => $profile?->partner_contact ?? '—',
        'mother_contact'  => $profile?->mother_contact ?? '—',
        'skills'          => $profile?->skills ?? [],
        'note'            => $profile?->notes ?? 'No notes on file.',
        'schedule'        => $profile?->work_schedule ?? [],
        'performance'     => $profile?->performance_metrics ?? [],
        'rating'          => $profile?->rating ?? 0,
        'profile_id'      => $profile?->id,
        'peer_reviews'    => $selectedUser?->peerReviews->sortByDesc('created_at')->values() ?? collect(),
        'goals'           => $selectedUser?->goals->sortBy(fn ($g) => $g->status === 'completed' ? 1 : 0)->values() ?? collect(),
    ];

    $goalsCompletedCount = $selectedWorker->goals->where('status', 'completed')->count();
    $goalsTotalCount = $selectedWorker->goals->count();

    $initials = fn($name) => collect(explode(' ', $name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');

    $openShiftUserIds = $openShiftUserIds ?? [];

    // ── Headline stats — all real counts, no fabricated trend figures ──
    $totalEmployees = $workers->count();
    $managerCount   = $workers->where('role', User::ROLE_MANAGER)->count();
    $staffCount     = $workers->where('role', User::ROLE_STAFF)->count();
    $onShiftCount   = count($openShiftUserIds);
    $newThisMonthCount = $workers->where('created_at', '>=', now()->startOfMonth())->count();

    // ── Directory view: workers grouped by branch, filtered by ?branch_id ──
    $reqBranchId = request()->integer('branch_id', 0);
    $directoryWorkers = $reqBranchId ? $workers->where('branch_id', $reqBranchId) : $workers;
    $workersByBranch = $branches
        ->when($reqBranchId, fn ($bs) => $bs->where('id', $reqBranchId))
        ->map(fn ($branch) => [
            'branch'   => $branch,
            'managers' => $directoryWorkers->where('branch_id', $branch->id)->where('role', User::ROLE_MANAGER)->values(),
            'staff'    => $directoryWorkers->where('branch_id', $branch->id)->where('role', User::ROLE_STAFF)->values(),
        ])
        ->filter(fn ($g) => $g['managers']->isNotEmpty() || $g['staff']->isNotEmpty())
        ->values();

    // ── Workers data for JS (pre-computed to avoid @json parsing issues) ──
    $workersJsData = $workers->map(fn($w) => [
        'id'         => $w->id,
        'name'       => $w->name,
        'email'      => $w->email,
        'role'       => $w->role,
        'branch_id'  => $w->branch_id,
        'branch_name'=> $w->branch?->name ?? '',
        'is_on_shift'=> in_array($w->id, $openShiftUserIds),
    ])->values();
@endphp
@extends('layouts.sidebar')

@section('title', 'Workers')

@section('content')
<style>
.form-error{font-size:11px;color:#dc2626;font-weight:600;display:none}
.form-error.is-visible{display:block}
.form-input.error{border-color:#dc2626}
@keyframes toast-in{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:translateX(0)}}
</style>

<div class="max-w-[1400px] mx-auto px-8 py-6 flex flex-col gap-4 max-[880px]:p-4">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="text-[22px] font-extrabold tracking-tight">Employees</div>
            <div class="text-[13px] text-ink-2 mt-0.5">Manage your roster, track shifts, and review performance.</div>
        </div>
        @include('partials._business-tabs', ['active' => 'workers'])
    </div>

    {{-- Branch Filter + Actions --}}
    <div class="flex items-center justify-between flex-wrap gap-3 pb-3 border-b border-line">
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ url('/business/workers') }}" class="px-4 py-[7px] rounded-full text-xs font-semibold no-underline transition-colors {{ !$reqBranchId ? 'bg-accent text-white' : 'border-[1.5px] border-line bg-white text-[var(--color-ink)] hover:border-accent hover:text-accent' }}">All</a>
            @foreach ($branches as $branch)
                <a href="{{ url('/business/workers') }}?branch_id={{ $branch->id }}" class="px-4 py-[7px] rounded-full text-xs font-semibold no-underline transition-colors {{ $reqBranchId === $branch->id ? 'bg-accent text-white' : 'border-[1.5px] border-line bg-white text-[var(--color-ink)] hover:border-accent hover:text-accent' }}">{{ $branch->name }}</a>
            @endforeach
        </div>
        <div class="flex items-center gap-2">
            <input type="text" id="searchInput" class="form-input" placeholder="Search employees…" style="padding:7px 12px;font-size:12px;border-radius:20px;min-width:180px;">
            <a href="{{ route('hiring.index') }}" class="px-4 py-[9px] rounded-full text-xs font-semibold no-underline bg-accent text-white transition-opacity hover:opacity-90 whitespace-nowrap">
                <span class="flex items-center gap-1.5">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    Open Positions
                </span>
            </a>
            <button type="button" id="addEmployeeBtn" class="px-4 py-[9px] rounded-full text-xs font-semibold cursor-pointer border-[1.5px] border-line bg-white text-[var(--color-ink)] transition-all duration-150 hover:bg-accent hover:text-white hover:border-accent whitespace-nowrap">
                <span class="flex items-center gap-1.5">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Employee
                </span>
            </button>
        </div>
    </div>

    {{-- Headline Stats --}}
    <div class="grid grid-cols-5 max-[880px]:grid-cols-2 card border-[1.5px] border-line overflow-hidden divide-x divide-line max-[880px]:divide-x-0">
        <div class="p-4 text-center max-[880px]:border-b max-[880px]:border-line">
            <div class="text-[28px] font-extrabold text-accent leading-none">{{ $totalEmployees }}</div>
            <div class="text-[10px] font-bold uppercase tracking-[.05em] opacity-50 mt-1.5">Total Employees</div>
        </div>
        <div class="p-4 text-center max-[880px]:border-b max-[880px]:border-line">
            <div class="text-[28px] font-extrabold text-accent leading-none">{{ $newThisMonthCount }}</div>
            <div class="text-[10px] font-bold uppercase tracking-[.05em] opacity-50 mt-1.5">New This Month</div>
        </div>
        <div class="p-4 text-center max-[880px]:border-b max-[880px]:border-line">
            <div class="text-[28px] font-extrabold text-accent leading-none">{{ $managerCount }}</div>
            <div class="text-[10px] font-bold uppercase tracking-[.05em] opacity-50 mt-1.5">Managers</div>
        </div>
        <div class="p-4 text-center">
            <div class="text-[28px] font-extrabold text-accent leading-none">{{ $staffCount }}</div>
            <div class="text-[10px] font-bold uppercase tracking-[.05em] opacity-50 mt-1.5">Staff</div>
        </div>
        <div class="p-4 text-center">
            <div class="text-[28px] font-extrabold {{ $onShiftCount > 0 ? 'text-green-600' : 'text-accent' }} leading-none">{{ $onShiftCount }}</div>
            <div class="text-[10px] font-bold uppercase tracking-[.05em] opacity-50 mt-1.5">On Shift Now</div>
        </div>
    </div>

    @if ($reqWorkerId)
    {{-- ═══════════════════ PROFILE VIEW ═══════════════════ --}}
    @include('partials._breadcrumbs', ['bc_items' => [
        ['label' => 'Employees', 'url' => url('/business/workers') . ($reqBranchId ? '?branch_id='.$reqBranchId : '')],
        ['label' => $selectedWorker->name, 'url' => null],
    ]])

        {{-- Worker Profile Card --}}
        <div class="card border-[1.5px] border-line overflow-hidden">
            <div class="flex flex-wrap items-center gap-4 px-6 py-5 border-b border-line">
                <div class="avatar avatar-lg border-[3px] border-accent/20">{{ $initials($selectedWorker->name) }}</div>
                <div class="flex-1 min-w-[140px]" style="word-break:keep-all">
                    <div class="text-lg font-extrabold">{{ $selectedWorker->name }}</div>
                    <span class="inline-block mt-1 px-3 py-[3px] rounded-full bg-accent/10 text-accent text-[11px] font-semibold">{{ $selectedWorker->role }}</span>
                </div>
                @if ($selectedUser)
                @php
                    $isOnShift = in_array($selectedUser->id, $openShiftUserIds);
                @endphp
                <div class="flex items-center gap-1.5 ml-auto flex-wrap justify-end max-[1200px]:basis-full max-[1200px]:ml-0">
                    <div id="onShiftIndicator" class="items-center gap-[5px] px-2.5 py-1 rounded-full bg-green-600/10 text-green-600 text-[10px] font-bold" style="display:{{ $isOnShift ? 'flex' : 'none' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>
                        On Shift
                    </div>
                    <button type="button" class="px-3 py-[7px] rounded-lg text-[11px] font-semibold cursor-pointer transition-all duration-[120ms] border-[1.5px] flex items-center gap-[5px] whitespace-nowrap {{ $isOnShift ? 'bg-green-600 text-white border-green-600' : 'bg-accent text-white border-accent' }}" id="clockBtn"
                        data-worker-id="{{ $selectedWorker->id }}"
                        data-on-shift="{{ $isOnShift ? 'true' : 'false' }}"
                        onclick="toggleClock(this)">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span id="clockBtnText">{{ $isOnShift ? 'Clock Out' : 'Clock In' }}</span>
                    </button>
                    <button type="button" class="px-3 py-[7px] rounded-lg text-[11px] font-semibold cursor-pointer transition-all duration-[120ms] border-[1.5px] border-line bg-white text-[var(--color-ink)] flex items-center gap-[5px] whitespace-nowrap hover:border-accent hover:text-accent" onclick="openProfileModal({{ $selectedWorker->id }}, {{ $selectedWorker->profile_id ?? 'null' }})" title="Edit profile">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        Profile
                    </button>
                    <button type="button" class="px-3 py-[7px] rounded-lg text-[11px] font-semibold cursor-pointer transition-all duration-[120ms] border-[1.5px] border-line bg-white text-[var(--color-ink)] flex items-center gap-[5px] whitespace-nowrap hover:border-accent hover:text-accent" onclick="openEditModal({{ $selectedWorker->id }})" title="Edit worker">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </button>
                    <button type="button" class="px-3 py-[7px] rounded-lg text-[11px] font-semibold cursor-pointer transition-all duration-[120ms] border-[1.5px] border-line bg-white text-[var(--color-ink)] flex items-center gap-[5px] whitespace-nowrap hover:border-red-600 hover:text-red-600" onclick="openDeleteModal({{ $selectedWorker->id }}, '{{ addslashes($selectedWorker->name) }}')" title="Delete worker">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Delete
                    </button>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] max-[1100px]:grid-cols-1">
                {{-- Col 1: Contact Info --}}
                <div class="px-6 py-5 border-r border-line max-[1100px]:border-r-0 max-[1100px]:border-b">
                    <div class="section-label mb-3.5">Contact Info</div>
                    <div class="flex flex-col gap-0.5 mb-3.5">
                        <span class="text-[10px] font-semibold uppercase tracking-[.04em] opacity-40">Number</span>
                        <span class="text-[13px] font-semibold [overflow-wrap:break-word]">{{ $selectedWorker->number }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5 mb-3.5">
                        <span class="text-[10px] font-semibold uppercase tracking-[.04em] opacity-40">Email</span>
                        <span class="text-[13px] font-semibold [overflow-wrap:break-word]">
                            <a href="mailto:{{ $selectedWorker->email }}" class="text-accent no-underline hover:underline">{{ $selectedWorker->email }}</a>
                        </span>
                    </div>
                    <div class="flex flex-col gap-0.5 mb-3.5">
                        <span class="text-[10px] font-semibold uppercase tracking-[.04em] opacity-40">Address</span>
                        <span class="text-[13px] font-semibold [overflow-wrap:break-word]">{{ $selectedWorker->address }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] font-semibold uppercase tracking-[.04em] opacity-40">Birthday</span>
                        <span class="text-[13px] font-semibold [overflow-wrap:break-word]">{{ $selectedWorker->birthday }}</span>
                    </div>
                </div>
                {{-- Col 2: Education & Emergency --}}
                <div class="px-6 py-5">
                    <div class="section-label mb-3.5">Education</div>
                    <div class="flex flex-col gap-0.5 mb-3.5">
                        <span class="text-[10px] font-semibold uppercase tracking-[.04em] opacity-40">Senior High</span>
                        <span class="text-[13px] font-semibold [overflow-wrap:break-word]">{{ $selectedWorker->senior_high }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5 mb-3.5">
                        <span class="text-[10px] font-semibold uppercase tracking-[.04em] opacity-40">College</span>
                        <span class="text-[13px] font-semibold [overflow-wrap:break-word]">{{ $selectedWorker->college }}</span>
                    </div>
                    <div class="section-label mb-3.5 mt-4">Emergency Contact</div>
                    <div class="flex flex-col gap-0.5 mb-3.5">
                        <span class="text-[10px] font-semibold uppercase tracking-[.04em] opacity-40">Partner's Contact</span>
                        <span class="text-[13px] font-semibold [overflow-wrap:break-word]">{{ $selectedWorker->partner_contact }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] font-semibold uppercase tracking-[.04em] opacity-40">Mother's Contact</span>
                        <span class="text-[13px] font-semibold [overflow-wrap:break-word]">{{ $selectedWorker->mother_contact }}</span>
                    </div>
                </div>
            </div>

            {{-- Skills & Notes Footer --}}
            <div class="grid grid-cols-2 border-t border-line px-6 py-4 max-[1100px]:grid-cols-1 max-[1100px]:gap-4">
                <div class="flex flex-col gap-2">
                    <span class="section-label">Skills</span>
                    <div class="flex gap-1.5 flex-wrap">
                        @foreach ($selectedWorker->skills as $skill)
                            <span class="px-3 py-1 rounded-full bg-accent/[.08] text-accent text-[11px] font-semibold">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    <span class="section-label">Note</span>
                    <div class="text-xs font-medium bg-[rgba(250,249,247,.6)] px-4 py-3 rounded-lg border border-[rgba(92,45,27,.08)] leading-relaxed w-full">{{ $selectedWorker->note }}</div>
                </div>
            </div>
        </div>

        {{-- Bottom Three-Up: Work Shift + Performance + Peer Review --}}
        <div class="grid grid-cols-3 gap-4 max-[1100px]:grid-cols-1">
            <div class="card card-accent-top p-5">
                <div class="section-label mb-4">Work Shift</div>
                <div class="grid grid-cols-[48px_1fr] gap-x-3 gap-y-0.5" id="scheduleDisplay">
                    @forelse ($selectedWorker->schedule as $day => $hours)
                        <div class="contents">
                            <span class="text-xs font-bold tracking-[.03em] py-[7px] border-b border-[rgba(92,45,27,.08)]">{{ $day }}</span>
                            <span class="text-xs font-medium py-[7px] border-b border-[rgba(92,45,27,.08)] opacity-75">{{ $hours }}</span>
                        </div>
                    @empty
                        <div class="contents"><span class="text-xs opacity-40 col-span-2 py-[7px]">No schedule set yet.</span></div>
                    @endforelse
                </div>
                <div class="flex items-center justify-between mt-3.5 pt-3 border-t border-line">
                    <span class="inline-block px-3 py-1 rounded-full bg-accent/[.08] text-accent text-[10px] font-bold uppercase tracking-[.04em]">Weekends — Off</span>
                    <button type="button" class="px-3.5 py-[5px] rounded-md border-[1.5px] border-line bg-white cursor-pointer text-[11px] font-semibold transition-all duration-[120ms] hover:border-accent hover:text-accent" onclick="openScheduleModal({{ $selectedWorker->id }})">Edit</button>
                </div>
            </div>
            <div class="card border-[1.5px] border-line p-5">
                <div class="flex items-center justify-between mb-3.5 pb-3 border-b border-line">
                    <span class="section-label">Performance</span>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-600/10 text-green-600 text-sm font-extrabold">
                                <span class="inline-flex gap-px text-green-600 text-[11px]">
                                    @php
                                        $fullStars = floor($selectedWorker->rating);
                                        $halfStar = $selectedWorker->rating - $fullStars >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                    @endphp
                                    @for ($i = 0; $i < $fullStars; $i++)&#9733;@endfor
                                    @if ($halfStar)&#9734;@endif
                                    @for ($i = 0; $i < $emptyStars; $i++)&#9734;@endfor
                                </span>
                                {{ number_format($selectedWorker->rating, 1) }}
                            </span>
                        </div>
                        <button type="button" class="border-[1.5px] border-line bg-white cursor-pointer px-3 py-1 rounded-md text-[11px] font-semibold transition-all duration-[120ms] hover:border-accent hover:text-accent" onclick="openPerfModal({{ $selectedWorker->id }})">Record</button>
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    @forelse ($selectedWorker->performance as $metric)
                        <div class="flex items-center gap-2.5 p-2.5 px-3.5 bg-[rgba(22,163,74,.04)] border border-[rgba(22,163,74,.12)] rounded-lg text-[13px] font-medium">
                            <span class="w-2 h-2 rounded-full bg-green-600 shrink-0"></span>
                            <span class="flex-1">{{ $metric }}</span>
                        </div>
                    @empty
                        <div class="flex items-center gap-2.5 p-2.5 px-3.5 bg-[rgba(22,163,74,.04)] border border-[rgba(22,163,74,.12)] rounded-lg text-[13px] font-medium">
                            <span class="flex-1 opacity-40">No performance data recorded yet.</span>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="card card-accent-top p-5 flex flex-col">
                <div class="flex items-center justify-between mb-3.5 pb-3 border-b border-line">
                    <span class="section-label">Peer Review</span>
                    <button type="button" class="border-[1.5px] border-line bg-white cursor-pointer px-3 py-1 rounded-md text-[11px] font-semibold transition-all duration-[120ms] hover:border-accent hover:text-accent" onclick="openReviewModal({{ $selectedWorker->id }})">
                        <span class="flex items-center gap-1">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add
                        </span>
                    </button>
                </div>
                <div class="flex flex-col gap-2.5 overflow-y-auto max-h-[260px] pr-0.5" id="peerReviewList">
                    @forelse ($selectedWorker->peer_reviews as $review)
                        @php
                            $reviewerName = $review->reviewer->name ?? 'Unknown';
                            $reviewerInitials = collect(explode(' ', $reviewerName))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
                        @endphp
                        <div class="relative p-3 pl-4 bg-[rgba(92,45,27,.03)] border border-[rgba(92,45,27,.08)] rounded-lg border-l-[3px] border-l-accent/40" data-review-id="{{ $review->id }}">
                            <div class="flex items-start gap-2.5">
                                <div class="avatar avatar-sm mt-0.5">{{ $reviewerInitials }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11.5px] font-bold truncate">{{ $reviewerName }}</span>
                                        <button type="button" class="w-6 h-6 rounded-full flex items-center justify-center text-ink-3 hover:bg-red-600/10 hover:text-red-600 transition-colors shrink-0 cursor-pointer bg-transparent border-none p-0" onclick="deletePeerReview({{ $review->id }})" title="Delete review">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        @if ($review->rating !== null)
                                            <span class="inline-flex gap-px text-accent text-[10px]">
                                                @php $rFull = floor($review->rating); $rHalf = $review->rating - $rFull >= 0.5; @endphp
                                                @for ($i = 0; $i < $rFull; $i++)&#9733;@endfor
                                                @if ($rHalf)&#9734;@endif
                                                @for ($i = 0; $i < (5 - $rFull - ($rHalf ? 1 : 0)); $i++)&#9734;@endfor
                                            </span>
                                        @endif
                                        <span class="text-[10px] opacity-40">{{ $review->created_at->format('M j, Y') }}</span>
                                    </div>
                                    <div class="text-[12.5px] mt-1.5 leading-snug italic opacity-90">&ldquo;{{ $review->comment }}&rdquo;</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state-icon !py-5">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span class="empty-state-text">No reviews yet.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- GOALS --}}
        <div class="card card-accent-top p-5">
            <div class="flex items-center justify-between mb-3 gap-3 flex-wrap">
                <span class="section-label">Goals</span>
                <div class="flex items-center gap-3">
                    @if ($goalsTotalCount > 0)
                        <span class="text-[11px] font-semibold opacity-50">{{ $goalsCompletedCount }} of {{ $goalsTotalCount }} completed</span>
                    @endif
                    <button type="button" class="border-[1.5px] border-line bg-white cursor-pointer px-3 py-1 rounded-md text-[11px] font-semibold transition-all duration-[120ms] hover:border-accent hover:text-accent" onclick="openGoalModal({{ $selectedWorker->id }})">
                        <span class="flex items-center gap-1">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Goal
                        </span>
                    </button>
                </div>
            </div>
            @if ($goalsTotalCount > 0)
                <div class="h-1.5 rounded-full bg-[rgba(92,45,27,.08)] overflow-hidden mb-4">
                    <div class="h-full bg-green-600 rounded-full transition-all duration-300" style="width: {{ $goalsTotalCount ? round($goalsCompletedCount / $goalsTotalCount * 100) : 0 }}%"></div>
                </div>
            @else
                <div class="mb-1"></div>
            @endif
            <div class="flex flex-col gap-1.5" id="goalsList">
                @forelse ($selectedWorker->goals as $goal)
                    @php $isDone = $goal->status === 'completed'; @endphp
                    <div class="flex items-center gap-3 p-2.5 px-3.5 rounded-lg border {{ $isDone ? 'bg-green-600/[.04] border-green-600/[.12]' : 'bg-[rgba(92,45,27,.03)] border-[rgba(92,45,27,.08)]' }}" data-goal-id="{{ $goal->id }}">
                        <label class="relative shrink-0 cursor-pointer block w-5 h-5">
                            <input type="checkbox" class="peer absolute inset-0 opacity-0 w-5 h-5 cursor-pointer m-0 z-10" {{ $isDone ? 'checked' : '' }} onchange="toggleGoal({{ $goal->id }}, this.checked)">
                            <span class="absolute inset-0 rounded-full border-[1.5px] border-line transition-colors peer-checked:bg-green-600 peer-checked:border-green-600"></span>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="absolute inset-0 m-auto opacity-0 peer-checked:opacity-100 transition-opacity"><polyline points="20 6 9 17 4 12"/></svg>
                        </label>
                        <span class="flex-1 text-[13px] font-medium {{ $isDone ? 'line-through opacity-50' : '' }}">{{ $goal->title }}</span>
                        @if ($goal->target_date)
                            <span class="text-[11px] font-semibold {{ $isDone ? 'opacity-30' : 'text-accent' }} shrink-0">Due {{ $goal->target_date->format('M j') }}</span>
                        @endif
                        <button type="button" class="w-6 h-6 rounded-full flex items-center justify-center text-ink-3 hover:bg-red-600/10 hover:text-red-600 transition-colors shrink-0 cursor-pointer bg-transparent border-none p-0" onclick="deleteGoal({{ $goal->id }})" title="Delete goal">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                @empty
                    <div class="empty-state-icon !py-5">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                        <span class="empty-state-text">No goals set yet.</span>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ACTIVITY HISTORY --}}
        <div class="summary-table-wrap" id="activitySection">
            <div class="px-5 py-3.5 border-b border-line flex items-center gap-2.5">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-70 shrink-0">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                <span class="text-[13px] font-bold">Activity — <span id="activityName">{{ $selectedWorker->name }}</span></span>
                <div class="ml-auto flex gap-1">
                    <button type="button" class="tab active !px-3 !py-1 !text-[11px]" id="actTabTransactions" onclick="switchActivity({{ $selectedWorker->id }}, 'transactions')">Orders</button>
                    <button type="button" class="tab !px-3 !py-1 !text-[11px]" id="actTabShifts" onclick="switchActivity({{ $selectedWorker->id }}, 'shifts')">Shifts</button>
                    <button type="button" class="tab !px-3 !py-1 !text-[11px]" id="actTabDiscrepancies" onclick="switchActivity({{ $selectedWorker->id }}, 'discrepancies')">Variances</button>
                </div>
            </div>
            <div id="activityBody">
                <table class="summary-table" id="activityTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item / Shift</th>
                            <th>Detail</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="activityTableBody">
                        @for ($i = 0; $i < 3; $i++)
                        <tr><td colspan="5" class="py-2.5 px-5"><div class="skeleton skeleton--text" style="margin-bottom:0"></div></td></tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ATTENDANCE TIMESHEET --}}
        <div class="summary-table-wrap" id="attendanceSection">
            <div class="px-5 py-3.5 border-b border-line flex items-center gap-2.5">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-70 shrink-0">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <span class="text-[13px] font-bold">Attendance &mdash; <span id="attendanceName">{{ $selectedWorker->name }}</span></span>
                <button type="button" class="ml-auto px-3 py-[5px] text-[11px] font-semibold border-[1.5px] border-line bg-white rounded-md cursor-pointer transition-all hover:bg-[rgba(92,45,27,.04)]" onclick="loadAttendance({{ $selectedWorker->id }})">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline align-middle mr-1">
                        <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    Refresh
                </button>
            </div>
            <table class="summary-table" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="attendanceBody">
                    @for ($i = 0; $i < 3; $i++)
                    <tr><td colspan="5" class="py-2.5 px-5"><div class="skeleton skeleton--text" style="margin-bottom:0"></div></td></tr>
                    @endfor
                </tbody>
            </table>
        </div>

    @else
    {{-- ═══════════════════ DIRECTORY VIEW ═══════════════════ --}}
    <div class="flex flex-col gap-5" id="directoryView">
        @forelse ($workersByBranch as $group)
            <div class="card border-[1.5px] border-line overflow-hidden">
                <div class="px-5 py-3 border-b border-line bg-[rgba(92,45,27,.02)]">
                    <span class="text-[12.5px] font-bold">{{ $group['branch']->name }}</span>
                    <span class="text-[11px] opacity-50 ml-1.5">{{ $group['managers']->count() + $group['staff']->count() }} {{ Str::plural('employee', $group['managers']->count() + $group['staff']->count()) }}</span>
                </div>
                <div class="grid grid-cols-2 max-[700px]:grid-cols-1 divide-x max-[700px]:divide-x-0 max-[700px]:divide-y divide-line">
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-[.06em] text-accent mb-2.5">Managers</div>
                        <div class="flex flex-col gap-1">
                            @forelse ($group['managers'] as $worker)
                                <a href="{{ url('/business/workers') }}?worker={{ $worker->id }}{{ $reqBranchId ? '&branch_id='.$reqBranchId : '' }}" class="employee-row flex items-center gap-2.5 px-2.5 py-2 rounded-lg no-underline text-[var(--color-ink)] transition-colors hover:bg-[rgba(92,45,27,.04)]" data-worker-name="{{ strtolower($worker->name) }}">
                                    <div class="avatar avatar-md">{{ $initials($worker->name) }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[13px] font-semibold truncate">{{ $worker->name }}</div>
                                        @if (in_array($worker->id, $openShiftUserIds))
                                            <div class="text-[10px] font-semibold text-green-600 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>On Shift</div>
                                        @endif
                                    </div>
                                    <button type="button" class="quick-view-trigger" title="Quick view"
                                        onclick="event.preventDefault(); event.stopPropagation(); showQuickView(this, {
                                            name: '{{ addslashes($worker->name) }}',
                                            initials: '{{ addslashes($initials($worker->name)) }}',
                                            role: 'Manager',
                                            branch: '{{ addslashes($group['branch']->name) }}',
                                            onShift: {{ in_array($worker->id, $openShiftUserIds) ? 'true' : 'false' }},
                                            profileUrl: '{{ url('/business/workers') }}?worker={{ $worker->id }}{{ $reqBranchId ? '&branch_id='.$reqBranchId : '' }}'
                                        });">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                    </button>
                                </a>
                            @empty
                                <div class="text-[12px] opacity-40 px-2.5 py-1.5">No managers at this branch.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="text-[10px] font-bold uppercase tracking-[.06em] text-accent mb-2.5">Staff</div>
                        <div class="flex flex-col gap-1">
                            @forelse ($group['staff'] as $worker)
                                <a href="{{ url('/business/workers') }}?worker={{ $worker->id }}{{ $reqBranchId ? '&branch_id='.$reqBranchId : '' }}" class="employee-row flex items-center gap-2.5 px-2.5 py-2 rounded-lg no-underline text-[var(--color-ink)] transition-colors hover:bg-[rgba(92,45,27,.04)]" data-worker-name="{{ strtolower($worker->name) }}">
                                    <div class="avatar avatar-md">{{ $initials($worker->name) }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[13px] font-semibold truncate">{{ $worker->name }}</div>
                                        @if (in_array($worker->id, $openShiftUserIds))
                                            <div class="text-[10px] font-semibold text-green-600 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>On Shift</div>
                                        @endif
                                    </div>
                                    <button type="button" class="quick-view-trigger" title="Quick view"
                                        onclick="event.preventDefault(); event.stopPropagation(); showQuickView(this, {
                                            name: '{{ addslashes($worker->name) }}',
                                            initials: '{{ addslashes($initials($worker->name)) }}',
                                            role: 'Staff',
                                            branch: '{{ addslashes($group['branch']->name) }}',
                                            onShift: {{ in_array($worker->id, $openShiftUserIds) ? 'true' : 'false' }},
                                            profileUrl: '{{ url('/business/workers') }}?worker={{ $worker->id }}{{ $reqBranchId ? '&branch_id='.$reqBranchId : '' }}'
                                        });">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                    </button>
                                </a>
                            @empty
                                <div class="text-[12px] opacity-40 px-2.5 py-1.5">No staff at this branch.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-[1.5px] border-line p-8 text-center text-sm opacity-50">
                No workers yet. Click "Add Employee" to get started.
            </div>
        @endforelse
    </div>

    {{-- OPEN POSITIONS --}}
    <div class="card card-accent-top p-5">
        <div class="flex items-center justify-between mb-3.5 pb-3 border-b border-line">
            <span class="section-label flex items-center gap-1.5">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                Open Positions
            </span>
            <a href="{{ route('hiring.index') }}" class="text-[11px] font-semibold text-accent no-underline hover:underline flex items-center gap-1">
                Manage in Hiring
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(210px,1fr))] gap-3">
            @forelse ($openPositions as $opening)
                <div class="p-3.5 bg-[rgba(92,45,27,.03)] border border-[rgba(92,45,27,.08)] rounded-lg transition-colors hover:border-accent/40 hover:bg-accent/[.03]">
                    <div class="flex items-start justify-between gap-2">
                        <div class="text-[13px] font-bold leading-tight">{{ $opening->title }}</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-accent/10 text-accent text-[10px] font-bold shrink-0">{{ $opening->applicants_count }}</span>
                    </div>
                    <div class="flex items-center gap-1 text-[11px] opacity-50 mt-1.5">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $opening->branch->name ?? 'All branches' }}
                    </div>
                    <div class="text-[11px] font-semibold text-accent mt-1">{{ $opening->applicants_count }} {{ Str::plural('applicant', $opening->applicants_count) }}</div>
                </div>
            @empty
                <div class="empty-state-icon !py-5">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <span class="empty-state-text">No open positions right now.</span>
                </div>
            @endforelse
        </div>
    </div>
    @endif

</div>

{{-- Toast container --}}
<div class="toast-container" id="toastContainer"></div>

{{-- ADD EMPLOYEE MODAL --}}
<div class="modal-overlay" id="addModal">
    <div class="bg-card rounded-[var(--radius-card)] shadow-[0_16px_48px_rgba(44,24,16,.25)] w-full max-w-[460px] max-h-[90vh] overflow-y-auto px-8 py-7 pb-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-[17px] font-extrabold">Add New Worker</h2>
            <button type="button" class="w-8 h-8 rounded-lg bg-transparent border-none cursor-pointer flex items-center justify-center text-lg transition-colors hover:bg-[rgba(92,45,27,.07)]" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form id="addForm" onsubmit="return submitAdd(event)">
            @csrf
            <div class="flex flex-col gap-3.5">
                <div class="form-group">
                    <label class="form-label">Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Juan dela Cruz" required>
                    <span class="form-error" id="addErrorName"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="e.g. juan@nita.com">
                    <span class="form-error" id="addErrorEmail"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">PIN <span class="text-red-600">*</span></label>
                    <input type="text" name="pin" class="form-input" placeholder="4-8 digit PIN" maxlength="8" required>
                    <span class="form-error" id="addErrorPin"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Branch <span class="text-red-600">*</span></label>
                    <select name="branch_id" class="form-input" required>
                        <option value="">Select a branch…</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <span class="form-error" id="addErrorBranch"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-input">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2.5 justify-end mt-5 pt-4 border-t border-line">
                <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-save" id="addSubmitBtn">Add Worker</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT EMPLOYEE MODAL --}}
<div class="modal-overlay" id="editModal">
    <div class="bg-card rounded-[var(--radius-card)] shadow-[0_16px_48px_rgba(44,24,16,.25)] w-full max-w-[460px] max-h-[90vh] overflow-y-auto px-8 py-7 pb-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-[17px] font-extrabold">Edit Worker</h2>
            <button type="button" class="w-8 h-8 rounded-lg bg-transparent border-none cursor-pointer flex items-center justify-center text-lg transition-colors hover:bg-[rgba(92,45,27,.07)]" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="return submitEdit(event)">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="editId">
            <div class="flex flex-col gap-3.5">
                <div class="form-group">
                    <label class="form-label">Name <span class="text-red-600">*</span></label>
                    <input type="text" name="name" id="editName" class="form-input" required>
                    <span class="form-error" id="editErrorName"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="editEmail" class="form-input">
                    <span class="form-error" id="editErrorEmail"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">New PIN <span class="text-[10px] opacity-40 font-normal">(leave blank to keep current)</span></label>
                    <input type="text" name="pin" class="form-input" placeholder="4-8 digit PIN" maxlength="8">
                    <span class="form-error" id="editErrorPin"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Branch <span class="text-red-600">*</span></label>
                    <select name="branch_id" id="editBranch" class="form-input" required>
                        <option value="">Select a branch…</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <span class="form-error" id="editErrorBranch"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" id="editRole" class="form-input">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2.5 justify-end mt-5 pt-4 border-t border-line">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-save" id="editSubmitBtn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal-overlay" id="deleteModal">
    <div class="bg-card rounded-[var(--radius-card)] shadow-[0_16px_48px_rgba(44,24,16,.25)] w-full max-w-[460px] max-h-[90vh] overflow-y-auto px-8 py-7 pb-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-[17px] font-extrabold">Delete Worker</h2>
            <button type="button" class="w-8 h-8 rounded-lg bg-transparent border-none cursor-pointer flex items-center justify-center text-lg transition-colors hover:bg-[rgba(92,45,27,.07)]" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="flex flex-col gap-3.5">
            <p class="text-sm leading-relaxed opacity-80">
                Are you sure you want to delete
                <strong id="deleteName"></strong>?
                This action cannot be undone.
            </p>
        </div>
        <div class="flex gap-2.5 justify-end mt-5 pt-4 border-t border-line">
            <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
            <button type="button" class="btn-danger" id="deleteConfirmBtn">Delete</button>
        </div>
        <form id="deleteForm" method="POST" style="display:none">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

{{-- SCHEDULE EDITOR MODAL --}}
<div class="modal-overlay" id="scheduleModal">
    <div class="bg-card rounded-[var(--radius-card)] shadow-[0_16px_48px_rgba(44,24,16,.25)] w-full max-w-[500px] max-h-[90vh] overflow-y-auto px-8 py-7 pb-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-[17px] font-extrabold">Edit Work Schedule</h2>
            <button type="button" class="w-8 h-8 rounded-lg bg-transparent border-none cursor-pointer flex items-center justify-center text-lg transition-colors hover:bg-[rgba(92,45,27,.07)]" onclick="closeModal('scheduleModal')">&times;</button>
        </div>
        <form id="scheduleForm" onsubmit="return submitSchedule(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="schedWorkerId" value="{{ $selectedWorker->id }}">
            <div class="flex flex-col gap-3.5">
                @php $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']; @endphp
                @foreach ($days as $day)
                    <div class="flex items-center gap-3">
                        <label class="min-w-[40px] text-[13px] opacity-80">{{ $day }}</label>
                        <input type="text" class="form-input sched-input flex-1" data-day="{{ $day }}" placeholder="e.g. 10:00 AM — 8:00 PM" value="{{ $selectedWorker->schedule[$day] ?? '' }}">
                    </div>
                @endforeach
                <div class="form-group">
                    <label class="form-label">Weekend Note</label>
                    <input type="text" class="form-input" id="schedWeekend" placeholder="e.g. Weekends — Off">
                </div>
            </div>
            <div class="flex gap-2.5 justify-end mt-5 pt-4 border-t border-line">
                <button type="button" class="btn-cancel" onclick="closeModal('scheduleModal')">Cancel</button>
                <button type="submit" class="btn-save" id="schedSubmitBtn">Save Schedule</button>
            </div>
        </form>
    </div>
</div>

{{-- RECORD PERFORMANCE MODAL --}}
<div class="modal-overlay" id="perfModal">
    <div class="bg-card rounded-[var(--radius-card)] shadow-[0_16px_48px_rgba(44,24,16,.25)] w-full max-w-[500px] max-h-[90vh] overflow-y-auto px-8 py-7 pb-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-[17px] font-extrabold">Record Performance</h2>
            <button type="button" class="w-8 h-8 rounded-lg bg-transparent border-none cursor-pointer flex items-center justify-center text-lg transition-colors hover:bg-[rgba(92,45,27,.07)]" onclick="closeModal('perfModal')">&times;</button>
        </div>
        <form id="perfForm" onsubmit="return submitPerformance(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="perfWorkerId" value="{{ $selectedWorker->id }}">
            <div class="flex flex-col gap-3.5">
                <div class="form-group">
                    <label class="form-label">Rating <span class="font-normal opacity-50 text-[10px]">(out of 5)</span></label>
                    <div class="flex items-center gap-3">
                        <input type="range" id="perfRating" min="0" max="5" step="0.1" value="{{ $selectedWorker->rating }}" oninput="document.getElementById('perfRatingDisplay').textContent = this.value" class="flex-1">
                        <span id="perfRatingDisplay" class="text-base font-extrabold min-w-[30px]">{{ $selectedWorker->rating }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Metrics <span class="font-normal opacity-50 text-[10px]">(one per line)</span></label>
                    <textarea class="form-input resize-y" id="perfMetrics" rows="5" placeholder="Always on time&#10;Good customer service&#10;Fast worker">{{ implode("\n", $selectedWorker->performance) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-input resize-y" id="perfNotes" rows="2" placeholder="Optional notes on this review…"></textarea>
                </div>
            </div>
            <div class="flex gap-2.5 justify-end mt-5 pt-4 border-t border-line">
                <button type="button" class="btn-cancel" onclick="closeModal('perfModal')">Cancel</button>
                <button type="submit" class="btn-save" id="perfSubmitBtn">Save Performance</button>
            </div>
        </form>
    </div>
</div>

{{-- PROFILE EDIT MODAL --}}
<div class="modal-overlay" id="profileModal">
    <div class="bg-card rounded-[var(--radius-card)] shadow-[0_16px_48px_rgba(44,24,16,.25)] w-full max-w-[560px] max-h-[90vh] overflow-y-auto px-8 py-7 pb-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-[17px] font-extrabold">Edit Profile</h2>
            <button type="button" class="w-8 h-8 rounded-lg bg-transparent border-none cursor-pointer flex items-center justify-center text-lg transition-colors hover:bg-[rgba(92,45,27,.07)]" onclick="closeModal('profileModal')">&times;</button>
        </div>
        <form id="profileForm" onsubmit="return submitProfile(event)">
            @csrf
            @method('PUT')
            <input type="hidden" name="worker_id" id="profileWorkerId">
            <div class="grid grid-cols-2 gap-3.5">
                {{-- Left column --}}
                <div class="flex flex-col gap-3.5">
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="pfPhone" class="form-input" placeholder="+63 912 345 6789">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" id="pfAddress" class="form-input" placeholder="123 Main St, City">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Birthday</label>
                        <input type="text" name="birthday" id="pfBirthday" class="form-input" placeholder="March 15, 1998">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Senior High</label>
                        <input type="text" name="senior_high" id="pfSeniorHigh" class="form-input" placeholder="School name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">College</label>
                        <input type="text" name="college" id="pfCollege" class="form-input" placeholder="University — Course">
                    </div>
                </div>
                {{-- Right column --}}
                <div class="flex flex-col gap-3.5">
                    <div class="form-group">
                        <label class="form-label">Partner's Contact</label>
                        <input type="text" name="partner_contact" id="pfPartnerContact" class="form-input" placeholder="+63 917 654 3210">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mother's Contact</label>
                        <input type="text" name="mother_contact" id="pfMotherContact" class="form-input" placeholder="+63 908 777 8888">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Skills <span class="text-[10px] opacity-40">(comma-separated)</span></label>
                        <input type="text" name="skills" id="pfSkills" class="form-input" placeholder="Barista, Chef, Marketing">
                    </div>
                    <div class="form-group col-span-2">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="pfNotes" class="form-input resize-y" rows="3" placeholder="Notes about this worker…"></textarea>
                    </div>
                </div>
            </div>
            <div class="flex gap-2.5 justify-end mt-5 pt-4 border-t border-line">
                <button type="button" class="btn-cancel" onclick="closeModal('profileModal')">Cancel</button>
                <button type="submit" class="btn-save" id="profileSubmitBtn">Save Profile</button>
            </div>
        </form>
    </div>
</div>

{{-- ADD PEER REVIEW MODAL --}}
<div class="modal-overlay" id="reviewModal">
    <div class="bg-card rounded-[var(--radius-card)] shadow-[0_16px_48px_rgba(44,24,16,.25)] w-full max-w-[460px] max-h-[90vh] overflow-y-auto px-8 py-7 pb-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-[17px] font-extrabold">Add Peer Review</h2>
            <button type="button" class="w-8 h-8 rounded-lg bg-transparent border-none cursor-pointer flex items-center justify-center text-lg transition-colors hover:bg-[rgba(92,45,27,.07)]" onclick="closeModal('reviewModal')">&times;</button>
        </div>
        <form id="reviewForm" onsubmit="return submitReview(event)">
            @csrf
            <input type="hidden" id="reviewWorkerId">
            <div class="flex flex-col gap-3.5">
                <div class="form-group">
                    <label class="form-label">Rating <span class="font-normal opacity-50 text-[10px]">(optional, out of 5)</span></label>
                    <div class="flex items-center gap-3">
                        <input type="range" id="reviewRating" min="0" max="5" step="0.5" value="0" oninput="document.getElementById('reviewRatingDisplay').textContent = this.value" class="flex-1">
                        <span id="reviewRatingDisplay" class="text-base font-extrabold min-w-[24px]">0</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Comment <span class="text-red-600">*</span></label>
                    <textarea id="reviewComment" class="form-input resize-y" rows="4" placeholder="How has this worker been doing?" required></textarea>
                    <span class="form-error" id="reviewErrorComment"></span>
                </div>
            </div>
            <div class="flex gap-2.5 justify-end mt-5 pt-4 border-t border-line">
                <button type="button" class="btn-cancel" onclick="closeModal('reviewModal')">Cancel</button>
                <button type="submit" class="btn-save" id="reviewSubmitBtn">Add Review</button>
            </div>
        </form>
    </div>
</div>

{{-- ADD GOAL MODAL --}}
<div class="modal-overlay" id="goalModal">
    <div class="bg-card rounded-[var(--radius-card)] shadow-[0_16px_48px_rgba(44,24,16,.25)] w-full max-w-[460px] max-h-[90vh] overflow-y-auto px-8 py-7 pb-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-[17px] font-extrabold">Add Goal</h2>
            <button type="button" class="w-8 h-8 rounded-lg bg-transparent border-none cursor-pointer flex items-center justify-center text-lg transition-colors hover:bg-[rgba(92,45,27,.07)]" onclick="closeModal('goalModal')">&times;</button>
        </div>
        <form id="goalForm" onsubmit="return submitGoal(event)">
            @csrf
            <input type="hidden" id="goalWorkerId">
            <div class="flex flex-col gap-3.5">
                <div class="form-group">
                    <label class="form-label">Goal <span class="text-red-600">*</span></label>
                    <input type="text" id="goalTitle" class="form-input" placeholder="e.g. Complete barista certification" required>
                    <span class="form-error" id="goalErrorTitle"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Target Date <span class="font-normal opacity-50 text-[10px]">(optional)</span></label>
                    <input type="date" id="goalTargetDate" class="form-input">
                </div>
            </div>
            <div class="flex gap-2.5 justify-end mt-5 pt-4 border-t border-line">
                <button type="button" class="btn-cancel" onclick="closeModal('goalModal')">Cancel</button>
                <button type="submit" class="btn-save" id="goalSubmitBtn">Add Goal</button>
            </div>
        </form>
    </div>
</div>

{{-- QUICK VIEW POPOVER (Directory view) --}}
<div id="quickViewPopover" class="quick-view-popover" style="display:none;">
    <div class="flex items-center gap-2.5 mb-2.5">
        <div class="avatar avatar-md" id="qvAvatar"></div>
        <div class="flex-1 min-w-0">
            <div class="text-[13px] font-bold truncate" id="qvName"></div>
            <div class="text-[11px] opacity-60" id="qvRole"></div>
        </div>
    </div>
    <div class="text-[12px] opacity-80 mb-1" id="qvBranch"></div>
    <div class="text-[11px] font-semibold flex items-center gap-1 mb-3" id="qvShift"></div>
    <a href="#" id="qvProfileLink" class="btn-sm" style="display:block;text-align:center;text-decoration:none;">View Full Profile</a>
</div>

<style>
    .quick-view-trigger {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 6px;
        border: none;
        background: transparent;
        color: var(--color-ink-2, #8a8580);
        cursor: pointer;
        flex-shrink: 0;
    }
    .quick-view-trigger:hover {
        background: rgba(92, 45, 27, .08);
        color: var(--color-accent, #5C2D1B);
    }
    .quick-view-popover {
        position: fixed;
        z-index: 60;
        width: 220px;
        background: var(--color-surface, #fff);
        border: 1.5px solid var(--color-line, #e5e0da);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        padding: 14px;
    }
</style>

<script>
    // ── CSRF Token Setup ──
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const workersData = @json($workersJsData);

    // ── Toast Notification ──
    function showToast(message, type) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast toast--' + type;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity .3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // ── Modal Helpers ──
    function openModal(id) {
        document.getElementById(id).classList.add('is-open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('is-open');
        document.querySelectorAll('#' + id + ' .form-error').forEach(el => {
            el.classList.remove('is-visible');
            el.textContent = '';
        });
        document.querySelectorAll('#' + id + ' .form-input').forEach(el => {
            el.classList.remove('error');
        });
    }

    // Close modals on backdrop click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('is-open');
            }
        });
    });

    // ── Quick View Popover (Directory view) ──
    function showQuickView(triggerEl, worker) {
        const popover = document.getElementById('quickViewPopover');

        document.getElementById('qvAvatar').textContent = worker.initials;
        document.getElementById('qvName').textContent = worker.name;
        document.getElementById('qvRole').textContent = worker.role;
        document.getElementById('qvBranch').textContent = worker.branch;
        document.getElementById('qvShift').innerHTML = worker.onShift
            ? '<span class="w-1.5 h-1.5 rounded-full bg-green-600" style="display:inline-block;"></span><span class="text-green-600">On Shift</span>'
            : '<span class="w-1.5 h-1.5 rounded-full bg-gray-400" style="display:inline-block;"></span><span class="opacity-50">Off Duty</span>';
        document.getElementById('qvProfileLink').href = worker.profileUrl;

        popover.style.display = 'block';
        const rect = triggerEl.getBoundingClientRect();
        const popRect = popover.getBoundingClientRect();
        let top = rect.bottom + 6;
        let left = rect.right - popRect.width;
        if (left < 8) left = 8;
        if (top + popRect.height > window.innerHeight - 8) top = rect.top - popRect.height - 6;
        popover.style.top = top + 'px';
        popover.style.left = left + 'px';
    }

    document.addEventListener('click', function (e) {
        const popover = document.getElementById('quickViewPopover');
        if (popover.style.display === 'none') return;
        if (!popover.contains(e.target) && !e.target.closest('.quick-view-trigger')) {
            popover.style.display = 'none';
        }
    });

    // ── Live Search (Directory view) ──
    function filterEmployees() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();

        document.querySelectorAll('#directoryView .employee-row').forEach(row => {
            const name = row.getAttribute('data-worker-name') || '';
            row.style.display = (!query || name.includes(query)) ? '' : 'none';
        });
    }

    document.getElementById('searchInput')?.addEventListener('input', filterEmployees);

    // ── Clock In / Clock Out ──
    async function toggleClock(btn) {
        const indicator = document.getElementById('onShiftIndicator');
        const btnText = document.getElementById('clockBtnText');

        const currentlyOnShift = btn.getAttribute('data-on-shift') === 'true';
        const workerId = parseInt(btn.getAttribute('data-worker-id'));

        btn.disabled = true;
        btn.style.opacity = '.6';

        const endpoint = currentlyOnShift
            ? '{{ url('/business/workers') }}/' + workerId + '/clock-out'
            : '{{ url('/business/workers') }}/' + workerId + '/clock-in';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });

            const data = await response.json();

            if (!response.ok) {
                showToast(data.message || 'Failed to toggle shift.', 'error');
                btn.disabled = false;
                btn.style.opacity = '1';
                return;
            }

            showToast(data.message, 'success');

            // Toggle state visually without page reload
            const newOnShift = !currentlyOnShift;
            if (newOnShift) {
                indicator.style.display = 'flex';
                btn.style.background = '#16a34a';
                btn.style.color = '#fff';
                btn.style.borderColor = '#16a34a';
                btnText.textContent = 'Clock Out';
                btn.setAttribute('data-on-shift', 'true');
            } else {
                indicator.style.display = 'none';
                btn.style.background = '#fff';
                btn.style.color = 'var(--brown)';
                btn.style.borderColor = 'var(--border)';
                btnText.textContent = 'Clock In';
                btn.setAttribute('data-on-shift', 'false');
            }

            // Reload attendance list
            loadAttendance(workerId);

            btn.disabled = false;
            btn.style.opacity = '1';
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    }

    // ── Load Attendance History ──
    async function loadAttendance(workerId) {
        const tbody = document.getElementById('attendanceBody');
        const nameSpan = document.getElementById('attendanceName');

        tbody.innerHTML = Array.from({length: 3}).map(() => '<tr><td colspan="5" class="py-2.5 px-5"><div class="skeleton skeleton--text" style="margin-bottom:0"></div></td></tr>').join('');

        try {
            const response = await fetch('{{ url('/business/workers') }}/' + workerId + '/attendance', {
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json();

            if (!response.ok || !data.shifts) {
                tbody.innerHTML = `
                    <tr><td colspan="5" style="text-align:center;opacity:.4;padding:20px;">
                        No attendance data available.
                    </td></tr>`;
                return;
            }

            if (nameSpan) {
                const worker = workersData.find(w => w.id === workerId);
                if (worker) nameSpan.textContent = worker.name;
            }

            if (data.shifts.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="5" style="text-align:center;opacity:.4;padding:20px;">
                        No shifts recorded yet.
                    </td></tr>`;
                return;
            }

            tbody.innerHTML = data.shifts.map(s => `
                <tr>
                    <td style="font-weight:600">${s.date || '—'}</td>
                    <td>${s.clock_in || '—'}</td>
                    <td>${s.clock_out || '—'}</td>
                    <td>${s.duration || '—'}</td>
                    <td>
                        <span class="badge ${s.status === 'open' ? 'badge-open' : 'badge-closed'}">
                            ${s.status === 'open' ? 'On Shift' : 'Completed'}
                        </span>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `
                <tr><td colspan="5" style="text-align:center;opacity:.4;padding:20px;">
                    Failed to load attendance data.
                </td></tr>`;
        }
    }

    // ── Load attendance on page load for the selected worker ──
    document.addEventListener('DOMContentLoaded', function() {
        const selectedId = {{ $selectedWorker->id }};
        if (selectedId > 0) {
            loadAttendance(selectedId);
        }
    });

    // ── Open Add Modal ──
    document.getElementById('addEmployeeBtn')?.addEventListener('click', function() {
        openModal('addModal');
    });

    // ── Add Form Submission ──
    async function submitAdd(event) {
        event.preventDefault();
        const form = document.getElementById('addForm');
        const btn = document.getElementById('addSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'Adding…';

        try {
            const formData = new FormData(form);
            const response = await fetch('{{ route('business.workers.store') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    for (const [field, messages] of Object.entries(data.errors)) {
                        const errorEl = document.getElementById('addError' + field.charAt(0).toUpperCase() + field.slice(1));
                        const inputEl = form.querySelector('[name="' + field + '"]');
                        if (errorEl) {
                            errorEl.textContent = messages[0];
                            errorEl.classList.add('is-visible');
                        }
                        if (inputEl) inputEl.classList.add('error');
                    }
                } else {
                    showToast(data.message || 'An error occurred.', 'error');
                }
                btn.disabled = false;
                btn.textContent = 'Add Worker';
                return false;
            }

            showToast(data.message || 'Worker added successfully.', 'success');
            closeModal('addModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Add Worker';
        }

        return false;
    }

    // ── Profile Data Store (fetched from the current page state) ──
    const currentProfile = {
        phone: '{{ addslashes($selectedWorker->number) }}',
        address: '{{ addslashes($selectedWorker->address) }}',
        birthday: '{{ addslashes($selectedWorker->birthday) }}',
        senior_high: '{{ addslashes($selectedWorker->senior_high) }}',
        college: '{{ addslashes($selectedWorker->college) }}',
        partner_contact: '{{ addslashes($selectedWorker->partner_contact) }}',
        mother_contact: '{{ addslashes($selectedWorker->mother_contact) }}',
        skills: @json($selectedWorker->skills ?? []),
        notes: '{{ addslashes($selectedWorker->note) }}',
    };

    // ── Open Profile Edit Modal ──
    function openProfileModal(workerId, profileId) {
        document.getElementById('profileWorkerId').value = workerId;
        document.getElementById('pfPhone').value = currentProfile.phone !== '—' ? currentProfile.phone : '';
        document.getElementById('pfAddress').value = currentProfile.address !== '—' ? currentProfile.address : '';
        document.getElementById('pfBirthday').value = currentProfile.birthday !== '—' ? currentProfile.birthday : '';
        document.getElementById('pfSeniorHigh').value = currentProfile.senior_high !== '—' ? currentProfile.senior_high : '';
        document.getElementById('pfCollege').value = currentProfile.college !== '—' ? currentProfile.college : '';
        document.getElementById('pfPartnerContact').value = currentProfile.partner_contact !== '—' ? currentProfile.partner_contact : '';
        document.getElementById('pfMotherContact').value = currentProfile.mother_contact !== '—' ? currentProfile.mother_contact : '';
        document.getElementById('pfSkills').value = Array.isArray(currentProfile.skills) ? currentProfile.skills.join(', ') : '';
        document.getElementById('pfNotes').value = currentProfile.notes !== 'No notes on file.' ? currentProfile.notes : '';
        openModal('profileModal');
    }

    // ── Profile Form Submission ──
    async function submitProfile(event) {
        event.preventDefault();
        const btn = document.getElementById('profileSubmitBtn');
        const workerId = document.getElementById('profileWorkerId').value;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        try {
            // Build form data with skills as array
            const formData = new FormData();
            formData.append('phone', document.getElementById('pfPhone').value);
            formData.append('address', document.getElementById('pfAddress').value);
            formData.append('birthday', document.getElementById('pfBirthday').value);
            formData.append('senior_high', document.getElementById('pfSeniorHigh').value);
            formData.append('college', document.getElementById('pfCollege').value);
            formData.append('partner_contact', document.getElementById('pfPartnerContact').value);
            formData.append('mother_contact', document.getElementById('pfMotherContact').value);
            formData.append('notes', document.getElementById('pfNotes').value);

            // Split skills by comma
            const skillsRaw = document.getElementById('pfSkills').value;
            const skillsArr = skillsRaw.split(',').map(s => s.trim()).filter(s => s.length > 0);
            skillsArr.forEach((skill, i) => formData.append('skills[' + i + ']', skill));

            formData.append('_method', 'PUT');

            const response = await fetch('{{ url('/business/workers') }}/' + workerId + '/profile', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                showToast(data.message || 'Failed to save profile.', 'error');
                btn.disabled = false;
                btn.textContent = 'Save Profile';
                return;
            }

            showToast('Profile saved successfully.', 'success');
            closeModal('profileModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Save Profile';
        }

        return false;
    }

    // ── Open Edit Modal ──
    function openEditModal(workerId) {
        const worker = workersData.find(w => w.id === workerId);
        if (!worker) return;

        document.getElementById('editId').value = worker.id;
        document.getElementById('editName').value = worker.name;
        document.getElementById('editEmail').value = worker.email || '';
        document.getElementById('editBranch').value = worker.branch_id;
        document.getElementById('editRole').value = worker.role;

        openModal('editModal');
    }

    // ── Edit Form Submission ──
    async function submitEdit(event) {
        event.preventDefault();
        const form = document.getElementById('editForm');
        const btn = document.getElementById('editSubmitBtn');
        const workerId = document.getElementById('editId').value;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        try {
            const formData = new FormData(form); // @method('PUT') hidden input already included
            const response = await fetch('{{ url('/business/workers') }}/' + workerId, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    for (const [field, messages] of Object.entries(data.errors)) {
                        const errorEl = document.getElementById('editError' + field.charAt(0).toUpperCase() + field.slice(1));
                        const inputEl = form.querySelector('[name="' + field + '"]');
                        if (errorEl) {
                            errorEl.textContent = messages[0];
                            errorEl.classList.add('is-visible');
                        }
                        if (inputEl) inputEl.classList.add('error');
                    }
                } else {
                    showToast(data.message || 'An error occurred.', 'error');
                }
                btn.disabled = false;
                btn.textContent = 'Save Changes';
                return false;
            }

            showToast(data.message || 'Worker updated successfully.', 'success');
            closeModal('editModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Save Changes';
        }

        return false;
    }

    // ── Open Delete Modal ──
    function openDeleteModal(workerId, workerName) {
        document.getElementById('deleteName').textContent = workerName;
        const confirmBtn = document.getElementById('deleteConfirmBtn');

        // Remove old handler and attach new one
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);

        newBtn.addEventListener('click', async function() {
            newBtn.disabled = true;
            newBtn.textContent = 'Deleting…';

            try {
                const response = await fetch('{{ url('/business/workers') }}/' + workerId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    showToast(data.message || 'An error occurred.', 'error');
                    newBtn.disabled = false;
                    newBtn.textContent = 'Delete';
                    return;
                }

                showToast(data.message || 'Worker deleted successfully.', 'success');
                closeModal('deleteModal');
                setTimeout(() => location.reload(), 800);
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
                newBtn.disabled = false;
                newBtn.textContent = 'Delete';
            }
        });

        openModal('deleteModal');
    }

    // ── Schedule Editor ──
    function openScheduleModal(workerId) {
        document.getElementById('schedWorkerId').value = workerId;
        openModal('scheduleModal');
    }

    async function submitSchedule(event) {
        event.preventDefault();
        const workerId = document.getElementById('schedWorkerId').value;
        const btn = document.getElementById('schedSubmitBtn');
        btn.disabled = true; btn.textContent = 'Saving…';

        const schedule = {};
        document.querySelectorAll('.sched-input').forEach(inp => {
            schedule[inp.getAttribute('data-day')] = inp.value.trim();
        });

        try {
            const res = await fetch('{{ url("/business/workers") }}/' + workerId + '/profile', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PUT' },
                body: JSON.stringify({ work_schedule: schedule }),
            });
            const data = await res.json();
            if (!res.ok) { throw new Error(data.message || 'Save failed'); }
            showToast('Schedule saved! Refreshing…', 'success');
            closeModal('scheduleModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast(err.message, 'error');
            btn.disabled = false; btn.textContent = 'Save Schedule';
        }
    }

    // ── Performance Recording ──
    function openPerfModal(workerId) {
        document.getElementById('perfWorkerId').value = workerId;
        openModal('perfModal');
    }

    async function submitPerformance(event) {
        event.preventDefault();
        const workerId = document.getElementById('perfWorkerId').value;
        const btn = document.getElementById('perfSubmitBtn');
        btn.disabled = true; btn.textContent = 'Saving…';

        const rating = parseFloat(document.getElementById('perfRating').value);
        const metricsRaw = document.getElementById('perfMetrics').value;
        const metrics = metricsRaw.split('\n').map(s => s.trim()).filter(s => s.length > 0);
        const notes = document.getElementById('perfNotes').value.trim();

        const payload = { rating, performance_metrics: metrics };
        if (notes) payload.notes = notes;

        try {
            const res = await fetch('{{ url("/business/workers") }}/' + workerId + '/profile', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PUT' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok) { throw new Error(data.message || 'Save failed'); }
            showToast('Performance saved! Refreshing…', 'success');
            closeModal('perfModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast(err.message, 'error');
            btn.disabled = false; btn.textContent = 'Save Performance';
        }
    }

    // ── Peer Reviews ──
    function openReviewModal(workerId) {
        document.getElementById('reviewWorkerId').value = workerId;
        document.getElementById('reviewRating').value = 0;
        document.getElementById('reviewRatingDisplay').textContent = '0';
        document.getElementById('reviewComment').value = '';
        openModal('reviewModal');
    }

    async function submitReview(event) {
        event.preventDefault();
        const workerId = document.getElementById('reviewWorkerId').value;
        const btn = document.getElementById('reviewSubmitBtn');
        btn.disabled = true; btn.textContent = 'Saving…';

        const rating = parseFloat(document.getElementById('reviewRating').value);
        const comment = document.getElementById('reviewComment').value.trim();

        try {
            const res = await fetch('{{ url("/business/workers") }}/' + workerId + '/peer-reviews', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ comment, rating: rating > 0 ? rating : null }),
            });
            const data = await res.json();
            if (!res.ok) {
                const errorEl = document.getElementById('reviewErrorComment');
                if (data.errors?.comment && errorEl) {
                    errorEl.textContent = data.errors.comment[0];
                    errorEl.classList.add('is-visible');
                    btn.disabled = false; btn.textContent = 'Add Review';
                    return false;
                }
                throw new Error(data.message || 'Failed to add review');
            }
            showToast('Review added!', 'success');
            closeModal('reviewModal');
            setTimeout(() => location.reload(), 600);
        } catch (err) {
            showToast(err.message, 'error');
            btn.disabled = false; btn.textContent = 'Add Review';
        }
        return false;
    }

    async function deletePeerReview(reviewId) {
        if (!confirm('Delete this review?')) return;
        try {
            const res = await fetch('{{ url("/business/workers/peer-reviews") }}/' + reviewId, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-HTTP-Method-Override': 'DELETE' },
            });
            if (!res.ok) { const data = await res.json(); throw new Error(data.message || 'Delete failed'); }
            document.querySelector('[data-review-id="' + reviewId + '"]')?.remove();
            showToast('Review deleted.', 'success');
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    // ── Goals ──
    function openGoalModal(workerId) {
        document.getElementById('goalWorkerId').value = workerId;
        document.getElementById('goalTitle').value = '';
        document.getElementById('goalTargetDate').value = '';
        openModal('goalModal');
    }

    async function submitGoal(event) {
        event.preventDefault();
        const workerId = document.getElementById('goalWorkerId').value;
        const btn = document.getElementById('goalSubmitBtn');
        btn.disabled = true; btn.textContent = 'Saving…';

        const title = document.getElementById('goalTitle').value.trim();
        const targetDate = document.getElementById('goalTargetDate').value;

        try {
            const res = await fetch('{{ url("/business/workers") }}/' + workerId + '/goals', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ title, target_date: targetDate || null }),
            });
            const data = await res.json();
            if (!res.ok) {
                const errorEl = document.getElementById('goalErrorTitle');
                if (data.errors?.title && errorEl) {
                    errorEl.textContent = data.errors.title[0];
                    errorEl.classList.add('is-visible');
                    btn.disabled = false; btn.textContent = 'Add Goal';
                    return false;
                }
                throw new Error(data.message || 'Failed to add goal');
            }
            showToast('Goal added!', 'success');
            closeModal('goalModal');
            setTimeout(() => location.reload(), 600);
        } catch (err) {
            showToast(err.message, 'error');
            btn.disabled = false; btn.textContent = 'Add Goal';
        }
        return false;
    }

    async function toggleGoal(goalId, completed) {
        try {
            const res = await fetch('{{ url("/business/workers/goals") }}/' + goalId + '/status', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PUT' },
                body: JSON.stringify({ status: completed ? 'completed' : 'pending' }),
            });
            if (!res.ok) { const data = await res.json(); throw new Error(data.message || 'Update failed'); }
            const row = document.querySelector('[data-goal-id="' + goalId + '"]');
            const label = row?.querySelector('span.flex-1');
            if (label) label.classList.toggle('line-through', completed);
            if (label) label.classList.toggle('opacity-50', completed);
            if (row) {
                row.classList.toggle('bg-green-600/[.04]', completed);
                row.classList.toggle('border-green-600/[.12]', completed);
                row.classList.toggle('bg-[rgba(92,45,27,.03)]', !completed);
                row.classList.toggle('border-[rgba(92,45,27,.08)]', !completed);
            }
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    async function deleteGoal(goalId) {
        if (!confirm('Delete this goal?')) return;
        try {
            const res = await fetch('{{ url("/business/workers/goals") }}/' + goalId, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-HTTP-Method-Override': 'DELETE' },
            });
            if (!res.ok) { const data = await res.json(); throw new Error(data.message || 'Delete failed'); }
            document.querySelector('[data-goal-id="' + goalId + '"]')?.remove();
            showToast('Goal deleted.', 'success');
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    // ── Activity History Tabs ──
    let currentActivityWorker = {{ $selectedWorker->id }};
    let currentActivityType = 'transactions';

    function switchActivity(workerId, type) {
        currentActivityWorker = workerId;
        currentActivityType = type;

        document.querySelectorAll('[id^="actTab"]').forEach(t => t.classList.remove('active'));
        const tabId = 'actTab' + type.charAt(0).toUpperCase() + type.slice(1);
        const tabEl = document.getElementById(tabId);
        if (tabEl) tabEl.classList.add('active');

        const thead = document.querySelector('#activityTable thead tr');
        if (type === 'shifts') {
            thead.innerHTML = '<th>Date</th><th>Clock In</th><th>Clock Out</th><th>Duration</th><th>Status</th>';
        } else if (type === 'discrepancies') {
            thead.innerHTML = '<th>Date</th><th>Ingredient</th><th>Expected</th><th>Actual</th><th>Variance</th>';
        } else {
            thead.innerHTML = '<th>Date</th><th>Item / Shift</th><th>Detail</th><th>Amount</th><th>Status</th>';
        }
        loadActivity(workerId, type);
    }

    async function loadActivity(workerId, type) {
        const tbody = document.getElementById('activityTableBody');
        tbody.innerHTML = Array.from({length: 3}).map(() => '<tr><td colspan="5" class="py-2.5 px-5"><div class="skeleton skeleton--text" style="margin-bottom:0"></div></td></tr>').join('');
        try {
            const res = await fetch('{{ url("/business/workers") }}/' + workerId + '/activity?type=' + type, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            const data = json.data || [];
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;opacity:.3;padding:20px;">No records found.</td></tr>';
                return;
            }
            if (type === 'shifts') {
                tbody.innerHTML = data.map(s => `<tr><td>${s.date}</td><td>${s.clock_in}</td><td>${s.clock_out}</td><td>${s.duration}</td><td><span class="badge ${s.status === 'open' ? 'badge-open' : 'badge-closed'}">${s.status}</span></td></tr>`).join('');
            } else if (type === 'discrepancies') {
                tbody.innerHTML = data.map(d => `<tr><td>${d.date}</td><td>${d.ingredient}</td><td>${d.expected}</td><td>${d.actual}</td><td style="color:${d.severity === 'severe' ? '#dc2626' : d.severity === 'moderate' ? '#d97706' : '#16a34a'};font-weight:700;">${d.variance > 0 ? '+' : ''}${d.variance}</td></tr>`).join('');
            } else {
                tbody.innerHTML = data.map(t => `<tr><td>${t.date}</td><td>${t.product}</td><td>Qty: ${t.quantity}</td><td>₱${parseFloat(t.total).toFixed(2)}</td><td>${t.sync_status || 'synced'}</td></tr>`).join('');
            }
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#dc2626;padding:20px;">Failed to load data.</td></tr>';
        }
    }

    // ── Auto-load activity on page load ──
    document.addEventListener('DOMContentLoaded', function () {
        if ({{ $selectedWorker->id }} > 0) {
            loadActivity({{ $selectedWorker->id }}, 'transactions');
        }
    });
</script>

@endsection
